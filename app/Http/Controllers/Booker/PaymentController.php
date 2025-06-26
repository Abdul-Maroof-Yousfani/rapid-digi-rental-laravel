<?php

namespace App\Http\Controllers\Booker;

use Carbon\Carbon;
use App\Models\Bank;
use App\Models\Booking;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PaymentData;
use Illuminate\Http\Request;
use App\Models\PaymentMethod;
use App\Services\ZohoInvoice;
use App\Models\DepositHandling;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\BookingPaymentHistory;
use Illuminate\Support\Facades\Validator;

class PaymentController extends Controller
{
    protected $zohoinvoice;
    public function __construct(ZohoInvoice $zohoinvoice) {
        $this->zohoinvoice = $zohoinvoice;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('booker.payment.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $bookings= Booking::with('payment')->orderBy('id', 'DESC')->get();
        $bookingsPartial = Booking::with('payment')
            ->whereDoesntHave('payment')
            ->orderBy('id', 'DESC')
            ->get();
        $paymentMethod= PaymentMethod::all();
        $bank= Bank::all();
        return view('booker.payment.create', compact(
            'paymentMethod',
            'bookings',
            'bookingsPartial',
            'bank'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $rules= [
            'booking_id' => 'required',
            'payment_method' => 'required',
            'booking_amount' => 'required',
            'amount_receive' => 'required',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'invoice_id.*' => 'required',
            'invPaidAmount.*' => 'required',
        ];

        if($request['payment_method']==3){ $rules['bank_id'] = 'required'; }
        $validator= Validator::make($request->all(), $rules);
        if($validator->fails()){
            $errormessage= implode('\n', $validator->errors()->all());
            return redirect()->back()->with('error', $errormessage)->withInput();
        } else {
            try {
                DB::beginTransaction();
                $imagePath = null;
                if ($request->hasFile('image')) {
                    $image = $request->file('image');
                    $imageName = time() . '.' . $image->getClientOriginalExtension();
                    $image->move(public_path('assets/images'), $imageName);
                    $imagePath= 'assets/images/' . $imageName;
                }
                $pendingAmount= $request['booking_amount'] - $request['amount_receive'];
                $paymentStatus= $pendingAmount==0 ? 'paid' : 'pending';

                $beforeUpdateAmount= 0;
                $beforeUpdateDate;
                if($request->payment_id){
                    $payment= Payment::find($request->payment_id);
                    $beforeUpdateAmount = $payment->paid_amount ?? 0;
                    $beforeUpdateDate = $payment->created_at;
                    $payment->update([
                        'booking_id' => $request['booking_id'],
                        'payment_method' => $request['payment_method'],
                        'bank_id' => $request['bank_id'] ?? null,
                        'booking_amount' => $request['booking_amount'],
                        'paid_amount' => $request['amount_receive'],
                        'pending_amount' => $pendingAmount,
                        'payment_status' => $paymentStatus,
                        'receipt' => $imagePath,
                    ]);
                } else {
                    $payment= Payment::create([
                        'booking_id' => $request['booking_id'],
                        'payment_method' => $request['payment_method'],
                        'bank_id' => $request['bank_id'] ?? null,
                        'booking_amount' => $request['booking_amount'],
                        'paid_amount' => $request['amount_receive'],
                        'pending_amount' => $pendingAmount,
                        'payment_status' => $paymentStatus,
                        'receipt' => $imagePath,
                    ]);
                }

                BookingPaymentHistory::create([
                    'booking_id' => $request['booking_id'],
                    'payment_id' => $payment->id,
                    'payment_method_id' => $request['payment_method'],
                    'paid_amount' => $request['amount_receive'] - $beforeUpdateAmount,
                    'payment_date' => $beforeUpdateDate,
                    'user_id' => Auth::user()->id,
                ]);

                $paymentDataMap = [];
                foreach ($request['invoice_id'] as $key => $invoice_ids) {
                    $invoiceAmount= $request['invoice_amount'][$key];
                    $invPaidAmount= $request['invPaidAmount'][$key];
                    $pendingAmount= $invoiceAmount - $invPaidAmount;
                    $status= $invoiceAmount == $invPaidAmount ? 'paid' : 'pending';
                    $paymentDataID = $request->paymentData_id[$key];
                    $paymentdata = $paymentDataID ? PaymentData::find($paymentDataID) : null;
                    if($paymentdata){
                        $paymentdata->update([
                            'invoice_id' => $invoice_ids,
                            'payment_id' => $payment->id,
                            'status' => $status,
                            'invoice_amount' => $invoiceAmount,
                            'paid_amount' => $invPaidAmount,
                            'pending_amount' => $pendingAmount,
                        ]);
                    } else {
                        $paymentdata= PaymentData::create([
                            'invoice_id' => $invoice_ids,
                            'payment_id' => $payment->id,
                            'status' => $status,
                            'invoice_amount' => $invoiceAmount,
                            'paid_amount' => $invPaidAmount,
                            'pending_amount' => $pendingAmount,
                        ]);
                    }
                    $paymentDataMap[$key] = $paymentdata->id;
                }

                foreach ($request['addDepositAmount'] as $index => $deductAmount) {
                    if (floatval($deductAmount) > 0) {
                        DepositHandling::create([
                            'payment_data_id' => $paymentDataMap[$index],
                            'booking_id' => $request['booking_id'],
                            'deduct_deposit' => $deductAmount,
                        ]);
                    }
                }

                $paymentDataList = PaymentData::with('invoice')
                    ->where('payment_id', $payment->id)
                    ->where('status', 'paid')
                    ->get();

                foreach ($paymentDataList as $key => $paymentData) {
                    if ($paymentData->invoice) {
                        $invoiceID= $paymentData->invoice->zoho_invoice_id;
                        $this->zohoinvoice->markAsSent($invoiceID);
                        $invoice = Invoice::find($paymentData->invoice->id);
                        $invoice->update([
                            'invoice_status' => 'sent'
                        ]);
                    } else {
                        return redirect()->route('booker.payment.index')->with('success', 'Record inserted But Not Send Because Invoice ID Not Found');
                    }
                }
                DB::commit();
                return redirect()->route('booker.payment.index')->with('success', 'Payment Create Successfully!');
            } catch (\Exception $exp) {
                DB::rollback();
                return redirect()->back()->withErrors('error', $exp->getMessage())->withInput();
            }
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    // Clear Pending Payment
    public function pendingPayment($id, Request $request)
    {
        // dd($request->all());
        $payment= Payment::find($request->payment_id);
        $beforeUpdateAmount=$payment->paid_amount ?? 0;
        if(!$payment){
                $imagePath = null;
                if ($request->hasFile('image')) {
                    $image = $request->file('image');
                    $imageName = time() . '.' . $image->getClientOriginalExtension();
                    $image->move(public_path('assets/images'), $imageName);
                    $imagePath= 'assets/images/' . $imageName;
                }
                $pendingAmount= $request['booking_amount'] - $request['amount_receive'];
                $paymentStatus= $pendingAmount==0 ? 'paid' : 'pending';
                $payment= Payment::create([
                    'booking_id' => $request['booking_id'],
                    'payment_method' => $request['payment_method'],
                    'bank_id' => $request['bank_id'] ?? null,
                    'booking_amount' => $request['booking_amount'],
                    'paid_amount' => $request['amount_receive'],
                    'pending_amount' => $pendingAmount,
                    'payment_status' => $paymentStatus,
                    'receipt' => $imagePath,
                ]);

                BookingPaymentHistory::create([
                    'booking_id' => $request['booking_id'],
                    'payment_id' => $payment->id,
                    'payment_method_id' => $request['payment_method'],
                    'paid_amount' => $beforeUpdateAmount,
                ]);

                $paymentDataMap = [];
                foreach ($request['invoice_id'] as $key => $invoice_ids) {
                    $invoiceAmount= $request['invoice_amount'][$key];
                    $invPaidAmount= $request['invPaidAmount'][$key];
                    $pendingAmount= $invoiceAmount - $invPaidAmount;
                    $status= $invoiceAmount == $invPaidAmount ? 'paid' : 'pending';
                    $paymentdata= PaymentData::create([
                        'invoice_id' => $invoice_ids,
                        'payment_id' => $payment->id,
                        'status' => $status,
                        'invoice_amount' => $invoiceAmount,
                        'paid_amount' => $invPaidAmount,
                        'pending_amount' => $pendingAmount,
                    ]);
                    $paymentDataMap[$key] = $paymentdata->id;
                }

                foreach ($request['addDepositAmount'] as $index => $deductAmount) {
                    if (floatval($deductAmount) > 0) {
                        DepositHandling::create([
                            'payment_data_id' => $paymentDataMap[$index],
                            'booking_id' => $request['booking_id'],
                            'deduct_deposit' => $deductAmount,
                        ]);
                    }
                }
        } else {
            $payment->update([
                'pending_amount' => $request['pending_amount'],
                'paid_amount' => $request['amount_receive'],
                'payment_status' => $request['pending_amount']==0 ? 'paid':'pending'
            ]);

            BookingPaymentHistory::create([
                'booking_id' => $request['booking_id'],
                'payment_id' => $payment->id,
                'payment_method_id' => $request['payment_method'],
                'paid_amount' => ($request['amount_receive'] - $beforeUpdateAmount),
            ]);

            foreach ($request['paymentData_id'] as $key => $paymentDataID) {
                $paymentData= PaymentData::find($paymentDataID);
                $invoiceAmount= $request['invoice_amount'][$key];
                $invPaidAmount= $request['invPaidAmount'][$key];
                $pendingAmount= $invoiceAmount-$invPaidAmount;
                $paymentStatus= $pendingAmount==0 ? 'paid' : 'pending';
                $paymentData->update([
                    'paid_amount' => $invPaidAmount,
                    'pending_amount' => $pendingAmount,
                    'status' => $paymentStatus,
                ]);
            }
        }

        $paymentDataList = PaymentData::with('invoice')
                    ->where('payment_id', $payment->id)
                    ->where('status', 'paid')
                    ->get();

        foreach ($paymentDataList as $key => $paymentData) {
            if ($paymentData->invoice) {
                $invoiceID= $paymentData->invoice->zoho_invoice_id;
                $this->zohoinvoice->markAsSent($invoiceID);
                $invoice = Invoice::find($paymentData->invoice->id);
                $invoice->update([
                    'invoice_status' => 'sent'
                ]);
            } else {
                return redirect()->route('booker.payment.index')->with('success', 'Record inserted But Not Send Because Invoice ID Not Found');
            }
        }

        return redirect()->route('booker.payment.index')->with('success', 'Payment Create Successfully!');
    }

    public function paymentHistory($paymentID)
    {
        $paymentHistory= BookingPaymentHistory::with('payment', 'paymentMethod')->where('payment_id', $paymentID)->get();
        return view('booker.payment.view-payment-history', compact('paymentHistory'));
    }
}

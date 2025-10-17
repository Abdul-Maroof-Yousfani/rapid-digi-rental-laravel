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
use App\Models\Deposit;
use Illuminate\Support\Facades\Validator;

class PaymentController extends Controller
{
    protected $zohoinvoice;
    public function __construct(ZohoInvoice $zohoinvoice)
    {
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
        // $bookings = Booking::whereHas('invoice', function ($query) {
        //     $query->where('invoice_status', '!=', 'paid');
        // })
        $bookings = Booking::whereDoesntHave('payment', function ($query) {
            $query->where('payment_status', 'paid');
        })
            ->with(['invoice', 'payment', 'customer'])
            ->orderBy('id', 'DESC')
            ->get();

        $bookingsPartial = Booking::with('payment', 'customer')
            ->whereDoesntHave('payment')
            ->orderBy('id', 'DESC')
            ->get();
        $paymentMethod = PaymentMethod::all();
        $bank = Bank::all();
        return view('booker.payment.create', compact(
            'paymentMethod',
            'bookings',
            'bookingsPartial',
            'bank'
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // return $request->all();
        $rules = [
            'booking_id' => 'required',
            'payment_method' => 'required',
            'booking_amount' => 'required',
            // 'amount_receive' => 'required',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'invoice_id.*' => 'required',
            'reference_invoice_number.*' => 'nullable',
            'invPaidAmount.*' => 'required',
            'remarks.*' => 'nullable|string|max:255',
            'adjust_invoice' => 'nullable|in:0,1',
            'used_deposit_amount' => 'nullable|in:0,1',
        ];

        if ($request['adjust_invoice'] == 1) {
            $rules['reference_invoice_number.*'] = 'required|exists:payment_data,invoice_id';
        }

        if ($request['payment_method'] == 3) {
            $rules['bank_id'] = 'required';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            $errormessage = implode('\n', $validator->errors()->all());
            return redirect()->back()->with('error', $errormessage)->withInput();
        }

        try {
            DB::beginTransaction();

            $imagePath = null;
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = time() . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('assets/images'), $imageName);
                $imagePath = 'assets/images/' . $imageName;
            }

            $bookingAmount = floatval($request->input('booking_amount', 0));
            $amountReceive = floatval($request->input('amount_receive', 0));
            $initialDeposit = floatval($request->input('initial_deposit', 0));
            $usedDepositFlag = (int)$request->input('used_deposit_amount') === 1;

            $depositUsed = 0;
            if ($usedDepositFlag) {
                $depositUsed = array_sum($request->addDepositAmount ?? []);
            }
            // Calculate paid amount as cash + deposit used
            $paidAmount = $amountReceive;

            // Calculate pending amount
            $pendingAmount = $bookingAmount - $paidAmount;

            $paymentStatus = $pendingAmount <= 0 ? 'paid' : 'pending';
            $bookingStatus = $pendingAmount <= 0 ? 'sent' : 'partially paid';
            if ($request['adjust_invoice'] == 1) {
                $booking = Booking::find($request->booking_id);

                if ($booking && $booking->deposit) {
                    $booking->deposit->update([
                        'deposit_amount' => 0,
                    ]);
                }

                DepositHandling::where('booking_id', $booking->id)->update([
                    'deduct_deposit' => 0,
                ]);
            }

            $beforeUpdateAmount = 0;
            $beforeUpdateDate = Carbon::now();
            if ($request->payment_id) {
                // return $paymentStatus;

                $payment = Payment::find($request->payment_id);

                // $tyt=($payment->pending_amount - $amountReceive - $depositUsed);
                // dd($tyt);   
                $beforeUpdateAmount = $payment->paid_amount ?? 0;
                $beforeUpdateDate = $payment->created_at;
                $payment->update([
                    'booking_id' => $request['booking_id'],
                    'payment_method' => $request['payment_method'],
                    'bank_id' => $request['bank_id'] ?? null,
                    'booking_amount' => $bookingAmount,
                    'paid_amount' => $payment->paid_amount + $paidAmount + $depositUsed,
                    'pending_amount' => $payment->pending_amount - $amountReceive - $depositUsed,
                    // 'payment_status' => $paymentStatus,
                    'receipt' => $imagePath,
                ]);
            } else {
                // return "kk";

                $payment = Payment::create([
                    'booking_id' => $request['booking_id'],
                    'payment_method' => $request['payment_method'],
                    'bank_id' => $request['bank_id'] ?? null,
                    'booking_amount' => $bookingAmount,
                    'paid_amount' => $paidAmount,
                    'pending_amount' => $bookingAmount - $paidAmount,
                    // 'payment_status' => $paymentStatus,
                    'receipt' => $imagePath,
                ]);
            }

            if ($payment->paid_amount == $payment->booking_amount) {
                $payment->update([
                    'payment_status' => 'paid',
                ]);
            } else {
                $payment->update([
                    'payment_status' => 'pending',
                ]);
            }
            // return $payment->pending_amount;

            BookingPaymentHistory::create([
                'booking_id' => $request['booking_id'],
                'payment_id' => $payment->id,
                'invoice_id' => $request['invoice_id'],
                'payment_method_id' => $request['payment_method'],
                'paid_amount' => $paidAmount + $depositUsed,
                'payment_date' => $beforeUpdateDate,
                'user_id' => Auth::user()->id,
            ]);

            $paymentDataMap = [];
            $pendingAmounts = [];

            $remainingPayment = $paidAmount; // total payment user made
            $incrementalPayments = [];
            try {
                foreach ($request['invoice_id'] as $key => $invoice_ids) {
                    $invoiceAmount = floatval($request['invoice_amount'][$key]);
                    $paymentDataID = $request->paymentData_id[$key] ?? null;

                    $paymentdata = $paymentDataID ? PaymentData::find($paymentDataID) : null;

                    $currentPaid = $paymentdata ? $paymentdata->paid_amount : 0;

                    // Amount still pending for this invoice
                    $pendingBefore = $invoiceAmount - $currentPaid;

                    // Decide how much we can pay towards this invoice from remaining payment
                    $payThisTime = min($pendingBefore, $remainingPayment);

                    // Calculate new totals
                    $newPaidAmount = $currentPaid + $payThisTime;
                    $newPending = $invoiceAmount - $newPaidAmount;
                    $newStatus1 = ($newPending <= 0) ? 'paid' : 'pending';
                    $newStatus = ($newPending <= 0) ? 'paid' : 'partially paid';

                    if ($paymentdata) {
                        $paymentdata->update([
                            'invoice_id' => $invoice_ids,
                            'payment_id' => $payment->id,
                            'invoice_amount' => $invoiceAmount,
                            'paid_amount' => $newPaidAmount + $depositUsed,
                            'status' => $newStatus1,
                            'pending_amount' => $newPending - $depositUsed,
                            'reference_invoice_number' => $request['reference_invoice_number'][$key] ?? null,
                            'remarks' => $request['remarks'][$key] ?? null,
                        ]);
                    } else {
                        $paymentdata = PaymentData::create([
                            'invoice_id' => $invoice_ids,
                            'payment_id' => $payment->id,
                            'status' => $newStatus1,
                            'invoice_amount' => $invoiceAmount,
                            'paid_amount' => $payThisTime,
                            'pending_amount' => $newPending,
                            'reference_invoice_number' => $request['reference_invoice_number'][$key] ?? null,
                            'remarks' => $request['remarks'][$key] ?? null,
                        ]);
                    }
                    // Update invoice status
                    if ($paymentdata->invoice) {
                        $paymentdata->invoice->update([
                            'invoice_status' => $newStatus,
                        ]);
                    }


                    // Deduct from remaining payment
                    $remainingPayment -= $payThisTime;

                    $paymentDataMap[$key] = $paymentdata->id;
                    $pendingAmounts[] = $remainingPayment;
                    $incrementalPayments[$key] = $payThisTime;
                }
                DB::commit();
            } catch (\Throwable $e) {
                DB::rollBack();

                return response()->json([
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ], 500);
            }



            if ($depositUsed > 0) {
                $booking = Booking::find($request->booking_id);
                $deposit = $booking->deposit()->lockForUpdate()->first();

                if ($deposit) {
                    $newDepositAmount = max(0, $deposit->deposit_amount - $depositUsed);
                    $deposit->update(['deposit_amount' => $newDepositAmount]);

                    foreach ($paymentDataMap as $key => $paymentDataId) {
                        DepositHandling::create([
                            'payment_data_id' => $paymentDataId,
                            'booking_id'      => $booking->id,
                            'deduct_deposit'  => $pendingAmounts[$key], // match index with payment
                        ]);
                    }
                }
            }

            $paymentDataList = PaymentData::with('invoice')
                ->where('payment_id', $payment->id)
                ->get();
            try {
                foreach ($paymentDataList as $paymentData) {
                    if ($paymentData->invoice) {
                        $newStatus = $paymentData->pending_amount <= 0 ? 'paid' : 'partially paid';
                        $paymentData->invoice->update([
                            'invoice_status' => $newStatus,
                        ]);
                    }
                }
                DB::commit();
            } catch (\Throwable $e) {
                DB::rollBack();

                return redirect()->back()->with('error', $e->getMessage());
            }

            // return $paymentDataList;
            foreach ($paymentDataList as $key => $paymentData) {
                if ($paymentData->invoice) {
                    $this->zohoinvoice->markAsSent($paymentData->invoice->zoho_invoice_id);

                    $amountToPay = $incrementalPayments[$key] ?? 0;
                    $customerId  = $paymentData->invoice->booking->customer->zoho_customer_id;
// dd($amountToPay);
                    // ✅ only send if valid
                    if ($amountToPay > 0 && !empty($customerId)) {
                        $this->zohoinvoice->recordPayment(
                            $customerId,
                            $paymentData->invoice->zoho_invoice_id,
                            $amountToPay,
                            now()->format('Y-m-d')
                        );
                    }
                }
            }


            DB::commit();

            return redirect()->route('payment.index')->with('success', 'Payment created successfully!');
        } catch (\Exception $exp) {
            DB::rollback();
            // return redirect()->back()->with('error', $exp->getMessage());
            return response($exp->getMessage(), 500);
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
        $payment = Payment::find($request->payment_id);
        $beforeUpdateAmount = $payment->paid_amount ?? 0;
        if (!$payment) {
            $imagePath = null;
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = time() . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('assets/images'), $imageName);
                $imagePath = 'assets/images/' . $imageName;
            }
            $pendingAmount = $request['booking_amount'] - $request['amount_receive'];
            $paymentStatus = $pendingAmount == 0 ? 'paid' : 'pending';
            $payment = Payment::create([
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
                $invoiceAmount = $request['invoice_amount'][$key];
                $invPaidAmount = $request['invPaidAmount'][$key];
                $pendingAmount = $invoiceAmount - $invPaidAmount;
                $status = $invoiceAmount == $invPaidAmount ? 'paid' : 'pending';
                $paymentdata = PaymentData::create([
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
                'payment_status' => $request['pending_amount'] == 0 ? 'paid' : 'pending'
            ]);

            BookingPaymentHistory::create([
                'booking_id' => $request['booking_id'],
                'payment_id' => $payment->id,
                'payment_method_id' => $request['payment_method'],
                'paid_amount' => ($request['amount_receive'] - $beforeUpdateAmount),
            ]);

            foreach ($request['paymentData_id'] as $key => $paymentDataID) {
                $paymentData = PaymentData::find($paymentDataID);
                $invoiceAmount = $request['invoice_amount'][$key];
                $invPaidAmount = $request['invPaidAmount'][$key];
                $pendingAmount = $invoiceAmount - $invPaidAmount;
                $paymentStatus = $pendingAmount == 0 ? 'paid' : 'pending';
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
                $invoiceID = $paymentData->invoice->zoho_invoice_id;
                $this->zohoinvoice->markAsSent($invoiceID);
                $invoice = Invoice::find($paymentData->invoice->id);
                $invoice->update([
                    'invoice_status' => 'sent'
                ]);
            } else {
                return redirect()->route('payment.index')->with('success', 'Record inserted But Not Send Because Invoice ID Not Found');
            }
        }

        return redirect()->route('payment.index')->with('success', 'Payment Created Successfully!');
    }

    public function paymentHistory($paymentID)
    {
        $paymentHistory = BookingPaymentHistory::with('payment', 'paymentMethod')->where('payment_id', $paymentID)->get();
        return view('booker.payment.view-payment-history', compact('paymentHistory'));
    }
}

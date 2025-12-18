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
        $bookings = Booking::whereHas('invoice', function ($query) {
            // $query->where('invoice_status', '!=', 'paid');
        })->orderBy('id', 'DESC')
            ->get();
        // $bookings = Booking::whereDoesntHave('payment', function ($query) {
        //     $query->where('payment_status', 'paid');
        // })
        //     ->with(['invoice', 'payment', 'customer'])
        //     ->orderBy('id', 'DESC')
        //     ->get();

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
            'payment_date' => 'required',
            // 'amount_receive' => 'required',
            'image' => 'nullable|mimes:jpeg,png,jpg,gif,svg,pdf|max:5120',
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
            $usedDepositFlag = (int) $request->input('used_deposit_amount') === 1;

            $depositUsed = 0;
            if ($usedDepositFlag) {
                $depositUsed = array_sum($request->addDepositAmount ?? []);
            }
            // dd($depositUsed);
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
            $paymentDate = $request['payment_date']
                ? Carbon::parse($request['payment_date'])
                : now();

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
                    'payment_date' => $request['payment_date'],
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
                    'paid_amount' => $paidAmount + $depositUsed,
                    'pending_amount' => $bookingAmount - $paidAmount - $depositUsed,
                    'payment_date' => $request['payment_date'],
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
                'payment_date' => $request['payment_date'],
                'user_id' => Auth::user()->id,
            ]);

            $paymentDataMap = [];
            $pendingAmounts = [];

            $remainingPayment = $paidAmount; // total payment user made
            $incrementalPayments = [];
            
            // Get selected invoice IDs
            $selectedInvoiceIds = $request->input('selected_invoices', []);
            
            try {
                foreach ($request['invoice_id'] as $key => $invoice_ids) {
                    // Only process invoices that are checked/selected
                    if (!in_array($invoice_ids, $selectedInvoiceIds)) {
                        continue;
                    }
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
                            'paid_amount' => $payThisTime + $depositUsed,
                            'pending_amount' => $newPending - $depositUsed,
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
// dd($request->initial_deposit);


            if ($request['adjust_invoice'] == 1) {
                $booking = Booking::find($request->booking_id);
                $deposit = $booking->deposit()->lockForUpdate()->first();

                if ($deposit) {
                    $newDepositAmount = max(0, $deposit->deposit_amount - $request->initial_deposit);
                    $deposit->update(['deposit_amount' => $newDepositAmount]);

                    // If adjust_invoice is checked, transfer deposit to selected booking
                    $targetBookingId = $booking->id; // Default to current booking
                    if ($request['adjust_invoice'] == 1 && $request->reference_invoice_number) {
                        $targetBookingId = $request->reference_invoice_number; // Use selected booking
                        
                        // Transfer deposit to selected booking
                        $selectedBooking = Booking::find($targetBookingId);
                        if ($selectedBooking) {
                            $selectedDeposit = $selectedBooking->deposit;
                            
                            if ($selectedDeposit) {
                                // Add deposit to existing deposit
                                $selectedDeposit->update([
                                    'deposit_amount' => $selectedDeposit->deposit_amount + $request->initial_deposit,
                                    'initial_deposit' => $selectedDeposit->initial_deposit + $request->initial_deposit,
                                ]);
                            } else {
                                // Create new deposit for selected booking
                                $newDeposit = Deposit::create([
                                    'deposit_amount' => $request->initial_deposit,
                                    'initial_deposit' => $request->initial_deposit,
                                ]);
                                $selectedBooking->update(['deposit_id' => $newDeposit->id]);
                            }
                        }
                    }

                    foreach ($paymentDataMap as $key => $paymentDataId) {
                        DepositHandling::create([
                            'payment_data_id' => $paymentDataId,
                            'booking_id' => $targetBookingId, // Apply to selected booking if adjust_invoice is checked
                            'deduct_deposit' => $pendingAmounts[$key], // match index with payment
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
                    $customerId = $paymentData->invoice->booking->customer->zoho_customer_id;
                    // dd($amountToPay);
                    if ($amountToPay > 0 && !empty($customerId)) {
                        $this->zohoinvoice->recordPayment(
                            $customerId,
                            $paymentData->invoice->zoho_invoice_id,
                            $amountToPay,
                            $paymentDate->format('Y-m-d')
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
        try {
            DB::beginTransaction();
            
            $payment = Payment::find($id);
            
            if (!$payment) {
                return redirect()->back()->with('error', 'Payment Not Found!');
            }

            // Delete related PaymentData records
            PaymentData::where('payment_id', $payment->id)->delete();

            // Delete related BookingPaymentHistory records
            BookingPaymentHistory::where('payment_id', $payment->id)->delete();

            // Delete the payment
            $payment->delete();

            DB::commit();

            return redirect()->back()->with('success', 'Payment Deleted Successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error deleting payment: ' . $e->getMessage());
        }
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
            // Get selected invoice IDs
            $selectedInvoiceIds = $request->input('selected_invoices', []);
            
            foreach ($request['invoice_id'] as $key => $invoice_ids) {
                // Only process invoices that are checked/selected
                if (!empty($selectedInvoiceIds) && !in_array($invoice_ids, $selectedInvoiceIds)) {
                    continue;
                }
                
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

    /**
     * Delete individual PaymentData entry
     */
    public function destroyPaymentData($paymentDataId)
    {
        try {
            DB::beginTransaction();
            
            $paymentData = PaymentData::with('payment', 'invoice')->find($paymentDataId);
            
            if (!$paymentData) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment Data Not Found!'
                ], 404);
            }

            $payment = $paymentData->payment;
            $paidAmount = $paymentData->paid_amount ?? 0;
            $invoice = $paymentData->invoice; // Get invoice before deleting

            // Delete related DepositHandling if exists
            DepositHandling::where('payment_data_id', $paymentDataId)->delete();

            // Delete the PaymentData
            $paymentData->delete();

            // Recalculate payment totals
            $totalPaidAmount = PaymentData::where('payment_id', $payment->id)->sum('paid_amount');
            $pendingAmount = $payment->booking_amount - $totalPaidAmount;
            $paymentStatus = $pendingAmount <= 0 ? 'paid' : 'pending';

            // Update payment totals
            $payment->update([
                'paid_amount' => $totalPaidAmount,
                'pending_amount' => $pendingAmount,
                'payment_status' => $paymentStatus,
            ]);

            // Update invoice status if exists
            if ($invoice) {
                $invoicePaidAmount = PaymentData::where('invoice_id', $invoice->id)->sum('paid_amount');
                $invoiceTotalAmount = floatval($invoice->total_amount);
                $invoicePendingAmount = $invoiceTotalAmount - $invoicePaidAmount;
                
                // Determine invoice status
                if ($invoicePendingAmount <= 0) {
                    $invoiceStatus = 'paid';
                } elseif ($invoicePaidAmount > 0) {
                    $invoiceStatus = 'partially paid';
                } else {
                    $invoiceStatus = 'sent';
                }
                
                $invoice->update([
                    'invoice_status' => $invoiceStatus,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Payment Data Deleted Successfully!',
                'payment' => [
                    'paid_amount' => $totalPaidAmount,
                    'pending_amount' => $pendingAmount,
                    'payment_status' => $paymentStatus,
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error deleting payment data: ' . $e->getMessage()
            ], 500);
        }
    }
}

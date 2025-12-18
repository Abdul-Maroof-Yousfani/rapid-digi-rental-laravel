<?php

namespace App\Http\Controllers\Booker;

use App\Http\Controllers\Controller;
use App\Models\Bank;
use App\Models\Booking;
use App\Models\CreditNote;
use App\Models\DepositHandling;
use App\Models\Invoice;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Services\ZohoInvoice;
use Illuminate\Support\Facades\Log;

class CreditnoteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    protected $zohoinvoice;

    public function __construct(ZohoInvoice $zohoinvoice)
    {
        $this->zohoinvoice = $zohoinvoice;
    }


    public function index()
    {
        $creditNote = CreditNote::with('paymentMethod', 'booking')->paginate(10);
        return view('booker.creditnote.index', compact('creditNote'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Get all booking IDs which already have a credit note
        $creditNoteBookingIds = CreditNote::pluck('booking_id')->toArray();

        // Get bookings that have a deposit amount greater than zero
        // Removed payment condition - bookings with deposit should show regardless of payment status
        $bookings = Booking::whereHas('deposit', function ($query) {
            $query->where('deposit_amount', '>', 0);
        })
            ->with('deposit', 'customer', 'depositHandling', 'payment')
            ->orderBy('id', 'DESC')
            ->get();

        // Filter bookings:
        // - Must have remaining deposit (deposit_amount > 0)
        // - Must not already have a credit note created
        $filterBooking = $bookings->filter(function ($booking) use ($creditNoteBookingIds) {
            return $booking->deposit 
                && $booking->deposit->deposit_amount > 0
                && !in_array($booking->id, $creditNoteBookingIds);
        });

        $refundMethod = PaymentMethod::all();
        $bank = Bank::all();

        return view('booker.creditnote.create', compact('filterBooking', 'refundMethod', 'bank'));
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $rules = [
            'booking_id' => 'required',
            'refund_method' => 'required',
            'refund_amount' => 'required',
            'remaining_deposit' => 'required',
            'refund_date' => 'required',
        ];

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

            // Generate credit note number
            $lastCreditNote = CreditNote::orderBy('id', 'desc')->first();
            $nextNumber = $lastCreditNote ? $lastCreditNote->id + 1 : 1;
            $creditNoteNo = 'CN-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

            // Fetch booking and invoice details
            $booking = Booking::where('id', $request['booking_id'])->first();
            if (!$booking) {
                throw new \Exception('Booking not found.');
            }

            $invoice = Invoice::where('booking_id', $request['booking_id'])->first();
            if (!$invoice || !$invoice->zoho_invoice_id) {
                throw new \Exception('No associated Zoho invoice found for this booking.');
            }

            // Create local credit note
            $creditNote = CreditNote::create([
                'credit_note_no' => $creditNoteNo,
                'booking_id' => $request['booking_id'],
                'payment_method' => $request['refund_method'],
                'bank_id' => $request['bank_id'],
                'remaining_deposit' => $request['remaining_deposit'],
                'refund_amount' => $request['refund_amount'],
                'remarks' => $request['remarks'],
                'refund_date' => $request['refund_date'],
                'status' => 1,
            ]);

            // Prepare line items for Zoho credit note
            $lineItems = [
                [
                    'name' => 'Refund for Booking #' . $booking->id,
                    'description' => $request['remarks'] ?? 'Refund for booking',
                    'quantity' => 1,
                    'rate' => $request['refund_amount'],
                    'item_id' => null, // Optional: Fetch from Zoho if needed
                    'tax_id' => null, // Optional: Add if applicable
                ],
            ];

            // Create credit note in Zoho
            Log::info('Attempting to create Zoho credit note', [
                'booking_id' => $request['booking_id'],
                'credit_note_no' => $creditNoteNo,
                'customer_id' => $booking->customer_id,
                'invoice_id' => $invoice->zoho_invoice_id,
            ]);


            try {
                $zohoResponse = $this->zohoinvoice->createZohoCreditNote(
                    $booking->customer_id,
                    $invoice->zoho_invoice_id,
                    $request['remarks'] ?? 'Credit note for refund',
                    $invoice->currency_code ?? 'AED',
                    $invoice->place_of_supply ?? 'AE',
                    $lineItems,
                    null, // Pass null so Zoho auto-generates
                    $request['refund_date']
                );


                Log::info('Zoho response', ['response' => $zohoResponse]);
            } catch (\Exception $e) {
                Log::error('Zoho API call failed', ['message' => $e->getMessage()]);
                return "Zoho API call failed: " . $e->getMessage();
            }

            // return "ok2";

            $zohoCreditNoteId = $zohoResponse['creditnote']['creditnote_id'] ?? null;
            if (!$zohoCreditNoteId) {
                Log::error('Failed to create Zoho credit note', [
                    'response' => $zohoResponse,
                    'credit_note_no' => $creditNoteNo,
                ]);
                throw new \Exception('Failed to create credit note in Zoho: ' . ($zohoResponse['message'] ?? 'Unknown error'));
            }

            // Save Zoho credit note ID
            $creditNote->update(['zoho_credit_note_id' => $zohoCreditNoteId]);

            Log::info('Zoho credit note created successfully', [
                'zoho_credit_note_id' => $zohoCreditNoteId,
                'credit_note_no' => $creditNoteNo,
            ]);

            DB::commit();
            return redirect()->route('credit-note.index')->with('success', 'Credit Note Created Successfully in both systems.');
        } catch (\Exception $exp) {
            DB::rollback();
            Log::error('Credit note creation failed', [
                'error' => $exp->getMessage(),
                'booking_id' => $request['booking_id'],
                'credit_note_no' => $creditNoteNo,
            ]);
            return redirect()->back()->with('error', $exp->getMessage())->withInput();
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

    public function viewCreditNote(string $id)
    {
        $creditNote = CreditNote::with('paymentMethod', 'booking')->find($id);
        return view('booker.creditnote.credit-note-view', compact('creditNote'));
    }
}

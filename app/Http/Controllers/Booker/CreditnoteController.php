<?php

namespace App\Http\Controllers\Booker;

use App\Http\Controllers\Controller;
use App\Models\Bank;
use App\Models\Booking;
use App\Models\CreditNote;
use App\Models\DepositHandling;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CreditnoteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $creditNote= CreditNote::with('paymentMethod', 'booking')->get();
        return view('booker.creditnote.index', compact('creditNote'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // $booking= Booking::whereHas('deposit', function ($query){
        //     $query->where('deposit_amount', '>', 0);
        // })->with('deposit', 'customer', 'depositHandling')->orderBy('id', 'DESC')->get();

        // $filterBooking= $booking->filter(function($booking){
        //     $totalDeducted = $booking->depositHandling->sum('deduct_deposit');
        //     return $booking->deposit->deposit_amount > $totalDeducted;
        // });

        // Get all booking IDs which already have a credit note
        $creditNoteBookingIds = CreditNote::pluck('booking_id')->toArray();

        $booking = Booking::whereHas('deposit', function ($query) {
            $query->where('deposit_amount', '>', 0);
        })
        ->with('deposit', 'customer', 'depositHandling')
        ->orderBy('id', 'DESC')
        ->get();

        $filterBooking = $booking->filter(function ($booking) use ($creditNoteBookingIds) {
            $totalDeducted = $booking->depositHandling->sum('deduct_deposit');

            // Only allow bookings that have deposit left AND no credit note created yet
            return $booking->deposit->deposit_amount > $totalDeducted
                && !in_array($booking->id, $creditNoteBookingIds);
        });
        $refundMethod= PaymentMethod::all();
        $bank= Bank::all();
        return view('booker.creditnote.create', compact('filterBooking', 'refundMethod', 'bank'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $rules= [
            'booking_id' => 'required',
            'refund_method' => 'required',
            'refund_amount' => 'required',
            'remaining_deposit' => 'required',
            'refund_date' => 'required',
        ];

        if($request['payment_method']==3){ $rules['bank_id'] = 'required'; }
        $validator= Validator::make($request->all(), $rules);
        if($validator->fails()){
            $errormessage= implode('\n', $validator->errors()->all());
            return redirect()->back()->with('error', $errormessage)->withInput();
        } else {
            try {
                DB::beginTransaction();
                $lastCreditNote = CreditNote::orderBy('id', 'desc')->first();
                $nextNumber = $lastCreditNote ? $lastCreditNote->id + 1 : 1;
                $creditNoteNo = 'CN-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT); // CN-0001
                CreditNote::create([
                    'credit_note_no' => $creditNoteNo,
                    'booking_id' => $request['booking_id'],
                    'payment_method' => $request['refund_method'],
                    'bank_id' => $request['bank_id'],
                    'remaining_deposit' => $request['remaining_deposit'],
                    'refund_amount' => $request['refund_amount'],
                    'remarks' => $request['remarks'],
                    'refund_date' => $request['refund_date'],
                ]);
                return redirect()->route('credit-note.index')->with('success', 'Credit Note Created Successfully.')->withInput();
                DB::commit();
            } catch (\Exception $exp) {
                DB::rollback();
                return redirect()->back()->with('error', $exp->getMessage())->withInput();;
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

    public function viewCreditNote(string $id)
    {
        $creditNote= CreditNote::with('paymentMethod', 'booking')->find($id);
        return view('booker.creditnote.credit-note-view', compact('creditNote'));
    }
}

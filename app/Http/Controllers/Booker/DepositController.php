<?php

namespace App\Http\Controllers\Booker;

use App\Models\Deposit;
use App\Models\Booking;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class DepositController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $deposits = Deposit::with(['booking', 'transferredBooking'])
            ->where('initial_deposit','>', 0)
            ->orderBy('id', 'DESC')
            ->paginate(10);
        
        return view('booker.deposit.index', compact('deposits'));
    }

    /**
     * Show the form for transferring deposit to another booking.
     */
    public function transfer($depositId)
    {
        $deposit = Deposit::with('booking.customer')->findOrFail($depositId);
        
        // Check if deposit is already transferred
        if ($deposit->is_transferred == 1) {
            return redirect()->route('get.deposit')
                ->with('error', 'This deposit has already been transferred.');
        }

        // Check if deposit has remaining amount
        if ($deposit->deposit_amount <= 0) {
            return redirect()->route('get.deposit')
                ->with('error', 'This deposit has no remaining amount to transfer.');
        }

        // Get all bookings that don't have a deposit (excluding the source booking)
        $bookings = Booking::with('customer')
            ->where('id', '!=', $deposit->booking->id ?? 0)
            ->whereNull('deposit_id')
            ->orderBy('id', 'DESC')
            ->get();

        return view('booker.deposit.transfer', compact('deposit', 'bookings'));
    }

    /**
     * Store the deposit transfer.
     */
    public function storeTransfer(Request $request, $depositId)
    {
        $request->validate([
            'to_booking_id' => 'required|exists:bookings,id',
            'transfer_amount' => 'required|numeric|min:0.01',
        ]);

        $deposit = Deposit::with('booking')->findOrFail($depositId);
        $toBooking = Booking::findOrFail($request->to_booking_id);

        // Check if deposit is already transferred
        if ($deposit->is_transferred == 1) {
            return redirect()->route('get.deposit')
                ->with('error', 'This deposit has already been transferred.');
        }

        // Check if transfer amount is valid
        if ($request->transfer_amount > $deposit->deposit_amount) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Transfer amount cannot exceed remaining deposit amount (' . number_format($deposit->deposit_amount, 2) . ').');
        }

        // Check if destination booking already has a deposit
        if ($toBooking->deposit_id) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'The destination booking already has a deposit. Please select a different booking.');
        }

        try {
            DB::beginTransaction();

            // Create new deposit for destination booking
            $newDeposit = Deposit::create([
                'deposit_amount' => $request->transfer_amount,
                'initial_deposit' => $request->transfer_amount,
                'is_transferred' => 0,
                'transferred_booking_id' => null,
            ]);

            // Update destination booking with new deposit
            $toBooking->update(['deposit_id' => $newDeposit->id]);

            // Update source deposit
            $deposit->update([
                'deposit_amount' => $deposit->deposit_amount - $request->transfer_amount,
                'is_transferred' => 1,
                'transferred_booking_id' => $toBooking->id,
            ]);

            DB::commit();

            return redirect()->route('get.deposit')
                ->with('success', 'Deposit transferred successfully from Booking #' . ($deposit->booking->id ?? 'N/A') . ' to Booking #' . $toBooking->id);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to transfer deposit: ' . $e->getMessage());
        }
    }
}


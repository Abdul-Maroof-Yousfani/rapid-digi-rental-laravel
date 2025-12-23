<?php

namespace App\Http\Controllers\Booker;

use App\Models\Deposit;
use App\Models\Booking;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

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
}


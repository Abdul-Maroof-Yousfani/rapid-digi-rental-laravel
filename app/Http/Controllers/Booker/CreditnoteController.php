<?php

namespace App\Http\Controllers\Booker;

use App\Http\Controllers\Controller;
use App\Models\Bank;
use App\Models\Booking;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;

class CreditnoteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('booker.creditnote.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $booking= Booking::with('deposit')->get();
        $refundMethod= PaymentMethod::all();
        $bank= Bank::all();
        return view('booker.creditnote.create', compact('booking', 'refundMethod', 'bank'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
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
}

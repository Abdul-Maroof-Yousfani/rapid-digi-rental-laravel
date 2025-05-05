<?php

namespace App\Http\Controllers\Booker;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Invoice;
use App\Models\Booking;
use App\Models\Vehicletype;

class InvoiceController extends Controller
{
    public function index(string $id)
    {
        $invoice= Invoice::where('booking_id', $id)->get();
        $booking= Booking::findOrFail($id);
        return view('booker.invoice.index', compact('invoice', 'booking'));
    }

    public function create($id)
    {
        $vehicletypes= Vehicletype::all();
        $booking = Booking::findOrFail($id);
        return view('booker.invoice.create', compact('vehicletypes', 'booking'));
    }

    public function store(Request $request)
    {
        dd($request->all());
    }
}

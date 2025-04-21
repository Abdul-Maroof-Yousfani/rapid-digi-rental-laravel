<?php

namespace App\Http\Controllers\Booker;

use App\Models\Vehicle;
use App\Models\Customer;
use App\Models\Vehicletype;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class BookingController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:manage booking'])->only(['index','create', 'store', 'edit', 'update', 'destroy']);
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('booker.booking.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $customers= Customer::all();
        $vehicletypes= Vehicletype::all();
        return view('booker.booking.create', compact('customers', 'vehicletypes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if($request->all()){
            dd($request->all());
            $data= [
                'status' => true,
                'msg' => 'Data Received'
            ];
        } else {
            $data= [
                'status' => false,
                'msg' => 'Data Not Received'
            ];
            return response()->json($data);
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
}

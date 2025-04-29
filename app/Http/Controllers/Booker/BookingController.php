<?php

namespace App\Http\Controllers\Booker;

use App\Http\Controllers\Api\ZohoController;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingData;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Vehicle;
use App\Models\Vehicletype;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use App\Services\ZohoInvoice;
use Illuminate\Support\Facades\Validator;

class BookingController extends Controller
{

    protected $zohoinvoice;

    public function __construct(ZohoInvoice $zohoinvoice)
    {
        $this->zohoinvoice = $zohoinvoice;
        $this->middleware(['permission:manage booking'])->only(['index','create', 'store', 'edit', 'update', 'destroy']);
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $booking= Booking::with('invoice', 'customer')->get();
        return view('booker.booking.index', compact('booking'));
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
        $validator= Validator::make($request->all(), [
            'customer_id' => 'required',
            'notes' => 'required',
            'vehicle' => 'required',
            'vehicletypes' => 'required',
            'booking_date' => 'required',
            'return_date' => 'required',
            'price' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors('error', $validator->messages())->withInput();
        }else {
            $notes= $request->notes;
            $currency_code= "AED";
            $lineitems= [];
            foreach ($request->vehicle as $key => $vehicleId) {
                $vehicle = Vehicle::find($vehicleId);
                if (!$vehicle) {
                    continue;
                }
                $vehicleName = !empty($vehicle->name) ? $vehicle->name : $vehicle->temp_vehicle_detail;
                $description = $request->description[$key] ?? ($request->booking_date[$key] . " TO " . $request->return_date[$key]);
                if (is_array($description)) {
                    $description = implode(', ', $description);
                }
                $lineitems[]= [
                    'name' => $vehicleName,
                    'description' => $description,
                    'rate' => (float) $request->price[$key],
                    'quantity' => $request->quantity[$key],
                    'discount' => $request->discount[$key],
                    'tax_percentage' => $request->tax[$key],
                ];
            }
            $customerId=  $request->customer_id;
            $invoiceResponse = $this->zohoinvoice->createInvoice($customerId, $notes, $currency_code, $lineitems);
            $zohoInvoiceNumber = $invoiceResponse['invoice']['invoice_number'] ?? null;
            $zohoInvoiceId = $invoiceResponse['invoice']['invoice_id'] ?? null;
            if (!empty($zohoInvoiceId)) {
                try {
                    DB::beginTransaction();
                    $total_price = array_sum($request->price);
                    $booking= Booking::create([
                        'customer_id' => $customerId,
                        'notes' => $request['notes'],
                        'total_price' => $total_price,
                    ]);

                    foreach ($request->vehicle as $key => $vehicle_id) {
                        $booking_data= BookingData::create([
                            'booking_id' => $booking->id,
                            'vehicle_id' => $vehicle_id,
                            'start_date' => $request['booking_date'][$key],
                            'end_date' => $request['return_date'][$key],
                            'price' => $request['price'][$key],
                        ]);
                    }

                    Invoice::create([
                        'booking_id' => $booking->id,
                        'zoho_invoice_id' => $zohoInvoiceId,
                        'zoho_invoice_number' => $zohoInvoiceNumber,
                        'type' => 'Booking Invoice',
                        'amount' => $total_price,
                        'status' => 1,
                    ]);

                    DB::commit();
                    return redirect()->route('booker.customer-booking.index')->with('success', 'Booking Created Successfully.')->withInput();

                } catch (\Exception $exp) {
                    DB::rollBack();
                    return redirect()->back()->withErrors('error', $exp->getMessage())->withInput();
                }
            } else {
                return redirect()->back()->withErrors('error', 'Invoice ID Not Fetch')->withInput();
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
        $customers= Customer::all();
        $vehicletypes= Vehicletype::all();
        return view('booker.booking.edit', compact('customers', 'vehicletypes'));
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

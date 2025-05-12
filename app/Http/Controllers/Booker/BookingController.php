<?php

namespace App\Http\Controllers\Booker;

use App\Http\Controllers\Api\ZohoController;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingData;
use App\Models\Customer;
use App\Models\Deposit;
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
        $booking = Booking::with('invoice', 'customer', 'deposit')->orderBy('id', 'desc')->get();
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
            'agreement_no' => 'required|unique:bookings,agreement_no',
            'deposit_amount' => 'required',
            'invoice_status' => 'required',
            'notes' => 'required',
            'vehicle.*' => 'required',
            'vehicletypes.*' => 'required',
            'booking_date.*' => 'required',
            'return_date.*' => 'required',
            'price.*' => 'required',
        ]);
        if ($validator->fails()) {
            $errorMessages = implode("\n", $validator->errors()->all());
            return redirect()->back()->with('error', $errorMessages)->withInput();
        }else {
            $notes= $request->notes;
            $currency_code= "AED";
            $lineitems= [];
            foreach ($request->vehicle as $key => $vehicleId) {
                $taxID= '';
                if(isset($request->tax[$key])){
                    $tax= [
                        "tax_name" => 'asd',
                        "tax_percentage" => 10.5,
                    ];
                    $taxResponse= $this->zohoinvoice->taxCreate($tax);
                    $taxID= $taxResponse['tax']['tax_id'] ?? null;
                    dd($taxResponse);
                }
                $vehicle = Vehicle::find($vehicleId);
                if (!$vehicle) { continue; }
                $vehicleName = $vehicle->vehicle_name ?? $vehicle->temp_vehicle_detail;
                $description = $request->description[$key] ?? ($request->booking_date[$key] . " TO " . $request->return_date[$key]);
                if (is_array($description)) { $description = implode(', ', $description); }
                $lineitems[]= [
                    'name' => $vehicleName,
                    'description' => $description,
                    'rate' => (float) $request->price[$key],
                    'quantity' => 1,
                    'discount' => $request->discount[$key].'%',
                    'tax_id' => $taxID,
                    // 'tax_percentage' => $request->tax[$key],
                ];
            }
            $customerId=  $request->customer_id;
            $invoiceResponse = $this->zohoinvoice->createInvoice($customerId, $notes, $currency_code, $lineitems);
            $zohoInvoiceNumber = $invoiceResponse['invoice']['invoice_number'] ?? null;
            $zohoInvoiceId = $invoiceResponse['invoice']['invoice_id'] ?? null;
            $zohoInvoiceTotal = $invoiceResponse['invoice']['total'] ?? null;
            if($request->invoice_status == 'sent' && isset($zohoInvoiceId)){
                $this->zohoinvoice->markAsSent($zohoInvoiceId);
            }
            if (!empty($zohoInvoiceId)) {
                try {
                    DB::beginTransaction();
                    $booking= Booking::create([
                        'customer_id' => $customerId,
                        'agreement_no' => $request['agreement_no'],
                        'notes' => $request['notes'],
                    ]);

                    $invoice= Invoice::create([
                        'booking_id' => $booking->id,
                        'zoho_invoice_id' => $zohoInvoiceId,
                        'zoho_invoice_number' => $zohoInvoiceNumber,
                        'invoice_status' => $request->invoice_status,
                        'total_price' => number_format($zohoInvoiceTotal, 2, '.', ''),
                        'status' => 1,
                    ]);

                    Deposit::create([
                        'booking_id' => $booking->id,
                        'deposit_amount' => $request->deposit_amount,
                    ]);

                    foreach ($request->vehicle as $key => $vehicle_id) {
                        $booking_data= BookingData::create([
                            'booking_id' => $booking->id,
                            'vehicle_id' => $vehicle_id,
                            'invoice_id' => $invoice->id,
                            'start_date' => $request['booking_date'][$key],
                            'end_date' => $request['return_date'][$key],
                            'price' => $request['price'][$key],
                            'transaction_type' => 1,
                        ]);
                    }

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
        $booking= Booking::find($id);
        if(!$booking){
            return redirect()->back()->with('error', 'Booking not Found');
        }else{
            $invoiceID= Invoice::where('booking_id', $id)->first();
            $zohocolumn = $this->zohoinvoice->getInvoice($invoiceID->zoho_invoice_id);
            $booking_data= BookingData::where('booking_id', $booking->id)->where('transaction_type', 1)->orderBy('id', 'ASC')->get();
            $customers= Customer::all();
            $vehicletypes= Vehicletype::all();
            $vehicles = Vehicle::whereIn('id', $booking_data->pluck('vehicle_id'))->get();
            $vehicleTypeMap = Vehicle::whereIn('id', $booking_data->pluck('vehicle_id'))
            ->pluck('vehicletypes', 'id');
            $vehiclesByType = Vehicle::all()->groupBy('vehicletypes');

            return view('booker.booking.edit', compact('zohocolumn', 'customers', 'vehicletypes', 'booking',
            'booking_data',
            'vehicles',
            'vehicleTypeMap',
            'vehiclesByType'));
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $rules = [
            'customer_id' => 'required',
            'agreement_no' => 'required|unique:bookings,agreement_no,' . $id,
            'deposit_amount' => 'required',
            'notes' => 'required',
            'vehicle.*' => 'required',
            'vehicletypes.*' => 'required',
            'booking_date.*' => 'required',
            'return_date.*' => 'required',
            'price.*' => 'required',
        ];
        $invoice = Invoice::where('booking_id', $id)->first();
        if ($invoice && $invoice->invoice_status === 'sent') {
            $rules['reason'] = 'required';
        }
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $errorMessages = implode("\n", $validator->errors()->all());
            return redirect()->back()->with('error', $errorMessages)->withInput();
        } else {
            $notes= $request->notes;
            $currency_code= "AED";
            $lineitems= [];
            foreach ($request->vehicle as $key => $vehicleId) {
                $vehicle = Vehicle::find($vehicleId);
                if (!$vehicle) { continue; }
                $vehicleName = $vehicle->vehicle_name ?? $vehicle->temp_vehicle_detail;
                $description = $request->description[$key] ?? ($request->booking_date[$key] . " TO " . $request->return_date[$key]);
                if (is_array($description)) { $description = implode(', ', $description); }
                $lineitems[]= [
                    'name' => $vehicleName,
                    'description' => $description,
                    'rate' => (float) $request->price[$key],
                    'quantity' => 1,
                    'discount' => $request->discount[$key].'%',
                    'tax_percentage' => $request->tax[$key],
                ];
            }
            $invoiceID= $invoice->zoho_invoice_id;
            $customerId=  $request->customer_id;
            $customer= Customer::select('zoho_customer_id')->where('id', $customerId)->first();
            $json= [
                'customer_id' => $customer->zoho_customer_id,
                'notes' => $notes,
                'currency_code' => $currency_code,
                'line_items' => $lineitems,
                'reason' => $request->reason,
            ];
            $invoiceResponse = $this->zohoinvoice->updateInvoice($invoiceID, $json);
            $zohoInvoiceNumber = $invoiceResponse['invoice']['invoice_number'] ?? null;
            $zohoInvoiceId = $invoiceResponse['invoice']['invoice_id'] ?? null;
            $zohoInvoiceTotal = $invoiceResponse['invoice']['total'] ?? null;
            if (!empty($zohoInvoiceId)) {
                try {
                    DB::beginTransaction();
                    $booking = Booking::findOrFail($id);
                    $booking->update([
                        'customer_id' => $customerId,
                        'agreement_no' => $request['agreement_no'],
                        'notes' => $request['notes'],
                    ]);

                    $invoice= Invoice::updateOrCreate(
                        ['booking_id' => $booking->id],
                        [
                            'zoho_invoice_id' => $zohoInvoiceId,
                            'zoho_invoice_number' => $zohoInvoiceNumber,
                            'total_price' => number_format($zohoInvoiceTotal, 2, '.', ''),
                            'status' => 1,
                        ]
                    );

                    Deposit::updateOrCreate(
                        ['booking_id' => $booking->id],
                        [
                            'deposit_amount' => $request->deposit_amount,
                        ]
                    );

                    BookingData::where('booking_id', $booking->id)->where('invoice_id', $invoice->id)->forceDelete();
                    foreach ($request->vehicle as $key => $vehicle_id) {
                        $booking_data= BookingData::create([
                            'booking_id' => $booking->id,
                            'vehicle_id' => $vehicle_id,
                            'invoice_id' => $invoice->id,
                            'start_date' => $request['booking_date'][$key],
                            'end_date' => $request['return_date'][$key],
                            'price' => $request['price'][$key],
                            'transaction_type' => '1',
                        ]);
                    }

                    DB::commit();
                    return redirect()->route('booker.customer-booking.index')->with('success', 'Booking Updated Successfully.')->withInput();

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
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $booking= Booking::find($id);
        if($booking){
            // $invoice= Invoice::where('booking_id', $id)->first();
            // BookingData::where('booking_id', $id)->where('invoice_id', $invoice->id)->delete();
            BookingData::where('booking_id', $id)->delete();
            Invoice::where('booking_id', $id)->delete();
            $booking->delete();
            return redirect()->back()->with('success', 'Booking Deleted Successfully!');
        }else{
            return redirect()->back()->with('error', 'Booking Not Found!');
        }
    }
}

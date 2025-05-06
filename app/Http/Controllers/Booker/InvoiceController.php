<?php

namespace App\Http\Controllers\Booker;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Invoice;
use App\Models\Booking;
use App\Models\Vehicle;
use App\Models\Vehicletype;
use App\Models\Customer;
use App\Services\ZohoInvoice;
use App\Models\BookingData;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    protected $zohoinvoice;
    public function __construct(ZohoInvoice $zohoinvoice)
    {
        $this->zohoinvoice= $zohoinvoice;
    }

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
        $validator= Validator::make($request->all(), [
            'customer_id' => 'required',
            'booking_id' => 'required',
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
            $invoiceTypes = [ 2 => 'Renew', 3 => 'Fine', 4 => 'Salik', ];
            foreach ($request->vehicle as $key => $vehicleId) {
                $vehicle = Vehicle::find($vehicleId);
                if (!$vehicle) { continue; }
                $vehicleName = !empty($vehicle->name) ? $vehicle->name : $vehicle->temp_vehicle_detail;
                $description = $request->description[$key] ?? ($request->booking_date[$key] . " TO " . $request->return_date[$key]);
                if (is_array($description)) { $description = implode(', ', $description); }
                $invoiceTypeText = $invoiceTypes[$request['invoice_type'][$key]];
                $lineitems[]= [
                    'name' => $vehicleName,
                    'description' => $description."\n".$invoiceTypeText,
                    'rate' => (float) $request->price[$key],
                    'quantity' => 1,
                    'discount' => $request->discount[$key],
                    'discount_type' => 'parcentage',
                    'tax_percentage' => $request->tax[$key],
                ];
            }
            $customerId=  $request->customer_id;
            $invoiceResponse = $this->zohoinvoice->createInvoice($customerId, $notes, $currency_code, $lineitems);
            $zohoInvoiceNumber = $invoiceResponse['invoice']['invoice_number'] ?? null;
            $zohoInvoiceId = $invoiceResponse['invoice']['invoice_id'] ?? null;
            $zohoInvoiceTotal = $invoiceResponse['invoice']['total'] ?? null;
            if (!empty($zohoInvoiceId)) {
                try {
                    DB::beginTransaction();
                    $invoice= Invoice::create([
                        'booking_id' => $request->booking_id,
                        'zoho_invoice_id' => $zohoInvoiceId,
                        'zoho_invoice_number' => $zohoInvoiceNumber,
                        'type' => 'Booking Invoice',
                        'total_price' => number_format($zohoInvoiceTotal, 2, '.', ''),
                        'status' => 1,
                    ]);

                    foreach ($request->vehicle as $key => $vehicle_id) {
                        $booking_data= BookingData::create([
                            'booking_id' => $request->booking_id,
                            'vehicle_id' => $vehicle_id,
                            'invoice_id' => $invoice->id,
                            'start_date' => $request['booking_date'][$key],
                            'end_date' => $request['return_date'][$key],
                            'price' => $request['price'][$key],
                            'transaction_type' => $request['invoice_type'][$key],
                        ]);
                    }

                    DB::commit();
                    return redirect()->route('booker.view.invoice', $request->booking_id)->with('success', 'Invoice Created Successfully.')->withInput();

                } catch (\Exception $exp) {
                    DB::rollBack();
                    return redirect()->back()->withErrors('error', $exp->getMessage())->withInput();
                }
            } else {
                return redirect()->back()->withErrors('error', 'Invoice ID Not Fetch')->withInput();
            }
        }
    }

    public function edit(string $id)
    {
        $invoice= Invoice::find($id);
        $booking= Booking::find($invoice->id);
        if(!$invoice){
            return redirect()->back()->with('error', 'Invoice not Found');
        }else{
            // $invoiceID= Invoice::where('booking_id', $id)->first();
            $zohocolumn = $this->zohoinvoice->getInvoice($invoice->zoho_invoice_id);
            $booking_data= BookingData::where('invoice_id', $invoice->id)->get();
            $customers= Customer::all();
            $vehicletypes= Vehicletype::all();
            $vehicles = Vehicle::whereIn('id', $booking_data->pluck('vehicle_id'))->get();
            $vehicleTypeMap = Vehicle::whereIn('id', $booking_data->pluck('vehicle_id'))
            ->pluck('vehicletypes', 'id');
            $vehiclesByType = Vehicle::all()->groupBy('vehicletypes');

            return view('booker.invoice.edit', compact('zohocolumn', 'customers', 'vehicletypes', 'booking',
            'booking_data',
            'vehicles',
            'vehicleTypeMap',
            'vehiclesByType',
            'invoice'));
        }
    }
}




// $booking= Booking::find($invoice->id);
// if(!$invoice){
//     return redirect()->back()->with('error', 'Invoice not Found');
// }else{
//     // $invoiceID= Invoice::where('booking_id', $id)->first();
//     $zohocolumn = $this->zohoinvoice->getInvoice($invoice->zoho_invoice_id);
//     $booking_data= BookingData::where('booking_id', $booking->id)->where('transaction_type', 1)->get();
//     $customers= Customer::all();
//     $vehicletypes= Vehicletype::all();
//     $vehicles = Vehicle::whereIn('id', $booking_data->pluck('vehicle_id'))->get();
//     $vehicleTypeMap = Vehicle::whereIn('id', $booking_data->pluck('vehicle_id'))
//     ->pluck('vehicletypes', 'id');
//     $vehiclesByType = Vehicle::all()->groupBy('vehicletypes');

//     return view('booker.booking.edit', compact('zohocolumn', 'customers', 'vehicletypes', 'booking',
//     'booking_data',
//     'vehicles',
//     'vehicleTypeMap',
//     'vehiclesByType'));
// }

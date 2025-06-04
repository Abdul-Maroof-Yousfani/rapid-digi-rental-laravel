<?php

namespace App\Http\Controllers\Booker;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingData;
use App\Models\Customer;
use App\Models\Deposit;
use App\Models\Invoice;
use App\Models\Vehicle;
use App\Models\Vehicletype;
use App\Services\ZohoInvoice;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class InvoiceController extends Controller
{
    protected $zohoinvoice;
    public function __construct(ZohoInvoice $zohoinvoice)
    {
        $this->zohoinvoice= $zohoinvoice;
    }

    public function index(string $id)
    {
        $invoice= Invoice::with('bookingData')
                ->where('booking_id', $id)->where('created_at', '>=', Carbon::now()->subDays(15))
                ->whereHas('bookingData', function ($query) {
                    $query->where('transaction_type', '!=', 1);
                })->orderBy('id', 'DESC')->get();
        $booking= Booking::findOrFail($id);
        return view('booker.invoice.index', compact('invoice', 'booking'));
    }

    public function create($id)
    {
        $booking = Booking::findOrFail($id);
        $vehicleIds = BookingData::where('booking_id', $id)->pluck('vehicle_id');
        $vehicleTypeIds = Vehicle::whereIn('id', $vehicleIds)->pluck('vehicletypes')->unique();
        $vehicletypes = Vehicletype::whereIn('id', $vehicleTypeIds)->get();
        $taxlist= $this->zohoinvoice->taxList();
        return view('booker.invoice.create', compact('vehicletypes', 'booking', 'taxlist'));
    }

    public function store(Request $request)
    {
        $rules= [
            'customer_id' => 'required',
            'booking_id' => 'required',
            'notes' => 'required',
            'vehicle.*' => 'required',
            'vehicletypes.*' => 'required',
            'quantity.*' => 'required',
            'invoice_type.*' => 'required',
            'price.*' => 'required',
        ];
        $validator= Validator::make($request->all(), $rules);
        $validator->sometimes('booking_date.*', 'required', function($input, $key) {
            $index = explode('.', $key)[1] ?? null;
            return $index !== null && isset($input->invoice_type[$index]) && $input->invoice_type[$index] == 2;
        });

        $validator->sometimes('return_date.*', 'required', function($input, $key) {
            $index = explode('.', $key)[1] ?? null;
            return $index !== null && isset($input->invoice_type[$index]) && $input->invoice_type[$index] == 2;
        });

        if ($validator->fails()) {
            $errorMessages = implode("\n", $validator->errors()->all());
            return redirect()->back()->with('error', $errorMessages)->withInput();
        } else {
            $notes= $request->notes;
            $currency_code= "AED";
            $lineitems= [];
            $invoiceTypes = [ 2 => 'Renew', 3 => 'Fine', 4 => 'Salik', ];
            foreach ($request->vehicle as $key => $vehicleId) {
                $vehicle = Vehicle::find($vehicleId);
                if (!$vehicle) { continue; }
                $vehicleName = $vehicle->vehicle_name ?? $vehicle->temp_vehicle_detail;
                $invoiceType = $request->invoice_type[$key];
                $invoiceTypeText = $invoiceTypes[$invoiceType];
                if ($invoiceType == 2) {
                    $dateRange = ($request->booking_date[$key] ?? '-') . " TO " . ($request->return_date[$key] ?? '-');
                } else {
                    $dateRange = '';
                }

                $description = $request->description[$key] ?? trim($dateRange);
                if (is_array($description)) { $description = implode(', ', $description); }
                $lineitems[]= [
                    'name' => $vehicleName,
                    'description' => $description."\n".$invoiceTypeText,
                    'rate' => (float) $request->price[$key],
                    'quantity' => $request->quantity[$key],
                    'tax_id' => $request->tax[$key]
                ];
            }
            $customerId=  $request->customer_id;
            $invoiceResponse = $this->zohoinvoice->createInvoice($customerId, $notes, $currency_code, $lineitems);
            $zohoInvoiceNumber = $invoiceResponse['invoice']['invoice_number'] ?? null;
            $zohoInvoiceId = $invoiceResponse['invoice']['invoice_id'] ?? null;
            $zohoInvoiceTotal = $invoiceResponse['invoice']['total'] ?? null;
            $zohoLineItems = $invoiceResponse['invoice']['line_items'] ?? [];
            if (!empty($zohoInvoiceId)) {
                try {
                    DB::beginTransaction();
                    $invoice= Invoice::create([
                        'booking_id' => $request->booking_id,
                        'zoho_invoice_id' => $zohoInvoiceId,
                        'zoho_invoice_number' => $zohoInvoiceNumber,
                        'total_amount' => number_format($zohoInvoiceTotal, 2, '.', ''),
                        'status' => 1,
                    ]);

                    foreach ($request->vehicle as $key => $vehicle_id) {
                        $lineItemData= $zohoLineItems[$key] ?? [];
                        $booking_data= BookingData::create([
                            'booking_id' => $request->booking_id,
                            'vehicle_id' => $vehicle_id,
                            'invoice_id' => $invoice->id,
                            'start_date' => $request['booking_date'][$key] ?? null,
                            'end_date' => $request['return_date'][$key] ?? null,
                            'price' => $request['price'][$key],
                            'transaction_type' => $request['invoice_type'][$key],
                            'description' => $lineItemData['description'],
                            'quantity' => $request['quantity'][$key],
                            'tax_percent' => $request['tax_percent'][$key] ?? 0,
                            'item_total' => $lineItemData['item_total'],
                            'tax_name' => $lineItemData['tax_name'] ?? null,
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
        $invoice = Invoice::find($id);
        if(!$invoice){
            return redirect()->back()->with('error', 'Invoice not Found');
        }else{
            // Get Line Items From Zoho and DB
            $zohocolumn = $this->zohoinvoice->getInvoice($invoice->zoho_invoice_id);
            $booking_data = BookingData::where('invoice_id', $invoice->id)->where('transaction_type', '!=', 1)->orderBy('id', 'ASC')->get();
            $taxlist= $this->zohoinvoice->taxList();

            // Get Vehicles and Vehile and Vehicle Type Against booking
            $booking = Booking::find($invoice->booking_id);
            $vehicle_ids = $booking_data->pluck('vehicle_id');
            $vehicles = Vehicle::whereIn('id', $vehicle_ids)->get();
            $vehicle_type_ids = $vehicles->pluck('vehicletypes')->unique();
            $vehicletypes = Vehicletype::whereIn('id', $vehicle_type_ids)->get();

            return view('booker.invoice.edit', compact('zohocolumn', 'vehicletypes', 'vehicles', 'invoice', 'booking_data', 'taxlist'));
        }
    }

    public function update(Request $request, string $id)
    {
        $rules= [
            'customer_id' => 'required',
            'booking_id' => 'required',
            'notes' => 'required',
            'vehicle.*' => 'required',
            'vehicletypes.*' => 'required',
            'quantity.*' => 'required',
            'invoice_type.*' => 'required',
            'price.*' => 'required',
        ];

        $validator= Validator::make($request->all(),$rules);
        $validator->sometimes('booking_date.*', 'required', function($input, $key) {
            $index = explode('.', $key)[1] ?? null;
            return $index !== null && isset($input->invoice_type[$index]) && $input->invoice_type[$index] == 2;
        });

        $validator->sometimes('return_date.*', 'required', function($input, $key) {
            $index = explode('.', $key)[1] ?? null;
            return $index !== null && isset($input->invoice_type[$index]) && $input->invoice_type[$index] == 2;
        });

        $invoice= Invoice::find($id);
        if ($invoice && $invoice->invoice_status === 'sent') {
            $validator->sometimes('reason', 'required');
        }
        if ($validator->fails()) {
            $errorMessages = implode("\n", $validator->errors()->all());
            return redirect()->back()->with('error', $errorMessages)->withInput();
        } else {
            $notes= $request->notes;
            $currency_code= "AED";
            $lineitems= [];
            $invoiceTypes = [ 2 => 'Renew', 3 => 'Fine', 4 => 'Salik', ];
            foreach ($request->vehicle as $key => $vehicleId) {
                $vehicle = Vehicle::find($vehicleId);
                if (!$vehicle) { continue; }
                $vehicleName = $vehicle->vehicle_name ?? $vehicle->temp_vehicle_detail;
                $invoiceType = $request->invoice_type[$key];
                $invoiceTypeText = $invoiceTypes[$invoiceType];
                if ($invoiceType == 2) {
                    $dateRange = ($request->booking_date[$key] ?? '-') . " TO " . ($request->return_date[$key] ?? '-');
                } else {
                    $dateRange = '';
                }

                $description = $request->description[$key] ?? trim($dateRange);
                if (is_array($description)) { $description = implode(', ', $description); }
                $lineitems[]= [
                    'name' => $vehicleName,
                    'description' => $description."\n".$invoiceTypeText,
                    'rate' => (float) $request->price[$key],
                    'quantity' => $request->quantity[$key],
                    'tax_id' => $request->tax[$key]
                ];
            }
            // $invoice= Invoice::where('booking_id', $request->booking_id)->where('id', $id)->first();
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
            $zohoLineItems = $invoiceResponse['invoice']['line_items'] ?? [];
            if (!empty($zohoInvoiceId)) {
                try {
                    DB::beginTransaction();
                    $invoice->update([
                            'booking_id' => $request->booking_id,
                            'zoho_invoice_id' => $zohoInvoiceId,
                            'zoho_invoice_number' => $zohoInvoiceNumber,
                            'total_amount' => number_format($zohoInvoiceTotal, 2, '.', ''),
                            'status' => 1,
                    ]);

                    BookingData::where('booking_id', $request->booking_id)->where('invoice_id', $invoice->id)->forceDelete();
                    foreach ($request->vehicle as $key => $vehicle_id) {
                        $lineItemData= $zohoLineItems[$key] ?? [];
                        $booking_data= BookingData::create([
                            'booking_id' => $request->booking_id,
                            'vehicle_id' => $vehicle_id,
                            'invoice_id' => $invoice->id,
                            'start_date' => $request['booking_date'][$key] ?? null,
                            'end_date' => $request['return_date'][$key] ?? null,
                            'price' => $request['price'][$key],
                            'transaction_type' => $request['invoice_type'][$key],
                            'description' => $lineItemData['description'],
                            'quantity' => $request['quantity'][$key],
                            'tax_percent' => $request['tax_percent'][$key] ?? 0,
                            'item_total' => $lineItemData['item_total'],
                            'tax_name' => $lineItemData['tax_name'] ?? null,
                        ]);
                    }

                    DB::commit();
                    return redirect()->route('booker.view.invoice', $request->booking_id)->with('success', 'Booking Updated Successfully.')->withInput();

                } catch (\Exception $exp) {
                    DB::rollBack();
                    return redirect()->back()->withErrors('error', $exp->getMessage())->withInput();
                }
            } else {
                return redirect()->back()->withErrors('error', 'Invoice ID Not Fetch')->withInput();
            }
        }
    }

    public function viewInvoice($id){
        return view('booker.invoice.invoice-view');
    }

}

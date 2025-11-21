<?php

namespace App\Http\Controllers\Booker;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingData;
use App\Models\Customer;
use App\Models\Deductiontype;
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
        $this->zohoinvoice = $zohoinvoice;

        $this->middleware('permission:view invoice')->only(['index']);
        $this->middleware('permission:create invoice')->only(['create', 'store']);
        $this->middleware('permission:edit invoice')->only(['edit', 'update']);
        $this->middleware('permission:delete invoice')->only(['destroy']);
    }

    public function index(string $id)
    {
        $invoice = Invoice::with('bookingData')
            ->where('booking_id', $id)
            // ->where('created_at', '>=', Carbon::now()->subDays(15))
            // ->whereHas('bookingData', function ($query) {
            //     $query->where('transaction_type', '!=', 1);
            // })
            ->orderBy('id', 'DESC')->get();
        $booking = Booking::findOrFail($id);
        return view('booker.invoice.index', compact('invoice', 'booking'));
    }

    public function create($id)
    {
        $invoiceTypes = Deductiontype::where('status', 1)->get();


        $booking = Booking::findOrFail($id);
        $vehicleIds = BookingData::where('booking_id', $id)->pluck('vehicle_id');
        $vehicleTypeIds = Vehicle::whereIn('id', $vehicleIds)->pluck('vehicletypes')->unique();
        $vehicletypes = Vehicletype::whereIn('id', $vehicleTypeIds)->get();
        $taxlist = $this->zohoinvoice->taxList();
        return view('booker.invoice.create', compact('vehicletypes', 'booking', 'taxlist', 'invoiceTypes'));
    }

    public function store(Request $request)
    {
        // return $request->all();
        $rules = [
            'customer_id' => 'required',
            'booking_id' => 'required',
            'notes' => 'required',
            'vehicle.*' => 'required',
            'vehicletypes.*' => 'required',
            'quantity.*' => 'required',
            'invoice_type.*' => 'required',
            'price.*' => 'required',
        ];
        $validator = Validator::make($request->all(), $rules);
        $validator->sometimes('booking_date.*', 'required', function ($input, $key) {
            $index = explode('.', $key)[1] ?? null;
            return $index !== null && isset($input->invoice_type[$index]) && strtolower($input->invoice_type[$index]) === 'renew';
        });

        $validator->sometimes('return_date.*', 'required', function ($input, $key) {
            $index = explode('.', $key)[1] ?? null;
            return $index !== null && isset($input->invoice_type[$index]) && strtolower($input->invoice_type[$index]) === 'renew';
        });

        if ($validator->fails()) {
            $errorMessages = implode("\n", $validator->errors()->all());
            return redirect()->back()->with('error', $errorMessages)->withInput();
        } else {
            $notes = $request->notes;
            $currency_code = "AED";
            $lineitems = [];
            // $invoiceTypes = [2 => 'Renew', 3 => 'Fine', 4 => 'Salik',];

            foreach ($request->vehicle as $key => $vehicleId) {
                $vehicle = Vehicle::find($vehicleId);
                if (!$vehicle) {
                    continue;
                }

                $vehicleName = $vehicle->vehicle_name ?? $vehicle->temp_vehicle_detail;
                $invoiceTypeText = $request->invoice_type[$key];

                $invoiceTypeModel = Deductiontype::select('id')->whereRaw('LOWER(name) = ?', [strtolower($invoiceTypeText)])->first();

                // if (!$invoiceTypeModel) {
                //     continue; // skip invalid type
                // }

                $bookingDate = $request->booking_date[$key] ?? null;
                $returnDate = $request->return_date[$key] ?? null;
                $dateRange = (strtolower($invoiceTypeText) === 'renew' && $bookingDate && $returnDate)
                    ? "$bookingDate TO $returnDate"
                    : '';

                $description = $request->description[$key] ?? $dateRange;
                if (is_array($description)) {
                    $description = implode(', ', $description);
                }

                $lineitems[] = [
                    'name' => $vehicleName,
                    'description' => $description . "\n" . $invoiceTypeText,
                    'rate' => (float) $request->price[$key],
                    'quantity' => $request->quantity[$key],
                    'tax_id' => $request->tax[$key],
                ];
            }
            $customerId = $request->customer_id;
            $book = Booking::with('salePerson')->find($request->booking_id);

            if ($book && $book->salePerson) {
                $salesperson_id = $book->salePerson->id;
                $salesperson_name = $book->salePerson->name;
            } else {
                $salesperson_id = null;
                $salesperson_name = null;
            }

            $invoiceResponse = $this->zohoinvoice->createInvoice($customerId, $notes, $currency_code, $lineitems, $salesperson_id, $salesperson_name);
            $zohoInvoiceNumber = $invoiceResponse['invoice']['invoice_number'] ?? null;
            $zohoInvoiceId = $invoiceResponse['invoice']['invoice_id'] ?? null;
            $zohoInvoiceTotal = $invoiceResponse['invoice']['total'] ?? null;
            $zohoLineItems = $invoiceResponse['invoice']['line_items'] ?? [];

            if (!empty($zohoInvoiceId)) {
                try {
                    DB::beginTransaction();
                    $invoice = Invoice::create([
                        'booking_id' => $request->booking_id,
                        'zoho_invoice_id' => $zohoInvoiceId,
                        'zoho_invoice_number' => $zohoInvoiceNumber,
                        'total_amount' => number_format($zohoInvoiceTotal, 2, '.', ''),
                        'status' => 1,
                    ]);

                    foreach ($request->vehicle as $key => $vehicle_id) {
                        // return $request;
                        $invoiceTypeText = $request->invoice_type[$key];
                        $invoiceTypeModel = Deductiontype::select('id')
                            ->whereRaw('LOWER(name) = ?', [strtolower($invoiceTypeText)])
                            ->first();

                        $price = $request['price'][$key];
                        $amount = $request['amount'][$key];
                        $quantity = $request['quantity'][$key];
                        $taxPercent = $request['tax_percent'][$key] ?? 0;
                        if (!empty($request['tax_percent'][$key])) {
                            $taxName = 'VAT ' . $taxPercent . '%';
                        } else {
                            $taxName = null;
                        }

                        // Tax Add Calculation in Item Total
                        $subTotal = $price * $quantity;
                        $taxAmount = ($subTotal * $taxPercent) / 100;
                        $itemTotal = $subTotal + $taxAmount;

                        $lineItemData = $zohoLineItems[$key] ?? [];
                        $booking_data = BookingData::create([
                            'booking_id' => $request->booking_id,
                            'vehicle_id' => $vehicle_id,
                            'invoice_id' => $invoice->id,
                            'start_date' => $request['booking_date'][$key] ?? null,
                            'end_date' => $request['return_date'][$key] ?? null,
                            'price' => $price,
                            'transaction_type' => isset($request['return_date'][$key]) ? 2 : 1,
                            'description' => $lineItemData['description'] ?? null,
                            'quantity' => $quantity,
                            'tax_percent' => $taxPercent,
                            'item_total' => $amount,   // FIX
                            'tax_name' => $taxName,
                            'deductiontype_id' => $invoiceTypeModel->id ?? null,
                            'view_type' => 2,
                            'non_refundable_amount' => $request['non_refundable_amount'][$key] ?? 0,
                            'deposit_type' => $request['deposit_type'][$key] ?? null,
                        ]);

                    }

                    DB::commit();
                    return redirect()->route('view.invoice', $invoice->id)->with('success', 'Invoice Created Successfully.')->withInput();
                } catch (\Exception $exp) {
                    DB::rollBack();
                    return redirect()->back()->with('error', $exp->getMessage());
                    // return response($exp->getMessage(), 500);

                }
            } else {
                return redirect()->back()->with('error', 'Invoice ID Not Fetch')->withInput();
            }
        }
    }


    public function edit(string $id)
    {
        $invoiceTypes = Deductiontype::where('status', 1)->get();


        $invoice = Invoice::find($id);
        if (!$invoice) {
            return redirect()->back()->with('error', 'Invoice not Found');
        } else {
            // Get Line Items From Zoho and DB
            $zohocolumn = $this->zohoinvoice->getInvoice($invoice->zoho_invoice_id);
            $booking_data = BookingData::with('invoice_type')->where('invoice_id', $invoice->id)->where('transaction_type', '!=', 1)->orderBy('id', 'ASC')->get();
            $taxlist = $this->zohoinvoice->taxList();

            // Get Vehicles and Vehile and Vehicle Type Against booking
            $booking = Booking::find($invoice->booking_id);
            $vehicle_ids = $booking_data->pluck('vehicle_id');
            $vehicles = Vehicle::whereIn('id', $vehicle_ids)->get();
            $vehicle_type_ids = $vehicles->pluck('vehicletypes')->unique();
            $vehicletypes = Vehicletype::whereIn('id', $vehicle_type_ids)->get();
            // return $booking_data;
            return view('booker.invoice.edit', compact('zohocolumn', 'vehicletypes', 'vehicles', 'invoice', 'booking_data', 'taxlist', 'invoiceTypes'));
        }
    }

    public function update(Request $request, string $id)
    {
        $rules = [
            'customer_id' => 'required',
            'booking_id' => 'required',
            'notes' => 'required',
            'vehicle.*' => 'required',
            'vehicletypes.*' => 'required',
            'quantity.*' => 'required',
            'invoice_type.*' => 'required',
            'price.*' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);
        $validator->sometimes('booking_date.*', 'required', function ($input, $key) {
            $index = explode('.', $key)[1] ?? null;
            return $index !== null && isset($input->invoice_type[$index]) && strtolower($input->invoice_type[$index]) === 'renew';
        });

        $validator->sometimes('return_date.*', 'required', function ($input, $key) {
            $index = explode('.', $key)[1] ?? null;
            return $index !== null && isset($input->invoice_type[$index]) && strtolower($input->invoice_type[$index]) === 'renew';
        });

        $invoice = Invoice::find($id);
        if ($invoice && $invoice->invoice_status === 'sent') {
            $validator->sometimes('reason', 'required', function () {
                return true;
            });
        }
        if ($validator->fails()) {
            $errorMessages = implode("\n", $validator->errors()->all());
            return redirect()->back()->with('error', $errorMessages)->withInput();
        } else {
            $notes = $request->notes;
            $currency_code = "AED";
            $lineitems = [];
            // $invoiceTypes = [1 => 'test est', 4 => 'fine', 8 => 'Renew',];
            foreach ($request->vehicle as $key => $vehicleId) {
                $vehicle = Vehicle::find($vehicleId);
                if (!$vehicle) {
                    continue;
                }
                $vehicleName = $vehicle->vehicle_name ?? $vehicle->temp_vehicle_detail;
                $invoiceTypeText = $request->invoice_type[$key];

                $invoiceTypeModel = Deductiontype::select('id')->whereRaw('LOWER(name) = ?', [strtolower($invoiceTypeText)])->first();

                $bookingDate = $request->booking_date[$key] ?? null;
                $returnDate = $request->return_date[$key] ?? null;
                $dateRange = (strtolower($invoiceTypeText) === 'renew' && $bookingDate && $returnDate)
                    ? "$bookingDate TO $returnDate"
                    : '';

                $description = $request->description[$key] ?? $dateRange;
                if (is_array($description)) {
                    $description = implode(', ', $description);
                }

                $lineitems[] = [
                    'name' => $vehicleName,
                    'description' => $description . "\n" . $invoiceTypeText,
                    'rate' => (float) $request->price[$key],
                    'quantity' => $request->quantity[$key],
                    'tax_id' => $request->tax[$key]
                ];
            }
            // $invoice= Invoice::where('booking_id', $request->booking_id)->where('id', $id)->first();
            $invoiceID = $invoice->zoho_invoice_id;
            $customerId = $request->customer_id;
            $customer = Customer::select('zoho_customer_id')->where('id', $customerId)->first();
            $json = [
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

                        $price = $request['price'][$key];
                        $amount = $request['amount'][$key];
                        $quantity = $request['quantity'][$key];
                        $taxPercent = $request['tax_percent'][$key] ?? 0;

                        // Tax Add Calculation in Item Total
                        $subTotal = $price * $quantity;
                        $taxAmount = ($subTotal * $taxPercent) / 100;
                        $itemTotal = $subTotal + $taxAmount;

                        $lineItemData = $zohoLineItems[$key] ?? [];

                        $booking_data = BookingData::create([
                            'booking_id' => $request->booking_id,
                            'vehicle_id' => $vehicle_id,
                            'invoice_id' => $invoice->id,
                            'start_date' => $request['booking_date'][$key] ?? null,
                            'end_date' => $request['return_date'][$key] ?? null,
                            'price' => $price,
                            'transaction_type' => $request['return_date'][$key] ? 2 : 1,
                            'description' => $lineItemData['description'],
                            'quantity' => $quantity,
                            'view_type' => 2,
                            'tax_percent' => $taxPercent,
                            'item_total' => $amount,
                            'tax_name' => $lineItemData['tax_name'] ?? null,
                        ]);
                    }

                    DB::commit();
                    return redirect()->route('view.invoice', $invoice->id)->with('success', 'Booking Updated Successfully.')->withInput();
                } catch (\Exception $exp) {
                    DB::rollBack();
                    return redirect()->back()->with('error', $exp->getMessage());
                }
            } else {
                return redirect()->back()->withErrors('error', 'Invoice ID Not Fetch')->withInput();
            }
        }
    }

    public function destroy(string $id)
    {
        // dd($id);
        $invoice = Invoice::where('zoho_invoice_number', $id)->first();

        if (!$invoice) {
            return redirect()->back()->with('error', 'Invoice Not Found!');
        }

        BookingData::where('invoice_id', $invoice->id)->delete();

        $invoice->delete();

        return redirect()->back()->with('success', 'Invoice Deleted Successfully!');
    }


    public function viewInvoice($id)
    {
        $company =
            $invoice = Invoice::with(['bookingData.invoice_type', 'booking', 'paymentData'])->find($id);
        if (!$invoice) {
            return redirect()->back()->with('error', 'Invoice Not Found');
        }
        // dd($invoice->bookingData);

        return view('booker.invoice.invoice-view', compact('invoice'));
    }


    public function getInvoiceList()
    {
        $booking = Invoice::with('booking', 'bookingData')
            ->orderByDesc('zoho_invoice_number')
            ->paginate(10); 

        return view('booker.invoice.invoice-list', compact('booking'));
    }

}

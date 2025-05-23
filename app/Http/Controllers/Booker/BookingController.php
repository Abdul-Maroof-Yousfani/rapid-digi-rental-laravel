<?php

namespace App\Http\Controllers\Booker;

use App\Http\Controllers\Api\ZohoController;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingData;
use App\Models\CreditNote;
use App\Models\Customer;
use App\Models\Deposit;
use App\Models\DepositHandling;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\SalePerson;
use App\Models\Vehicle;
use App\Models\Vehicletype;
use App\Services\ZohoInvoice;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
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
        $booking = Booking::with('invoice', 'customer', 'deposit', 'salePerson')
                    // ->where('created_at', '>=', Carbon::now()->subDays(15))
                    ->orderBy('id', 'desc')->get();
        return view('booker.booking.index', compact('booking'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $customers= Customer::all();
        $vehicletypes= Vehicletype::all();
        $salePerson= SalePerson::all();
        $taxlist= $this->zohoinvoice->taxList();
        return view('booker.booking.create', compact('customers', 'vehicletypes', 'salePerson', 'taxlist'));
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
            'sale_person_id' => 'required',
            // 'invoice_status' => 'required',
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
                    'tax_id' => $request->tax[$key]
                ];
            }
            $customerId=  $request->customer_id;
            $invoiceResponse = $this->zohoinvoice->createInvoice($customerId, $notes, $currency_code, $lineitems);
            $zohoInvoiceNumber = $invoiceResponse['invoice']['invoice_number'] ?? null;
            $zohoInvoiceId = $invoiceResponse['invoice']['invoice_id'] ?? null;
            $zohoInvoiceTotal = $invoiceResponse['invoice']['total'] ?? null;
            $zohoLineItems = $invoiceResponse['invoice']['line_items'] ?? [];
            // if($request->invoice_status == 'sent' && isset($zohoInvoiceId)){
            //     $this->zohoinvoice->markAsSent($zohoInvoiceId);
            // }
            if (!empty($zohoInvoiceId)) {
                try {
                    DB::beginTransaction();

                    $deposit =Deposit::create([
                        'deposit_amount' => $request->deposit_amount,
                    ]);

                    $booking= Booking::create([
                        'customer_id' => $customerId,
                        'agreement_no' => $request['agreement_no'],
                        'notes' => $request['notes'],
                        'sale_person_id' => $request['sale_person_id'],
                        'deposit_id' => $deposit->id,
                    ]);

                    $invoice= Invoice::create([
                        'booking_id' => $booking->id,
                        'zoho_invoice_id' => $zohoInvoiceId,
                        'zoho_invoice_number' => $zohoInvoiceNumber,
                        // 'invoice_status' => $request->invoice_status,
                        'total_amount' => number_format($zohoInvoiceTotal, 2, '.', ''),
                        'status' => 1,
                    ]);



                    foreach ($request->vehicle as $key => $vehicle_id) {
                        $lineItemData= $zohoLineItems[$key] ?? [];
                        $booking_data= BookingData::create([
                            'booking_id' => $booking->id,
                            'vehicle_id' => $vehicle_id,
                            'invoice_id' => $invoice->id,
                            'start_date' => $request['booking_date'][$key],
                            'end_date' => $request['return_date'][$key],
                            'price' => $request['price'][$key],
                            'transaction_type' => 1,
                            'description' => $lineItemData['description'],
                            'quantity' => 1,
                            'tax_percent' => $request['tax_percent'][$key] ?? 0,
                            'item_total' => $lineItemData['item_total'],
                            'tax_name' => $lineItemData['tax_name'] ?? null,
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
            $salePerson= SalePerson::all();
            $taxlist= $this->zohoinvoice->taxList();

            return view('booker.booking.edit', compact('zohocolumn', 'customers', 'vehicletypes', 'booking',
            'taxlist',
            'salePerson',
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
            'sale_person_id' => 'required',
            'deposit_amount' => 'required',
            'notes' => 'required',
            'vehicle.*' => 'required',
            'vehicletypes.*' => 'required',
            'booking_date.*' => 'required',
            'return_date.*' => 'required',
            'price.*' => 'required',
        ];
        $invoice = Invoice::where('booking_id', $id)->first();
        // if ($invoice && $invoice->invoice_status === 'sent') {
        //     $rules['reason'] = 'required';
        // }
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $errorMessages = implode("\n", $validator->errors()->all());
            return redirect()->back()->with('error', $errorMessages)->withInput();
        } else {
            // dd($request->all());
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
                    'tax_id' => $request->tax[$key]
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
            $zohoLineItems = $invoiceResponse['invoice']['line_items'] ?? [];
            if (!empty($zohoInvoiceId)) {
                try {
                    DB::beginTransaction();
                    $booking = Booking::findOrFail($id);
                    if ($booking->deposit_id) {
                        $deposit = Deposit::find($booking->deposit_id);
                        if ($deposit) {
                            $deposit->update([
                                'deposit_amount' => $request->deposit_amount,
                            ]);
                        }
                    }

                    $booking->update([
                        'customer_id' => $customerId,
                        'agreement_no' => $request['agreement_no'],
                        'notes' => $request['notes'],
                        'sale_person_id' => $request['sale_person_id'],
                    ]);

                    $invoice= Invoice::updateOrCreate(
                        ['booking_id' => $booking->id],
                        [
                            'zoho_invoice_id' => $zohoInvoiceId,
                            'zoho_invoice_number' => $zohoInvoiceNumber,
                            'total_amount' => number_format($zohoInvoiceTotal, 2, '.', ''),
                            'status' => 1,
                        ]
                    );

                    BookingData::where('booking_id', $booking->id)->where('invoice_id', $invoice->id)->forceDelete();
                    foreach ($request->vehicle as $key => $vehicle_id) {
                        $lineItemData= $zohoLineItems[$key] ?? [];
                        $booking_data= BookingData::create([
                            'booking_id' => $booking->id,
                            'vehicle_id' => $vehicle_id,
                            'invoice_id' => $invoice->id,
                            'start_date' => $request['booking_date'][$key],
                            'end_date' => $request['return_date'][$key],
                            'price' => $request['price'][$key],
                            'transaction_type' => 1,
                            'description' => $lineItemData['description'],
                            'quantity' => 1,
                            'tax_percent' => $request['tax_percent'][$key] ?? 0,
                            'item_total' => $lineItemData['item_total'],
                            'tax_name' => $lineItemData['tax_name'] ?? null,
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

    // public function closeBooking(string $bookingID)
    // {
    //     $booking= Booking::find($bookingID);
    //     if($booking->deposit->deposit_amount == 0){
    //         $payment= Payment::where('booking_id', $bookingID)->first();
    //         $paymentAmount= $payment->pending_amount;
    //         /**return 'This Booking Deposit in zero | Pending Amount is '.$paymentAmount. '<br> But You Can Close This Booking';*/
    //         return redirect()->back()->with('error', 'Your Pending Amount is'.$paymentAmount.' But You Can Closed');
    //     } else {
    //         $depositHandling= DepositHandling::where('booking_id', $bookingID)->sum('deduct_deposit');
    //         $payment= Payment::where('booking_id', $bookingID)->first();
    //         $paymentAmount= $payment->pending_amount ?? 0;
    //         $paidAmount= $payment->paid_amount ?? 0;
    //         if($depositHandling == $booking->deposit->deposit_amount){
    //             /**return 'Your Initial Deposit Amount is '.$booking->deposit->deposit_amount.
    //                 '<br>Your Deposit Adjust Amount Is '. $depositHandling.
    //                 '<br>Paid Amount '.$paidAmount-$depositHandling.
    //                 '<br>Your Pending Amount is '.$paymentAmount.
    //                 '<br>But You Can close booking';*/
    //             return redirect()->back()->with('error', 'Your Pending Amount is '.$paymentAmount);
    //         } else {
    //             $creditNote= CreditNote::where('booking_id', $bookingID)->first();
    //             if($creditNote){
    //                     /**return 'Refund Deposit Amount '.$creditNote->refund_amount.'<br>'
    //                         .'Previous Deposit Adjust '.$depositHandling.'<br>'
    //                         .'Initial Deposit '.$booking->deposit->deposit_amount.' = '.$creditNote->refund_amount + $depositHandling
    //                         .'<br>You Can close Booking';*/
    //             return redirect()->back()->with('success', 'Booking is Closed'.$paymentAmount);
    //             } else {
    //                 $payable= $booking->deposit->deposit_amount - $depositHandling;
    //                     /**return 'Payable Remaining Deposit Is : '.$payable.
    //                         '<br>Can not close booking Please make Credit note';*/
    //                 return redirect()->back()->with('success', 'Your Deposit Payable is '. $payable. ' Make Credit Note');
    //             }
    //         }
    //     }
    // }


    public function checkCloseEligibility($id)
    {
        $booking = Booking::with('deposit', 'payment')->find($id);
        $depositHandling = DepositHandling::where('booking_id', $id)->sum('deduct_deposit');
        $payment = Payment::where('booking_id', $id)->first();
        $depositAmount = $booking->deposit->deposit_amount ?? 0;
        $pending = $payment->pending_amount ?? 0;

        if ($pending > 0) {
            return response()->json(['status' => 'pending_payment', 'amount' => $pending]);
        }

        // Total Deposit Recieved
        if ($depositAmount > 0 && $depositAmount > $depositHandling) {
            $creditNote = CreditNote::where('booking_id', $id)->first();
            if(!$creditNote){
                $payable = $depositAmount - $depositHandling;
                return response()->json(['status' => 'deposit_remaining', 'deposit_amount' => $payable]);
            }
        }
        return response()->json(['status' => 'can_close']);
    }

    public function closeBooking($id)
    {
        $booking = Booking::find($id);
        $booking->update(['booking_status' => 'closed']);
        return response()->json(['success' => true]);
    }

    public function forceCloseBooking($id)
    {
        $booking = Booking::find($id);
        $booking->update(['booking_status' => 'closed']);
        return response()->json(['success' => true]);
    }
}

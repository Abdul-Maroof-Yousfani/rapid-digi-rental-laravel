<?php

namespace App\Http\Controllers\Booker;

use Carbon\Carbon;
use App\Models\Booking;
use App\Models\Deposit;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Vehicle;
use App\Models\Customer;
use App\Models\CreditNote;
use App\Models\SalePerson;
use App\Models\BookingData;
use App\Models\Vehicletype;
use Illuminate\Http\Request;
use App\Services\ZohoInvoice;
use App\Events\BookingCreated;
use App\Models\DepositHandling;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Api\ZohoController;

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
        // $booking = Booking::with('invoice', 'customer', 'deposit', 'salePerson')
        //             ->where('created_at', '>=', Carbon::now()->subDays(15))
        //             ->orderBy('id', 'desc')->get();
        // return view('booker.booking.index', compact('booking'));

        $booking = Invoice::with('booking', 'bookingData')
                    ->whereHas('bookingData', function($query){
                        $query->where('transaction_type', 1);
                    })
                    ->whereHas('booking', function($query){
                        $query->where('created_at', '>=', Carbon::now()->subDays(15));
                        $query->where('booking_cancel', '0');
                    })->orderBy('id', 'desc')->get();

        return view('booker.booking.index', compact('booking'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $customers= Customer::orderBy('id', 'DESC')->get();
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
            'started_at' => 'required',
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
                    'rate' => (float) str_replace(',', '', $request->price[$key]),
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
                        'started_at' => $request['started_at']
                    ]);

                    $invoice= Invoice::create([
                        'booking_id' => $booking->id,
                        'zoho_invoice_id' => $zohoInvoiceId,
                        'zoho_invoice_number' => $zohoInvoiceNumber,
                        'total_amount' => number_format($zohoInvoiceTotal, 2, '.', ''),
                        'status' => 1,
                    ]);

                    foreach ($request->vehicle as $key => $vehicle_id) {

                        $price = $request['price'][$key];
                        $quantity = 1;
                        $taxPercent = $request['tax_percent'][$key] ?? 0;

                        // Tax Add Calculation in Item Total
                        $subTotal = $price * $quantity;
                        $taxAmount = ($subTotal * $taxPercent) / 100;
                        $itemTotal = $subTotal + $taxAmount;

                        $lineItemData= $zohoLineItems[$key] ?? [];
                        $booking_data= BookingData::create([
                            'booking_id' => $booking->id,
                            'vehicle_id' => $vehicle_id,
                            'invoice_id' => $invoice->id,
                            'start_date' => $request['booking_date'][$key],
                            'end_date' => $request['return_date'][$key],
                            'price' => $price,
                            'transaction_type' => 1,
                            'description' => $lineItemData['description'],
                            'quantity' => $quantity,
                            'tax_percent' => $taxPercent,
                            'item_total' => $itemTotal,
                            'tax_name' => $lineItemData['tax_name'] ?? null,
                        ]);

                        Vehicle::where('id', $vehicle_id)->update([
                            'vehicle_status_id' => 33 // yahaan '2' booked wali ID honi chahiye
                        ]);
                    }

                    DB::commit();
                    return redirect()->route('booker.customer-booking.index')->with('success', 'Booking Created Successfully.')->withInput();

                } catch (\Exception $exp) {
                    DB::rollBack();
                    return redirect()->back()->with('error', $exp->getMessage());
                }
            } else {
                return redirect()->back()->with('error', 'Invoice ID Not Fetch');
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





    public function bookingReport(Request $request)
    {
        $query= BookingData::with('vehicle.investor', 'booking')
                  ->whereHas('vehicle', function($query1){
                    $query1->whereHas('investor', function($query2){
                        $query2->where('user_id', Auth::user()->id);
                    });
                  });

        $booking= $query->get();

        return view('reports.report_booking', compact('booking'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $invoice= Invoice::with('booking')->find($id);
        if($invoice->booking->booking_status != 'closed'){
            if(!$invoice){
                return redirect()->back()->with('error', 'Booking not Found');
            }else{
                $zohocolumn = $this->zohoinvoice->getInvoice($invoice->zoho_invoice_id);
                $booking_data= BookingData::where('invoice_id', $invoice->id)->where('transaction_type', 1)->orderBy('id', 'ASC')->get();
                $customers= Customer::all();
                $vehicletypes= Vehicletype::all();
                $vehicles = Vehicle::whereIn('id', $booking_data->pluck('vehicle_id'))->get();
                $vehicleTypeMap = Vehicle::whereIn('id', $booking_data->pluck('vehicle_id'))
                ->pluck('vehicletypes', 'id');
                $vehiclesByType = Vehicle::all()->groupBy('vehicletypes');
                $salePerson= SalePerson::all();
                $taxlist= $this->zohoinvoice->taxList();

                return view('booker.booking.edit', compact('zohocolumn', 'customers', 'vehicletypes', 'invoice',
                'taxlist',
                'salePerson',
                'booking_data',
                'vehicles',
                'vehicleTypeMap',
                'vehiclesByType'));
            }
        } else {
            return redirect()->back()->with('error', 'This booking is closed. You cannot edit it.');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $invoice = Invoice::with('booking')->find($id);
        $rules = [
            'customer_id' => 'required',
            'agreement_no' => 'required|unique:bookings,agreement_no,' . $invoice->booking->id,
            'sale_person_id' => 'required',
            'deposit_amount' => 'required',
            'notes' => 'required',
            'vehicle.*' => 'required',
            'vehicletypes.*' => 'required',
            'booking_date.*' => 'required',
            'return_date.*' => 'required',
            'price.*' => 'required',
        ];
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
                    'rate' => (float) str_replace(',', '', $request->price[$key]),
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
                    $booking = Booking::findOrFail($invoice->booking->id);
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

                    $invoice->update(
                        [
                            'zoho_invoice_id' => $zohoInvoiceId,
                            'zoho_invoice_number' => $zohoInvoiceNumber,
                            'total_amount' => number_format($zohoInvoiceTotal, 2, '.', ''),
                            'status' => 1,
                        ]
                    );

                    // BookingData::where('booking_id', $booking->id)->where('invoice_id', $invoice->id)->forceDelete();
                    BookingData::where('invoice_id', $invoice->id)->forceDelete();
                    foreach ($request->vehicle as $key => $vehicle_id) {

                        $price = $request['price'][$key];
                        $quantity = 1;
                        $taxPercent = $request['tax_percent'][$key] ?? 0;

                        // Tax Add Calculation in Item Total
                        $subTotal = $price * $quantity;
                        $taxAmount = ($subTotal * $taxPercent) / 100;
                        $itemTotal = $subTotal + $taxAmount;

                        $lineItemData= $zohoLineItems[$key] ?? [];
                        $booking_data= BookingData::create([
                            'booking_id' => $booking->id,
                            'vehicle_id' => $vehicle_id,
                            'invoice_id' => $invoice->id,
                            'start_date' => $request['booking_date'][$key],
                            'end_date' => $request['return_date'][$key],
                            'price' => $price,
                            'transaction_type' => 1,
                            'description' => $lineItemData['description'],
                            'quantity' => $quantity,
                            'tax_percent' => $taxPercent,
                            'item_total' =>  $itemTotal,
                            'tax_name' => $lineItemData['tax_name'] ?? null,
                        ]);

                        Vehicle::where('id', $vehicle_id)->update([
                            'vehicle_status_id' => 33 // yahaan '2' booked wali ID honi chahiye
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

    public function isBookingActive($id)
    {
        $today = Carbon::today();

        // Check rent booking active detail
        $rentBookingData = BookingData::where('booking_id', $id)
                        ->where('transaction_type',1)
                        ->whereDate('end_date', '>=', $today)
                        ->with('vehicle')
                        ->get();

        $rentDetails = [];
        foreach ($rentBookingData as $data) {
            $bookingDataID = $data->id;
            $vehicleName = $data->vehicle->vehicle_name ?? $data->vehicle->temp_vehicle_detail;
            $numberPlate = $data->vehicle->number_plate ?? '';
            $grossRentAmount = $data->price;
            $rentAmount = $data->item_total;
            $taxPercent = $data->tax_percent;
            $returnDate = $data->end_date;
            $startDate = Carbon::parse($data->start_date)->startOfDay();
            $endDate = Carbon::parse($data->end_date)->endOfDay();

            // Total Rent Days including both start & end dates
            $totalRentDays = $startDate->diffInDays($endDate) + 1;

            if ($startDate->gte($today)) {
                $remainingDays = $startDate->diffInDays($endDate) + 1;
            } elseif ($endDate->gte($today)) {
                $remainingDays = $today->diffInDays($endDate) + 1;
            } else {
                $remainingDays = 0;
            }

            $rentDetails[] = [
                'bookingDataID' => $bookingDataID,
                'end_date' => $returnDate,
                'start_date' => $data->start_date,
                'vehicle_name' => $vehicleName,
                'number_plate' => $numberPlate,
                'gross_rent_amount' => $grossRentAmount,
                'tax_percent' => $taxPercent,
                'rent_amount' => $rentAmount,
                'total_rent_days' => $totalRentDays,
                'rent_remaining_days' => $remainingDays,
            ];
        }

        // Check Renew booking active detail
        $renewBookingData = BookingData::where('booking_id', $id)
                        ->where('transaction_type',2)
                        ->whereDate('end_date', '>=', $today)
                        ->with('vehicle')
                        ->get();

        $renewDetial = [];
        foreach ($renewBookingData as $data) {
            $bookingDataID = $data->id;
            $vehicleName = $data->vehicle->vehicle_name ?? $data->vehicle->temp_vehicle_detail;
            $numberPlate = $data->vehicle->number_plate ?? '';
            $grossRenewAmount = $data->price;
            $renewAmount = $data->item_total;
            $taxPercent = $data->tax_percent;
            $returnDate = $data->end_date;
            $startDate = Carbon::parse($data->start_date)->startOfDay();
            $endDate = Carbon::parse($data->end_date)->endOfDay();

            // Total Rent Days including both start & end dates
            $totalRenewDays = $startDate->diffInDays($endDate) + 1;

            if ($startDate->gte($today)) {
                $remainingDays = $startDate->diffInDays($endDate) + 1;
            } elseif ($endDate->gte($today)) {
                $remainingDays = $today->diffInDays($endDate) + 1;
            } else {
                $remainingDays = 0;
            }

            $renewDetial[] = [
                'bookingDataID' => $bookingDataID,
                'end_date' => $returnDate,
                'start_date' => $data->start_date,
                'vehicle_name' => $vehicleName,
                'number_plate' => $numberPlate,
                'gross_renew_amount' => $grossRenewAmount,
                'tax_percent' => $taxPercent,
                'renew_amount' => $renewAmount,
                'total_renew_days' => $totalRenewDays,
                'renew_remaining_days' => $remainingDays,
            ];
        }

        // Check Booking is active or not
        $activeBooking = BookingData::where('booking_id', $id)
            ->whereIn('transaction_type', [1, 2])
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->exists();

        return response()->json([
            'is_active' => $activeBooking,
            'rent_details' => $rentDetails,
            'renew_details' => $renewDetial,
        ]);
    }


    public function checkCloseEligibility($id)
    {
        $booking = Booking::with('deposit', 'payment')->find($id);
        $depositHandling = DepositHandling::where('booking_id', $id)->sum('deduct_deposit');
        $payment = Payment::where('booking_id', $id)->first();
        $depositAmount = $booking->deposit->deposit_amount ?? 0;
        $pending = $payment->pending_amount ?? 0;

        if ($depositAmount > 0 && $depositAmount > $depositHandling) {
            $creditNote = CreditNote::where('booking_id', $id)->first();
            if(!$creditNote){
                $payable = $depositAmount - $depositHandling;
                return response()->json(['status' => 'deposit_remaining', 'deposit_amount' => $payable]);
            }
        }

        if($pending){
            if ($pending > 0) {
                return response()->json(['status' => 'pending_payment', 'amount' => $pending]); }
        }else {
            return response()->json(['status' => 'not_received', 'amount' => 'not receive']);
        }


        return response()->json(['status' => 'can_close']);
    }

    public function closeBooking($id)
    {
        $booking = Booking::with('bookingData')->find($id);
        if (!$booking) {
            return response()->json(['success' => false, 'message' => 'Booking not found']);
        }
        $booking->update(['booking_status' => 'closed']);
        $vehicleIds = $booking->bookingData->pluck('vehicle_id')->unique();
        Vehicle::whereIn('id', $vehicleIds)->update(['vehicle_status_id' => 1]);
        return response()->json(['success' => true]);
    }

    public function forceCloseBooking($id)
    {
        $booking = Booking::find($id);
        $booking->update(['booking_status' => 'closed']);
        $vehicleIds = $booking->bookingData->pluck('vehicle_id')->unique();
        Vehicle::whereIn('id', $vehicleIds)->update(['vehicle_status_id' => 1]);
        return response()->json(['success' => true]);
    }
}

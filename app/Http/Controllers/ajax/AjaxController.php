<?php

namespace App\Http\Controllers\ajax;

use App\Models\Bank;
use App\Models\Booking;
use App\Models\Deposit;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Vehicle;
use App\Models\Customer;
use App\Models\CreditNote;
use App\Models\SalePerson;
use App\Models\BookingData;
use App\Models\PaymentData;
use Illuminate\Http\Request;
use App\Models\Vehiclestatus;
use App\Services\ZohoInvoice;
use App\Models\DepositHandling;
use App\Jobs\UpdateZohoInvoiceJob;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class AjaxController extends Controller
{

    protected $zohoinvoice;
    public function __construct(ZohoInvoice $zohoinvoice){
        $this->zohoinvoice= $zohoinvoice;
    }


    public function getVehicleByType(Request $request, $id)
    {
        $startDate = $request->start_date;
        $endDate = $request->end_date;
        $bookingID = $request->bookingID;
        $selectedVehicleId = $request->selectedVehicleId;

        $bookedVehicleIds = BookingData::where(function ($query) use ($startDate, $endDate) {
            $query->whereBetween('start_date', [$startDate, $endDate])
                ->orWhereBetween('end_date', [$startDate, $endDate])
                ->orWhere(function ($query) use ($startDate, $endDate) {
                    $query->where('start_date', '<=', $startDate)
                        ->where('end_date', '>=', $endDate);
                });
        })
        ->when($bookingID, function ($query) use ($bookingID) {
            $query->where('booking_id', '!=', $bookingID);
        })
        ->pluck('vehicle_id');

        // $vehicles = Vehicle::where('vehicletypes', $id)
        // ->whereNotIn('id', $bookedVehicleIds)
        // ->where('vehicle_status_id', 1) // available wali status
        // ->get();

        $vehicles = Vehicle::where('vehicletypes', $id)
                    ->where(function ($query) use ($bookedVehicleIds, $selectedVehicleId) {
                        $query->where(function ($q) use ($bookedVehicleIds) {
                            $q->whereNotIn('id', $bookedVehicleIds);
                            // ->where('vehicle_status_id', 1);
                        });

                        if (!empty($selectedVehicleId)) {
                            $query->orWhere('id', $selectedVehicleId); // booked vehicle bhi lani hai agar dates change nahi hui
                        }
                    })->get();

        return response()->json($vehicles);
    }




    public function getNoByVehicle($id)
    {
        $vehicle= Vehicle::where('id', $id)->first();
        if($vehicle){
            return response()->json([
                'vehicle_status' => $vehicle->vehiclestatus->name ?? 'N/A',
                'number_plate' => $vehicle->number_plate,
                'investor' => $vehicle->investor->name,
                'status' => $vehicle->status==1 ? "Active" : "Inactive",
            ], 200);
        }else{
            return response()->json([], 200);
        }
    }

    public function getVehicleAgaistBooking(Request $request, $vehicleTypeId, $bookingId)
    {
        // $bookedVehicleIds = BookingData::where('booking_id', $bookingId)->pluck('vehicle_id')->unique();
        // $vehicles = Vehicle::where('vehicletypes', $vehicleTypeId)
        //     ->whereIn('id', $bookedVehicleIds)
        //     ->get(['id', 'number_plate', 'temp_vehicle_detail', 'vehicle_name']);
        // return response()->json($vehicles);


        $startDate = $request->start_date;
        $endDate = $request->end_date;
        $invoiceType = $request->invoice_type;
        $bookedVehicleIds = BookingData::where('booking_id', $bookingId)->pluck('vehicle_id')->unique();
        $query = Vehicle::where('vehicletypes', $vehicleTypeId)
            ->whereIn('id', $bookedVehicleIds);

        if($invoiceType == 2 && $startDate && $endDate){
            $bookedInRange = BookingData::where(function ($q) use ($startDate, $endDate){
                $q->whereBetween('start_date', [$startDate, $endDate])
                  ->orWhereBetween('end_date', [$startDate, $endDate])
                  ->orWhere(function ($q) use ($startDate, $endDate) {
                        $q->where('start_date', '<=', $startDate)
                            ->where('end_date', '>=', $endDate);
                    });
            })->pluck('vehicle_id')->unique();
            $query->whereNotIn('id', $bookedInRange);
        }

        $vehicles = $query->get(['id', 'number_plate', 'temp_vehicle_detail', 'vehicle_name']);
        return response()->json($vehicles);
    }

    public function getBookingDetail($booking_id)
    {
        $booking = Booking::find($booking_id);
        if(!$booking_id){
            return response()->json([ 'error' => 'Data Not Found' ]);
        }
        $bookingAmount = Invoice::where('booking_id', $booking_id)->sum('total_amount');
        $payment = Payment::where('booking_id', $booking_id)->first();
        $remainingAmount = $bookingAmount - ($payment->paid_amount ?? 0);

        // Calculate Remaining Deposit Amount
        $initialDeposit = $booking->deposit->deposit_amount ?? 0;
        $deductAmount = DepositHandling::where('booking_id', $booking_id)->sum('deduct_deposit');
        $creditNote = $booking->creditNote; // will be null if not exists
        $refundAmount = $creditNote->refund_amount ?? 0;
        $adjustedInitialDeposit = $initialDeposit - $refundAmount;
        $deductAmount = DepositHandling::where('booking_id', $booking_id)->sum('deduct_deposit') ?? 0;
        $remainingDeposit = $adjustedInitialDeposit - $deductAmount;
        // if($deductAmount){ $remainingDeposit= $initialDeposit - $deductAmount; }

        $creditNoteDetail = [];
        if ($creditNote) {
            $creditNoteDetail[] = [
                'id' => $creditNote->id,
                'CN_no' => $creditNote->credit_note_no,
                'refund_amount' => $creditNote->refund_amount,
            ];
        }


        $getVehicle = BookingData::with('vehicle')->select('vehicle_id')
                    ->where('booking_id', $booking_id)
                    ->groupBy('vehicle_id')->get();

        $vehicles= [];
        foreach ($getVehicle as $value) {
            $vehicles[]= [
                'type' => $value->vehicle->vehicletype->name,
                'name' => $value->vehicle->number_plate.' | '.($value->vehicle->vehicle_name ?? $value->vehicle->temp_vehicle_detail)
            ];
        }

        $invoices = Invoice::where('booking_id', $booking_id)->get()->map(function($invoice) {
            $bookingData = BookingData::where('invoice_id', $invoice->id)->get();
            $summary = [
                'salik_qty' => 0,
                'salik_amount' => 0,
                'fine_qty' => 0,
                'fine_amount' => 0,
                'renew_amount' => 0,
                'rent_amount' => 0,
            ];

            foreach ($bookingData as $data) {
                switch ($data->transaction_type) {
                    case 4: // Salik
                        $summary['salik_qty'] += $data->quantity;
                        $summary['salik_amount'] += $data->item_total;
                        break;
                    case 3: // Fine
                        $summary['fine_qty'] += $data->quantity;
                        $summary['fine_amount'] += $data->item_total;
                        break;
                    case 2: // Renew
                        $summary['renew_amount'] += $data->item_total;
                        break;
                    case 1: // Rent
                        $summary['rent_amount'] += $data->item_total;
                        break;
                }
            }

            // PaymentData se total paid amount
            $paymentData = PaymentData::where('invoice_id', $invoice->id)->first();
            if($paymentData){
                $getDeposit = DepositHandling::where('payment_data_id', $paymentData->id)->first();
            }
            return [
                'id' => $paymentData->id ?? null, // PaymentData Primary Key
                'paid_amount' => $paymentData->paid_amount ?? 0,
                'deposit_amount' => $getDeposit->deduct_deposit ?? 0,
                'zoho_invoice_number' => $invoice->zoho_invoice_number,
                'invoice_status' => $invoice->invoice_status,
                'invoice_amount' => $invoice->total_amount,
                'invoice_id' => $invoice->id,
                'summary' => $summary,
            ];
        });

        return response()->json([
            'id' => $payment->id ?? null, // Payment Primary Key
            'paid_amount' => $payment->paid_amount ?? 0,
            'remaining_amount' => $remainingAmount,
            'booking_amount' => $bookingAmount,
            'deposit_amount' => $initialDeposit,
            'credit_note_detail' => $creditNoteDetail,
            'deduct_amount' => $deductAmount ?? 0,
            'remaining_deposit' => $remainingDeposit,
            'customer' => $booking->customer->customer_name,
            'invoice_detail' => $invoices,
            'vehicle' => $vehicles,
        ]);
    }

    public function getVehicleForEditForm($id)
    {
        $vehicle = Vehicle::with('vehicletype', 'investor')->find($id);
        if (!$vehicle) {
            return response()->json([
                'success' => false,
                'error' => 'Vehicle Not Found'
            ], 404);
        }
        return response()->json([
            'success' => true,
            'data' => $vehicle
        ]);
    }

    public function getSalemanForEditForm($id)
    {
        $salePerson= SalePerson::find($id);
        if(!$salePerson){
            return response()->json([
                'success' => false,
                'error' => 'Sale Person Not Found'
            ], 404);
        }
        return response()->json([
            'success' => true,
            'data' => $salePerson
        ]);
    }

    public function getBankForEditForm($id)
    {
        $bank= Bank::find($id);
        if(!$bank){
            return response()->json([
                'success' => false,
                'error' => 'Bank Not Found'
            ], 404);
        }
        return response()->json([
            'success' => true,
            'data' => $bank
        ]);
    }

    public function getVehicleStatusForEditForm($id){
        $status= Vehiclestatus::find($id);
        if(!$status){
            return response()->json([
                'success' => false,
                'error' => 'Status Not Found'
            ], 404);
        }
        return response()->json([
            'success' => true,
            'data' => $status
        ]);
    }

    public function getCustomerForEditForm($id)
    {
        $customers= Customer::find($id);
        if(!$customers){
            return response()->json([
                'success' => false,
                'error' => 'Customer Not Found !'
            ], 404);
        }
        return response()->json([
            'success' => true,
            'data' => $customers
        ]);
    }

    public function bookingCancellation($id)
    {
        $booking = Booking::find($id);
        if (!$booking) {
            return response()->json([
                'success' => false,
                'data' => 'Booking not found.'
            ], 404);
        } else {
            $booking->booking_cancel = '1';
            $booking->save();
            return response()->json([
                'success' => true,
                'data' => 'Booking Cancelled.'
            ], 200);
        }
    }

    public function bookingConvertPartial(Request $request)
    {
        // dd($request->all());
        $updatedRecords = [];
        $invoiceUpdates = [];
        foreach ($request->bookingDataID as $key => $bookingDataID) {
            $booking_data = BookingData::with(['invoice', 'vehicle'])->find($bookingDataID);

            if ($booking_data && $booking_data->invoice) {
                // Step 1: Update Local DB
                $booking_data->update([
                    'end_date' => $request['end_date'][$key],
                    'price' => $request['new_gross_rent_amount'][$key],
                    'item_total' => $request['new_amount'][$key],
                ]);

                $invoiceId = $booking_data->invoice->zoho_invoice_id;

                // Use fallback for vehicle name
                $vehicleName = $booking_data->vehicle->vehicle_name ?? $booking_data->vehicle->temp_vehicle_detail ?? '';
                $description = $booking_data->description ?? '';
                $newRate = $request['new_gross_rent_amount'][$key];

                $invoiceUpdates[$invoiceId][] = [
                    'name' => $vehicleName,
                    'description' => $description,
                    'rate' => $newRate,
                ];

                $updatedRecords[] = $booking_data;
            }
        }

        // $zohoResponses = [];

        foreach ($invoiceUpdates as $invoiceId => $updates) {
            UpdateZohoInvoiceJob::dispatch($invoiceId, $updates);
        }

        return response()->json([
            'success' => true,
            'data' => $updatedRecords,
            // 'zoho_response' => $zohoResponses,
        ]);
    }


    // public function bookingConvertPartial(Request $request)
    // {
    //     $updatedRecords = [];
    //     $invoiceUpdates = [];

    //     foreach ($request->bookingDataID as $key => $bookingDataID) {
    //         $booking_data = BookingData::with(['invoice', 'vehicle'])->find($bookingDataID);

    //         if ($booking_data && $booking_data->invoice) {
    //             // Step 1: Update Local DB
    //             $booking_data->update([
    //                 'end_date' => $request['end_date'][$key],
    //                 'price' => $request['new_amount'][$key],
    //             ]);

    //             $invoiceId = $booking_data->invoice->zoho_invoice_id;

    //             // Use fallback for vehicle name
    //             $vehicleName = $booking_data->vehicle->vehicle_name ?? $booking_data->vehicle->temp_vehicle_detail ?? '';
    //             $description = $booking_data->description ?? '';
    //             $newRate = $request['new_amount'][$key];

    //             $invoiceUpdates[$invoiceId][] = [
    //                 'name' => $vehicleName,
    //                 'description' => $description,
    //                 'rate' => $newRate,
    //             ];

    //             $updatedRecords[] = $booking_data;
    //         }
    //     }

    //     $zohoResponses = [];

    //     foreach ($invoiceUpdates as $invoiceId => $updates) {
    //         $invoiceData = $this->zohoinvoice->getInvoice($invoiceId);

    //         if (!isset($invoiceData['invoice'])) continue;

    //         $originalInvoice = $invoiceData['invoice'];
    //         $lineItems = $originalInvoice['line_items'];

    //         foreach ($lineItems as &$lineItem) {
    //             foreach ($updates as $update) {
    //                 if (
    //                     strtolower(trim($lineItem['name'])) === strtolower(trim($update['name'])) &&
    //                     strtolower(trim($lineItem['description'])) === strtolower(trim($update['description']))
    //                 ) {
    //                     $lineItem['rate'] = $update['rate'];
    //                     $lineItem['item_total'] = $update['rate'] * $lineItem['quantity']; // Optional
    //                 }
    //             }
    //         }

    //         $updatePayload = [
    //             'customer_id' => $originalInvoice['customer_id'],
    //             'currency_code' => $originalInvoice['currency_code'],
    //             'notes' => $originalInvoice['notes'] ?? '',
    //             'line_items' => $lineItems,
    //         ];

    //         $response = $this->zohoinvoice->updateInvoice($invoiceId, $updatePayload);
    //         \Log::info("Zoho Invoice Updated [$invoiceId]", $response);

    //         $zohoResponses[$invoiceId] = $response;
    //     }

    //     return response()->json([
    //         'success' => true,
    //         'data' => $updatedRecords,
    //         'zoho_response' => $zohoResponses,
    //     ]);
    // }
}

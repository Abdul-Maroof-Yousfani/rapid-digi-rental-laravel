<?php

namespace App\Http\Controllers\ajax;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingData;
use App\Models\Deposit;
use App\Models\DepositHandling;
use App\Models\Invoice;
use App\Models\PaymentData;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class AjaxController extends Controller
{
    public function getVehicleByType($id)
    {
        $vehicle= Vehicle::where('vehicletypes', $id)->where('vehicle_status_id', 1)->get();
        return response()->json($vehicle);
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

    public function getVehicleAgaistBooking($vehicleTypeId, $bookingId)
    {
        $bookedVehicleIds = BookingData::where('booking_id', $bookingId)->pluck('vehicle_id')->unique();
        $vehicles = Vehicle::where('vehicletypes', $vehicleTypeId)
            ->whereIn('id', $bookedVehicleIds)
            ->get(['id', 'number_plate', 'temp_vehicle_detail', 'vehicle_name']);

        return response()->json($vehicles);
    }

    public function getBookingDetail($booking_id)
    {
        $booking = Booking::find($booking_id);
        if(!$booking_id){
            return response()->json([ 'error' => 'Data Not Found' ]);
        }
        $bookingAmount = Invoice::where('booking_id', $booking_id)->sum('total_amount');
        $deductAmount = DepositHandling::where('booking_id', $booking_id)->sum('deduct_deposit');
        $getVehicle = BookingData::with('vehicle')->select('vehicle_id')
                    ->where('booking_id', $booking_id)->groupBy('vehicle_id')->get();
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

            return [
                'zoho_invoice_number' => $invoice->zoho_invoice_number,
                'invoice_status' => $invoice->invoice_status,
                'invoice_amount' => $invoice->total_amount,
                'invoice_id' => $invoice->id,
                'summary' => $summary,
            ];
        });

        return response()->json([
            'booking_amount' => $bookingAmount,
            'deposit_amount' => $booking->deposit->deposit_amount ?? 0,
            'invoice_detail' => $invoices,
            'customer' => $booking->customer->customer_name,
            'vehicle' => $vehicles,
            'deduct_amount' => $deductAmount ?? 0
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

}

<?php

namespace App\Http\Controllers\ajax;

use App\Models\Vehicle;
use App\Models\BookingData;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AjaxController extends Controller
{
    public function getVehicleByType($id)
    {
        $vehicle= Vehicle::where('vehicletypes', $id)->get();
        return response()->json($vehicle);
    }

    public function getNoByVehicle($id)
    {
        $vehicle= Vehicle::where('id', $id)->first();
        if($vehicle){
            return response()->json([
                'booking_status' => $vehicle->booking_status==1 ? "Available" : "Not Available",
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
}

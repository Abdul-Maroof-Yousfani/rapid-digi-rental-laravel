<?php

namespace App\Http\Controllers\ajax;

use App\Models\Vehicle;
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
}

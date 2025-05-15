<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Models\Vehiclestatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class VehiclestatusController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $status= Vehiclestatus::all();
        return view('admin.vehicle.status.index', compact('status'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.vehicle.status.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator= Validator::make($request->all(), [
            'name' => 'required',
        ]);
        if($validator->fails()){
            return redirect()->back()->withErrors($validator->messages())->withInput();
        }else{
            try {
                DB::beginTransaction();
                Vehiclestatus::create([
                    'name' => $request->name,
                ]);
                return redirect()->route('admin.vehicle-status.index')->with('success', 'Status Added Successfully!');
                DB::commit();
            } catch (\Exception $exp) {
                DB::rollBack();
                return redirect()->back()->with('error', $exp->getMessage());
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
        $status= Vehiclestatus::find($id);
        if(!$status){
            return redirect()->route('admin.vehicle-status.index')->with('error', 'Status Not Found');
        }
        return view('admin.vehicle.status.edit', compact('status'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validator= Validator::make($request->all(), [
            'name' => 'required',
        ]);
        if($validator->fails()){
            return redirect()->back()->withErrors($validator->messages())->withInput();
        }else{
            try {
                DB::beginTransaction();
                $status= Vehiclestatus::find($id);
                $status->update([
                    'name' => $request->name,
                ]);
                return redirect()->route('admin.vehicle-status.index')->with('success', 'Status Updated Successfully!');
                DB::commit();
            } catch (\Exception $exp) {
                DB::rollBack();
                return redirect()->back()->with('error', $exp->getMessage());
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $status= Vehiclestatus::find($id);
        if(!$status){
            return redirect()->back()->with('error', 'Status Not Found');
        }else{
            $status->delete();
            return redirect()->back()->with('success', 'Status Deleted Successfully!');
        }
    }

    public function StatusForm(){
        $status= Vehiclestatus::all();
        $vehicle= Vehicle::where('vehicle_status_id', null)->get();
        return view('booker.assignstatus.create', compact('status', 'vehicle'));
    }

    public function assignStatus(Request $request)
    {
        $validator= Validator::make($request->all(), [
            'vehicle_id' => 'required',
            'status' => 'required',
        ]);
        if($validator->fails()){
            return redirect()->back()->withErrors($validator->messages())->withInput();
        }
        $vehicle= Vehicle::find($request->vehicle_id);
        if($vehicle){
            $status= Vehiclestatus::find($request->status);
            $vehicle->update([
                'vehicle_status_id' => $request->status
            ]);
            return redirect()->route('booker.assined.vehicle')->with('success', 'Vehicle Status is '. $status->name);
        }
    }

    public function viewAssinedVehicle()
    {
        $vehicles = Vehicle::with('vehiclestatus')
            ->whereNotNull('vehicle_status_id')
            ->get();
        return view('booker.assignstatus.index', compact('vehicles'));
    }

    public function editAssinedVehicle($id)
    {
        $vehicle= Vehicle::find($id);
        $status= Vehiclestatus::all();
        return view('booker.assignstatus.edit', compact('status', 'vehicle'));
    }

    public function updateAssinedVehicle(Request $request, string $id)
    {
        $validator= Validator::make($request->all(), [
            'status' => 'required',
        ]);
        if($validator->fails()){
            $errorMessages = implode("\n", $validator->errors()->all());
            return redirect()->back()->with('error', $errorMessages)->withInput();
        }
        $vehicle= Vehicle::find($id);
        $vehicle->update([
            'vehicle_status_id' => $request->status
        ]);
        return redirect()->route('booker.assined.vehicle')->with('success', 'Status Updated Successfully!');
    }

    public function deleteAssinedVehicle($id)
    {
        $vehicle = Vehicle::find($id);
        if ($vehicle) {
            $vehicle->update([
                'vehicle_status_id' => null
            ]);
        return redirect()->route('booker.assined.vehicle')->with('success', 'Status removed to ' . $vehicle->vehicle_name);
        }
        return redirect()->route('booker.assined.vehicle')->with('error', 'Vehicle not found.');
    }
}

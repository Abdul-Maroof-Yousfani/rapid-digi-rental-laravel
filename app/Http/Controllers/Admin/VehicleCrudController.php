<?php

namespace App\Http\Controllers\Admin;

use Exeption;
use App\Models\User;
use League\Csv\Reader;
use App\Models\Vehicle;
use App\Models\Investor;
use App\Models\Vehicletype;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class VehicleCrudController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:manage vehicles');
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $vehicletypes= Vehicletype::all();
        if($vehicletypes->isEmpty()) { return redirect()->route('vehicle-type.create')->with('error', 'First Add Vehicle Type Then You can Add Vehicle'); }
        $investor= Investor::all();
        if($investor->isEmpty()) { return redirect()->route('investor.create')->with('error', 'First Add Investor Then You can Add Vehicle'); }
        $vehicle= Vehicle::orderBy('id', 'DESC')->get();
        return view("admin.vehicle.index", compact('vehicle', 'vehicletypes', 'investor'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $vehicletypes= Vehicletype::all();
        if($vehicletypes->isEmpty()) { return redirect()->route('vehicle-type.create')->with('error', 'First Add Vehicle Type Then You can Add Vehicle'); }
        $investor= Investor::all();
        if($investor->isEmpty()) { return redirect()->route('investor.create')->with('error', 'First Add Investor Then You can Add Vehicle'); }
        return view("admin.vehicle.create", compact('vehicletypes', 'investor'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator= Validator::make($request->all(), [
            'vehicle_name' => 'required',
            'vehicletypes' => 'required',
            'number_plate' => 'required|unique:vehicles,number_plate',
            'investor_id' => 'required',
            'car_make' => 'required',
            'year' => 'required'
        ]);

        if($validator->fails()){
            if($request->ajax()){
                return response()->json(['error' => $validator->errors()], 422);
            } else {
                return redirect()->back()->withErrors($validator->messages())->withInput();
            }
        } else{
            try {
                $vehicle = Vehicle::create([
                    'vehicle_name' => $request['vehicle_name'],
                    'vehicletypes' => $request['vehicletypes'],
                    'investor_id' => $request['investor_id'],
                    'car_make' => $request['car_make'],
                    'year' => $request['year'],
                    'number_plate' => $request['number_plate'],
                    'status' => $request['status'],
                ]);
                if($request->ajax()){
                    return response()->json(['success' => 'Vehicle Added Successfully!', 'data' => $vehicle->load('vehicletype', 'investor')]);
                } else {
                    return redirect()->route('admin.vehicle.index')->with('success', 'Vehicle Added Against Investor Successfully!');
                }
            } catch (\Exception $exp) {
                return redirect()->back()->with('error', $exp->getMessage());
            }
        }
    }


    public function importCsv(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'importCsv' => 'required|file|mimes:csv|max:2048',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator->messages());
        }

        $file = $request->file('importCsv');
        if (strtolower($file->getClientOriginalExtension()) !== "csv") {
            return redirect()->back()->with("error", "Invalid file format. Please upload a CSV file.");
        }

        $csv = Reader::createFromPath($file->getRealPath(), 'r');
        $csv->setHeaderOffset(0); // first row is headers

        foreach ($csv->getRecords() as $record) {
            $record = array_change_key_case(array_map('trim', $record), CASE_LOWER);
            if (
                isset($record['plate no']) &&
                isset($record['car make-model & year']) &&
                isset($record['investor id']) &&
                isset($record['vehicle type id'])
            ) {
                Vehicle::create([
                    'number_plate' => $record['plate no'],
                    'temp_vehicle_detail' => $record['car make-model & year'],
                    'investor_id' => $record['investor id'],
                    'vehicletypes' => $record['vehicle type id'],
                ]);
            }
        }
        return redirect()->back()->with('success', 'CSV imported successfully!');

    }

    public function csvSample()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="sample.csv"',
        ];

        $columns = ['plate no', 'car make-model & year', 'investor id', 'vehicle type id'];

        $rows = [
            ['ABC-123', 'SUZUKI ERTIGA 2023', '1', '1'],
            ['ACS-093', 'NISSAN SUNNY 2024', '1', '1'],
        ];

        // CSV banana
        $callback = function() use ($columns, $rows) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($rows as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
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
        // $investor= User::has('investor')->with('investor')->get();
        $vehicle= Vehicle::find($id);
        if(!$vehicle){ return redirect()->route('admin.vehicle.index')->with('error', 'Vehicle Not Found'); }
        $vehicletypes= Vehicletype::all();
        $investor= Investor::all();
        return view("admin.vehicle.edit", compact('vehicle', 'vehicletypes', 'investor'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $vehicle= Vehicle::find($id);
        $validator= Validator::make($request->all(), [
            'vehicle_name' => 'required',
            'vehicletypes' => 'required',
            'number_plate' => 'required|unique:vehicles,number_plate,'.$id,
            'investor_id' => 'required',
            'car_make' => 'required',
            'year' => 'required'
        ]);

        if($validator->fails()){
            return redirect()->back()->withErrors($validator->messages())->withInput();
        }
        else{
            try {
                $vehicle->update([
                    'vehicle_name' => $request['vehicle_name'],
                    'vehicletypes' => $request['vehicletypes'],
                    'investor_id' => $request['investor_id'],
                    'car_make' => $request['car_make'],
                    'year' => $request['year'],
                    'number_plate' => $request['number_plate'],
                    'status' => $request['status'],
                ]);
                return redirect()->route('admin.vehicle.index')->with('success', 'Vehicle Updated Against Investor Successfully!');
            } catch (\Exception $exp) {
                return redirect()->back()->with('error', $exp->getMessage());
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $vehicle= Vehicle::find($id);
            if(!$vehicle) {
                return response()->json(['error' => 'Vehicle Not Found']);
            } else {
                $vehicle->delete();
                return response()->json(['success' => 'Vehicle Deleted Successfully!'], 200);
            }
        } catch (Exeption $exp) {
            return response()->json(['error' => $exp->getMessage()], 500);
        }
    }
}

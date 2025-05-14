<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VehicleStatus;
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
        $status= VehicleStatus::all();
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
                VehicleStatus::create([
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
        $status= VehicleStatus::find($id);
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
                $status= VehicleStatus::find($id);
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
        $status= VehicleStatus::find($id);
        if(!$status){
            return redirect()->back()->with('error', 'Status Not Found');
        }else{
            $status->delete();
            return redirect()->back()->with('success', 'Status Deleted Successfully!');
        }
    }
}

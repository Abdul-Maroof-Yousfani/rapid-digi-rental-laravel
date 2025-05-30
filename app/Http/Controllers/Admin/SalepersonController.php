<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SalePerson;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SalepersonController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $salePerson= SalePerson::all();
        return view('admin.saleperson.index', compact('salePerson'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.saleperson.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator= Validator::make($request->all(), [
            'name' => 'required',
            'status' => 'required',
        ]);

        if($validator->fails()){
            if ($request->ajax()) { return response()->json(['errors' => $validator->errors()], 422); }
            else { return redirect()->back()->withErrors($validator->messages())->withInput(); }
        }else{
            try {
                DB::beginTransaction();
                $salemen = SalePerson::create([
                    'name' => $request->name,
                    'status' => $request->status,
                ]);
                DB::commit();
                if ($request->ajax()) {
                    return response()->json(['success' => 'Sale Men Added Successfully!', 'data' => $salemen]);
                } else {
                    return redirect()->route('admin.sale-person.index')->with('success', 'Sale Person Added Successfully!');
                }
            } catch (\Exception $exp) {
                DB::rollBack();
                if ($request->ajax()) {
                    return response()->json(['error' => $exp->getMessage()], 500);
                } else {
                    return redirect()->back()->with('error', $exp->getMessage());
                }
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
        $salePerson= SalePerson::find($id);
        if(!$salePerson){
            return redirect()->route('admin.sale-person.index')->with('error', 'Sale Person Not Found');
        }
        return view('admin.saleperson.edit', compact('salePerson'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validator= Validator::make($request->all(), [
            'name' => 'required',
            'status' => 'required',
        ]);

        if($validator->fails()){
            return redirect()->back()->withErrors($validator->messages())->withInput();
        }else{
            try {
                DB::beginTransaction();
                $salePerson= SalePerson::find($id);
                $salePerson->update([
                    'name' => $request->name,
                    'status' => $request->status,
                ]);
                DB::commit();
                if($request->ajax()){
                    return response()->json(['success' => 'Sale Person Updated Successfully', 'data' => $salePerson]);
                } else {
                    return redirect()->route('admin.sale-person.index')->with('success', 'Sale Person Updated Successfully!');
                }
            } catch (\Exception $exp) {
                DB::rollBack();
                if($request->ajax()){
                    return response()->json(['error' => $exp->getMessage()]);
                } else {
                    return redirect()->back()->with('error', $exp->getMessage());
                }
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $salePerson= SalePerson::find($id);
            if(!$salePerson){
                return response()->json(['error' => 'Sale Person Not Found']);
                // return redirect()->back()->with('error', 'Sale Person Not Found');
            }else{
                $salePerson->delete();
                return response()->json(['success' => 'Sale Person Deleted Successfully!']);
                // return redirect()->back()->with('success', 'Sale Person Deleted Successfully!');
            }
        } catch (Exeption $exp) {
            return response()->json(['error' => 'Internal Server Error!']);
        }
    }
}

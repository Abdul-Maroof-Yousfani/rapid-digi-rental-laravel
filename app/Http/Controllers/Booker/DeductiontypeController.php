<?php

namespace App\Http\Controllers\Booker;

use App\Http\Controllers\Controller;
use App\Models\Deductiontype;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DeductiontypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        $deductionType = Deductiontype::paginate(10);
        return view('booker.deductiontype.index', compact('deductionType'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('booker.deductiontype.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $rules = [
            'name' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $errormessage = implode('\n', $validator->errors()->all());
            return redirect()->back()->with('error', $errormessage)->withInput();
        } else {
            try {
                DB::beginTransaction();
                // return $request['name'];

                Deductiontype::create([
                    'name' => $request['name'],
                    'status' => $request['status'],
                ]);
                DB::commit();
                return redirect()->route('invoice-type.index')->with('success', 'Invoice Type Created Successfully.')->withInput();
            } catch (\Exception $exp) {
                DB::rollback();
                return redirect()->back()->with('error', $exp->getMessage())->withInput();;
            }
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Deductiontype $deductiontype)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $type = Deductiontype::find($id);
        return view('booker.deductiontype.edit', compact('type'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $deductionType = Deductiontype::find($id);
        $validator = Validator::make($request->all(), [
            'name' => 'required',
        ]);
        // return $request;
        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['error' => $validator->errors()], 422);
            } else {
                $errorMessages = implode("\n", $validator->errors()->all());
                return redirect()->back()->with('error', $errorMessages)->withInput();
            }
        } else {
            try {
                DB::beginTransaction();
                $deductionType->update([
                    'name' => $request['name'],
                    'status' => $request['status'],
                ]);
                DB::commit();
                if ($request->ajax()) {
                    return response()->json(['success' => 'Invoice Type Updated Successfully!', 'data' => $deductionType], 200);
                } else {
                    return redirect()->route('invoice-type.index')->with('success', 'Invoice Type Updated Successfully!');
                }
            } catch (\Exception $exp) {
                DB::rollBack();
                if ($request->ajax()) {
                    return response()->json(['error' => $exp->getMessage()], 422);
                } else {
                    return redirect()->back()->withErrors('error', $exp->getMessage())->withInput();
                }
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {

        $deductionType = Deductiontype::find($id);
        if (!$deductionType) {
            // return response()->json(['error' => 'Invoice Type Not Found']);
            return redirect()->route('invoice-type.index')->with('error', 'Invoice Type Not Found');
        } else {
            $deductionType->forceDelete();
            // return response()->json(['success' => 'Invoice Type Deleted Successfully!']);
            return redirect()->route('invoice-type.index')->with('success', 'Invoice Type Deleted Successfully!');
        }
    }
}

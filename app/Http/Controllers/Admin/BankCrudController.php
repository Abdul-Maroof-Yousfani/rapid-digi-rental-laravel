<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use QueryException;
use App\Models\Bank;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class BankCrudController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $bank= Bank::where('created_at', '>=', Carbon::now()->subdays(15))->orderBy('id', 'DESC')->get();
        return view('admin.bank.index', compact('bank'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.bank.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator= Validator::make($request->all(), [
            'bank_name' => 'required',
            'account_name' => 'required',
            'account_no' => 'required|unique:banks,account_number',
            'iban' => 'required',
        ]);
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
                $bank = Bank::create([
                    'bank_name' => $request['bank_name'],
                    'account_name' => $request['account_name'],
                    'account_number' => $request['account_no'],
                    'iban' => $request['iban'],
                    'swift_code' => $request['swift_code'],
                    'branch' => $request['branch'],
                    'currency' => $request['currency'],
                    'notes' => $request['notes'],
                ]);
                DB::commit();
                if ($request->ajax()) {
                    return response()->json(['success' => 'Bank Added Successfully!', 'data' => $bank]);
                } else {
                    return redirect()->route('admin.bank.index')->with('success', 'Bank Added Successfully!');
                }
            } catch (\Exeption $exp) {
                DB::rollBack();
                if ($request->ajax()) {
                    return response()->json(['error' => $exp->getMessage()], 500);
                } else {
                    return redirect()->back()->withErrors('error', $exp->getMessage())->withInput();
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
        $bank= Bank::find($id);
        return view('admin.bank.edit', compact('bank'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $bank= Bank::find($id);
        $validator= Validator::make($request->all(), [
            'bank_name' => 'required',
            'account_name' => 'required',
            'account_no' => 'required|unique:banks,account_number,'. $bank->id,
            'iban' => 'required',
        ]);
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
                $bank->update([
                    'bank_name' => $request['bank_name'],
                    'account_name' => $request['account_name'],
                    'account_number' => $request['account_no'],
                    'iban' => $request['iban'],
                    'swift_code' => $request['swift_code'],
                    'branch' => $request['branch'],
                    'currency' => $request['currency'] ?? 'AED',
                    'notes' => $request['notes'],
                ]);
                DB::commit();
                if ($request->ajax()) {
                    return response()->json(['success' => 'Bank Updated Successfully!', 'data' => $bank], 200);
                } else {
                    return redirect()->route('admin.bank.index')->with('success', 'Bank Updated Successfully!');
                }
            } catch (\Exeption $exp) {
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
        try {
            $bank= Bank::find($id);
            if(!$bank){
                return response()->json(['error' => 'Bank Not Found']);
            }else{
                $bank->forceDelete();
                return response()->json(['success' => 'Bank Deleted Successfully!']);
            }
        } catch (QueryException $e) {
            if ($e->getCode() === '23000') {
                return response()->json([
                    'error' => 'Cannot delete bank because it is referenced in payments.'
                ], 400);
            } else {
                return response()->json([
                    'error' => 'Database error occurred. ' . $e->getMessage()
                ], 500);
            }
        }
    }
}

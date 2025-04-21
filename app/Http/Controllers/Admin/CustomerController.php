<?php

namespace App\Http\Controllers\Admin;

use permission;
use App\Models\Customer;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class CustomerController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:manage customers'])->only(['index','create', 'store', 'edit', 'update', 'destroy']);
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $customers= Customer::all();
        return view('admin.customer.index', compact('customers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.customer.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator= Validator::make($request->all(), [
            'customer_name' => 'required',
            'email' => 'nullable|email|unique:customers,email',
            'phone' => 'required|unique:customers,phone',
            'licence' => 'required|unique:customers,licence',
            'gender' => 'required',
            'cnic' => 'required',
            'dob' => 'required',
            'address' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }else{
            try {
                Customer::create([
                    'customer_name' => $request['customer_name'],
                    'email' => $request['email'],
                    'phone' => $request['phone'],
                    'licence' => $request['licence'],
                    'cnic' => $request['cnic'],
                    'dob' => $request['dob'],
                    'address' => $request['address'],
                    'gender' => $request['gender'],
                    'city' => $request['city'],
                    'state' => $request['state'],
                    'country' => $request['country'],
                    'postal_code' => $request['postal_code'],
                    'status' => $request['status'],
                ]);
                return redirect()->route(auth()->user()->hasRole('admin') ? 'admin.customer.index' : 'booker.customer.index')->with('success', 'Customer Added Successfully!');
            } catch (\Exception $exp) {
                return redirect()->back()->with('error', $exp->getMessage());
            }
        }

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $customer= Customer::find($id);
        if (!$customer) {
            return response()->json(['error' => 'Customer not found'], 404);
        }
        return response()->json([
            'user' => $customer,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $customers= Customer::find($id);
        if($customers){ return view('admin.customer.edit', compact('customers')); }
        else{ return redirect()->route(auth()->user()->hasRole('admin') ? 'admin.customer.index' : 'booker.customer.index')->with('error', 'Customer Not Found !'); }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validator= Validator::make($request->all(), [
            'customer_name' => 'required',
            'email' => 'nullable|email|unique:customers,email,'.$id,
            'phone' => 'required|unique:customers,phone,'.$id,
            'licence' => 'required|unique:customers,licence,'.$id,
            'cnic' => 'required',
            'dob' => 'required',
            'address' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        else{
            try {
                $customer = Customer::findOrFail($id);
                $customer->update([
                    'customer_name' => $request['customer_name'],
                    'email' => $request['email'],
                    'phone' => $request['phone'],
                    'licence' => $request['licence'],
                    'cnic' => $request['cnic'],
                    'dob' => $request['dob'],
                    'address' => $request['address'],
                    'gender' => $request['gender'],
                    'city' => $request['city'],
                    'state' => $request['state'],
                    'country' => $request['country'],
                    'postal_code' => $request['postal_code'],
                    'status' => $request['status'],
                ]);
                return redirect()->route(auth()->user()->hasRole('admin') ? 'admin.customer.index' : 'booker.customer.index')->with('success', 'Customer Updated Successfully!');

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
        $customers= Customer::find($id);
        if($customers){
            $customers->delete();
            return redirect()->route(auth()->user()->hasRole('admin') ? 'admin.customer.index' : 'booker.customer.index')->with("success", "Customer Deleted Successfully !");
        } else{
            return redirect()->route(auth()->user()->hasRole('admin') ? 'admin.customer.index' : 'booker.customer.index')->with('error', 'Customer Not Found !');
        }
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Investor;
use App\Models\Vehicletype;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class InvestorCrudController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:manage investors');
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $investors= Investor::all();
        return view('admin.investor.index', compact('investors'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.investor.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator= Validator::make($request->all(), [
            'investor_name' =>  'required',
            'email' =>  'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
            'password_confirmation' => 'required|min:8',
            'phone' =>  'required|unique:investors,phone',
            'gender' => 'required',
            'cnic' => 'required',
            'address' => 'required'
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        else{
            try {
                $user= User::create([
                    'name' => $request['investor_name'],
                    'email' => $request['email'],
                    'password' => Hash::make($request['password']),
                ]);
    
                $user->assignRole('investor');
                $user->givePermissionTo(['manage vehicles']);

                Investor::create([
                    'user_id' => $user->id,
                    'name' => $request['investor_name'],
                    'phone' => $request['phone'],
                    'address' => $request['address'],
                    'gender' => $request['gender'],
                    'dob' => $request['dob'],
                    'cnic' => $request['cnic'],
                    'postal_code' => $request['postal_code'],
                    'city' => $request['city'],
                    'state' => $request['state'],
                    'country' => $request['country'],
                    'status' => $request['status'],
                ]);
    
                return redirect()->route('admin.investor.index')->with('success', 'Investor Added Successfully');
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
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $investor= Investor::find($id);
        if(!$investor){
            return redirect()->route('admin.investor.index')->with('error', 'Investor Not Found');
        }
        return view('admin.investor.edit', compact('investor'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $investor= Investor::find($id);
        if(!$investor){
            return redirect()->route('admin.investor.index')->with('error', 'Investor Not Found');
        }
        $user= User::find($investor->user_id);
        $validator= Validator::make($request->all(), [
            'investor_name' =>  'required',
            'email' =>  'required|email|unique:users,email,'. $investor->user_id,
            'phone' =>  'required|unique:investors,phone,'. $id,
            'gender' => 'required',
            'cnic' => 'required',
            'address' => 'required'
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        else{
            try {
                $user->update([
                    'name' => $request['investor_name'],
                    'email' => $request['email'],
                ]);
    
                $user->assignRole('investor');
                $investor->update([
                    'user_id' => $user->id,
                    'name' => $request['investor_name'],
                    'phone' => $request['phone'],
                    'address' => $request['address'],
                    'gender' => $request['gender'],
                    'dob' => $request['dob'],
                    'cnic' => $request['cnic'],
                    'postal_code' => $request['postal_code'],
                    'city' => $request['city'],
                    'state' => $request['state'],
                    'country' => $request['country'],
                    'status' => $request['status'],
                ]);
    
                return redirect()->route('admin.investor.index')->with('success', 'Investor Updated Successfully');
            } catch (\Exception $exp) {
                return redirect()->back()->with('error', 'Something went wrong'.$exp->getMessage());
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $investor= Investor::findOrFail($id);
        // $investor= User::find($user_id);
        if(!$investor){
            return redirect()->route('admin.investor.index')->with('error', 'Investor Not Found');
        }else{
            $investor->vehicle->each->delete();
            $investor->delete();
            $investor->user->delete();
            return redirect()->route('admin.investor.index')->with('success', 'Investor Deleted with Vehicle Successfully');
        }
    }
}

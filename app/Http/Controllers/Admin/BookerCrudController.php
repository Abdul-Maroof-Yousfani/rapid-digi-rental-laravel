<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Booker;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class BookerCrudController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:manage bookers');
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $bookers= Booker::all();
        return view("admin.booker.index", compact('bookers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view("admin.booker.create");
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator= Validator::make($request->all(), [
            'booker_name' =>  'required',
            'email' =>  'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
            'password_confirmation' => 'required|min:8',
            'phone' =>  'required|unique:bookers,phone',
            'gender' => 'required',
            'cnic' => 'required',
            'address' => 'required'
        ]);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        } else {
            try {
                $user= User::create([
                    'name'=> $request['booker_name'],
                    'email'=> $request['email'],
                    'password'=> Hash::make($request['password']),
                ]);

                $user->assignRole('booker');

                Booker::create([
                    'user_id' => $user->id,
                    'name' => $request['booker_name'],
                    'phone' => $request['phone'],
                    'address' => $request['address'],
                    'gender' => $request['gender'],
                    'dob' => $request['dob'],
                    'cnic' => $request['cnic'],
                    'city' => $request['city'],
                    'postal_code' => $request['postal_code'],
                    'state' => $request['state'],
                    'country' => $request['country'],
                    'status' => $request['status'],
                ]);

                return redirect()->route('admin.booker.index')->with('success', 'Booker Added Successfully!')->withInput();
            } catch (\Exception $exp){
                return redirect()->back()->withErrors('error', $exp->getMessage());
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
        $booker= Booker::find($id);
        if(!$booker){
            return redirect()->route('admin.booker.index')->with('error', 'Booker Not Found');
        }
        return view('admin.booker.edit', compact('booker'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        dd($request->all());
        $booker= Booker::find($id);
        if(!$booker){
            return redirect()->route('admin.booker.index')->with('error', 'Booker Not Found');
        }
        $user= User::find($booker->user_id);
        $validator= Validator::make($request->all(), [
            'booker_name' =>  'required',
            'email' =>  'required|email|unique:users,email,'. $booker->user_id,
            'password' => 'nullable|min:8',
            'phone' =>  'required|unique:bookers,phone,'. $id,
            'gender' => 'required',
            'cnic' => 'required',
            'address' => 'required'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        } else{
            // // Handle password update
            // if ($request->filled('password')) {
            //     $data['password'] = bcrypt($request->password);
            // }
            // $user->update([
            //     'name' => $request['booker_name'],
            //     'email' => $request['email'],
            //     'password' => bcrypt($request->password),
            // ]);


            $updateData = [
                'name' => $request->booker_name,
                'email' => $request->email,
            ];

            if ($request->filled('password')) {
                $updateData['password'] = bcrypt($request->password);
            }

            $user->update($updateData);

            $user->assignRole('booker');

            $booker->update([
                'user_id' => $user->id,
                'name' => $request['booker_name'],
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

            return redirect()->route('admin.booker.index')->with('success', 'Booker Updated Successfully');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $booker= Booker::findOrFail($id);
        if(!$booker){
            return redirect()->route('admin.booker.index')->with('error', 'booker Not Found');
        }else{
            $booker->delete();
            $booker->user->delete();
            return redirect()->route('admin.booker.index')->with('success', 'booker Deleted Successfully');
        }
    }
}

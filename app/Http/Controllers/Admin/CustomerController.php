<?php

namespace App\Http\Controllers\Admin;

use permission;
use Carbon\Carbon;
use App\Models\Customer;
use Illuminate\Http\Request;
use App\Services\ZohoInvoice;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\QueryException;
use Throwable;

class CustomerController extends Controller
{

    protected $zohoinvoice;

    public function __construct(ZohoInvoice $zohoinvoice)
    {
        $this->zohoinvoice = $zohoinvoice;
        // $this->middleware(['permission:manage customers'])->only(['index','create', 'store', 'edit', 'update', 'destroy']);

        $this->middleware('permission:view customer')->only(['index']);
        $this->middleware('permission:create customer')->only(['create', 'store']);
        $this->middleware('permission:edit customer')->only(['edit', 'update']);
        $this->middleware('permission:delete customer')->only(['destroy']);
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $zohoCustomers = $this->zohoinvoice->getAllCustomers();
        } catch (\Exception $e) {
            return response()->view('sitedown-error', [], 500);
        }
        $zohoCustomers = $this->zohoinvoice->getAllCustomers();
        $dbCustomerIds = Customer::pluck('zoho_customer_id')->toArray();
        $missing = [];
        foreach ($zohoCustomers as $customer) {
            if (!in_array($customer['contact_id'], $dbCustomerIds)) {
                $missing[] = $customer['contact_id'];
            }
        }
        $shouldEnableSync = count($missing) > 0;
        $customers= Customer::where('created_at', '>=', Carbon::now()->subDays(15))->orderBy('id', 'DESC')->get();
        $customers = Customer::orderBy('id', 'DESC')->paginate(10);
        return view('admin.customer.index', compact('customers', 'shouldEnableSync'));
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
        $validator = Validator::make($request->all(), [
            'customer_name' => 'required',
            'email' => 'nullable|email|unique:customers,email',
            'phone' => 'required|unique:customers,phone',
            'licence' => 'nullable|unique:customers,licence',
            // 'gender' => 'required',
            // 'cnic' => 'required',
            // 'trn_no' => 'required',
            // 'dob' => 'required',
            'address' => 'required',
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['error' => $validator->errors()], 422);
            } else {
                return redirect()->back()->withErrors($validator)->withInput();
            }
        } else {
            $customer_name = $request->customer_name;
            $status = $request->status;
            $email = $request->email;
            $phone = $request->phone;
            $address = $request->address;
            $city = $request->city;
            $state = $request->state;
            $country = $request->country;
            $postal_code = $request->postal_code;
            $billing_address = [
                'address' => $address,
                'city' => $city,
                'state' => $state,
                'country' => $country,
                'zip' => $postal_code,
            ];
            $contact_person = [
                [
                    'email' => $email,
                    'phone' => $phone,
                ]
            ];
            $contact = $this->zohoinvoice->searchCustomer($email, $phone);
            if ($contact) {
                $customerId = $contact['contact_id']; // already exists in Zoho
            } else {
                $customerResponse = $this->zohoinvoice->createCustomer($customer_name, $status, $contact_person, $billing_address);
                $customerId = $customerResponse['contact']['contact_id'];
            }
            if (isset($customerId)) {
                // try {
                $customer = Customer::create([
                    'zoho_customer_id' => $customerId,
                    'customer_name' => $customer_name,
                    'email' => $email,
                    'phone' => $phone,
                    'licence' => $request['licence'],
                    'cnic' => $request['cnic'] ?? null,
                    // 'trn_no' => $request['trn_no'],
                    'dob' => null,
                    'address' => $address,
                    'gender' => null,
                    'city' => $city,
                    'state' => $state,
                    'country' => $country,
                    'postal_code' => $postal_code,
                    'status' => $status,
                ]);
                if ($request->ajax()) {
                    return response()->json(['success' => 'Customer Added Successfully!', 'data' => $customer]);
                } else {
                    return redirect()->route(auth()->user()->hasRole('admin') ? 'admin.customer.index' : 'booker.customer.index')->with('success', 'Customer Added Successfully!');
                }
                // } catch (\Exception $exp) {
                //     if ($request->ajax()){
                //         return response()->json(['error' => $exp->getMessage()]);
                //     } else {
                //         return redirect()->back()->with('error', $exp->getMessage());
                //     }
                // }
            } else {
                return redirect()->back()->withErrors('error', 'Customer ID Not Fetch')->withInput();
            }
        }

    }

    // public function syncCustomersFromZoho()
    // {
    //     $contact= $this->zohoinvoice->getAllCustomers();
    //     try {
    //         DB::beginTransaction();
    //         foreach ($contact as $customer) {
    //             $exists = Customer::where('zoho_customer_id', $customer['contact_id'])->exists();
    //             if ($exists) {
    //                 continue;
    //             }

    //             $fullDetail= $this->zohoinvoice->getCustomerDetail($customer['contact_id']);
    //             $billing = $fullDetail['contact']['billing_address'] ?? [];
    //             Customer::create([
    //                 'zoho_customer_id' => $customer['contact_id'],
    //                 'customer_name' => $customer['contact_name'],
    //                 'email' => $customer['email'] ?? null,
    //                 'phone' => $customer['phone'] ?? null,
    //                 'address' => $billing['address'] ?? null,
    //                 'city' => $billing['city'] ?? null,
    //                 'state' => $billing['state'] ?? null,
    //                 'country' => $billing['country'] ?? null,
    //                 'postal_code' => $billing['zip'] ?? null,
    //                 'status' => 1,
    //                 'gender' => null,
    //                 'cnic' => null,
    //                 'dob' => null,
    //                 'licence' => null,
    //             ]);
    //         }
    //         DB::commit();
    //         return redirect()->back()->with('success', 'Customers synced successfully with Rapid System.');
    //     } catch (QueryException $e) {
    //         DB::rollBack();
    //         if ($e->getCode() == 23000) {
    //             return redirect()->back()->with('error', $e->getMessage());
    //         }

    //         return redirect()->back()->with('error', 'Database error occurred.');
    //     } catch (Exception $exp) {
    //         dd($exp);
    //         DB::rollback();
    //         return redirect()->back()->with('error', $exp->getMessage());
    //     }
    // }


    public function syncCustomersFromZoho()
    {
        $contact = $this->zohoinvoice->getAllCustomers();
        try {
            DB::beginTransaction();

            foreach ($contact as $customer) {
                $existingCustomer = Customer::withTrashed()
                    ->where('zoho_customer_id', $customer['contact_id'])
                    ->first();

                $fullDetail = $this->zohoinvoice->getCustomerDetail($customer['contact_id']);

                $billing = $fullDetail['contact']['billing_address'] ?? [];

                // If customer exists (even soft-deleted)
                if ($existingCustomer) {
                    if ($existingCustomer->trashed()) {
                        // Restore and update
                        $existingCustomer->restore();
                        $existingCustomer->update([
                            'customer_name' => $customer['contact_name'],
                            'email' => $customer['email'] ?? null,
                            'phone' => $customer['phone'] ?? null,
                            'trn_no' => $fullDetail['contact']['tax_reg_no'] ?? null,
                            'address' => $billing['address'] ?? null,
                            'city' => $billing['city'] ?? null,
                            'state' => $billing['state'] ?? null,
                            'country' => $billing['country'] ?? null,
                            'postal_code' => $billing['zip'] ?? null,
                            'status' => 1,
                        ]);
                    }

                    continue; // Skip create
                }

                // If completely new customer
                Customer::create([
                    'zoho_customer_id' => $customer['contact_id'],
                    'customer_name' => $customer['contact_name'],
                    'email' => $customer['email'] ?? null,
                    'phone' => $customer['phone'] ?? null,
                    'address' => $billing['address'] ?? null,
                    'trn_no' => $fullDetail['contact']['tax_reg_no'] ?? null,
                    'city' => $billing['city'] ?? null,
                    'state' => $billing['state'] ?? null,
                    'country' => $billing['country'] ?? null,
                    'postal_code' => $billing['zip'] ?? null,
                    'status' => 1,
                    'gender' => null,
                    'cnic' => null,
                    'dob' => null,
                    'licence' => null,
                ]);
            }

            DB::commit();
            return redirect()->back()->with('success', 'Customers synced successfully with Rapid System.');

        } catch (QueryException $e) {
            DB::rollBack();

            if ($e->getCode() == 23000) {
                return redirect()->back()->with('error', 'Duplicate entry found. Possibly same phone, email, or licence already exists.');
            }

            return redirect()->back()->with('error', 'Database error occurred.');
        } catch (\Exception $exp) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Something went wrong: ' . $exp->getMessage());
        }
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $customer = Customer::find($id);
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
        $customers = Customer::find($id);
        if ($customers) {
            return view('admin.customer.edit', compact('customers'));
        } else {
            return redirect()->route(auth()->user()->hasRole('admin') ? 'admin.customer.index' : 'booker.customer.index')->with('error', 'Customer Not Found !');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'customer_name' => 'required',
            'email' => 'nullable|email|unique:customers,email,' . $id,
            'phone' => 'required|unique:customers,phone,' . $id,
            'licence' => 'nullable|unique:customers,licence,' . $id,
            // 'cnic' => 'required',
            // 'trn_no' => 'required',
            // 'dob' => 'required',
            'address' => 'required',
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['error' => $validator->errors()], 422);
            } else {
                return redirect()->back()->withErrors($validator)->withInput();
            }
        } else {
            $customer_name = $request->customer_name;
            $status = $request->status;
            $email = $request->email;
            $phone = $request->phone;
            $address = $request->address;
            $city = $request->city;
            $state = $request->state;
            $country = $request->country;
            $postal_code = $request->postal_code;
            $billing_address = [
                'address' => $address,
                'city' => $city,
                'state' => $state,
                'country' => $country,
                'zip' => $postal_code,
            ];
            $contact_person = [
                [
                    'email' => $email,
                    'phone' => $phone,
                ]
            ];
            $response = $this->zohoinvoice->updateCustomer($id, $customer_name, $status, $contact_person, $billing_address);
            try {
                $customer = Customer::findOrFail($id);
                $customer->update([
                    'customer_name' => $customer_name,
                    'email' => $email,
                    'phone' => $phone,
                    'licence' => $request['licence'],
                    'cnic' => $request['cnic'] ?? null,
                    // 'trn_no' => $request['trn_no'],
                    'dob' => null,
                    'address' => $address,
                    'gender' => null,
                    'city' => $city,
                    'state' => $state,
                    'country' => $country,
                    'postal_code' => $postal_code,
                    'status' => $status,
                ]);
                if ($request->ajax()) {
                    return response()->json(['success' => 'Customer Updated Successfully!', 'data' => $customer], 200);
                } else {
                    return redirect()->route(auth()->user()->hasRole('admin') ? 'admin.customer.index' : 'booker.customer.index')->with('success', 'Customer Updated Successfully!');
                }
            } catch (\Exception $exp) {
                if ($request->ajax()) {
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
            $customers = Customer::find($id);
            if ($customers) {
                $customers->delete();
                return response()->json(['success' => 'Customer Deleted Successfully!'], 200);
            } else {
                return response()->json(['error' => 'Customer Not Found!'], 404);
            }
        } catch (\Exception $exp) {
            return response()->json(['error' => $exp->getMessage()], 500);
        }
    }
}

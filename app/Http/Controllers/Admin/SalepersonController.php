<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SalePerson;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\QueryException;
use App\Services\ZohoInvoice;


class SalepersonController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    protected $zohoinvoice;

    public function __construct(ZohoInvoice $zohoinvoice)
    {
        $this->zohoinvoice = $zohoinvoice;

    }


    public function syncSalespersonFromZoho()
    {
        try {
            $salespersons = $this->zohoinvoice->getAllSalespersons();
            
            // Check if no salespersons were returned
            if (empty($salespersons)) {
                return redirect()->back()->with('warning', 
                    'No salespersons found in Zoho. This may be due to: ' .
                    '1) The salespersons endpoint is not available in your Zoho Billing plan, ' .
                    '2) OAuth scope does not include salespersons access, or ' .
                    '3) No salespersons exist in your Zoho organization. ' .
                    'Please check your Zoho Billing API documentation and OAuth scopes.'
                );
            }
            
            DB::beginTransaction();
            $syncedCount = 0;
            $updatedCount = 0;
            $createdCount = 0;

            foreach ($salespersons as $sp) {
                // Validate required fields
                if (!isset($sp['salesperson_id']) || !isset($sp['salesperson_name'])) {
                    \Log::warning('Zoho salesperson missing required fields', ['data' => $sp]);
                    continue;
                }
                
                $existingSalesPerson = SalePerson::withTrashed()
                    ->where('zoho_salesperson_id', $sp['salesperson_id'])
                    ->first();

                if ($existingSalesPerson) {
                    if ($existingSalesPerson->trashed()) {
                        $existingSalesPerson->restore();
                    }

                    $existingSalesPerson->update([
                        'name' => $sp['salesperson_name'],
                        'email' => $sp['salesperson_email'] ?? null,
                        'status' => '1',
                    ]);
                    $updatedCount++;
                } else {
                    SalePerson::create([
                        'zoho_salesperson_id' => $sp['salesperson_id'],
                        'name' => $sp['salesperson_name'],
                        'email' => $sp['salesperson_email'] ?? null,
                        'status' => '1',
                    ]);
                    $createdCount++;
                }
                $syncedCount++;
            }

            DB::commit();
            
            $message = "Salespersons synced successfully! ";
            $message .= "Total: {$syncedCount}, ";
            $message .= "Created: {$createdCount}, ";
            $message .= "Updated: {$updatedCount}.";
            
            return redirect()->back()->with('success', $message);

        } catch (QueryException $e) {
            DB::rollBack();
            if ($e->getCode() == 23000) {
                return redirect()->back()->with('error', 'Duplicate entry found.');
            }
            return redirect()->back()->with('error', 'Database error occurred: ' . $e->getMessage());
        } catch (\Exception $exp) {
            DB::rollBack();
            \Log::error('Zoho syncSalespersonFromZoho error', [
                'error' => $exp->getMessage(),
                'trace' => $exp->getTraceAsString()
            ]);
            return redirect()->back()->with('error', 'Failed to sync salespersons: ' . $exp->getMessage());
        }
    }


    public function index()
    {
        $salePerson = SalePerson::all();
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
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'status' => 'required',
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['errors' => $validator->errors()], 422);
            } else {
                return redirect()->back()->withErrors($validator->messages())->withInput();
            }
        } else {
            try {
                DB::beginTransaction();
                $salemen = SalePerson::create([
                    'name' => $request->name,
                    'status' => $request->status,
                    'is_manager' => $request->has('is_manager') ? 1 : 0,
                ]);
                DB::commit();
                if ($request->ajax()) {
                    return response()->json(['success' => 'Salesperson Added Successfully!', 'data' => $salemen]);
                } else {
                    return redirect()->route('sale-person.index')->with('success', 'Salesperson Added Successfully!');
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
        $salePerson = SalePerson::find($id);
        if (!$salePerson) {
            return redirect()->route('sale-person.index')->with('error', 'Sale Person Not Found');
        }
        return view('admin.saleperson.edit', compact('salePerson'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'status' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator->messages())->withInput();
        } else {
            try {
                DB::beginTransaction();
                $salePerson = SalePerson::find($id);
                $salePerson->update([
                    'name' => $request->name,
                    'status' => $request->status,
                    'is_manager' => $request->has('is_manager') ? 1 : 0,
                ]);
                DB::commit();
                if ($request->ajax()) {
                    return response()->json(['success' => 'Sale Person Updated Successfully', 'data' => $salePerson]);
                } else {
                    return redirect()->route('sale-person.index')->with('success', 'Sale Person Updated Successfully!');
                }
            } catch (\Exception $exp) {
                DB::rollBack();
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
            $salePerson = SalePerson::find($id);
            if (!$salePerson) {
                return response()->json(['error' => 'Sale Person Not Found']);
                // return redirect()->back()->with('error', 'Sale Person Not Found');
            } else {
                $salePerson->delete();
                return response()->json(['success' => 'Sale Person Deleted Successfully!']);
                // return redirect()->back()->with('success', 'Sale Person Deleted Successfully!');
            }
        } catch (Exeption $exp) {
            return response()->json(['error' => 'Internal Server Error!']);
        }
    }
}

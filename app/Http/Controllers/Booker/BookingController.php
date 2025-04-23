<?php

namespace App\Http\Controllers\Booker;

use App\Models\Vehicle;
use App\Models\Customer;
use App\Models\Vehicletype;
use Illuminate\Http\Request;
use App\Services\ZohoService;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;

class BookingController extends Controller
{
    protected $zohoService;
    public function __construct(ZohoService $zohoService)
    {
        $this->zohoService= $zohoService;
        $this->middleware(['permission:manage booking'])->only(['index','create', 'store', 'edit', 'update', 'destroy']);
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        dd($this->zohoService->getAccessToken());
        return view('booker.booking.index');

        // $clientID = config('services.zoho.client_id');
        // $clientSecret = config('services.zoho.client_secret');
        // $redirectUri = config('services.zoho.redirect_uri');
        // $refreshToken = "1000.4f137b39002c78113a04602a1e77ab26.e8c266a2a304861e19a5b79aee2d00b9";
        // $grandtype = "refresh_token";
        
        // $response = Http::withOptions(['verify' => false])->asForm()->post('https://accounts.zoho.com/oauth/v2/token', [
        //     'refresh_token' => $refreshToken,
        //     'client_id' => $clientID,
        //     'client_secret' => $clientSecret,
        //     'redirect_uri' => $redirectUri,
        //     'grant_type' => 'refresh_token',
        // ]);
        

        // return $response;

    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $customers= Customer::all();
        $vehicletypes= Vehicletype::all();
        return view('booker.booking.create', compact('customers', 'vehicletypes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if($request->all()){
            dd($request->all());
            $data= [
                'status' => true,
                'msg' => 'Data Received'
            ];
        } else {
            $data= [
                'status' => false,
                'msg' => 'Data Not Received'
            ];
            return response()->json($data);
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
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}

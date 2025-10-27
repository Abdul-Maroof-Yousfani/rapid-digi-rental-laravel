<?php

namespace App\Http\Controllers\Booker;

use App\Http\Controllers\Controller;
use App\Models\VehiclesImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Services\ZohoService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VehicleUploadController extends Controller
{
    protected $zohoinvoice;

    public function __construct(ZohoService $zohoinvoice)
    {
        $this->zohoinvoice = $zohoinvoice;
    }

    public function uploadVehicles(Request $request)
    {
        try {
            ini_set('max_execution_time', 0);
            ini_set('memory_limit', '-1');

            $import = new VehiclesImport();
            Excel::import($import, $request->file('xlsx_file'));

            return back()->with('success', 'Vehicles processed successfully.');
        } catch (\Exception $e) {
            Log::error('Vehicle upload failed: ' . $e->getMessage());
            return back()->with('error', 'Upload failed: ' . $e->getMessage());
        }
    }


}
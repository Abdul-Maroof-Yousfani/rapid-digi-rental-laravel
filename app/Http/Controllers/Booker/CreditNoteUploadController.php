<?php

namespace App\Http\Controllers\Booker;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\CreditNotesImport;
use App\Models\Booking;
use App\Models\Invoice;
use App\Models\BookingData;
use App\Models\Deposit;
use App\Models\Vehicle;
use App\Models\Notification;
use App\Services\ZohoService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CreditNoteUploadController extends Controller
{
    protected $zohoinvoice;

    public function __construct(ZohoService $zohoinvoice)
    {
        $this->zohoinvoice = $zohoinvoice;
    }

    public function uploadCreditNotes(Request $request)
    {
        try {
            ini_set('max_execution_time', 0);
            ini_set('memory_limit', '-1');

            $import = new CreditNotesImport();
            Excel::import($import, $request->file('xlsx_file'));

            return back()->with('success', 'Invoices processed successfully.');
        } catch (\Exception $e) {
            Log::error('Invoice upload failed: ' . $e->getMessage());
            return back()->with('error', 'Upload failed: ' . $e->getMessage());
        }
    }


}
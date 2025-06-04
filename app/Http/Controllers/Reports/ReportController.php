<?php

namespace App\Http\Controllers\Reports;

use Carbon\Carbon;
use App\Models\Vehicle;
use App\Models\Investor;
use App\Models\BookingData;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ReportController extends Controller
{
    public function soaReport()
    {
        $investor= Investor::all();
        return view('reports.soa-report', compact('investor'));
    }

    public function getSoaReportList(Request $request)
    {
        $month = $request['month'];
        $investorId = $request['investor_id'];
        $bookingData= BookingData::with('vehicle')
            ->when($investorId, function($query) use ($investorId){
                $query->whereHas('vehicle', function($q) use ($investorId){
                    $q->where('investor_id', $investorId);
                });
            })
            ->when($month, function($query) use ($month) {
                $from = Carbon::parse($month)->startOfMonth();
                $to = Carbon::parse($month)->endOfMonth();
                $query->where(function ($q) use ($from, $to) {
                    $q->where('start_date', '<=', $to)
                      ->where('end_date', '>=', $from);
                });
            })->get();
        return view('reports.reportlist.get-soa-list', compact('bookingData'));
    }
}


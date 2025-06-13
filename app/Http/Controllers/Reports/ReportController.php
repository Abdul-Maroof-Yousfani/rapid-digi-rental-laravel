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
        $from = Carbon::parse($month)->startOfMonth();
        $to = Carbon::parse($month)->endOfMonth();

        $vehicles = Vehicle::with(['bookingData' => function ($q) use ($from, $to) {
            $q->where('start_date', '<=', $to)
            ->where('end_date', '>=', $from);
        }])
        ->when($investorId, function ($query) use ($investorId) {
            $query->where('investor_id', $investorId);
        })
        ->get();

        return view('reports.reportlist.get-soa-list', compact('vehicles', 'from', 'to', 'month'));
    }
}

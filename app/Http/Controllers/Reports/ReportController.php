<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingData;
use App\Models\Customer;
use App\Models\Investor;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{

    // SOA Report Functions
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

    // customer wise sales reports function
    public function customerWiseReport()
    {
        $customers= Customer::all();
        return view('reports.customer-wise-report', compact('customers'));
    }

    public function getCustomerWiseSaleReportList(Request $request)
    {
        $fromDate= $request['fromDate'];
        $toDate= $request['toDate'];
        $customerID= $request['customer_id'];
        $booking = Booking::with('bookingData', 'customer')
                ->withSum('bookingData as total_price', 'price')
                ->when($fromDate && $toDate, function ($query) use ($fromDate, $toDate){
                    $query->whereBetween('created_at', [$fromDate, $toDate]);
                })
                ->when($customerID, function ($query) use ($customerID){
                    $query->whereHas('customer', function($q) use ($customerID){
                        $q->where('id', $customerID);
                    });
                })
                ->get()
                ->map(function ($booking) {
                    $booking->total_price = $booking->bookingData->sum('price');
                    return $booking;
                });

        return view('reports.reportlist.get-customer-wise-list', compact('booking'));
    }

}

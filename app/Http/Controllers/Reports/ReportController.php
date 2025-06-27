<?php

namespace App\Http\Controllers\Reports;

use Carbon\Carbon;
use App\Models\Booking;
use App\Models\Invoice;
use App\Models\Vehicle;
use App\Models\Customer;
use App\Models\Investor;
use App\Models\SalePerson;
use App\Models\BookingData;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

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
                ->withSum('bookingData as item_total', 'item_total')
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

    // customer wise receivable reports function
    public function customerWiseReceivable()
    {
        $customers= Customer::all();
        $bookings= Booking::all();
        return view('reports.customer-wise-receivable', compact('bookings', 'customers'));
    }

    public function getCustomerWiseReceivableList(Request $request)
    {
        $customerID= $request->customer_id;
        $booking = Booking::with('invoice', 'payment')
                    ->whereHas('payment', function($q1){
                        $q1->where('pending_amount', '!==', null);
                    })
                    ->when($customerID, function ($query) use ($customerID){
                        $query->where('customer_id', $customerID);
                    })
                    ->get();
        return view('reports.reportlist.get-customer-wise-receivable-list', compact('booking'));
    }


    // Salemen Wise Receivable report functions
    public function salemenWiseReport()
    {
        $saleman= SalePerson::all();
        return view('reports.salemen-wise-report', compact('saleman'));
    }

    public function getSalemenWiseReportList(Request $request)
    {
        $salemenID= $request->saleman_id;
        $booking= Booking::with('bookingData', 'invoice', 'salePerson')
                    ->when($salemenID, function ($query) use ($salemenID) {
                        $query->whereHas('salePerson', function($q1) use ($salemenID) {
                            $q1->where('id', $salemenID);
                        });
                    })->get();
        return view('reports.reportlist.get-salemen-wise-list', compact('booking'));
    }


}

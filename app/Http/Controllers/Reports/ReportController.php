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
use Illuminate\Support\Facades\Auth;

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
        $from = Carbon::parse($request->from_date)->startOfDay();
        $to = Carbon::parse($request->to_date)->endOfDay();
        $investorId = $request['investor_id'];

        $vehicles = Vehicle::with(['bookingData' => function ($q) use ($from, $to) {
            $q->where('start_date', '<=', $to)
            ->where('end_date', '>=', $from);
        }])
        ->when($investorId, function ($query) use ($investorId) {
            $query->where('investor_id', $investorId);
        })
        ->get();

        $selectedInvestor = null;
        if ($investorId) {
            $selectedInvestor = Investor::find($investorId);
        }

        $html = view('reports.reportlist.get-soa-list', compact('vehicles', 'from', 'to'))->render();

        return response()->json([
            'html' => $html,
            'investor_name' => $selectedInvestor ? $selectedInvestor->name : null,
            'percentage' => $selectedInvestor ? $selectedInvestor->percentage : 0,
        ]);

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

    // Investor Vehicle report functions
    public function investorVehicleReport()
    {
        return view('reports.investor-vehicle-report');
    }

    public function getInvestorVehicleReportList(Request $request)
    {
        $from = $request->from_date ? Carbon::parse($request->from_date)->startOfDay() : null;
        $to = $request->to_date ? Carbon::parse($request->to_date)->endOfDay() : null;
        $query= BookingData::with('vehicle.investor', 'booking')
                  ->whereHas('vehicle', function($query1){
                    $query1->whereHas('investor', function($query2){
                        $query2->where('user_id', Auth::user()->id);
                    });
                  })
                  ->when($from && $to, function ($query) use ($from, $to) {
                        $query->where('start_date', '<=', $to)
                              ->where('end_date', '>=', $from);
                  })
                  ->where('transaction_type', '!=', 3)
                  ->where('transaction_type', '!=', 4);

        $booking= $query->get();
        return view('reports.reportlist.get-investor-vehilce-list', compact('booking'));
    }


}

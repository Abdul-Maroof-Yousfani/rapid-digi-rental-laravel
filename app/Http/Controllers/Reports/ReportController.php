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
use App\Models\Payment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{

    public function __construct()
    {
        $this->middleware('permission:view reports')->only([
            'soaReport',
            'getSoaReportList',
            'customerWiseReport',
            'getCustomerWiseSaleReportList',
            'customerWiseReceivable',
            'getCustomerWiseReceivableList',
            'salemenWiseReport',
            'getSalemenWiseReportList',
        ]);

        $this->middleware('permission:view investor reports')->only([
            'investorVehicleReport',
            'getInvestorVehicleReportList'
        ]);
    }

    // SOA Report Functions
    public function soaReport()
    {
        $investor = Investor::all();
        return view('reports.soa-report', compact('investor'));
    }
    public function getSoaReportList(Request $request)
    {
        $from = Carbon::parse($request->from_date)->startOfDay();
        $to = Carbon::parse($request->to_date)->endOfDay();
        $investorId = $request['investor_id'];
        $type = $request['type'];
        $payment_status = $request['payment_status'];

        // Get vehicles with bookings in range
        $vehicles = Vehicle::with([
            'bookingData' => function ($q) use ($from, $to) {
                $q->where('start_date', '<=', $to)
                    ->where('end_date', '>=', $from)
                    ->with('invoice'); // eager load invoice inside bookingData
            }
        ])
            ->when($investorId, fn($query) => $query->where('investor_id', $investorId))
            ->when($type, function ($query) use ($type, $from, $to) {
                if ($type == 1) {
                    $query->whereHas('bookingData', fn($q) => $q->where('start_date', '<=', $to)
                        ->where('end_date', '>=', $from));
                } elseif ($type == 2) {
                    $query->whereDoesntHave('bookingData', fn($q) => $q->where('start_date', '<=', $to)
                        ->where('end_date', '>=', $from));
                }
            })
            ->get();

        // Get all booking IDs in range
        $bookingIds = $vehicles->pluck('bookingData.*.booking_id')->flatten()->unique();

        $bookingPriceMap = [];
        $bookingIsRentedMap = [];

        foreach ($vehicles as $vehicle) {
            foreach ($vehicle->bookingData as $booking) {
                $bookingPriceMap[$booking->booking_id] = $booking->price;
                $bookingIsRentedMap[$booking->booking_id] = true; // if booking exists, it’s rented
            }
        }

        $payments = Payment::whereIn('booking_id', $bookingIds)
            ->select('booking_id', 'paid_amount')
            ->get()
            ->map(function ($payment) use ($bookingPriceMap, $bookingIsRentedMap) {
                $price = $bookingPriceMap[$payment->booking_id] ?? 0;
                $isRented = $bookingIsRentedMap[$payment->booking_id] ?? false;

                if ($payment->paid_amount == 0 && $isRented) {
                    $payment->status = 'Pending';
                } elseif ($payment->paid_amount == 0) {
                    $payment->status = '-';
                } elseif ($payment->paid_amount >= $price) {
                    $payment->status = 'Paid';
                } else {
                    $payment->status = 'Partially Paid';
                }

                return $payment;
            });


        if ($payment_status) {
            $vehicles = $vehicles->filter(function ($vehicle) use ($payments, $payment_status, $from, $to) {

                $bookingsInRange = $vehicle->bookingData
                    ->filter(fn($b) => $b->start_date <= $to && $b->end_date >= $from);

                // Vehicle has no bookings → skip for Pending
                if ($bookingsInRange->isEmpty()) {
                    return false;
                }

                foreach ($bookingsInRange as $booking) {

                    $payment = $payments->firstWhere('booking_id', $booking->booking_id);

                    // Determine status exactly like Blade
                    if ($payment) {
                        $paidAmount = $payment->paid_amount;
                    } else {
                        $paidAmount = 0;
                    }

                    $isRented = true; // since $bookingsInRange is not empty
                    $price = $booking->price;

                    if ($paidAmount == 0 && $isRented) {
                        $status = 'Pending';
                    } elseif ($paidAmount == 0) {
                        $status = '-';
                    } elseif ($paidAmount >= $price) {
                        $status = 'Paid';
                    } else {
                        $status = 'Partially Paid';
                    }

                    if ($status === $payment_status) {
                        return true; // include this vehicle
                    }
                }

                return false; // no booking matched
            })->values();
        }



        $selectedInvestor = $investorId ? Investor::find($investorId) : null;

        $html = view('reports.reportlist.get-soa-list', compact('vehicles', 'payments', 'from', 'to'))->render();

        return response()->json([
            'html' => $html,
            'investor_name' => $selectedInvestor?->name,
            'percentage' => $selectedInvestor?->percentage ?? 0,
            'till_date' => $to->format('d-F-Y'),
        ]);
    }


    // customer wise sales reports function
    public function customerWiseReport()
    {
        $customers = Customer::all();
        return view('reports.customer-wise-report', compact('customers'));
    }

    public function customerWiseDetailReport(Request $request, $customer_id)
    {
        $fromDate = $request->query('fromDate');
        $toDate = $request->query('toDate');

        $booking = Booking::with('bookingData', 'customer', 'invoice')
            ->withSum('bookingData as item_total', 'item_total')
            ->when($customer_id, function ($query) use ($customer_id) {
                $query->whereHas('customer', function ($q) use ($customer_id) {
                    $q->where('id', $customer_id);
                });
            })
            ->when($fromDate && $toDate, function ($query) use ($fromDate, $toDate) {
                $query->whereBetween('created_at', [$fromDate, $toDate]);
            })
            ->get()
            ->map(function ($booking) {
                $booking->total_price = $booking->bookingData->sum('price');
                return $booking;
            });
        return view('reports.customer-wise-detail-report', compact('booking'));
    }

    public function getCustomerWiseSaleReportList(Request $request)
    {
        $fromDate = $request['fromDate'];
        $toDate = $request['toDate'];
        $customerID = $request['customer_id'];
        $bookings = Booking::with(['bookingData', 'customer', 'payment'])
            ->when($fromDate && $toDate, fn($q) => $q->whereBetween('created_at', [$fromDate, $toDate]))
            ->when($customerID, fn($q) => $q->where('customer_id', $customerID))
            ->get();

        $bookingsGrouped = $bookings->groupBy('customer_id')->map(function ($group) {
            $first = $group->first();

            $itemTotal = $group->reduce(function ($carry, $b) {
                if ($b->relationLoaded('bookingData') && $b->bookingData instanceof \Illuminate\Support\Collection) {
                    return $carry + (float) $b->bookingData->sum('item_total');
                }
                return $carry + (float) ($b->item_total ?? 0);
            }, 0.0);

            $totalPrice = $group->reduce(function ($carry, $b) {
                if ($b->relationLoaded('bookingData') && $b->bookingData instanceof \Illuminate\Support\Collection) {
                    return $carry + (float) ($b->bookingData->first()->price ?? 0);
                }

            }, 0.0);
            // dd($totalPrice);

            $paidAmount = $group->reduce(function ($carry, $b) {
                if ($b->relationLoaded('payment')) {
                    if ($b->payment instanceof \Illuminate\Support\Collection) {
                        return $carry + (float) $b->payment->sum('paid_amount');
                    }
                    return $carry + (float) ($b->payment->paid_amount ?? 0);
                }
                return $carry;
            }, 0.0);

            $first->item_total = $itemTotal;
            $first->total_price = $totalPrice;
            $first->paid_amount = $paidAmount;
            $first->bookings_count = $group->count();

            return $first;
        })->values();
        // dd($bookingsGrouped);

        return view('reports.reportlist.get-customer-wise-list', ['booking' => $bookingsGrouped, 'fromDate' => $fromDate, 'toDate' => $toDate]);
    }



    // customer wise receivable reports function
    public function customerWiseReceivable()
    {
        $customers = Customer::all();
        $bookings = Booking::all();
        return view('reports.customer-wise-receivable', compact('bookings', 'customers'));
    }

    public function getCustomerWiseReceivableList(Request $request)
{
    $fromDate = $request->input('from_date');
    $toDate = $request->input('to_date');
    $customerID = $request->input('customer_id');

    $booking = Booking::with(['invoice', 'payment', 'customer', 'bookingData.invoice'])
        ->when($customerID, fn($q) => $q->where('customer_id', $customerID))
        ->when($fromDate && $toDate, fn($q) => $q->whereBetween(DB::raw('DATE(created_at)'), [$fromDate, $toDate]))
        ->get();

    return view('reports.reportlist.get-customer-wise-receivable-list', compact('booking'));
}


    // Salemen Wise Receivable report functions
    public function salemenWiseReport()
    {
        $saleman = SalePerson::all();
        return view('reports.salemen-wise-report', compact('saleman'));
    }

    public function getSalemenWiseReportList(Request $request)
    {
        $fromDate = $request['fromDate'];
        $toDate = $request['toDate'];
        $salemenID = $request->saleman_id;
        $booking = Booking::with('bookingData', 'invoice', 'salePerson')
            ->when($fromDate && $toDate, function ($query) use ($fromDate, $toDate) {
                $query->whereBetween('created_at', [$fromDate, $toDate]);
            })
            ->when($salemenID, function ($query) use ($salemenID) {
                $query->whereHas('salePerson', function ($q1) use ($salemenID) {
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
        $query = BookingData::with('vehicle.investor', 'booking')
            ->whereHas('vehicle', function ($query1) {
                $query1->whereHas('investor', function ($query2) {
                    $query2->where('user_id', Auth::user()->id);
                });
            })
            ->when($from && $to, function ($query) use ($from, $to) {
                $query->where('start_date', '<=', $to)
                    ->where('end_date', '>=', $from);
            })
            ->where('transaction_type', '!=', 3)
            ->where('transaction_type', '!=', 4);

        $booking = $query->get();
        return view('reports.reportlist.get-investor-vehilce-list', compact('booking'));
    }
}

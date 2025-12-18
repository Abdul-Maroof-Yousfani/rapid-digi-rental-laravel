<?php

namespace App\Http\Controllers\Reports;

use App\Models\PaymentData;
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
use App\Models\BookingPaymentHistory;
use App\Exports\CustomerLedgerExport;
use Maatwebsite\Excel\Facades\Excel;
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
        // Validate and parse dates
        if (!$request->from_date || !$request->to_date) {
            return response()->json([
                'html' => '<tr><td colspan="6" class="text-center"><h3 style="color:#0d6efd;">Please select both From Date and To Date</h3></td></tr>',
                'investor_name' => null,
                'percentage' => 0,
                'till_date' => '',
            ]);
        }

        $from = Carbon::parse($request->from_date)->startOfDay();
        $to = Carbon::parse($request->to_date)->endOfDay();
        $investorId = $request['investor_id'];
        $type = $request['type'];
        $payment_status = $request['payment_status'];

        // Get vehicles with bookings in range - only vehicles that have bookings in the date range
        $vehicles = Vehicle::with([
            'bookingData' => function ($q) use ($from, $to) {
                $q->where('start_date', '<=', $to)
                    ->where('end_date', '>=', $from)
                    ->with('invoice'); // eager load invoice inside bookingData
            }
        ])
            ->whereHas('bookingData', function ($q) use ($from, $to) {
                $q->where('start_date', '<=', $to)
                    ->where('end_date', '>=', $from);
            })
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

                // Vehicle has no bookings → skip
                if ($bookingsInRange->isEmpty()) {
                    return false;
                }

                // Calculate total price (sum of all bookings) - exactly like Blade
                $price = $bookingsInRange->sum('price');
                $isRented = $bookingsInRange->isNotEmpty();

                // Get payment for the first booking (exactly like Blade)
                $firstBooking = $bookingsInRange->first();
                $bookingPayment = $payments->firstWhere('booking_id', $firstBooking->booking_id ?? null);
                $paidAmount = $bookingPayment->paid_amount ?? 0;

                // Determine status exactly like Blade view
                if ($paidAmount == 0 && $isRented) {
                    $status = 'Pending';
                } elseif ($paidAmount == 0) {
                    $status = '-';
                } elseif ($paidAmount >= $price) {
                    $status = 'Paid';
                } else {
                    $status = 'Partially Paid';
                }

                // Match the selected payment status
                return $status === $payment_status;
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
                $query->whereBetween('started_at', [$fromDate, $toDate]);
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
            ->when($fromDate && $toDate, fn($q) => $q->whereBetween('started_at', [$fromDate, $toDate]))
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
            ->when($fromDate && $toDate, fn($q) => $q->whereBetween(DB::raw('DATE(started_at)'), [$fromDate, $toDate]))
            ->get();

        return view('reports.reportlist.get-customer-wise-receivable-list', compact('booking'));
    }


    // Salemen Wise Receivable report functions
    public function salemenWiseReport()
    {
        $saleman = SalePerson::all();
        return view('reports.salemen-wise-report', compact('saleman'));
    }

    public function customerLedger()
    {
        $customers = Customer::all();
        $bookings = Booking::all();
        return view('reports.customer-ledger', compact('bookings', 'customers'));
    }

    public function getCustomerLedgerList(Request $request)
    {
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');
        $customerID = $request->input('customer_id');

        $query = PaymentData::with([
            'invoice',
            'payment.paymentMethod',
            'payment.bank',
            'payment.booking.customer',
            'payment.booking.bookingData',
            'payment.bookingPaymentHistories'
        ])
            ->whereHas('payment.booking', function ($query) use ($customerID) {
                if ($customerID) {
                    $query->where('customer_id', $customerID);
                }
            });

        if ($fromDate && $toDate) {
            $query->whereExists(function ($subQuery) use ($fromDate, $toDate) {
                $subQuery->select(DB::raw(1))
                    ->from('booking_payment_histories')
                    ->whereColumn('booking_payment_histories.payment_id', 'payment_data.payment_id')
                    ->where(function ($q) {
                        $q->where(function ($invoiceQuery) {
                            $invoiceQuery->whereColumn('booking_payment_histories.invoice_id', 'payment_data.invoice_id')
                                ->whereNotNull('payment_data.invoice_id')
                                ->whereNotNull('booking_payment_histories.invoice_id');
                        })
                        ->orWhere(function ($noInvoiceInHistory) {
                            $noInvoiceInHistory->whereNotNull('payment_data.invoice_id')
                                ->whereNull('booking_payment_histories.invoice_id');
                        })
                        ->orWhere(function ($bothNull) {
                            $bothNull->whereNull('payment_data.invoice_id')
                                ->whereNull('booking_payment_histories.invoice_id');
                        });
                    })
                    ->whereBetween(DB::raw('DATE(booking_payment_histories.payment_date)'), [$fromDate, $toDate]);
            });
        }

        $paymentData = $query->select('payment_data.*')
            ->addSelect([
                DB::raw('(SELECT payment_date FROM booking_payment_histories 
                    WHERE booking_payment_histories.payment_id = payment_data.payment_id 
                    AND (
                        (booking_payment_histories.invoice_id = payment_data.invoice_id AND payment_data.invoice_id IS NOT NULL)
                        OR (payment_data.invoice_id IS NULL AND booking_payment_histories.invoice_id IS NULL)
                    )
                    ORDER BY payment_date ASC LIMIT 1) as history_payment_date')
            ])
            ->orderBy(DB::raw('COALESCE(history_payment_date, payment_data.created_at)'), 'ASC')
            ->get()
            ->loadMissing([
                'invoice',
                'payment.paymentMethod',
                'payment.bank',
                'payment.booking.customer',
                'payment.booking.bookingData',
                'payment.bookingPaymentHistories'
            ]);

        $ledgerData = $paymentData->filter(function ($item) {
            return $item->payment !== null;
        })->map(function ($item) use ($fromDate, $toDate) {
            $payment = $item->payment;
            $booking = $payment->booking ?? null;
            $invoice = $item->invoice;

            $paymentMethodName = $payment && $payment->paymentMethod ? $payment->paymentMethod->name : 'N/A';
            $description = $booking && $booking->bookingData ? ($booking->bookingData->first()->description ?? '') : '';

            $itemDesc = $paymentMethodName;

            $invoiceNumber = $invoice ? ($invoice->zoho_invoice_number ?? '') : '';
            $invoiceId = $invoice ? ($invoice->id ?? null) : null;

            $invoiceAmount = $item->invoice_amount ?? 0;
            $paymentReceive = $item->paid_amount ?? 0;

            $outstanding = $invoiceAmount - $paymentReceive;

            $invoiceStatus = '';
            if ($invoice) {
                $invoiceStatus = $invoice->invoice_status ?? '';
                if ($booking && $booking->deposit_type && $outstanding <= 0) {
                    $invoiceStatus = 'deposited full';
                }
            }

            $paymentDate = '';
            if ($payment && $payment->bookingPaymentHistories) {
                $history = null;
                
                if ($fromDate && $toDate) {
                    $filteredHistories = $payment->bookingPaymentHistories->filter(function ($h) use ($fromDate, $toDate) {
                        if (!$h->payment_date) return false;
                        $historyDate = Carbon::parse($h->payment_date)->format('Y-m-d');
                        return $historyDate >= $fromDate && $historyDate <= $toDate;
                    });
                    
                    if ($invoice && $invoice->id) {
                        $history = $filteredHistories->where('invoice_id', $invoice->id)->first();
                    }
                    
                    if (!$history) {
                        $history = $filteredHistories->first();
                    }
                }
                
                if (!$history && $invoice && $invoice->id) {
                    $history = $payment->bookingPaymentHistories
                        ->where('invoice_id', $invoice->id)
                        ->first();
                }
                
                if (!$history) {
                    $history = $payment->bookingPaymentHistories->first();
                }
                
                if ($history && $history->payment_date) {
                    $paymentDate = Carbon::parse($history->payment_date)->format('Y-m-d');
                }
            }
            
            if (empty($paymentDate)) {
                if ($payment && $payment->payment_date) {
                    $paymentDate = Carbon::parse($payment->payment_date)->format('Y-m-d');
                } elseif ($item->created_at) {
                    $paymentDate = $item->created_at->format('Y-m-d');
                } else {
                    $paymentDate = '';
                }
            }

            return (object) [
                'date' => $paymentDate,
                'invoice_number' => $invoiceNumber,
                'invoice_id' => $invoiceId,
                'description' => $description, 
                'item_desc' => $itemDesc,
                'invoice_amount' => $invoiceAmount, 
                'payment_receive' => $paymentReceive,
                'outstanding' => $outstanding,
                'invoice_status' => $invoiceStatus,
            ];
        });

        return view('reports.reportlist.get-customer-ledger-list', compact('ledgerData'));
    }

    public function exportCustomerLedger(Request $request)
    {
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');
        $customerID = $request->input('customer_id');

        $query = PaymentData::with([
            'invoice',
            'payment.paymentMethod',
            'payment.bank',
            'payment.booking.customer',
            'payment.booking.bookingData',
            'payment.bookingPaymentHistories'
        ])
            ->whereHas('payment.booking', function ($query) use ($customerID) {
                if ($customerID) {
                    $query->where('customer_id', $customerID);
                }
            });

        if ($fromDate && $toDate) {
            $query->whereExists(function ($subQuery) use ($fromDate, $toDate) {
                $subQuery->select(DB::raw(1))
                    ->from('booking_payment_histories')
                    ->whereColumn('booking_payment_histories.payment_id', 'payment_data.payment_id')
                    ->where(function ($q) {
                        $q->where(function ($invoiceQuery) {
                            $invoiceQuery->whereColumn('booking_payment_histories.invoice_id', 'payment_data.invoice_id')
                                ->whereNotNull('payment_data.invoice_id')
                                ->whereNotNull('booking_payment_histories.invoice_id');
                        })
                        ->orWhere(function ($noInvoiceInHistory) {
                            $noInvoiceInHistory->whereNotNull('payment_data.invoice_id')
                                ->whereNull('booking_payment_histories.invoice_id');
                        })
                        ->orWhere(function ($bothNull) {
                            $bothNull->whereNull('payment_data.invoice_id')
                                ->whereNull('booking_payment_histories.invoice_id');
                        });
                    })
                    ->whereBetween(DB::raw('DATE(booking_payment_histories.payment_date)'), [$fromDate, $toDate]);
            });
        }

       
        $paymentData = $query->select('payment_data.*')
            ->addSelect([
                DB::raw('(SELECT payment_date FROM booking_payment_histories 
                    WHERE booking_payment_histories.payment_id = payment_data.payment_id 
                    AND (
                        (booking_payment_histories.invoice_id = payment_data.invoice_id AND payment_data.invoice_id IS NOT NULL)
                        OR (payment_data.invoice_id IS NULL AND booking_payment_histories.invoice_id IS NULL)
                    )
                    ORDER BY payment_date ASC LIMIT 1) as history_payment_date')
            ])
            ->orderBy(DB::raw('COALESCE(history_payment_date, payment_data.created_at)'), 'ASC')
            ->get()
            ->loadMissing([
                'invoice',
                'payment.paymentMethod',
                'payment.bank',
                'payment.booking.customer',
                'payment.booking.bookingData',
                'payment.bookingPaymentHistories'
            ]);

        $ledgerData = $paymentData->filter(function ($item) {
            return $item->payment !== null;
        })->map(function ($item) use ($fromDate, $toDate) {
            $payment = $item->payment;
            $booking = $payment->booking ?? null;
            $invoice = $item->invoice;

            $paymentMethodName = $payment && $payment->paymentMethod ? $payment->paymentMethod->name : 'N/A';
            $description = $booking && $booking->bookingData ? ($booking->bookingData->first()->description ?? '') : '';

            $itemDesc = $paymentMethodName;

            $invoiceNumber = $invoice ? ($invoice->zoho_invoice_number ?? '') : '';
            $invoiceId = $invoice ? ($invoice->id ?? null) : null;

            $invoiceAmount = $item->invoice_amount ?? 0;
            $paymentReceive = $item->paid_amount ?? 0;

            $outstanding = $invoiceAmount - $paymentReceive;

            $invoiceStatus = '';
            if ($invoice) {
                $invoiceStatus = $invoice->invoice_status ?? '';
                if ($booking && $booking->deposit_type && $outstanding <= 0) {
                    $invoiceStatus = 'deposited full';
                }
            }

            $paymentDate = '';
            if ($payment && $payment->bookingPaymentHistories) {
                $history = null;
                
                if ($fromDate && $toDate) {
                    $filteredHistories = $payment->bookingPaymentHistories->filter(function ($h) use ($fromDate, $toDate) {
                        if (!$h->payment_date) return false;
                        $historyDate = Carbon::parse($h->payment_date)->format('Y-m-d');
                        return $historyDate >= $fromDate && $historyDate <= $toDate;
                    });
                    
                    if ($invoice && $invoice->id) {
                        $history = $filteredHistories->where('invoice_id', $invoice->id)->first();
                    }
                    
                    if (!$history) {
                        $history = $filteredHistories->first();
                    }
                }
                
                if (!$history && $invoice && $invoice->id) {
                    $history = $payment->bookingPaymentHistories
                        ->where('invoice_id', $invoice->id)
                        ->first();
                }
                
                if (!$history) {
                    $history = $payment->bookingPaymentHistories->first();
                }
                
                if ($history && $history->payment_date) {
                    $paymentDate = Carbon::parse($history->payment_date)->format('Y-m-d');
                }
            }
            
            if (empty($paymentDate)) {
                if ($payment && $payment->payment_date) {
                    $paymentDate = Carbon::parse($payment->payment_date)->format('Y-m-d');
                } elseif ($item->created_at) {
                    $paymentDate = $item->created_at->format('Y-m-d');
                } else {
                    $paymentDate = '';
                }
            }

            return (object) [
                'date' => $paymentDate,
                'invoice_number' => $invoiceNumber,
                'invoice_id' => $invoiceId,
                'description' => $description,
                'item_desc' => $itemDesc,
                'invoice_amount' => $invoiceAmount,
                'payment_receive' => $paymentReceive,
                'outstanding' => $outstanding,
                'invoice_status' => $invoiceStatus,
            ];
        });

        $customerName = '';
        if ($customerID) {
            $customer = Customer::find($customerID);
            $customerName = $customer ? '_' . str_replace(' ', '_', $customer->customer_name) : '';
        }
        
        $dateRange = '';
        if ($fromDate && $toDate) {
            $dateRange = '_' . $fromDate . '_to_' . $toDate;
        }
        
        $filename = 'Customer_Ledger' . $customerName . $dateRange . '_' . date('Y-m-d_His') . '.xlsx';

        return Excel::download(new CustomerLedgerExport($ledgerData), $filename);
    }
    

    public function getSalemenWiseReportList(Request $request)
    {
        $fromDate = $request['fromDate'];
        $toDate = $request['toDate'];
        $salemenID = $request->saleman_id;
        $booking = Booking::with('bookingData', 'invoice', 'salePerson')
            ->when($fromDate && $toDate, function ($query) use ($fromDate, $toDate) {
                $query->whereBetween('started_at', [$fromDate, $toDate]);
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

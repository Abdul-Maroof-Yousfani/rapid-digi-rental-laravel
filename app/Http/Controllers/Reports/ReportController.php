<?php

namespace App\Http\Controllers\Reports;

use App\Exports\SoaReportExport;
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
use Maatwebsite\Excel\Excel as ExcelManager;
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
                'html' => '<tr><td colspan="7" class="text-center"><h3 style="color:#0d6efd;">Please select both From Date and To Date</h3></td></tr>',
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

        // Query invoices directly that:
        // 1. Have invoice_date within date range
        // 2. Have payment_data with invoice_id
        // 3. Have booking_data with tax_percent > 0
        $invoiceQuery = Invoice::with([
            'booking.bookingData.vehicle',
            'booking.customer',
            'bookingData' => function ($q) {
                $q->where('tax_percent', '>', 0);
            }
        ])
            ->whereBetween(DB::raw('DATE(invoice_date)'), [$from->format('Y-m-d'), $to->format('Y-m-d')])
            ->whereHas('paymentData', function ($q) {
                $q->whereNotNull('invoice_id');
            })
            ->whereHas('bookingData', function ($q) {
                $q->where('tax_percent', '>', 0);
            });

        // Filter by investor if selected
        if ($investorId) {
            $invoiceQuery->whereHas('bookingData.vehicle', function ($q) use ($investorId) {
                $q->where('investor_id', $investorId);
            });
        }

        // Filter by type (rented/not rented)
        if ($type) {
            if ($type == 1) {
                // Rented - has booking data
                $invoiceQuery->whereHas('bookingData');
            } elseif ($type == 2) {
                // Not Rented - no booking data
                $invoiceQuery->whereDoesntHave('bookingData');
            }
        }

        $invoices = $invoiceQuery->get();

        // Build SOA data from invoices
        $soaData = $invoices->map(function ($invoice) {
            // Get booking_data row where tax_percent > 0 for this invoice
            $bookingDataWithTax = $invoice->bookingData->where('tax_percent', '>', 0)->first();
            
            if (!$bookingDataWithTax) {
                return null;
            }

            $vehicle = $bookingDataWithTax->vehicle ?? null;

            // Get rental amount from booking_data row
            $rentalAmount = $bookingDataWithTax->price ?? 0;

            // Calculate rental period from start_date and end_date
            $rentalPeriod = '-';
            if ($bookingDataWithTax->start_date && $bookingDataWithTax->end_date) {
                $startDate = Carbon::parse($bookingDataWithTax->start_date);
                $endDate = Carbon::parse($bookingDataWithTax->end_date);
                $days = $startDate->diffInDays($endDate) + 1;
                
                if ($days >= 30) {
                    $months = round($days / 30); // Round to nearest month
                    if ($months == 1) {
                        $rentalPeriod = 'Monthly';
                    } else {
                        $rentalPeriod = $months . ' MONTHS';
                    }
                } else {
                    $rentalPeriod = $days . ' DAY' . ($days > 1 ? 'S' : '');
                }
            }

            // Get plate number from vehicle
            $plateNo = $vehicle ? ($vehicle->number_plate ?? '-') : '-';

            // Get car make-model & year
            $carDetails = '-';
            if ($vehicle) {
                $carDetails = $vehicle->temp_vehicle_detail ?? 
                    ($vehicle->vehicle_name . ' ' . $vehicle->car_make . ' ' . $vehicle->year);
            }

            return (object) [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->zoho_invoice_number ?? '-',
                'plate_no' => $plateNo,
                'car_details' => $carDetails,
                'rental_period' => $rentalPeriod,
                'rental_amount' => $rentalAmount,
            ];
        })->filter(function ($item) {
            return $item !== null;
        })->values();

        $selectedInvestor = $investorId ? Investor::find($investorId) : null;

        $html = view('reports.reportlist.get-soa-list', compact('soaData', 'from', 'to'))->render();

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

        /** Date filter by invoice_date */
        if ($fromDate && $toDate) {
            $query->whereHas('invoice', function ($q) use ($fromDate, $toDate) {
                $q->whereBetween(DB::raw('DATE(invoice_date)'), [$fromDate, $toDate]);
            });
        }

        $paymentData = $query->select('payment_data.*')
            ->addSelect([
                DB::raw('(SELECT invoice_date FROM invoices 
                    WHERE invoices.id = payment_data.invoice_id 
                    LIMIT 1) as invoice_date_for_sort')
            ])
            ->orderBy('invoice_date_for_sort', 'ASC')
            ->orderBy('payment_data.created_at', 'ASC')
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

            // $invoiceAmount = $item->payment->invoice->bookingData->sum('price') ?? 0;
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

            // Use invoice_date instead of payment_date
            $invoiceDate = '';
            if ($invoice && $invoice->invoice_date) {
                $invoiceDate = Carbon::parse($invoice->invoice_date)->format('Y-m-d');
            } elseif ($invoice && $invoice->created_at) {
                $invoiceDate = $invoice->created_at->format('Y-m-d');
            }

            return (object) [
                'date' => $invoiceDate,
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

        /** Date filter by invoice_date */
        if ($fromDate && $toDate) {
            $query->whereHas('invoice', function ($q) use ($fromDate, $toDate) {
                $q->whereBetween(DB::raw('DATE(invoice_date)'), [$fromDate, $toDate]);
            });
        }

       
        $paymentData = $query->select('payment_data.*')
            ->addSelect([
                DB::raw('(SELECT invoice_date FROM invoices 
                    WHERE invoices.id = payment_data.invoice_id 
                    LIMIT 1) as invoice_date_for_sort')
            ])
            ->orderBy('invoice_date_for_sort', 'ASC')
            ->orderBy('payment_data.created_at', 'ASC')
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

            // Use invoice_date instead of payment_date
            $invoiceDate = '';
            if ($invoice && $invoice->invoice_date) {
                $invoiceDate = Carbon::parse($invoice->invoice_date)->format('Y-m-d');
            } elseif ($invoice && $invoice->created_at) {
                $invoiceDate = $invoice->created_at->format('Y-m-d');
            }

            return (object) [
                'date' => $invoiceDate,
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

        try {
            // Try using facade first
            return Excel::download(new CustomerLedgerExport($ledgerData), $filename);
        } catch (\Exception $e) {
            // Fallback to direct instantiation if facade fails
            $excel = app(ExcelManager::class);
            return $excel->download(new CustomerLedgerExport($ledgerData), $filename);
        }
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

    public function exportSoaReport(Request $request)
    {
        $from = Carbon::parse($request->from_date)->startOfDay();
        $to = Carbon::parse($request->to_date)->endOfDay();
        $investorId = $request->input('investor_id');
        $type = $request->input('type');
        $payment_status = $request->input('payment_status');
        $excludedInvoices = $request->input('excluded_invoices') ? explode(',', $request->input('excluded_invoices')) : [];

        // Query invoices directly that:
        // 1. Have invoice_date within date range
        // 2. Have payment_data with invoice_id
        // 3. Have booking_data with tax_percent > 0
        $invoiceQuery = Invoice::with([
            'booking.bookingData.vehicle',
            'booking.customer',
            'bookingData' => function ($q) {
                $q->where('tax_percent', '>', 0);
            }
        ])
            ->whereBetween(DB::raw('DATE(invoice_date)'), [$from->format('Y-m-d'), $to->format('Y-m-d')])
            ->whereHas('paymentData', function ($q) {
                $q->whereNotNull('invoice_id');
            })
            ->whereHas('bookingData', function ($q) {
                $q->where('tax_percent', '>', 0);
            });

        // Exclude disabled invoices
        if (!empty($excludedInvoices)) {
            $invoiceQuery->whereNotIn('id', $excludedInvoices);
        }

        // Filter by investor if selected
        if ($investorId) {
            $invoiceQuery->whereHas('bookingData.vehicle', function ($q) use ($investorId) {
                $q->where('investor_id', $investorId);
            });
        }

        // Filter by type (rented/not rented)
        if ($type) {
            if ($type == 1) {
                // Rented - has booking data
                $invoiceQuery->whereHas('bookingData');
            } elseif ($type == 2) {
                // Not Rented - no booking data
                $invoiceQuery->whereDoesntHave('bookingData');
            }
        }

        $invoices = $invoiceQuery->get();

        // Build SOA data from invoices
        $soaData = $invoices->map(function ($invoice) {
            // Get booking_data row where tax_percent > 0 for this invoice
            $bookingDataWithTax = $invoice->bookingData->where('tax_percent', '>', 0)->first();
            
            if (!$bookingDataWithTax) {
                return null;
            }

            $vehicle = $bookingDataWithTax->vehicle ?? null;

            // Get rental amount from booking_data row
            $rentalAmount = $bookingDataWithTax->price ?? 0;

            // Calculate rental period from start_date and end_date
            $rentalPeriod = '-';
            if ($bookingDataWithTax->start_date && $bookingDataWithTax->end_date) {
                $startDate = Carbon::parse($bookingDataWithTax->start_date);
                $endDate = Carbon::parse($bookingDataWithTax->end_date);
                $days = $startDate->diffInDays($endDate) + 1;
                
                if ($days >= 30) {
                    $months = round($days / 30); // Round to nearest month
                    if ($months == 1) {
                        $rentalPeriod = 'Monthly';
                    } else {
                        $rentalPeriod = $months . ' MONTHS';
                    }
                } else {
                    $rentalPeriod = $days . ' DAY' . ($days > 1 ? 'S' : '');
                }
            }

            // Get plate number from vehicle
            $plateNo = $vehicle ? ($vehicle->number_plate ?? '-') : '-';

            // Get car make-model & year
            $carDetails = '-';
            if ($vehicle) {
                $carDetails = $vehicle->temp_vehicle_detail ?? 
                    ($vehicle->vehicle_name . ' ' . $vehicle->car_make . ' ' . $vehicle->year);
            }

            return (object) [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->zoho_invoice_number ?? '-',
                'plate_no' => $plateNo,
                'car_details' => $carDetails,
                'rental_period' => $rentalPeriod,
                'rental_amount' => $rentalAmount,
            ];
        })->filter(function ($item) {
            return $item !== null;
        })->values();

        $selectedInvestor = $investorId ? Investor::find($investorId) : null;
        
        $dateRange = '';
        if ($from && $to) {
            $dateRange = '_' . $from->format('Y-m-d') . '_to_' . $to->format('Y-m-d');
        }
        
        $investorName = $selectedInvestor ? '_' . str_replace(' ', '_', $selectedInvestor->name) : '';
        $filename = 'SOA_Report' . $investorName . $dateRange . '_' . date('Y-m-d_His') . '.xlsx';

        try {
            return Excel::download(new SoaReportExport($soaData), $filename);
        } catch (\Exception $e) {
            $excel = app(ExcelManager::class);
            return $excel->download(new SoaReportExport($soaData), $filename);
        }
    }
}

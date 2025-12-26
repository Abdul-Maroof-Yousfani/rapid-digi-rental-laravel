<?php

namespace App\Http\Controllers\ajax;

use App\Models\Bank;
use App\Models\Booking;
use App\Models\Deposit;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Vehicle;
use App\Models\Customer;
use App\Models\CreditNote;
use App\Models\SalePerson;
use App\Models\BookingData;
use App\Models\PaymentData;
use App\Models\Notification;
use Illuminate\Http\Request;
use App\Models\VehicleStatus;
use App\Services\ZohoInvoice;
use App\Models\DepositHandling;
use App\Jobs\UpdateZohoInvoiceJob;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\BookingPaymentHistory;

class AjaxController extends Controller
{

    protected $zohoinvoice;
    public function __construct(ZohoInvoice $zohoinvoice)
    {
        $this->zohoinvoice = $zohoinvoice;
    }


    public function getVehicleByType(Request $request, $id)
    {
        $startDate = $request->start_date;
        $endDate = $request->end_date;
        $bookingID = $request->bookingID;
        $selectedVehicleId = $request->selectedVehicleId;

        // Same logic as getVehicleAgaistBooking
        $bookedVehicleIds = BookingData::where(function ($q) use ($startDate, $endDate) {
            $q->whereRaw('start_date < ? AND end_date > ?', [$endDate, $startDate]);
        })
            ->when($bookingID, function ($q) use ($bookingID) {
                $q->where('booking_id', '!=', $bookingID);
            })
            ->pluck('vehicle_id')
            ->unique();

        $vehicles = Vehicle::where('vehicletypes', $id)
            ->where(function ($query) use ($bookedVehicleIds, $selectedVehicleId) {

                // Show available vehicles (not booked in this range)
                $query->whereNotIn('id', $bookedVehicleIds);

                // Also show the currently selected vehicle (for editing mode)
                if (!empty($selectedVehicleId)) {
                    $query->orWhere('id', $selectedVehicleId);
                }
            })
            ->get();

        return response()->json($vehicles);
    }





    public function getNoByVehicle($id)
    {
        $vehicle = Vehicle::where('id', $id)->first();
        if ($vehicle) {
            return response()->json([
                'vehicle_status' => $vehicle->vehiclestatus->name ?? 'N/A',
                'number_plate' => $vehicle->number_plate,
                'investor' => $vehicle->investor->name,
                'status' => $vehicle->status == 1 ? "Active" : "Inactive",
            ], 200);
        } else {
            return response()->json([], 200);
        }
    }

    public function getVehicleAgaistBooking(Request $request, $vehicleTypeId, $bookingId)
    {
        $startDate = $request->start_date;
        $endDate = $request->end_date;

        // If invoice type is NEW, return all vehicles of this type without any filtering
        if ($request->invoice_type == 'NEW') {
            return Vehicle::where('vehicletypes', $vehicleTypeId)
                ->get(['id', 'number_plate', 'temp_vehicle_detail', 'vehicle_name']);
        }

        $query = Vehicle::where('vehicletypes', $vehicleTypeId);

        if ($request->invoice_type != 'RENEW') {

            $bookedVehicleIds = Booking::where('id', $bookingId)
                ->get(['vehicle_id', 'replacement_vehicle_id'])
                ->flatMap(function ($item) {
                    return [$item->vehicle_id, $item->replacement_vehicle_id];
                })
                ->filter()
                ->unique()
                ->values();

            $query->whereIn('id', $bookedVehicleIds);
        }

        if ($startDate && $endDate) {

            $bookedInRange = BookingData::where(function ($q) use ($startDate, $endDate) {

                $q->whereRaw('start_date < ? AND end_date > ?', [$endDate, $startDate]);

            })->pluck('vehicle_id')->unique();

            $query->whereNotIn('id', $bookedInRange);
        }


        return $query->get(['id', 'number_plate', 'temp_vehicle_detail', 'vehicle_name']);
    }



    public function getPaymentHistory($paymentId)
    {
        $paymentHistory = BookingPaymentHistory::with('payment', 'paymentMethod')->where('payment_id', $paymentId)->get();
        return response()->json([
            'success' => true,
            'data' => $paymentHistory
        ]);
    }

    public function getPaymentData($paymentId)
    {
        $paymentData = PaymentData::with('invoice', 'payment.paymentMethod')
            ->where('payment_id', $paymentId)
            ->orderBy('id', 'DESC')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $paymentData
        ]);
    }

    public function getBookingDetail($booking_id)
    {
        $booking = Booking::find($booking_id);
        if (!$booking_id) {
            return response()->json(['error' => 'Data Not Found']);
        }
        $nonRefundableAmount = $booking->non_refundable_amount ?? 0;
        // $invoice1 = Invoice::where('booking_id', $booking_id)->value('id');
        $bookingAmount = BookingData::where('booking_id', $booking_id)->sum('item_total');
        // $bookingAmount = BookingData::with('booking' if bookings as deposit_id not null then find deposit_id in deposits and get )->where('booking_id', $booking_id)->sum('item_total');

        // Get all payments for this booking and sum the paid amounts
        $allPayments = Payment::where('booking_id', $booking_id)->get();
        $totalPaidAmount = $allPayments->sum('paid_amount');
        $totalCreditAmount = CreditNote::where('booking_id', $booking_id)->sum('refund_amount');
        $payment = $allPayments->sortByDesc('id')->first(); // Get latest payment for reference

        $remainingAmount = $bookingAmount - $totalPaidAmount - $totalCreditAmount;

        // Calculate Remaining Deposit Amount
        $initialDeposit = $booking->deposit->deposit_amount ?? 0;
        $deductAmount = DepositHandling::where('booking_id', $booking_id)->sum('deduct_deposit');
        $creditNote = $booking->creditNote; // will be null if not exists
        $refundAmount = $creditNote->refund_amount ?? 0;
        $adjustedInitialDeposit = $initialDeposit - $refundAmount;
        $deductAmount = DepositHandling::where('booking_id', $booking_id)->sum('deduct_deposit') ?? 0;
        $remainingDeposit = $adjustedInitialDeposit - $deductAmount;
        // if($deductAmount){ $remainingDeposit= $initialDeposit - $deductAmount; }

        $creditNoteDetail = [];
        if ($creditNote) {
            $creditNoteDetail[] = [
                'id' => $creditNote->id,
                'CN_no' => $creditNote->credit_note_no,
                'refund_amount' => $creditNote->refund_amount,
            ];
        }


        $getVehicle = BookingData::with('vehicle')->select('vehicle_id')
            ->where('booking_id', $booking_id)
            ->groupBy('vehicle_id')->get();

        $vehicles = [];

        foreach ($getVehicle as $value) {
            $vehicles[] = [
                'type' => $value->vehicle->vehicletype->name ?? null,
                'name' => isset($value->vehicle)
                    ? ($value->vehicle->number_plate ?? null) . ' | ' . ($value->vehicle->vehicle_name ?? $value->vehicle->temp_vehicle_detail ?? null)
                    : null,
            ];
        }


        $invoices = Invoice::where('booking_id', $booking_id)->get()->map(function ($invoice) {
            $bookingData = BookingData::where('invoice_id', $invoice->id)->get();
            $summary = [
                'salik_qty' => 0,
                'salik_amount' => 0,
                'park_qty' => 0,
                'park_amount' => 0,
                'fine_qty' => 0,
                'fine_amount' => 0,
                'renew_amount' => 0,
                'rent_amount' => 0,
            ];

            foreach ($bookingData as $data) {
                if (is_null($data->deductiontype_id)) {
                    // Rent
                    $summary['rent_amount'] += $data->item_total;
                    continue;
                }
                switch ($data->deductiontype_id) {
                    case 1: // Salik
                        $summary['salik_qty'] += $data->quantity;
                        $summary['salik_amount'] += $data->item_total;
                        break;
                    case 2: // Fine
                        $summary['fine_qty'] += $data->quantity;
                        $summary['fine_amount'] += $data->item_total;
                        break;
                    case 5: // Renew
                        $summary['renew_amount'] += $data->item_total;
                        break;
                    case 6: // Park
                        $summary['park_qty'] += $data->quantity;
                        $summary['park_amount'] += $data->item_total;
                        break;
                }
            }

            // Get ALL PaymentData records for this invoice and sum the paid amounts
            $allPaymentData = PaymentData::where('invoice_id', $invoice->id)->get();
            $totalPaidAmount = $allPaymentData->sum('paid_amount');

            // Get the latest PaymentData ID for reference
            $latestPaymentData = $allPaymentData->sortByDesc('id')->first();

            // Sum all deposit amounts from all PaymentData records for this invoice
            $totalDepositAmount = 0;
            if ($allPaymentData->isNotEmpty()) {
                $paymentDataIds = $allPaymentData->pluck('id');
                $totalDepositAmount = DepositHandling::whereIn('payment_data_id', $paymentDataIds)->sum('deduct_deposit');
            }

            return [
                'payment_data_id' => $latestPaymentData->id ?? null, // Latest PaymentData Primary Key for reference
                'paid_amount' => $totalPaidAmount, // Sum of all payments for this invoice
                'deposit_amount' => $totalDepositAmount, // Sum of all deposits for this invoice
                'initial_deposit' => $booking->deposit->initial_deposit ?? 0,
                'zoho_invoice_number' => $invoice->zoho_invoice_number,
                'invoice_status' => $invoice->invoice_status,
                'invoice_amount' => $bookingData->sum('item_total'),
                'invoice_id' => $invoice->id,
                'summary' => $summary,
            ];
        });

        $paidAmount1 = $totalPaidAmount;

        $allowDeposit = $paidAmount1 > 0 && $initialDeposit > 0;

        $initialDepositValue = $allowDeposit ? $initialDeposit : 0;

        return response()->json([
            'payment_id' => $payment->id ?? null, // Latest Payment Primary Key for reference
            'paid_amount' => $totalPaidAmount, // Sum of all payments for this booking
            'remaining_amount' => $remainingAmount,
            'booking_amount' => $bookingAmount,
            'deposit_amount' => $initialDeposit,
            'initial_deposit' => $booking->deposit->initial_deposit ?? 0,
            'credit_note_detail' => $creditNoteDetail,
            'deduct_amount' => $deductAmount ?? 0,
            'remaining_deposit' => $remainingDeposit,
            'customer' => $booking->customer->customer_name,
            'invoice_detail' => $invoices,
            'vehicle' => $vehicles,
            'non_refundable_amount' => $nonRefundableAmount,
        ]);
    }

    public function getInvoiceDetail($invoice_id)
    {
        // Eager load booking along with bookingData and its relations
        $invoice = Invoice::with([
            'booking',
            'bookingData.invoice_type',
            'bookingData.vehicle'
        ])->find($invoice_id);

        if (!$invoice) {
            return response()->json([
                'success' => false,
                'data' => 'Invoice Not Found'
            ]);
        } else {
            return response()->json([
                'success' => true,
                'data' => [
                    'invoice' => $invoice,
                    'booking_data' => $invoice->bookingData,
                    // Add deposit_type from booking here
                    'deposit_type' => $invoice->booking->deposit_type ?? null
                ]
            ]);
        }
    }


    public function getVehicleForEditForm($id)
    {
        $vehicle = Vehicle::with('vehicletype', 'investor')->find($id);
        if (!$vehicle) {
            return response()->json([
                'success' => false,
                'error' => 'Vehicle Not Found'
            ], 404);
        }
        return response()->json([
            'success' => true,
            'data' => $vehicle
        ]);
    }

    public function getSalemanForEditForm($id)
    {
        $salePerson = SalePerson::find($id);
        if (!$salePerson) {
            return response()->json([
                'success' => false,
                'error' => 'Sale Person Not Found'
            ], 404);
        }
        return response()->json([
            'success' => true,
            'data' => $salePerson
        ]);
    }

    public function getBankForEditForm($id)
    {
        $bank = Bank::find($id);
        if (!$bank) {
            return response()->json([
                'success' => false,
                'error' => 'Bank Not Found'
            ], 404);
        }
        return response()->json([
            'success' => true,
            'data' => $bank
        ]);
    }

    public function getVehicleStatusForEditForm($id)
    {
        $status = VehicleStatus::find($id);
        if (!$status) {
            return response()->json([
                'success' => false,
                'error' => 'Status Not Found'
            ], 404);
        }
        return response()->json([
            'success' => true,
            'data' => $status
        ]);
    }

    public function getCustomerForEditForm($id)
    {
        $customers = Customer::find($id);
        if (!$customers) {
            return response()->json([
                'success' => false,
                'error' => 'Customer Not Found !'
            ], 404);
        }
        return response()->json([
            'success' => true,
            'data' => $customers
        ]);
    }

    public function bookingCancellation($id)
    {
        $booking = Booking::find($id);
        if (!$booking) {
            return response()->json([
                'success' => false,
                'data' => 'Booking not found.'
            ], 404);
        } else {
            $booking->booking_cancel = '1';
            $booking->save();
            return response()->json([
                'success' => true,
                'data' => 'Booking Cancelled.'
            ], 200);
        }
    }

    public function markAsRead()
    {
        Notification::where('user_id', Auth::user()->id)
            ->where('is_read', 0)
            ->update(['is_read' => 1]);
        return response()->json(['status' => 'ok']);
    }

    public function bookingConvertPartial(Request $request)
    {
        // dd($request->all());
        $updatedRecords = [];
        $invoiceUpdates = [];
        foreach ($request->bookingDataID as $key => $bookingDataID) {
            $booking_data = BookingData::with(['invoice', 'vehicle'])->find($bookingDataID);

            if ($booking_data && $booking_data->invoice) {
                // Step 1: Update Local DB
                $booking_data->update([
                    'end_date' => $request['end_date'][$key],
                    'price' => $request['new_gross_rent_amount'][$key],
                    'item_total' => $request['new_amount'][$key],
                ]);

                $invoiceId = $booking_data->invoice->zoho_invoice_id;

                // Use fallback for vehicle name
                $vehicleName = $booking_data->vehicle->vehicle_name ?? $booking_data->vehicle->temp_vehicle_detail ?? '';
                $description = $booking_data->description ?? '';
                $newRate = $request['new_gross_rent_amount'][$key];

                $invoiceUpdates[$invoiceId][] = [
                    'name' => $vehicleName,
                    'description' => $description,
                    'rate' => $newRate,
                ];

                $updatedRecords[] = $booking_data;
            }
        }

        $totalAmount = BookingData::where('invoice_id', $request->invoice_id)->sum('item_total');
        $invoice = Invoice::find($request->invoice_id);
        $invoice->update([
            'total_amount' => $totalAmount,
        ]);

        // $zohoResponses = [];

        foreach ($invoiceUpdates as $invoiceId => $updates) {
            UpdateZohoInvoiceJob::dispatch($invoiceId, $updates);
        }

        return response()->json([
            'success' => true,
            'data' => $updatedRecords,
            // 'zoho_response' => $zohoResponses,
        ]);
    }


    // public function bookingConvertPartial(Request $request)
    // {
    //     $updatedRecords = [];
    //     $invoiceUpdates = [];

    //     foreach ($request->bookingDataID as $key => $bookingDataID) {
    //         $booking_data = BookingData::with(['invoice', 'vehicle'])->find($bookingDataID);

    //         if ($booking_data && $booking_data->invoice) {
    //             // Step 1: Update Local DB
    //             $booking_data->update([
    //                 'end_date' => $request['end_date'][$key],
    //                 'price' => $request['new_amount'][$key],
    //             ]);

    //             $invoiceId = $booking_data->invoice->zoho_invoice_id;

    //             // Use fallback for vehicle name
    //             $vehicleName = $booking_data->vehicle->vehicle_name ?? $booking_data->vehicle->temp_vehicle_detail ?? '';
    //             $description = $booking_data->description ?? '';
    //             $newRate = $request['new_amount'][$key];

    //             $invoiceUpdates[$invoiceId][] = [
    //                 'name' => $vehicleName,
    //                 'description' => $description,
    //                 'rate' => $newRate,
    //             ];

    //             $updatedRecords[] = $booking_data;
    //         }
    //     }

    //     $zohoResponses = [];

    //     foreach ($invoiceUpdates as $invoiceId => $updates) {
    //         $invoiceData = $this->zohoinvoice->getInvoice($invoiceId);

    //         if (!isset($invoiceData['invoice'])) continue;

    //         $originalInvoice = $invoiceData['invoice'];
    //         $lineItems = $originalInvoice['line_items'];

    //         foreach ($lineItems as &$lineItem) {
    //             foreach ($updates as $update) {
    //                 if (
    //                     strtolower(trim($lineItem['name'])) === strtolower(trim($update['name'])) &&
    //                     strtolower(trim($lineItem['description'])) === strtolower(trim($update['description']))
    //                 ) {
    //                     $lineItem['rate'] = $update['rate'];
    //                     $lineItem['item_total'] = $update['rate'] * $lineItem['quantity']; // Optional
    //                 }
    //             }
    //         }

    //         $updatePayload = [
    //             'customer_id' => $originalInvoice['customer_id'],
    //             'currency_code' => $originalInvoice['currency_code'],
    //             'notes' => $originalInvoice['notes'] ?? '',
    //             'line_items' => $lineItems,
    //         ];

    //         $response = $this->zohoinvoice->updateInvoice($invoiceId, $updatePayload);
    //         \Log::info("Zoho Invoice Updated [$invoiceId]", $response);

    //         $zohoResponses[$invoiceId] = $response;
    //     }

    //     return response()->json([
    //         'success' => true,
    //         'data' => $updatedRecords,
    //         'zoho_response' => $zohoResponses,
    //     ]);
    // }

    public function searchCustomer(Request $request)
    {
        $search = strtolower($request->search);
        $customers = Customer::when($search, function ($query, $search) {
            $query->whereRaw('LOWER(customer_name) LIKE ?', ["%{$search}%"])
                ->orWhereRaw('LOWER(email) LIKE ?', ["%{$search}%"])
                ->orWhereRaw('LOWER(licence) LIKE ?', ["%{$search}%"])
                ->orWhereRaw('LOWER(cnic) LIKE ?', ["%{$search}%"])
                ->orWhereRaw('LOWER(country) LIKE ?', ["%{$search}%"])
                ->orWhereRaw('LOWER(phone) LIKE ?', ["%{$search}%"]);
        })->get();

        return response()->json([
            'customers' => $customers
        ]);
    }

    public function searchPayment(Request $request)
    {
        $search = strtolower($request->search ?? '');
        $payments = Payment::with(['booking.customer', 'booking.invoice', 'paymentMethod'])
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    // Search by booking ID (if numeric)
                    if (is_numeric($search)) {
                        $q->whereHas('booking', function ($q1) use ($search) {
                            $q1->where('id', 'LIKE', "%{$search}%");
                        })
                            ->orWhere('id', $search);
                    } else {
                        // Search by customer name
                        $q->whereHas('booking.customer', function ($q1) use ($search) {
                            $q1->whereRaw('LOWER(customer_name) LIKE ?', ["%" . $search . "%"]);
                        });
                    }
                });
            })
            ->orderBy('id', 'DESC')
            ->paginate(10);

        return response()->json([
            'payments' => $payments->items(),
            'pagination' => [
                'current_page' => $payments->currentPage(),
                'last_page' => $payments->lastPage(),
                'per_page' => $payments->perPage(),
                'total' => $payments->total(),
            ]
        ]);
    }

    public function searchVehicle(Request $request)
    {
        $search = $request->search;
        $vehicles = Vehicle::with('investor', 'vehiclestatus', 'vehicletype')
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->whereHas('investor', function ($q1) use ($search) {
                        $q1->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($search) . '%']);
                    })
                        ->orWhere('vehicle_name', 'LIKE', "%$search%")
                        ->orWhere('temp_vehicle_detail', 'LIKE', "%$search%")
                        ->orWhere('car_make', 'LIKE', "%$search%")
                        ->orWhere('year', 'LIKE', "%$search%")
                        ->orWhere('number_plate', 'LIKE', "%$search%");
                });
            })
            ->get();



        return response()->json([
            'vehicle' => $vehicles
        ]);
    }

    public function searchBank(Request $request)
    {
        $search = $request->search;
        $banks = Bank::when($search, function ($query, $search) {
            $query->where(function ($q) use ($search) {
                $q->where('bank_name', 'LIKE', "%$search%")
                    ->orWhere('account_name', 'LIKE', "%$search%")
                    ->orWhere('branch', 'LIKE', "%$search%")
                    ->orWhere('swift_code', 'LIKE', "%$search%")
                    ->orWhere('iban', 'LIKE', "%$search%")
                    ->orWhere('account_number', 'LIKE', "%$search%");
            });
        })->get();

        return response()->json([
            'banks' => $banks
        ]);
    }

    public function searchCreditNote(Request $request)
    {
        $search = strtolower($request->search);

        $creditNote = CreditNote::with('booking.customer', 'booking.deposit', 'paymentMethod')
            ->where(function ($query) use ($search) {
                $query->whereRaw('LOWER(credit_note_no) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(remaining_deposit) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(refund_amount) LIKE ?', ["%{$search}%"])
                    ->orWhereHas('booking', function ($q1) use ($search) {
                        $q1->whereRaw('LOWER(agreement_no) LIKE ?', ["%{$search}%"])
                            ->orWhereHas('customer', function ($q2) use ($search) {
                                $q2->whereRaw('LOWER(customer_name) LIKE ?', ["%{$search}%"]);
                            });
                    });
            })
            ->get();

        return response()->json([
            'creditNote' => $creditNote
        ]);
    }

    public function searchDeposit(Request $request)
    {
        $search = strtolower($request->search);

        $deposits = Deposit::with('booking', 'transferredBooking')
            ->where(function ($query) use ($search) {
                $query->whereRaw('LOWER(id) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(deposit_amount) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(initial_deposit) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(transferred_booking_id) LIKE ?', ["%{$search}%"])
                    ->orWhereHas('booking', function ($q1) use ($search) {
                        $q1->whereRaw('LOWER(agreement_no) LIKE ?', ["%{$search}%"]);
                    })
                    ->orWhereHas('transferredBooking', function ($q2) use ($search) {
                        $q2->whereRaw('LOWER(agreement_no) LIKE ?', ["%{$search}%"]);
                    });
            })
            ->get();

        return response()->json([
            'deposits' => $deposits
        ]);
    }

    public function searchBooking(Request $request)
    {
        $search = strtolower($request->search);
        $bookings = Booking::with(['customer', 'deposit', 'salePerson', 'payment', 'invoice', 'invoices'])
            ->withSum('invoice as total_amount', 'total_amount')
            ->when($search, function ($query, $search) {
                if (is_numeric($search)) {
                    // Filter by booking ID if numeric
                    $query->where('id', 'LIKE', "$search%");
                } else {
                    // Filter by customer name (case-insensitive)
                    $query->whereHas('customer', function ($q1) use ($search) {
                        $q1->whereRaw('LOWER(customer_name) LIKE ?', ["%" . strtolower($search) . "%"]);
                    });
                }
            })
            ->orderByDesc('id')
            ->paginate(10);




        return response()->json([
            'bookings' => $bookings->items(),
            'can' => [
                'view' => auth()->user()->can('view booking'),
                'delete' => auth()->user()->can('delete booking'),
            ],
        ]);
    }

    public function searchInvoice(Request $request)
    {
        $search = strtolower($request->search);
        $invoices = Invoice::with(['booking.customer'])
            ->withSum('bookingData as item_total', 'item_total')
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    if (is_numeric($search)) {
                        $q->where('zoho_invoice_number', $search);
                    } else {
                        $q->whereHas('booking.customer', function ($q1) use ($search) {
                            $q1->whereRaw('LOWER(customer_name) LIKE ?', ["%" . strtolower($search) . "%"]);
                        });
                    }
                });
            })
            ->orderBy('zoho_invoice_number', 'DESC')
            ->paginate(10);


        return response()->json([
            'invoices' => $invoices->items(),
            'can' => [
                'view' => auth()->user()->can('view booking'),
                'delete' => auth()->user()->can('delete booking'),
            ],
        ]);

    }


    public function searchCustomerLedger(Request $request)
    {
        $search = trim($request->search ?? '');
        
        if (empty($search)) {
            return response()->json([
                'ledgerData' => []
            ]);
        }
        
        $searchLower = strtolower($search);
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');

        // Query from invoices table
        $query = Invoice::with([
            'booking.customer',
            'booking.bookingData',
            'paymentData.payment.paymentMethod',
            'paymentData.payment.bank'
        ])
            ->whereHas('booking.customer', function ($q) use ($searchLower) {
                $q->whereRaw('LOWER(customer_name) LIKE ?', ['%' . $searchLower . '%']);
            });

        // Date filter by invoice_date
        if ($fromDate && $toDate) {
            $query->whereBetween(DB::raw('DATE(invoice_date)'), [$fromDate, $toDate]);
        }

        $invoices = $query->orderBy('invoice_date', 'ASC')
            ->orderBy('id', 'ASC')
            ->get();

        // Build ledger data from invoices
        $ledgerData = $invoices->map(function ($invoice) {
            $booking = $invoice->booking ?? null;
            
            // Get description from booking data
            $description = '';
            if ($booking && $booking->bookingData && $booking->bookingData->isNotEmpty()) {
                $description = $booking->bookingData->first()->description ?? '';
            }

            // Get payment method name from payment_data (if exists)
            $itemDesc = 'N/A';
            $paymentData = $invoice->paymentData->first();
            if ($paymentData && $paymentData->payment && $paymentData->payment->paymentMethod) {
                $itemDesc = $paymentData->payment->paymentMethod->name;
            }

            // Get invoice amount from total_amount or calculate from booking_data
            $invoiceAmount = 0;
            if ($invoice->total_amount) {
                $invoiceAmount = (float) str_replace(',', '', $invoice->total_amount);
            } elseif ($booking && $booking->bookingData) {
                $invoiceAmount = $booking->bookingData->sum('item_total') ?? 0;
            }

            // Calculate payment received by summing all payment_data for this invoice
            $paymentReceive = $invoice->paymentData->sum('paid_amount') ?? 0;

            // Calculate outstanding
            $outstanding = $invoiceAmount - $paymentReceive;

            // Get invoice status
            $invoiceStatus = $invoice->invoice_status ?? '';
            if ($booking && $booking->deposit_type && $outstanding <= 0) {
                $invoiceStatus = 'deposited full';
            }

            // Get invoice date
            $invoiceDate = '';
            if ($invoice->invoice_date) {
                $invoiceDate = Carbon::parse($invoice->invoice_date)->format('Y-m-d');
            } elseif ($invoice->created_at) {
                $invoiceDate = $invoice->created_at->format('Y-m-d');
            }

            return (object) [
                'date' => $invoiceDate,
                'invoice_number' => $invoice->zoho_invoice_number ?? '',
                'invoice_id' => $invoice->id,
                'description' => $description,
                'item_desc' => $itemDesc,
                'invoice_amount' => $invoiceAmount,
                'payment_receive' => $paymentReceive,
                'outstanding' => $outstanding,
                'invoice_status' => $invoiceStatus,
            ];
        });

        return response()->json([
            'ledgerData' => $ledgerData->values()
        ]);
    }




    public function checkAgreementNoExist(Request $request)
    {
        $agreementNo = $request->agreement_no;

        if (!$agreementNo) {
            return response()->json([], 200);
        }

        $exists = Booking::where('agreement_no', $agreementNo)->exists();

        return response()->json([
            'exists' => $exists,
            'message' => $exists ? 'Agreement No. already exists' : 'Agreement No. is available',
        ], 200);
    }
}

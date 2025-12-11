<?php

namespace App\Http\Controllers\ajax;

use Carbon\Carbon;
use App\Models\Payment;
use App\Models\Customer;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class FilterviewController extends Controller
{
    public function getCustomerList(Request $request)
    {
        $fromDate= Carbon::parse($request->fromDate)->startOfDay();
        $toDate= Carbon::parse($request->toDate)->endOfDay();
        $customers= Customer::whereBetween('created_at', [$fromDate, $toDate])->get();
        return view('ajaxview.customer-view', compact('customers'));
    }

  public function getPaymentList(Request $request)
{
    $search = $request->search;
    $payment = Payment::with(['booking.customer', 'booking.invoice', 'paymentMethod'])
        ->when($search, function ($query, $search) {
            $searchLower = strtolower($search);
            $query->where(function ($q) use ($searchLower, $search) {
                // Search by booking ID (if numeric)
                if (is_numeric($search)) {
                    $q->whereHas('booking', function ($q1) use ($search) {
                        $q1->where('id', 'LIKE', "%{$search}%");
                    })
                    ->orWhere('id', $search);
                } else {
                    // Search by customer name
                    $q->whereHas('booking.customer', function ($q1) use ($searchLower) {
                        $q1->whereRaw('LOWER(customer_name) LIKE ?', ["%" . $searchLower . "%"]);
                    });
                }
            });
        })
        ->orderBy('id', 'DESC')
        ->paginate(10);

    // Return only the partial view (for AJAX)
    return view('ajaxview.payment-view', compact('payment'));
}

}

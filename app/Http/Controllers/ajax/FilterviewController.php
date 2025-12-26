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
    $search = $request->search ?? '';
    $page = $request->input('page', 1);

    $query = Payment::with(['booking.customer', 'booking.invoice', 'paymentMethod']);

    if (!empty($search)) {
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
    }

    $payment = $query->orderBy('id', 'DESC')->paginate(10, ['*'], 'page', $page);

    // Return JSON with pagination data for AJAX
    if ($request->ajax() || $request->wantsJson()) {
        return response()->json([
            'payments' => $payment->items(),
            'pagination' => [
                'current_page' => $payment->currentPage(),
                'last_page' => $payment->lastPage(),
                'per_page' => $payment->perPage(),
                'total' => $payment->total(),
                'from' => $payment->firstItem(),
                'to' => $payment->lastItem(),
            ]
        ]);
    }

    // Return only the partial view (for non-AJAX)
    return view('ajaxview.payment-view', compact('payment'));
}

}

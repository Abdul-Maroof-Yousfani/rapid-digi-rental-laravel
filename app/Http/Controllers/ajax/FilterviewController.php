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
    $payment = Payment::with('booking', 'paymentMethod')
        // ->where('created_at', '>=', Carbon::now()->subDays(15))
        ->orderBy('id', 'DESC')
        ->paginate(10);

    // Return only the partial view (for AJAX)
    return view('ajaxview.payment-view', compact('payment'));
}

}

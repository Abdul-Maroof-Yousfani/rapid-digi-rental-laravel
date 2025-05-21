<?php

namespace App\Http\Controllers\ajax;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Carbon\Carbon;
use Illuminate\Http\Request;

class FilterviewController extends Controller
{
    public function getCustomerList(Request $request)
    {
        $fromDate= Carbon::parse($request->fromDate)->startOfDay();
        $toDate= Carbon::parse($request->toDate)->endOfDay();
        $customers= Customer::whereBetween('created_at', [$fromDate, $toDate])->get();
        return view('ajaxview.customer-view', compact('customers'));
    }
}

<?php

namespace App\Http\Controllers\Investor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class InvestorController extends Controller
{
    public function __construct()
    {
        // Only users with 'investor' role can access this controller's methods
        $this->middleware('role:investor');
    }
    public function index(){
        return view('admin.dashboard');
    }
}

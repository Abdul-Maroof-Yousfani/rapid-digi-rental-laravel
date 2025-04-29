<?php

namespace App\Http\Controllers\Booker;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BookerController extends Controller
{
    public function __construct()
    {
        // Only users with 'booker' role can access this controller's methods
        $this->middleware('role:booker');
    }
    public function index(){
        return view('admin.dashboard');
    }
}

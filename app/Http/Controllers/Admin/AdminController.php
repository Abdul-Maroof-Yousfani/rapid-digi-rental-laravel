<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function __construct()
    {
        // Only users with 'admin' role can access this controller's methods
        $this->middleware('role:admin');
    }
    public function index(){
        return view('admin.dashboard');
    }
}

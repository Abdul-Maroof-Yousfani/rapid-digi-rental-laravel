<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    // public function __construct()
    // {
    //     // Only users with 'admin' role can access this controller's methods
    //     // $this->middleware('role:admin');

    //     $this->middleware(['auth', 'permission:view dashboard']);

    // }
    // public function index(){
    //     // return view('admin.dashboard');


    //     $user = Auth::user();
    //     if ($user->hasRole('admin')) {
    //         return view('admin.dashboard');
    //     } elseif ($user->hasRole('booker')) {
    //         return view('admin.dashboard');
    //     } elseif ($user->hasRole('investor')) {
    //         return view('admin.dashboard');
    //     }

    //     abort(403, 'Unauthorized');
    // }


    public function index()
    {
        $user = Auth::user();

        // 🔐 Runtime par permission check (this is safer than middleware)
        if (!$user->can('view dashboard')) {
            abort(403, 'Unauthorized (missing permission)');
        }

        if ($user->hasRole('admin') || $user->hasRole('booker') || $user->hasRole('investor')) {
            return view('admin.dashboard');
        }

        abort(403, 'Unauthorized (no valid role)');
    }


}

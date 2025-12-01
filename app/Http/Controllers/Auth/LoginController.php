<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    // protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }

    protected function authenticated(Request $request, $user)
    {
        // Check if user has the 'admin' role
        if ($user->hasRole('admin')) {
            return redirect()->route('dashboard');
        }

        // Check if user has the 'investor' role
        if ($user->hasRole('investor')) {
            return redirect()->route('dashboard');
        }

        // Check if user has the 'booker' role
        if ($user->hasRole('booker')) {
            return redirect()->route('customer-invoice');
        }
    }

}

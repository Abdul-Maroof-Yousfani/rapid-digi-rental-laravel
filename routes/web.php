<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ajax\AjaxController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Booker\BookerController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Booker\BookingController;
use App\Http\Controllers\Admin\BookerCrudController;
use App\Http\Controllers\Admin\VehicleCrudController;
use App\Http\Controllers\Investor\InvestorController;
use App\Http\Controllers\Admin\InvestorCrudController;
use App\Http\Controllers\Admin\VehicleTypeCrudController;








/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

// Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::get('/oauth/callback', function (Illuminate\Http\Request $request) {
    return "Authorization Code: " . $request->query('code');
});

Route::prefix('admin')->as('admin.')->middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/dashboard', [AdminController::class, 'index'])->name('dashboard')->middleware('permission:view admin dashboard');
    Route::resource('customer', CustomerController::class);
    Route::resource('investor', InvestorCrudController::class)->middleware('permission:manage investors');
    Route::resource('booker', BookerCrudController::class)->middleware('permission:manage bookers');
    Route::resource('vehicle', VehicleCrudController::class)->middleware('permission:manage vehicles');
    Route::post('vehicle/import-csv', [VehicleCrudController::class, 'importCsv'])->middleware('permission:import vehicles CSV');
    Route::resource('vehicle-type', VehicleTypeCrudController::class)->middleware('permission:manage vehicle types');
});

Route::get('get-vehicle-by-Type/{id}', [AjaxController::class, 'getVehicleByType'])->name("getVehicleByType");
Route::get('get-vehicle-detail/{id}', [AjaxController::class, 'getNoByVehicle'])->name("getNoByVehicle");
Route::prefix('booker')->as('booker.')->middleware(['auth', 'role:booker'])->group(function() {
    Route::get('/dashboard', [BookerController::class, 'index'])->name('dashboard')->middleware('permission:view booker dashboard');
    Route::resource('customer-booking', BookingController::class);
    Route::resource('customer', CustomerController::class);
});

Route::prefix('investor')->middleware(['auth', 'role:investor', 'permission:view investor dashboard'])->group(function() {
    Route::get('/dashboard', [InvestorController::class, 'index'])->name('investor.dashboard');
});
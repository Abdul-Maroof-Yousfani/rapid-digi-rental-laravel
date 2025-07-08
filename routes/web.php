<?php

use Illuminate\Http\Request;
use App\Events\BookingCreated;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Api\ZohoController;
use App\Http\Controllers\ajax\AjaxController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Booker\BookerController;
use App\Http\Controllers\Admin\BankCrudController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Booker\BookingController;
use App\Http\Controllers\Booker\InvoiceController;
use App\Http\Controllers\Booker\PaymentController;
use App\Http\Controllers\Reports\ReportController;
use App\Http\Controllers\ajax\FilterviewController;
use App\Http\Controllers\Admin\BookerCrudController;
use App\Http\Controllers\Admin\SalepersonController;
use App\Http\Controllers\Admin\VehicleCrudController;
use App\Http\Controllers\Booker\CreditnoteController;
use App\Http\Controllers\Investor\InvestorController;
use App\Http\Controllers\Admin\InvestorCrudController;
use App\Http\Controllers\Admin\VehiclestatusController;
use App\Http\Controllers\Admin\RolePermissionController;
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
    // return view('welcome');
    return redirect()->route('login');
});

Auth::routes();

// Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
    Route::get('/redirect-by-role', function () {
        $user = auth()->user();
        if ($user->hasRole('admin')) {
            return redirect()->route('dashboard');
        } elseif ($user->hasRole('booker')) {
            return redirect()->route('dashboard');
        } elseif ($user->hasRole('investor')) {
            return redirect()->route('dashboard');
        }
        return abort(403); // Unauthorized
    });

Route::get('/zoho/callback', function (Request $request) {
    return "Authorization Code. ". $request->query('code');

    Route::get('/get-accesstoken', [ZohoController::class, 'getAccessToken']);
    Route::get('/zoho/invoice', [ZohoController::class, 'createInvoice']);

    Route::get('testing', function () {
        $data = [
            'message' => 'test message',
            'table_id' => '2',
            'receiver_id' => 197,
        ];
        event(new BookingCreated($data, 2 , 67));
    });

});



// // Route::get('/get-authcode', [ZohoController::class, 'index']);
// // Route::get('/zoho/callback', [ZohoController::class, 'redirectToZoho']);
// // Route::post('/refresh-access-token', [ZohoController::class, 'getRefreshAndAccessToken']);




// Route::prefix('admin')->as('admin.')->middleware(['auth', 'role:admin'])->group(function () {
//     Route::get('/dashboard', [AdminController::class, 'index'])->name('dashboard')->middleware('permission:view admin dashboard');
//     Route::resource('customer', CustomerController::class);
//     Route::resource('investor', InvestorCrudController::class);
//     Route::resource('booker', BookerCrudController::class);
//     Route::resource('bank', BankCrudController::class);
//     Route::resource('vehicle', VehicleCrudController::class);
//     Route::resource('sale-person', SalepersonController::class);
//     Route::resource('vehicle-status', VehiclestatusController::class);
//     Route::post('vehicle/import-csv', [VehicleCrudController::class, 'importCsv'])->middleware('permission:import vehicles CSV');
//     Route::resource('vehicle-type', VehicleTypeCrudController::class);
//     Route::get('/csv-sample', [VehicleCrudController::class, 'csvSample'])->name('download.sample');
//     Route::get('sync-zoho-customers', [CustomerController::class, 'syncCustomersFromZoho'])->name('syncCustomersFromZoho');
// });

// // Notification Route Read
// Route::post('/mark-notifications-read', [AjaxController::class, 'markAsRead'])->name('mark-notifications-read');

// // Ajax Reports Route
// Route::get('get-soa-list', [ReportController::class, 'getSoaReportList'])->name("getSoaReportList");
// Route::get('get-customer-wise-sales-list', [ReportController::class, 'getCustomerWiseSaleReportList']);
// Route::get('get-customer-wise-receivable-list', [ReportController::class, 'getCustomerWiseReceivableList']);
// Route::get('get-salemen-wise-list', [ReportController::class, 'getSalemenWiseReportList']);
// Route::get('get-investor-vehicle-list', [ReportController::class, 'getInvestorVehicleReportList']);

// // AJAX Routes
// Route::get('get-vehicle-by-Type/{id}', [AjaxController::class, 'getVehicleByType'])->name("getVehicleByType");
// Route::get('get-vehicle-detail/{id}', [AjaxController::class, 'getNoByVehicle'])->name("getNoByVehicle");
// Route::get('get-vehicle-by-booking/{id}/booking/{booking_id}', [AjaxController::class, 'getVehicleAgaistBooking']);
// Route::get('get-booking-detail/{id}', [AjaxController::class, 'getBookingDetail']);
// Route::get('get-invoice-detail/{id}', [AjaxController::class, 'getInvoiceDetail']);
// Route::get('get-payment-history/{payment_id}', [AjaxController::class, 'getPaymentHistory']);
// Route::get('booking-cancellation/{id}', [AjaxController::class, 'bookingCancellation']);
// Route::get('check-bookingis-active/{id}', [BookingController::class, 'isBookingActive']);

// // Get Data For Edit Forms
// Route::get('get-vehicle-status-edit-form/{id}', [AjaxController::class, 'getVehicleStatusForEditForm']);
// Route::get('get-vehicle-for-edit-form/{id}', [AjaxController::class, 'getVehicleForEditForm']);
// Route::get('get-salemen-for-edit-form/{id}', [AjaxController::class, 'getSalemanForEditForm']);
// Route::get('get-bank-for-edit-form/{id}', [AjaxController::class, 'getBankForEditForm']);
// Route::get('get-customer-for-edit-form/{id}', [AjaxController::class, 'getCustomerForEditForm']);
// Route::post('booking-convert-partial', [AjaxController::class, 'bookingConvertPartial']);

// Route::get('search-customer', [AjaxController::class, 'searchCustomer']);
// Route::get('search-payment', [AjaxController::class, 'searchPayment']);
// Route::get('getCustomerList', [FilterviewController::class, 'getCustomerList']);
// Route::get('get-payment-list', [FilterviewController::class, 'getPaymentList']);
// Route::get('/check-status/{id}', [BookingController::class, 'checkCloseEligibility'])->name('booking.check');
// Route::post('/booking/force-close/{id}', [BookingController::class, 'forceCloseBooking'])->name('booker.booking.force-close');
// Route::post('/booking/close/{id}', [BookingController::class, 'closeBooking'])->name('booker.booking.close');

// Route::prefix('booker')->as('booker.')->middleware(['auth', 'role:booker'])->group(function() {
//     Route::get('/dashboard', [BookerController::class, 'index'])->name('dashboard')->middleware('permission:view booker dashboard');
//     Route::resource('customer-booking', BookingController::class);
//     Route::get('booking-close/{booking_id}', [BookingController::class, 'closeBooking']);
//     Route::resource('customer', CustomerController::class);
//     Route::resource('payment', PaymentController::class);
//     Route::resource('credit-note', CreditnoteController::class);
//     Route::post('pending-payment/{booking_id}', [PaymentController::class, 'pendingPayment']);
//     Route::get('payment-history/{payment_id}', [PaymentController::class, 'paymentHistory']);
//     Route::get('sync-zoho-customers', [CustomerController::class, 'syncCustomersFromZoho'])->name('syncCustomersFromZoho');
//     Route::get('booking/view-invoice/{invoice_id}', [InvoiceController::class, 'viewInvoice'])->name('view.invoice');
//     Route::get('view-credit-note/{cn_id}', [CreditnoteController::class, 'viewCreditNote'])->name('view.creditNote');
//     Route::get('booking/{id}', [InvoiceController::class, 'index'])->name('view.invoice');
//     Route::get('booking/{id}/create-invoice', [InvoiceController::class, 'create'])->name('create.invoice');
//     Route::post('booking/{id}/create-invoice', [InvoiceController::class, 'store'])->name('store.invoice');
//     Route::get('booking/{invoice_id}/edit-invoice', [InvoiceController::class, 'edit'])->name('edit.invoice');
//     Route::put('booking/{invoice_id}/update-invoice', [InvoiceController::class, 'update'])->name('update.invoice');
//     Route::delete('booking/{invoice_id}/delete-invoice', [InvoiceController::class, 'destroy'])->name('destroy.invoice');
//     // Route::patch('booking/{invoice_id}/update-invoice', [InvoiceController::class, 'updateInvoiceStatus'])->name('update.status');

//     Route::get('assign-status', [VehiclestatusController::class, 'StatusForm'])->name('status.form');
//     Route::post('assign-status', [VehiclestatusController::class, 'assignStatus'])->name('assign.status');
//     Route::get('vehicle-assigned', [VehiclestatusController::class, 'viewAssinedVehicle'])->name('assined.vehicle');
//     Route::get('vehicle-assigned/{vehicle_id}/edit', [VehiclestatusController::class, 'editAssinedVehicle'])->name('assined.vehicle.edit');
//     Route::post('assign-status/{vehicle_id}/update', [VehiclestatusController::class, 'updateAssinedVehicle'])->name('assined.vehicle.update');
//     Route::get('vehicle-assigned/{vehicle_id}/delete', [VehiclestatusController::class, 'deleteAssinedVehicle'])->name('assined.vehicle.delete');

//     // Reports Route
//     Route::get('/reports/soa-report', [ReportController::class, 'soaReport'])->name('soaReport');
//     Route::get('/reports/customer-wise-report', [ReportController::class, 'customerWiseReport'])->name('customerWiseReport');
//     Route::get('/reports/customer-wise-receivable', [ReportController::class, 'customerWiseReceivable'])->name('customerWiseReceivable');
//     Route::get('/reports/salemen-wise-report', [ReportController::class, 'salemenWiseReport'])->name('salemenWiseReport');


// });

// Route::prefix('investor')->as('investor.')->middleware(['auth', 'role:investor', 'permission:view investor dashboard'])->group(function() {
//     Route::get('/dashboard', [InvestorController::class, 'index'])->name('dashboard');
//     // Route::get('/reports/bookingReport', [BookingController::class, 'bookingReport'])->name('bookingReport');
//     Route::get('/reports/bookingReport', [ReportController::class, 'investorVehicleReport'])->name('bookingReport');
// });




Route::middleware('auth')->group(function () {

    // Route::get('/dashboard', [InvestorController::class, 'index'])->name('dashboard');


    Route::get('/dashboard', [AdminController::class, 'index'])->name('dashboard');
    Route::resource('customer', CustomerController::class);
    Route::resource('investor', InvestorCrudController::class);
    Route::resource('booker', BookerCrudController::class);
    Route::resource('bank', BankCrudController::class);
    Route::resource('vehicle', VehicleCrudController::class);
    Route::resource('sale-person', SalepersonController::class);
    Route::resource('vehicle-status', VehiclestatusController::class);
    Route::post('vehicle/import-csv', [VehicleCrudController::class, 'importCsv'])->middleware('permission:import vehicles CSV');
    Route::resource('vehicle-type', VehicleTypeCrudController::class);
    Route::get('/csv-sample', [VehicleCrudController::class, 'csvSample'])->name('download.sample');
    Route::get('sync-zoho-customers', [CustomerController::class, 'syncCustomersFromZoho'])->name('syncCustomersFromZoho');




    // Role Permissions Route
    Route::get('roles', [RolePermissionController::class, 'index'])->name('view.role');
    Route::get('assign-permission/{role_id}', [RolePermissionController::class, 'assignPermissionForm']);
    Route::post('assign-permission/{role_id}', [RolePermissionController::class, 'assignPermissions']);



    Route::resource('customer-booking', BookingController::class);
    Route::resource('payment', PaymentController::class);
    Route::get('booking/{id}', [InvoiceController::class, 'index'])->name('view.invoice');
    Route::get('booking/{id}/create-invoice', [InvoiceController::class, 'create'])->name('create.invoice');
    Route::post('booking/{id}/create-invoice', [InvoiceController::class, 'store'])->name('store.invoice');
    Route::get('booking/{invoice_id}/edit-invoice', [InvoiceController::class, 'edit'])->name('edit.invoice');
    Route::put('booking/{invoice_id}/update-invoice', [InvoiceController::class, 'update'])->name('update.invoice');
    Route::delete('booking/{invoice_id}/delete-invoice', [InvoiceController::class, 'destroy'])->name('destroy.invoice');




    Route::get('booking-close/{booking_id}', [BookingController::class, 'closeBooking']);
    Route::resource('customer', CustomerController::class);
    Route::resource('credit-note', CreditnoteController::class);
    Route::post('pending-payment/{booking_id}', [PaymentController::class, 'pendingPayment']);
    Route::get('payment-history/{payment_id}', [PaymentController::class, 'paymentHistory']);
    Route::get('sync-zoho-customers', [CustomerController::class, 'syncCustomersFromZoho'])->name('syncCustomersFromZoho');
    Route::get('booking/view-invoice/{invoice_id}', [InvoiceController::class, 'viewInvoice'])->name('view.invoice');
    Route::get('view-credit-note/{cn_id}', [CreditnoteController::class, 'viewCreditNote'])->name('view.creditNote');
    // Route::patch('booking/{invoice_id}/update-invoice', [InvoiceController::class, 'updateInvoiceStatus'])->name('update.status');

    Route::get('assign-status', [VehiclestatusController::class, 'StatusForm'])->name('status.form');
    Route::post('assign-status', [VehiclestatusController::class, 'assignStatus'])->name('assign.status');
    Route::get('vehicle-assigned', [VehiclestatusController::class, 'viewAssinedVehicle'])->name('assined.vehicle');
    Route::get('vehicle-assigned/{vehicle_id}/edit', [VehiclestatusController::class, 'editAssinedVehicle'])->name('assined.vehicle.edit');
    Route::post('assign-status/{vehicle_id}/update', [VehiclestatusController::class, 'updateAssinedVehicle'])->name('assined.vehicle.update');
    Route::delete('vehicle-assigned/{vehicle_id}/delete', [VehiclestatusController::class, 'deleteAssinedVehicle'])->name('assined.vehicle.delete');

    // Reports Route
    Route::get('/reports/soa-report', [ReportController::class, 'soaReport'])->name('soaReport');
    Route::get('/reports/customer-wise-report', [ReportController::class, 'customerWiseReport'])->name('customerWiseReport');
    Route::get('/reports/customer-wise-receivable', [ReportController::class, 'customerWiseReceivable'])->name('customerWiseReceivable');
    Route::get('/reports/salemen-wise-report', [ReportController::class, 'salemenWiseReport'])->name('salemenWiseReport');

    // investor Report
    Route::get('/reports/bookingReport', [ReportController::class, 'investorVehicleReport'])->name('bookingReport');


});


// Notification Route Read
Route::post('/mark-notifications-read', [AjaxController::class, 'markAsRead'])->name('mark-notifications-read');

// Ajax Reports Route
Route::get('get-soa-list', [ReportController::class, 'getSoaReportList'])->name("getSoaReportList");
Route::get('get-customer-wise-sales-list', [ReportController::class, 'getCustomerWiseSaleReportList']);
Route::get('get-customer-wise-receivable-list', [ReportController::class, 'getCustomerWiseReceivableList']);
Route::get('get-salemen-wise-list', [ReportController::class, 'getSalemenWiseReportList']);
Route::get('get-investor-vehicle-list', [ReportController::class, 'getInvestorVehicleReportList']);

// AJAX Routes
Route::get('get-vehicle-by-Type/{id}', [AjaxController::class, 'getVehicleByType'])->name("getVehicleByType");
Route::get('get-vehicle-detail/{id}', [AjaxController::class, 'getNoByVehicle'])->name("getNoByVehicle");
Route::get('get-vehicle-by-booking/{id}/booking/{booking_id}', [AjaxController::class, 'getVehicleAgaistBooking']);
Route::get('get-booking-detail/{id}', [AjaxController::class, 'getBookingDetail']);
Route::get('get-invoice-detail/{id}', [AjaxController::class, 'getInvoiceDetail']);
Route::get('get-payment-history/{payment_id}', [AjaxController::class, 'getPaymentHistory']);
Route::get('booking-cancellation/{id}', [AjaxController::class, 'bookingCancellation']);
Route::get('check-bookingis-active/{id}', [BookingController::class, 'isBookingActive']);

// Get Data For Edit Forms
Route::get('get-vehicle-status-edit-form/{id}', [AjaxController::class, 'getVehicleStatusForEditForm']);
Route::get('get-vehicle-for-edit-form/{id}', [AjaxController::class, 'getVehicleForEditForm']);
Route::get('get-salemen-for-edit-form/{id}', [AjaxController::class, 'getSalemanForEditForm']);
Route::get('get-bank-for-edit-form/{id}', [AjaxController::class, 'getBankForEditForm']);
Route::get('get-customer-for-edit-form/{id}', [AjaxController::class, 'getCustomerForEditForm']);
Route::post('booking-convert-partial', [AjaxController::class, 'bookingConvertPartial']);

Route::get('search-customer', [AjaxController::class, 'searchCustomer']);
Route::get('search-bank', [AjaxController::class, 'searchBank']);
Route::get('search-payment', [AjaxController::class, 'searchPayment']);
Route::get('search-vehicle', [AjaxController::class, 'searchVehicle']);
Route::get('getCustomerList', [FilterviewController::class, 'getCustomerList']);
Route::get('get-payment-list', [FilterviewController::class, 'getPaymentList']);
Route::get('/check-status/{id}', [BookingController::class, 'checkCloseEligibility'])->name('booking.check');
Route::post('/booking/force-close/{id}', [BookingController::class, 'forceCloseBooking'])->name('booker.booking.force-close');
Route::post('/booking/close/{id}', [BookingController::class, 'closeBooking'])->name('booker.booking.close');


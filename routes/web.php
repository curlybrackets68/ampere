<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LeadsController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\OrdersController;
use App\Http\Controllers\AmcMasterController;
use App\Http\Controllers\ServiceController;
use App\Models\Order;
use Illuminate\Support\Facades\Route;

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

Route::group(['middleware' => 'guest'], function () {
    Route::get('/', function () {
        return view('auth.index');
    });
    Route::get('login', [LoginController::class, 'showLogin'])->name(name: 'auth.show-login');
    Route::post('/login', [LoginController::class, 'login'])->name('auth.login');
});

Route::middleware(['auth'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'dashboard'])->name('dashboard');
    Route::get('get-inquiry-chart', [DashboardController::class, 'getInquiryChart'])->name('get-inquiry-chart');
    Route::get('get-order-chart', [DashboardController::class, 'getOrderChart'])->name('get-order-chart');
    Route::get('get-lead-chart', [DashboardController::class, 'getLeadChart'])->name('get-lead-chart');
    Route::get('inquiry', [DashboardController::class, 'inquiryDetails'])->name('inquiry');
    Route::get('logout', [LoginController::class, 'logout'])->name('auth.logout');
    Route::post('change-status', [DashboardController::class, 'changeStatus'])->name('inquiry.change-status');
    Route::post('export-inquiry', [DashboardController::class, 'export'])->name('user.inquiry.excel.export');
    Route::post('send-message', [DashboardController::class, 'sendMessage'])->name('send-message');
    Route::resource('leads', LeadsController::class);
    Route::post('export-leads', [LeadsController::class, 'export'])->name('user.leads.excel.export');

    Route::post('add-vehicle', [LeadsController::class, 'addVehicle'])->name('add-vehicle');
    Route::get('vehicle-details', [LeadsController::class, 'vehicleDetails'])->name('vehicle-details');

    Route::post('add-salesman', [LeadsController::class, 'addSalesman'])->name('add-salesman');
    Route::get('salesman-details/{id?}', [LeadsController::class, 'salesmanDetails'])->name('salesman-details');

    Route::get('salesman', [LeadsController::class, 'salesmanIndex'])->name('salesman');

    Route::post('add-lead-source', [LeadsController::class, 'addLeadSource'])->name('add-lead-source');
    Route::get('lead-source-details', [LeadsController::class, 'leadSourceDetails'])->name('lead-source-details');

   // Route::resource('orders', OrdersController::class);
    Route::get('orders', [OrdersController::class, 'index'])->name('orders');
    Route::post('orders-change-status', [OrdersController::class, 'changeStatus'])->name('orders.change-status');

    Route::get('get-history/{type_id?}', [OrdersController::class, 'getHistory'])->name('orders.get-history');

    //amc
    Route::resource('amc-master', AmcMasterController::class);
    Route::get('get-amc-package-master', [AmcMasterController::class, 'getAmcPackageMaster'])->name('amc-master.get-amc-package-master');
    Route::get('amc-master/renew/{amc_id}', [AmcMasterController::class, 'renew'])->name('amc-master.renew');
    Route::post('amc-master/renew-handel', [AmcMasterController::class, 'renewHandel'])->name('amc-master.renew-handel');
    
   // Route::resource('amc-master-service', ServiceController::class);
    Route::get('amc-master-service', [ServiceController::class, 'index'])->name('amc-master-service.index');
    Route::get('amc-master-service/service', [ServiceController::class, 'addService'])->name('amc-master-service.service');
    Route::get('amc-master-service/get-service-details-by-chassis-number', [ServiceController::class, 'getServiceDetailsByChassisNumber'])->name('amc-master-service.get-service-details-by-chassis-number');
});


include_once 'admin.php';

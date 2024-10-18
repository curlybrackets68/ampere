<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LoginController;
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
    Route::get('login', [LoginController::class, 'showLogin'])->name('auth.show-login');
    Route::post('/login', [LoginController::class, 'login'])->name('auth.login');
});

Route::middleware(['auth'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'dashboard'])->name('dashboard');
    Route::get('inquiry', [DashboardController::class, 'inquiryDetails'])->name('inquiry');
    Route::get('logout', [LoginController::class, 'logout'])->name('auth.logout');
    Route::post('change-status', [DashboardController::class, 'changeStatus'])->name('inquiry.change-status');
    Route::post('export-inquiry', [DashboardController::class, 'export'])->name('user.inquiry.excel.export');
    Route::post('send-message', [DashboardController::class, 'sendMessage'])->name('send-message');
});

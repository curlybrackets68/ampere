<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\LoginController;
use Illuminate\Support\Facades\Route;

Route::prefix("admin")->group(function () {
    Route::group(['middleware' => 'guest:admin'], function () {
        Route::get('/', function () {
            return view('admin.auth.index');
        });
        Route::get('/', [LoginController::class, 'showAdminLogin'])->name(name: 'admin-login');
        Route::post('login', [LoginController::class, 'adminLogin'])->name('admin.auth.login');
    });

    Route::middleware(['auth:admin'])->group(function () {
        Route::get('dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
        Route::get('logout', [LoginController::class, 'adminLogout'])->name('admin.logout');
    });
});

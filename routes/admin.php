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

        Route::get('users', [AdminController::class, 'userList'])->name('admin.users');
        Route::get('edit-user/{id}', [AdminController::class, 'editUser'])->name('admin.edit-user');
        Route::post('add-edit-user', [AdminController::class, 'addEditUser'])->name('admin.add-edit-user');
        Route::get('modules', [AdminController::class, 'modules'])->name('admin.modules');
        Route::post('add-edit-module', [AdminController::class, 'addEditModule'])->name('admin.add-edit-modules');

        Route::get('rights/{id}', [AdminController::class, 'userRights'])->name('admin.rights');
        Route::post('save-rights', [AdminController::class, 'saveUserRights'])->name('admin.save-rights');
    });
});

<?php

use App\Http\Controllers\Api\v1\InquiryDetailsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('v1')->group(function () {
    Route::post('add-inquiry', [InquiryDetailsController::class, 'addInquiry']);
    Route::post('check-inquiry', [InquiryDetailsController::class, 'checkInquiry']);
    Route::post('run-artisan', [InquiryDetailsController::class, 'runArtisan']);

});

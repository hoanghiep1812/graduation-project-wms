<?php

use App\Http\Controllers\Api\ChatbotApiController;
use App\Models\User;
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
Route::post('/login', [\App\Http\Controllers\Api\AuthController::class, 'login']);

Route::prefix('chatbot')->group(function () {

    Route::get('/products/all', [ChatbotApiController::class, 'getAllProducts']);

    Route::get('/inventory/by-product', [ChatbotApiController::class, 'checkInventory']);
    Route::get('/inventory/low-stock', [ChatbotApiController::class, 'getLowStock']);
    Route::get('/inventory/expiring', [ChatbotApiController::class, 'getExpiringBatches']);
    Route::get('/product/location', [ChatbotApiController::class, 'getProductLocation']);
    Route::get('/orders/status', [ChatbotApiController::class, 'getOrderStatus']);
    Route::get('/movement', [ChatbotApiController::class, 'getMonthlyMovement']);
    Route::get('/velocity', [ChatbotApiController::class, 'getVelocityReport']);
    Route::get('/velocity/by-product', [ChatbotApiController::class, 'checkProductVelocity']);
    Route::get('/bin/capacity', [ChatbotApiController::class, 'checkBinCapacity']);
    Route::get('/bin/products', [ChatbotApiController::class, 'getProductsInBin']);
    Route::get('/product/search', [ChatbotApiController::class, 'searchProductByName']);
    Route::get('/movement/exported', [ChatbotApiController::class, 'getMonthlyExportedProducts']);
    Route::get('/movement/imported', [ChatbotApiController::class, 'getMonthlyImportedProducts']);
    Route::get('/summary', [ChatbotApiController::class, 'getWarehouseSummary']);
    Route::get('/orders/pending', [ChatbotApiController::class, 'getPendingOrders']);
    Route::get('/orders/overdue', [ChatbotApiController::class, 'getOverdueOrders']);
    Route::get('/bin/overloaded', [ChatbotApiController::class, 'getOverloadedBins']);
    Route::get('/product/history', [ChatbotApiController::class, 'getProductHistory']);

    Route::get('/movement/never-exported',  [ChatbotApiController::class, 'getNeverExportedProducts']);
    Route::get('/movement/dead-stock',      [ChatbotApiController::class, 'getDeadStock']);
    Route::get('/movement/comparison',      [ChatbotApiController::class, 'getMonthlyComparison']);
    Route::get('/zone/summary',             [ChatbotApiController::class, 'getZoneSummary']);
    Route::get('/movement/today-imported',  [ChatbotApiController::class, 'getTodayImported']);
    Route::get('/movement/today-exported',  [ChatbotApiController::class, 'getTodayExported']);
    Route::get('/inventory/most-reserved',  [ChatbotApiController::class, 'getMostReserved']);
    Route::get('/inventory/adjustments', [ChatbotApiController::class, 'getAdjustmentHistory']);
    Route::get('/movement/check-product', [ChatbotApiController::class, 'checkProductInMovement']);
    Route::get('/movement/today-check', [ChatbotApiController::class, 'todayCheckProduct']);
});

Route::post('/update-fcm-token', function (Request $request) {
    $request->validate([
        'fcm_token' => 'required|string',
        'user_id' => 'required|exists:users,id' 
    ]);

    $user = User::find($request->user_id);
    $user->update(['fcm_token' => $request->fcm_token]);

    return response()->json([
        'success' => true,
        'message' => 'Đã cập nhật FCM Token thành công!'
    ]);
});

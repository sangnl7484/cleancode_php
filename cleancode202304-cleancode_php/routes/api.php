<?php

use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::get('/user', [\App\Http\Controllers\Api\AuthController::class, 'getUser']);
    Route::post('/logout', [\App\Http\Controllers\Api\AuthController::class, 'logout']);

    Route::get('customers/index', [CustomerController::class, 'index']);
    Route::post('customers/update', [CustomerController::class, 'update']);
    Route::post('customers/delete', [CustomerController::class, 'destroy']);
  
    Route::post('products/create', [ProductController::class, 'create']);
    Route::post('products/update', [ProductController::class, 'update']);
    Route::post('products/delete', [ProductController::class, 'delete']);

    Route::post('orders/change-status/{id}/{status}', [OrderController::class, 'changeStatusOrder']);
    Route::get('orders/{id}', [OrderController::class, 'orderDetail']);
    Route::get('orders', [OrderController::class, 'getOrderList']);
    Route::post('orders/create', [OrderController::class, 'createOrder']);

    Route::get('/dashboard/count', [DashboardController::class, 'count']);
    Route::get('/dashboard/top-user-orders', [DashboardController::class, 'topUserOrders']);
    Route::get('/dashboard/top-sellers', [DashboardController::class, 'topSellers']);

    Route::get('/report/index', [ReportController::class, 'index']);
    Route::get('/report/orders', [ReportController::class, 'OrdersReport']);
});

Route::post('/login', [\App\Http\Controllers\Api\AuthController::class, 'login']);

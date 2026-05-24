<?php

use App\Http\Controllers\Admin\BinLocationController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\InboundController;
use App\Http\Controllers\Admin\InventoryAdjustmentController;
use App\Http\Controllers\Admin\InventoryAuditController;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ReslottingController;
use App\Http\Controllers\Admin\SalesOrderController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\ZoneController;
use App\Http\Controllers\Admin\SupplierController;
use App\Http\Controllers\Admin\PartnerController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\StockMovementController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('login');
});

Route::controller(LoginController::class)->group(function () {
    Route::get('/login', 'showLoginForm')->name('login');
    Route::post('/login', 'login');
    Route::post('/logout', 'logout')->name('logout');
});

Route::middleware(['auth'])->group(function () {

    Route::post('/notifications/mark-read', function () {
        \App\Models\Notification::where('user_id', auth()->id())
            ->where('is_read', false)
            ->update(['is_read' => true]);
        return response()->json(['success' => true]);
    })->name('notifications.markRead');

    Route::get('/notifications/poll', function () {
        $userId = auth()->id();

        $unreadCount = \App\Models\Notification::where('user_id', $userId)
            ->where('is_read', false)
            ->count();

        $notifications = \App\Models\Notification::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $html = view('layouts.partials.notifications_list', compact('notifications'))->render();

        return response()->json([
            'unread_count' => $unreadCount,
            'html' => $html
        ]);
    })->name('notifications.poll');

    Route::get('/notifications/{id}/read', function ($id) {
        $noti = \App\Models\Notification::where('user_id', auth()->id())->find($id);

        if ($noti && !$noti->is_read) {
            $noti->update(['is_read' => true]);
        }

        
        return redirect()->route('admin.reslotting.index');
    })->name('notifications.read');

	Route::prefix('admin')->name('admin.')->group(function () {

    Route::middleware('role:staff')->group(function () {

	    Route::get('/dashboard', [DashboardController::class, 'index'])
	        ->name('dashboard.index');
	
	    Route::controller(SalesOrderController::class)
	        ->prefix('sales-orders')
	        ->name('sales_orders.')
	        ->group(function () {
	
	            Route::get('/', 'index')->name('index');
	            Route::get('/create', 'create')->name('create');
	            Route::post('/', 'store')->name('store');
	            Route::delete('/{id}', 'destroy')->name('destroy');
	            Route::post('/{id}/start-picking', 'startPicking')->name('start_picking');
	            Route::get('/{id}/picking', 'pickingRoute')->name('picking_route');
	            Route::post('/{id}/complete-picking', 'completePicking')->name('complete_picking');
	            Route::post('/{id}/confirm-shipment', 'confirmShipment')->name('confirm_shipment');
	            Route::get('/{id}/export-pdf', 'exportPdf')->name('export_pdf');
	        });
	
	    Route::controller(InboundController::class)
	        ->prefix('inbound')
	        ->name('inbound.')
	        ->group(function () {
	
	            Route::get('/', 'index')->name('index');
	            Route::get('/create', 'create')->name('create');
	            Route::post('/', 'store')->name('store');
	            Route::delete('/{id}', 'destroy')->name('destroy');
	            Route::get('/{id}/putaway', 'putaway')->name('putaway');
	            Route::post('/{id}/complete-putaway', 'completePutaway')->name('complete_putaway');
	            Route::get('/{id}/export-pdf', 'exportPdf')->name('export_pdf');
	        });
	
	    Route::controller(ReslottingController::class)
	        ->prefix('reslotting')
	        ->name('reslotting.')
	        ->group(function () {
	
	            Route::get('/', 'index')->name('index');
	            Route::post('/{id}/approve', 'approve')->name('approve');
	            Route::post('/{id}/reject', 'reject')->name('reject');
	            Route::post('/generate', 'generate')->name('generate');
	        });
	
	    Route::get('/inventory', [InventoryController::class, 'index'])
	        ->name('inventory.index');
	
	    Route::get('/inventory/export', [InventoryController::class, 'export'])
	        ->name('inventory.export');
	});
	
	Route::middleware('role:admin')->group(function () {

	    Route::resource('products', ProductController::class);
	    Route::resource('suppliers', SupplierController::class);
	    Route::resource('partners', PartnerController::class);
	    Route::resource('zones', ZoneController::class);
	    Route::resource('bins', BinLocationController::class);
	
	    Route::controller(UserController::class)
	        ->prefix('users')
	        ->name('users.')
	        ->group(function () {
	
	            Route::get('/', 'index')->name('index');
	            Route::post('/store', 'store')->name('store');
	            Route::post('/reset-password', 'resetPassword')->name('reset-password');
	            Route::delete('/{user}', 'destroy')->name('destroy');
	        });
	
	    Route::controller(InventoryAdjustmentController::class)
	        ->prefix('inventory-adjustments')
	        ->name('adjustments.')
	        ->group(function () {
	
	            Route::get('/create', 'create')->name('create');
	            Route::post('/', 'store')->name('store');
	        });
	
	    Route::get('/stock-movements', [StockMovementController::class, 'index'])
	        ->name('stock_movements.index');
	
	    Route::get('/stock-movements/export', [StockMovementController::class, 'export'])
	        ->name('stock_movements.export');
	
	    Route::controller(InventoryAuditController::class)
	        ->prefix('audits')
	        ->name('audits.')
	        ->group(function () {
	
	            Route::get('/', 'index')->name('index');
	            Route::post('/store', 'store')->name('store');
	            Route::get('/{id}', 'show')->name('show');
	            Route::post('/{id}/update', 'update')->name('update');
	            Route::post('/{id}/complete', 'complete')->name('complete');
	        });
		});
	});
});

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductDemandMetric;
use App\Models\PurchaseOrder;
use App\Models\SalesOrder;
use App\Models\SlottingRecommendation;
use App\Models\Inventory;
use App\Models\BinLocation;
use App\Services\InventoryAlertService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(InventoryAlertService $alertService)
    {
        $warehouseId = 1; 

        $pendingOutbound = SalesOrder::whereIn('status', ['draft', 'confirmed'])->count();
        $pendingInbound = PurchaseOrder::where('status', 'pending')->count();
        $pendingReslottingTasks = SlottingRecommendation::where('status', 'pending')->count();

        $capacityStats = BinLocation::where('warehouse_id', $warehouseId)
            ->selectRaw('SUM(max_capacity) as total_max, SUM(current_capacity) as total_used')
            ->first();

        $totalMax = $capacityStats->total_max ?? 1; 
        $totalUsed = $capacityStats->total_used ?? 0;
        $totalFree = max(0, $totalMax - $totalUsed);
        $usagePercentage = round(($totalUsed / $totalMax) * 100, 1);

        $abcStats = [
            'fast'   => ProductDemandMetric::where('velocity_category', 'FAST_MOVING')->count(),
            'medium' => ProductDemandMetric::where('velocity_category', 'MEDIUM_MOVING')->count(),
            'slow'   => ProductDemandMetric::where('velocity_category', 'SLOW_MOVING')->count(),
        ];

        $expiringBatches = Inventory::select('inventories.*')
            ->join('batches', 'inventories.batch_id', '=', 'batches.id')
            ->with(['product', 'batch', 'binLocation.zone'])
            ->where('inventories.on_hand_quantity', '>', 0)
            ->whereNotNull('batches.expiry_date')
            ->where('batches.expiry_date', '<=', Carbon::now()->addDays(30)) 
            ->orderBy('batches.expiry_date', 'asc')
            ->take(5)
            ->get();

        $criticalLowStocks = $alertService->getLowStockProducts($warehouseId);

        $lowStockProductsCount = $criticalLowStocks->count();
        $topCriticalLowStocks = $criticalLowStocks->sortBy('total_available')->take(5);

        $topSellers = ProductDemandMetric::with('product')
            ->orderBy('sales_30_days', 'desc')
            ->take(5)
            ->get();

        $recentOrders = SalesOrder::orderBy('created_at', 'desc')->take(5)->get();

        return view('admin.dashboard.index', compact(
            'pendingOutbound',
            'pendingInbound',
            'pendingReslottingTasks',
            'usagePercentage',
            'totalUsed',
            'totalFree', 
            'abcStats',
            'expiringBatches', 
            'lowStockProductsCount',
            'topCriticalLowStocks',
            'criticalLowStocks',
            'topSellers', 
            'recentOrders'
        ));
    }
}

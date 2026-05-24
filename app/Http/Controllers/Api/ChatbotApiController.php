<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BinLocation;
use App\Models\Inventory;
use App\Models\InventoryAdjustmentItem;
use App\Models\Product;
use App\Models\ProductDemandMetric;
use App\Models\PurchaseOrder;
use App\Models\SalesOrder;
use App\Models\SlottingRecommendation;
use App\Models\StockMovement;
use App\Models\Zone;
use Illuminate\Http\Request;

class ChatbotApiController extends Controller
{
    public function checkInventory(Request $request)
    {
        $sku = $request->query('sku');
        if (!$sku) {
            return response()->json(['error' => 'Missing SKU'], 400);
        }

        $qty = Inventory::whereHas('product', function ($q) use ($sku) {
            $q->where('sku', $sku);
        })
            ->selectRaw('SUM(on_hand_quantity - reserved_quantity) as available')
            ->value('available') ?? 0;

        return response()->json([
            'sku'           => $sku,
            'available_qty' => (int) $qty
        ]);
    }

    public function getLowStock(\App\Services\InventoryAlertService $alertService)
    {
        $warehouseId = 1;

        $criticalLowStocks = $alertService->getLowStockProducts($warehouseId);

        $items = $criticalLowStocks->map(function ($item) {
            $qty = data_get($item, 'total_available') 
                ?? data_get($item, 'total_qty') 
                ?? data_get($item, 'on_hand') 
                ?? data_get($item, 'qty') 
                ?? 0;

            if ($qty == 0 && isset($item->inventories)) {
                $qty = $item->inventories->sum('on_hand_quantity');
            }

            return [
                'name' => data_get($item, 'name', 'N/A'),
                'sku'  => data_get($item, 'sku', 'N/A'),
                'qty'  => (int) $qty,
                'min'  => (int) data_get($item, 'minimum_stock', 0) 
            ];
        })
        ->sortBy('qty')
        ->values();

        return response()->json([
            'items' => $items,
            'count' => $items->count()
        ]);
    }


    public function getProductLocation(Request $request)
    {
        $sku = $request->query('sku');
        if (!$sku) {
            return response()->json(['error' => 'Missing SKU'], 400);
        }

        $locations = Inventory::with('binLocation.zone')
            ->whereHas('product', function ($q) use ($sku) {
                $q->where('sku', $sku);
            })
            ->where('on_hand_quantity', '>', 0)
            ->get()
            ->map(function ($inv) {
                if (!$inv->binLocation) return null;

                return $inv->binLocation->code . ' (Khu: ' .
                    ($inv->binLocation->zone->code ?? 'N/A') . ')';
            })
            ->filter()
            ->unique()
            ->values();

        return response()->json([
            'sku'       => $sku,
            'locations' => $locations
        ]);
    }

    public function getOrderStatus(Request $request)
    {
        $so = $request->query('so_number');
        if (!$so) return response()->json(['error' => 'Missing SO Number'], 400);

        $order = SalesOrder::where('so_number', $so)->first();

        if (!$order) {
            return response()->json(['found' => false]);
        }

        return response()->json([
            'found' => true,
            'so_number' => $order->so_number,
            'status' => $order->status
        ]);
    }
    public function getMonthlyMovement(Request $request)
    {
        $sku = $request->query('sku');
        if (!$sku) {
            return response()->json(['error' => 'Missing SKU'], 400);
        }

        $product = Product::where('sku', $sku)->first();
        if (!$product) {
            return response()->json(['found' => false]);
        }

        $start = now()->startOfMonth();

        $movements = StockMovement::whereHas('inventory', function ($q) use ($product) {
            $q->where('product_id', $product->id);
        })
            ->where('created_at', '>=', $start)
            ->get();

        $totalIn = $movements
            ->whereIn('transaction_type', ['inbound', 'adjustment_increase', 'Nhập kho'])
            ->sum('quantity_change');

        $totalOut = $movements
            ->whereIn('transaction_type', ['outbound', 'loss', 'damage', 'Xuất kho'])
            ->sum('quantity_change');

        return response()->json([
            'found' => true,
            'sku'   => $sku,
            'name'  => $product->name,
            'month' => now()->format('m/Y'),
            'total_in'  => (int) abs($totalIn),
            'total_out' => (int) abs($totalOut),
        ]);
    }

    public function getVelocityReport(Request $request)
    {
        $type = $request->query('type', 'slow');

        $map = [
            'fast'   => 'FAST_MOVING',
            'medium' => 'MEDIUM_MOVING',
            'slow'   => 'SLOW_MOVING'
        ];

        $category = $map[$type] ?? 'SLOW_MOVING';

        $items = ProductDemandMetric::with('product')
            ->where('velocity_category', $category)
            ->orderBy('sales_30_days', $type === 'fast' ? 'desc' : 'asc')
            ->limit(5)
            ->get()
            ->map(function ($metric) {
                return [
                    'sku'   => $metric->product->sku ?? 'N/A',
                    'name'  => $metric->product->name ?? 'N/A',
                    'sales' => (int) $metric->sales_30_days
                ];
            });

        return response()->json([
            'type'  => $category,
            'items' => $items
        ]);
    }
    public function checkProductVelocity(Request $request)
    {
        $sku = $request->query('sku');
        if (!$sku) {
            return response()->json(['error' => 'Missing SKU'], 400);
        }

        $metric = ProductDemandMetric::whereHas('product', function ($q) use ($sku) {
            $q->where('sku', $sku);
        })->first();

        if (!$metric) {
            return response()->json(['found' => false]);
        }

        return response()->json([
            'found'           => true,
            'sku'             => $sku,
            'category'        => $metric->velocity_category,
            'sales_30_days'   => (int) $metric->sales_30_days,
            'sales_90_days'   => (int) $metric->sales_90_days,
            'last_calculated' => $metric->last_calculated_at
        ]);
    }


    public function checkBinCapacity(Request $request)
    {
        $binCode = $request->query('bin');
        if (!$binCode) {
            return response()->json(['error' => 'Missing Bin Code'], 400);
        }

        $bin = BinLocation::with('zone')
            ->where('code', 'LIKE', '%' . $binCode . '%')
            ->first();

        if (!$bin) {
            return response()->json(['found' => false]);
        }

        return response()->json([
            'found'     => true,
            'code'      => $bin->code,
            'zone'      => $bin->zone->code ?? 'N/A',
            'max'       => $bin->max_capacity,
            'current'   => $bin->current_capacity,
            'available' => $bin->max_capacity - $bin->current_capacity
        ]);
    }

    public function getProductsInBin(Request $request)
    {
        $binCode = $request->query('bin');
        if (!$binCode) return response()->json(['error' => 'Missing Bin Code'], 400);

        $bin = BinLocation::where('code', $binCode)
            ->orWhere('code', 'Kệ ' . $binCode)
            ->orWhere('code', 'LIKE', '%' . $binCode . '%')
            ->first();

        if (!$bin) return response()->json(['found' => false]);

        $products = Inventory::with('product')
            ->where('bin_location_id', $bin->id)
            ->where('on_hand_quantity', '>', 0)
            ->get()
            ->map(function ($inv) {
                return [
                    'sku'      => $inv->product->sku ?? 'N/A',
                    'name'     => $inv->product->name ?? 'N/A',
                    'quantity' => (int) $inv->on_hand_quantity,
                ];
            });

        return response()->json([
            'found'    => true,
            'bin'      => $bin->code,
            'zone'     => $bin->zone->code ?? 'N/A',
            'products' => $products,
            'total'    => $products->count(),
        ]);
    }
    public function searchProductByName(Request $request)
    {
        $keyword = $request->query('name');
        if (!$keyword) return response()->json(['error' => 'Missing name'], 400);

        $products = Product::where('name', 'LIKE', '%' . $keyword . '%')
            ->orWhere('sku', 'LIKE', '%' . $keyword . '%')
            ->limit(5)
            ->get(['sku', 'name']);

        return response()->json([
            'found'    => $products->count() > 0,
            'products' => $products,
            'count'    => $products->count(),
        ]);
    }

    public function getMonthlyExportedProducts(Request $request)
    {
        $month = $request->query('month', date('m'));
        $year = $request->query('year', date('Y'));

        $movements = StockMovement::with('inventory.product')
            ->where('transaction_type', 'outbound')
            ->whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->get()
            ->groupBy(fn($m) => $m->inventory->product_id ?? 0);

        $items = $movements->map(function ($group) {
            $product = $group->first()->inventory->product ?? null;
            if (!$product) return null;
            return [
                'sku'        => $product->sku,
                'name'       => $product->name,
                'total_out'  => (int) abs($group->sum('quantity_change')),
            ];
        })->filter()->sortByDesc('total_out')->values();

        return response()->json([
            'month' => str_pad($month, 2, '0', STR_PAD_LEFT) . '/' . $year,
            'total_products' => $items->count(),
            'items' => $items->values(),
        ]);
    }

    public function getMonthlyImportedProducts(Request $request)
    {
        $month = $request->query('month', date('m'));
        $year = $request->query('year', date('Y'));

        $movements = StockMovement::with('inventory.product')
            ->whereIn('transaction_type', ['inbound', 'adjustment_increase'])
            ->whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->get()
            ->groupBy(fn($m) => $m->inventory->product_id ?? 0);

        $items = $movements->map(function ($group) {
            $product = $group->first()->inventory->product ?? null;
            if (!$product) return null;
            return [
                'sku'       => $product->sku,
                'name'      => $product->name,
                'total_in'  => (int) abs($group->sum('quantity_change')),
            ];
        })->filter()->sortByDesc('total_in')->values();

        return response()->json([
            'month' => str_pad($month, 2, '0', STR_PAD_LEFT) . '/' . $year,
            'total_products' => $items->count(),
            'items' => $items,
        ]);
    }


    public function getWarehouseSummary(\App\Services\InventoryAlertService $alertService)
    {
        $warehouseId = 1;

        $pendingOutbound = SalesOrder::whereIn('status', ['draft', 'confirmed'])->count();
        $pendingInbound = PurchaseOrder::where('status', 'pending')->count();
        $pendingReslotting = SlottingRecommendation::where('status', 'pending')->count();

        $capacityStats = BinLocation::where('warehouse_id', $warehouseId)
            ->selectRaw('SUM(max_capacity) as total_max, SUM(current_capacity) as total_used')
            ->first();
        $totalMax = $capacityStats->total_max ?? 1;
        $totalUsed = $capacityStats->total_used ?? 0;
        $usagePercentage = round(($totalUsed / $totalMax) * 100, 1);

        $abcStats = [
            'fast'   => ProductDemandMetric::where('velocity_category', 'FAST_MOVING')->count(),
            'medium' => ProductDemandMetric::where('velocity_category', 'MEDIUM_MOVING')->count(),
            'slow'   => ProductDemandMetric::where('velocity_category', 'SLOW_MOVING')->count(),
        ];

        $criticalLowStocks = $alertService->getLowStockProducts($warehouseId);

        return response()->json([
            'kpi' => [
                'pending_outbound' => $pendingOutbound,
                'pending_inbound'  => $pendingInbound,
                'pending_reslotting' => $pendingReslotting,
            ],
            'space' => [
                'total_used' => (int) $totalUsed,
                'total_free' => (int) max(0, $totalMax - $totalUsed),
                'percentage' => $usagePercentage,
            ],
            'abc' => $abcStats,
            'alerts' => [
                'low_stock_count' => $criticalLowStocks->count(),
            ],
            'as_of' => now()->format('H:i d/m/Y'),
        ]);
    }


    public function getPendingOrders()
    {
        $orders = SalesOrder::whereIn('status', ['pending', 'processing'])
            ->orderBy('created_at', 'asc')
            ->limit(10)
            ->get(['so_number', 'status', 'created_at']);

        return response()->json([
            'count' => $orders->count(),
            'items' => $orders->map(fn($o) => [
                'so_number'  => $o->so_number,
                'status'     => $o->status,
                'created_at' => $o->created_at->format('d/m/Y'),
                'days_waiting' => $o->created_at->diffInDays(now()),
            ]),
        ]);
    }


    public function getOverdueOrders()
    {
        $orders = SalesOrder::where('status', 'pending')
            ->where('created_at', '<', now()->subDays(3))
            ->orderBy('created_at', 'asc')
            ->limit(10)
            ->get(['so_number', 'status', 'created_at']);

        return response()->json([
            'count' => $orders->count(),
            'items' => $orders->map(fn($o) => [
                'so_number'    => $o->so_number,
                'status'       => $o->status,
                'created_at'   => $o->created_at->format('d/m/Y'),
                'days_overdue' => $o->created_at->diffInDays(now()),
            ]),
        ]);
    }

    public function getOverloadedBins()
    {
        $bins = BinLocation::with('zone')
            ->whereRaw('current_capacity >= max_capacity * 0.9')
            ->orderByRaw('current_capacity / max_capacity DESC')
            ->limit(10)
            ->get();

        return response()->json([
            'count' => $bins->count(),
            'items' => $bins->map(fn($b) => [
                'code'       => $b->code,
                'zone'       => $b->zone->code ?? 'N/A',
                'current'    => $b->current_capacity,
                'max'        => $b->max_capacity,
                'percent'    => round($b->current_capacity / $b->max_capacity * 100, 1),
            ]),
        ]);
    }

    public function getProductHistory(Request $request)
    {
        $sku = $request->query('sku');
        if (!$sku) return response()->json(['error' => 'Missing SKU'], 400);

        $product = Product::where('sku', $sku)->first();
        if (!$product) return response()->json(['found' => false]);

        $movements = StockMovement::whereHas('inventory', fn($q) =>
        $q->where('product_id', $product->id))
            ->where('created_at', '>=', now()->subDays(7))
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get()
            ->map(fn($m) => [
                'type'     => $m->transaction_type,
                'quantity' => abs($m->quantity_change),
                'date'     => $m->created_at->format('d/m H:i'),
                'note'     => $m->note ?? '',
            ]);

        return response()->json([
            'found'    => true,
            'sku'      => $sku,
            'name'     => $product->name,
            'period'   => '7 ngày gần nhất',
            'movements' => $movements,
            'total_records' => $movements->count(),
        ]);
    }

    public function getNeverExportedProducts()
    {
        $exportedProductIds = StockMovement::with('inventory')
            ->whereIn('transaction_type', ['outbound', 'transfer_out', 'loss', 'damage'])
            ->whereMonth('created_at', date('m'))
            ->whereYear('created_at', date('Y'))
            ->get()
            ->pluck('inventory.product_id')
            ->filter()
            ->unique();

        $products = Inventory::with('product')
            ->selectRaw('product_id, SUM(on_hand_quantity) as total_qty')
            ->groupBy('product_id')
            ->havingRaw('total_qty > 0')
            ->whereNotIn('product_id', $exportedProductIds)
            ->get()
            ->map(fn($inv) => [
                'sku'       => $inv->product->sku   ?? 'N/A',
                'name'      => $inv->product->name  ?? 'N/A',
                'on_hand'   => (int) $inv->total_qty,
            ])
            ->sortByDesc('on_hand')
            ->values();

        return response()->json([
            'month'          => date('m/Y'),
            'total_products' => $products->count(),
            'items'          => $products,
        ]);
    }

    public function getDeadStock()
    {
        $recentExported = StockMovement::with('inventory')
            ->whereIn('transaction_type', ['outbound', 'transfer_out'])
            ->where('created_at', '>=', now()->subDays(30))
            ->get()
            ->pluck('inventory.product_id')
            ->filter()
            ->unique();

        $products = Inventory::with('product')
            ->selectRaw('product_id, SUM(on_hand_quantity) as total_qty')
            ->groupBy('product_id')
            ->havingRaw('total_qty > 0')
            ->whereNotIn('product_id', $recentExported)
            ->get()
            ->map(fn($inv) => [
                'sku'     => $inv->product->sku  ?? 'N/A',
                'name'    => $inv->product->name ?? 'N/A',
                'on_hand' => (int) $inv->total_qty,
            ])
            ->sortByDesc('on_hand')
            ->values();

        return response()->json([
            'period'         => '30 ngày',
            'total_products' => $products->count(),
            'items'          => $products,
        ]);
    }

    public function getMonthlyComparison()
    {
        $calcMonth = function (int $month, int $year) {
            $movements = StockMovement::whereMonth('created_at', $month)
                ->whereYear('created_at', $year)
                ->get();
            return [
                'in'  => (int) abs($movements->whereIn(
                    'transaction_type',
                    ['inbound', 'transfer_in', 'adjustment_increase']
                )->sum('quantity_change')),
                'out' => (int) abs($movements->whereIn(
                    'transaction_type',
                    ['outbound', 'transfer_out', 'loss', 'damage']
                )->sum('quantity_change')),
                'txn' => $movements->count(),
            ];
        };

        $thisMonth = now();
        $lastMonth = now()->subMonth();

        $current  = $calcMonth($thisMonth->month, $thisMonth->year);
        $previous = $calcMonth($lastMonth->month, $lastMonth->year);

        $inChange  = $previous['in']  > 0 ? round(($current['in']  - $previous['in'])  / $previous['in']  * 100, 1) : null;
        $outChange = $previous['out'] > 0 ? round(($current['out'] - $previous['out']) / $previous['out'] * 100, 1) : null;

        return response()->json([
            'this_month'  => $thisMonth->format('m/Y'),
            'last_month'  => $lastMonth->format('m/Y'),
            'current'     => $current,
            'previous'    => $previous,
            'in_change'   => $inChange,
            'out_change'  => $outChange,
        ]);
    }

    public function getZoneSummary()
    {
        $zones = Zone::with('binLocations')->get()
            ->map(function ($zone) {
                $bins    = $zone->binLocations;
                $total   = $bins->sum('max_capacity');
                $current = $bins->sum('current_capacity');
                return [
                    'zone'       => $zone->code,
                    'name'       => $zone->name ?? $zone->code,
                    'total_bins' => $bins->count(),
                    'capacity'   => (int) $total,
                    'used'       => (int) $current,
                    'free'       => (int) ($total - $current),
                    'percent'    => $total > 0 ? round($current / $total * 100, 1) : 0,
                ];
            })
            ->sortByDesc('percent')
            ->values();

        return response()->json([
            'total_zones' => $zones->count(),
            'zones'       => $zones,
        ]);
    }

    public function getTodayImported(Request $request)
    {
        $dateStr = $request->query('date');
        $targetDate = $dateStr ? \Carbon\Carbon::parse($dateStr) : today();

        $movements = StockMovement::with('inventory.product')
            ->whereIn('transaction_type', ['inbound', 'adjustment_increase'])
            ->whereDate('created_at', $targetDate)
            ->get()
            ->groupBy(fn($m) => $m->inventory->product_id ?? 0);

        $items = $movements->map(function ($group) {
            $product = $group->first()->inventory->product ?? null;
            if (!$product) return null;
            return [
                'sku'      => $product->sku,
                'name'     => $product->name,
                'total_in' => (int) abs($group->sum('quantity_change')),
            ];
        })->filter()->sortByDesc('total_in')->values();

        return response()->json([
            'date'           => $targetDate->format('d/m/Y'),
            'total_products' => $items->count(),
            'items'          => $items,
        ]);
    }

    public function getTodayExported(Request $request)
    {
        $dateStr = $request->query('date');
        $targetDate = $dateStr ? \Carbon\Carbon::parse($dateStr) : today();

        $movements = StockMovement::with('inventory.product')
            ->whereIn('transaction_type', ['outbound'])
            ->whereDate('created_at', $targetDate)
            ->get()
            ->groupBy(fn($m) => $m->inventory->product_id ?? 0);

        $items = $movements->map(function ($group) {
            $product = $group->first()->inventory->product ?? null;
            if (!$product) return null;
            return [
                'sku'       => $product->sku,
                'name'      => $product->name,
                'total_out' => (int) abs($group->sum('quantity_change')),
            ];
        })->filter()->sortByDesc('total_out')->values();

        return response()->json([
            'date'           => $targetDate->format('d/m/Y'),
            'total_products' => $items->count(),
            'items'          => $items,
        ]);
    }

    public function getMostReserved()
    {
        $items = Inventory::with('product')
            ->selectRaw('product_id, SUM(reserved_quantity) as total_reserved,
                     SUM(on_hand_quantity) as total_on_hand')
            ->groupBy('product_id')
            ->havingRaw('total_reserved > 0')
            ->orderByRaw('total_reserved DESC')
            ->limit(10)
            ->get()
            ->map(fn($inv) => [
                'sku'      => $inv->product->sku  ?? 'N/A',
                'name'     => $inv->product->name ?? 'N/A',
                'reserved' => (int) $inv->total_reserved,
                'on_hand'  => (int) $inv->total_on_hand,
                'percent'  => $inv->total_on_hand > 0
                    ? round($inv->total_reserved / $inv->total_on_hand * 100, 1)
                    : 0,
            ]);

        return response()->json([
            'total' => $items->count(),
            'items' => $items,
        ]);
    }
    public function checkProductInMovement(Request $request)
    {
        $sku  = $request->query('sku');
        $type = $request->query('type', 'both');
        if (!$sku) return response()->json(['error' => 'Missing SKU'], 400);

        $product = Product::where('sku', $sku)->first();
        if (!$product) return response()->json(['found' => false]);

        $inTypes  = ['inbound', 'adjustment_increase'];
        $outTypes = ['outbound', 'loss', 'damage'];

        $base = StockMovement::whereHas(
            'inventory',
            fn($q) => $q->where('product_id', $product->id)
        )
            ->whereMonth('created_at', date('m'))
            ->whereYear('created_at', date('Y'));

        $totalIn  = (clone $base)->whereIn('transaction_type', $inTypes)
            ->sum('quantity_change');
        $totalOut = (clone $base)->whereIn('transaction_type', $outTypes)
            ->sum('quantity_change');

        return response()->json([
            'found'     => true,
            'sku'       => $sku,
            'name'      => $product->name,
            'month'     => date('m/Y'),
            'has_in'    => abs($totalIn)  > 0,
            'has_out'   => abs($totalOut) > 0,
            'total_in'  => (int) abs($totalIn),
            'total_out' => (int) abs($totalOut),
        ]);
    }

    public function todayCheckProduct(Request $request)
    {
        $sku  = $request->query('sku');
        $dateStr = $request->query('date');
        $targetDate = $dateStr ? \Carbon\Carbon::parse($dateStr) : today();

        if (!$sku) return response()->json(['error' => 'Missing SKU'], 400);

        $product = Product::where('sku', $sku)->first();
        if (!$product) return response()->json(['found' => false]);

        $inTypes  = ['inbound', 'adjustment_increase'];
        $outTypes = ['outbound', 'loss', 'damage'];

        $base = StockMovement::whereHas(
            'inventory',
            fn($q) => $q->where('product_id', $product->id)
        )->whereDate('created_at', $targetDate);

        $totalIn  = (clone $base)->whereIn('transaction_type', $inTypes)->sum('quantity_change');
        $totalOut = (clone $base)->whereIn('transaction_type', $outTypes)->sum('quantity_change');

        return response()->json([
            'found'        => true,
            'sku'          => $sku,
            'name'         => $product->name,
            'date'         => $targetDate->format('d/m/Y'),
            'total_in'     => (int) abs($totalIn),
            'total_out'    => (int) abs($totalOut),
        ]);
    }
    public function getExpiringBatches()
    {
        $expiringBatches = Inventory::select('inventories.*')
            ->join('batches', 'inventories.batch_id', '=', 'batches.id')
            ->with(['product', 'batch', 'binLocation.zone'])
            ->where('inventories.on_hand_quantity', '>', 0)
            ->whereNotNull('batches.expiry_date')
            ->where('batches.expiry_date', '>=', now())
            ->orderBy('batches.expiry_date', 'asc')
            ->take(20)
            ->get();

        $data = $expiringBatches->map(function ($inv) {

            $batchName = !empty($inv->batch->batch_number)
                ? $inv->batch->batch_number
                : 'Nhập: ' . $inv->created_at->format('d/m/Y');
            return [
                'sku' => $inv->product->sku ?? 'N/A',
                'name' => $inv->product->name ?? 'Unknown',
                'batch_code'  => $batchName,
                'expiry_date' => \Carbon\Carbon::parse($inv->batch->expiry_date)->format('d/m/Y'),
                'location' => $inv->binLocation->code ?? 'N/A',
                'qty' => $inv->on_hand_quantity
            ];
        });

        return response()->json(['items' => $data]);
    }
    public function getAllProducts()
    {
        $products = Product::get(['sku', 'name']);
        return response()->json($products);
    }

    public function getAdjustmentHistory(Request $request)
    {
        $sku = $request->query('sku');
        $date = $request->query('date');

        $query = InventoryAdjustmentItem::with(['adjustment'])
            ->join('inventories', 'inventory_adjustment_items.inventory_id', '=', 'inventories.id')
            ->join('products', 'inventories.product_id', '=', 'products.id')
            ->where('products.sku', $sku)
            ->whereHas('adjustment', function ($q) {
                $q->where('status', '!=', 'cancelled');
            });

        if ($date) {
            $query->whereDate('inventory_adjustment_items.created_at', $date);
        }

        $records = $query->orderBy('inventory_adjustment_items.created_at', 'desc')->get();

        $items = [];
        foreach ($records as $rec) {
            $items[] = [
                'date' => $rec->adjustment->created_at->format('d/m/Y H:i'),
                'variance' => $rec->variance,
                'reason' => $rec->adjustment->reason,
                'counter' => $rec->adjustment->counter_name
            ];
        }

        return response()->json([
            'found' => true,
            'sku' => $sku,
            'items' => $items
        ]);
    }
}

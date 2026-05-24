<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockMovement;
use App\Models\ProductDemandMetric;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DemandAnalysisService
{
    public function calculateVelocityForAllProducts()
    {
        $now = Carbon::now();

        $start30 = $now->copy()->subMonth()->startOfMonth();
        $end30   = $now->copy()->subMonth()->endOfMonth();

        $start90 = $now->copy()->subMonths(3)->startOfMonth();
        $end90   = $end30;

        $movements = StockMovement::join('inventories', 'stock_movements.inventory_id', '=', 'inventories.id')
            ->where('stock_movements.transaction_type', 'outbound')
            ->whereBetween('stock_movements.created_at', [$start90, $end90])
            ->selectRaw('
                inventories.product_id,
                SUM(CASE 
                    WHEN stock_movements.created_at BETWEEN ? AND ? 
                    THEN ABS(stock_movements.quantity_change) 
                    ELSE 0 
                END) as sales_30_days,
                SUM(ABS(stock_movements.quantity_change)) as sales_90_days
            ', [$start30, $end30])
            ->groupBy('inventories.product_id')
            ->get();

        $totalWarehouseSales30Days = $movements->sum('sales_30_days');

        if ($totalWarehouseSales30Days <= 0) {
            $this->markAllAsSlow($now);
            return true;
        }

        $sortedMovements = $movements->sortByDesc('sales_30_days')->values();

        $cumulativeSales = 0;
        $processedProductIds = [];

        DB::transaction(function () use (
            $sortedMovements,
            $totalWarehouseSales30Days,
            &$cumulativeSales,
            &$processedProductIds,
            $now
        ) {
            foreach ($sortedMovements as $item) {
                $sales30 = $item->sales_30_days ?? 0;
                $sales90 = $item->sales_90_days ?? 0;
                $productId = $item->product_id;

                $processedProductIds[] = $productId;

                $cumulativeSales += $sales30;
                $cumulativePercentage = ($cumulativeSales / $totalWarehouseSales30Days) * 100;

                if ($cumulativePercentage <= 80) {
                    $category = 'FAST_MOVING';
                } elseif ($cumulativePercentage <= 95) {
                    $category = 'MEDIUM_MOVING';
                } else {
                    $category = 'SLOW_MOVING';
                }

                ProductDemandMetric::updateOrCreate(
                    ['product_id' => $productId],
                    [
                        'sales_30_days'      => $sales30,
                        'sales_90_days'      => $sales90,
                        'velocity_category'  => $category,
                        'last_calculated_at' => $now
                    ]
                );
            }

            Product::whereNotIn('id', $processedProductIds)
                ->chunk(500, function ($products) use ($now) {
                    foreach ($products as $product) {
                        ProductDemandMetric::updateOrCreate(
                            ['product_id' => $product->id],
                            [
                                'sales_30_days'      => 0,
                                'sales_90_days'      => 0,
                                'velocity_category'  => 'SLOW_MOVING',
                                'last_calculated_at' => $now
                            ]
                        );
                    }
                });
        });

        return true;
    }

    private function markAllAsSlow($now)
    {
        Product::chunk(500, function ($products) use ($now) {
            foreach ($products as $product) {
                ProductDemandMetric::updateOrCreate(
                    ['product_id' => $product->id],
                    [
                        'sales_30_days'      => 0,
                        'sales_90_days'      => 0,
                        'velocity_category'  => 'SLOW_MOVING',
                        'last_calculated_at' => $now
                    ]
                );
            }
        });
    }
}

<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\DB;

class InventoryAlertService
{
    public function getLowStockProducts($warehouseId = null)
    {
        $query = Product::select(
            'products.id',
            'products.name',
            'products.sku',
            'products.minimum_stock'
        )
            ->leftJoin('inventories', function ($join) use ($warehouseId) {
                $join->on('products.id', '=', 'inventories.product_id');

                if ($warehouseId) {
                    $join->where('inventories.warehouse_id', '=', $warehouseId);
                }
            })
            ->selectRaw('COALESCE(SUM(inventories.on_hand_quantity - inventories.reserved_quantity), 0) as total_available')
            ->groupBy(
                'products.id',
                'products.name',
                'products.sku',
                'products.minimum_stock'
            )
            ->havingRaw('total_available < products.minimum_stock')
            ->where('products.minimum_stock', '>', 0);

        return $query->get();
    }
}

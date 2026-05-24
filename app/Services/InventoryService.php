<?php

namespace App\Services;

use App\Models\Inventory;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    public function receiveStock($productId, $warehouseId, $binId, $quantity)
    {
        DB::transaction(function () use ($productId, $warehouseId, $binId, $quantity) {

            $inventory = Inventory::lockForUpdate()->firstOrCreate(
                [
                    'product_id' => $productId,
                    'warehouse_id' => $warehouseId,
                    'bin_location_id' => $binId
                ],
                [
                    'on_hand_quantity' => 0,
                    'reserved_quantity' => 0
                ]
            );

            $inventory->on_hand_quantity += $quantity;
            $inventory->save();
        });
    }

    public function reserveStock($productId, $warehouseId, $binId, $quantity)
    {
        DB::transaction(function () use ($productId, $warehouseId, $binId, $quantity) {

            $inventory = Inventory::where([
                'product_id' => $productId,
                'warehouse_id' => $warehouseId,
                'bin_location_id' => $binId
            ])->lockForUpdate()->first();

            if (!$inventory) {
                throw new \Exception("Inventory not found");
            }

            $available = $inventory->on_hand_quantity - $inventory->reserved_quantity;

            if ($available < $quantity) {
                throw new \Exception("Not enough available stock");
            }

            $inventory->reserved_quantity += $quantity;
            $inventory->save();
        });
    }

    public function completeShipment($productId, $warehouseId, $binId, $quantity)
    {
        DB::transaction(function () use ($productId, $warehouseId, $binId, $quantity) {

            $inventory = Inventory::where([
                'product_id' => $productId,
                'warehouse_id' => $warehouseId,
                'bin_location_id' => $binId
            ])->lockForUpdate()->first();

            if (!$inventory || $inventory->reserved_quantity < $quantity) {
                throw new \Exception("Invalid shipment quantity");
            }

            $inventory->reserved_quantity -= $quantity;
            $inventory->on_hand_quantity -= $quantity;
            $inventory->save();
        });
    }
}

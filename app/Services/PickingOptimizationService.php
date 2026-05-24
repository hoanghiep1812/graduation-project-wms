<?php

namespace App\Services;

use App\Models\Inventory;
use Exception;

class PickingOptimizationService
{
    /**
     *
     * @param int $productId 
     * @param int $warehouseId 
     * @param int $requestedQty 
     * @return array 
     * @throws Exception 
     */
    public function getOptimalPickingLocations(int $productId, int $warehouseId, int $requestedQty)
    {
        $inventories = Inventory::select('inventories.*')
            ->join('batches', 'inventories.batch_id', '=', 'batches.id')
            ->join('bin_locations', 'inventories.bin_location_id', '=', 'bin_locations.id')
            ->leftJoin('zones', 'bin_locations.zone_id', '=', 'zones.id')
            ->where('inventories.product_id', $productId)
            ->where('bin_locations.warehouse_id', $warehouseId)
            ->whereRaw('(inventories.on_hand_quantity - inventories.reserved_quantity) > 0')
            ->orderByRaw('batches.expiry_date IS NULL ASC')
            ->orderBy('batches.expiry_date', 'asc')
            ->orderByRaw('(inventories.on_hand_quantity - inventories.reserved_quantity) asc')
            ->orderBy('zones.distance_to_packing', 'asc')
            ->get();

        $allocatedLocations = [];
        $remainingToPick = $requestedQty;

        foreach ($inventories as $inv) {
            if ($remainingToPick <= 0) break;

            $availableQty = $inv->on_hand_quantity - $inv->reserved_quantity;
            $pickQty = min($availableQty, $remainingToPick);

            $reason = [
                'fefo'         => true,
                'clear_bin'    => ($pickQty == $availableQty),
                'near_packing' => ($inv->binLocation->zone->distance_to_packing <= 20)
            ];

            $allocatedLocations[] = [
                'inventory_id'  => $inv->id,
                'bin_code'      => $inv->binLocation->code,
                'zone_code'     => $inv->binLocation->zone ? $inv->binLocation->zone->code : 'N/A',
                'batch_number'  => $inv->batch->batch_number,
                'expiry_date'   => $inv->batch->expiry_date,
                'pick_quantity' => $pickQty,
                'reason'        => $reason
            ];

            $remainingToPick -= $pickQty;
        }

        if ($remainingToPick > 0) {
            throw new Exception("Không đủ hàng khả dụng. Kho chỉ còn có thể xuất tối đa " . ($requestedQty - $remainingToPick) . " sản phẩm.");
        }

        return $allocatedLocations;
    }
}

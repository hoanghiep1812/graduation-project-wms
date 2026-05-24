<?php

namespace App\Services;

use App\Models\BinLocation;
use App\Models\Inventory;
use App\Models\ProductDemandMetric;
use Exception;

class SlottingService
{
    public function suggestBinsForInbound(int $productId, int $warehouseId, int $quantityToStore)
    {
        $allocations = [];
        $remainingQty = $quantityToStore;

        $metric = ProductDemandMetric::where('product_id', $productId)->first();
        $hasHistory = $metric || Inventory::where('product_id', $productId)->exists();

        if ($hasHistory) {

            $category = $metric ? $metric->velocity_category : 'SLOW_MOVING';

            $targetZoneCode = match ($category) {
                'FAST_MOVING'   => 'ZONE_A',
                'MEDIUM_MOVING' => 'ZONE_B',
                'SLOW_MOVING'   => 'ZONE_C',
                default         => 'ZONE_C',
            };

            $existingInventories = Inventory::with('binLocation.zone')
                ->where('product_id', $productId)
                ->where('warehouse_id', $warehouseId)
                ->orderBy('on_hand_quantity', 'desc')
                ->get();

            $consolidationBins = $existingInventories
                ->pluck('binLocation')
                ->filter()
                ->unique('id');

            foreach ($consolidationBins as $bin) {
                if ($remainingQty <= 0) break;

                $availableSpace = $bin->max_capacity - $bin->current_capacity;
                if ($availableSpace <= 0) continue;

                $qtyToPut = min($availableSpace, $remainingQty);

                $allocations[] = [
                    'bin' => $bin,
                    'quantity' => $qtyToPut,
                    'category' => $category,
                    'is_consolidation' => true,
                    'reason' => 'Ưu tiên gom hàng: Kệ đã chứa cùng SKU.'
                ];

                $remainingQty -= $qtyToPut;

                $bin->current_capacity += $qtyToPut;
            }

            if ($remainingQty > 0) {

                $usedBinIds = array_map(fn($a) => $a['bin']->id, $allocations);

                $newBins = BinLocation::with('zone')
                    ->where('warehouse_id', $warehouseId)
                    ->whereHas('zone', fn($q) => $q->where('code', $targetZoneCode))
                    ->whereNotIn('id', $usedBinIds)
                    ->whereRaw('(max_capacity - current_capacity) > 0')
                    ->orderBy('current_capacity', 'asc')
                    ->get();

                foreach ($newBins as $bin) {
                    if ($remainingQty <= 0) break;

                    $availableSpace = $bin->max_capacity - $bin->current_capacity;
                    if ($availableSpace <= 0) continue;

                    $qtyToPut = min($availableSpace, $remainingQty);

                    $allocations[] = [
                        'bin' => $bin,
                        'quantity' => $qtyToPut,
                        'category' => $category,
                        'is_consolidation' => false,
                        'reason' => "Phân bổ theo Demand: {$category} → {$targetZoneCode}"
                    ];

                    $remainingQty -= $qtyToPut;
                    $bin->current_capacity += $qtyToPut;
                }
            }
        } else {
            $category = 'NEW_PRODUCT';
        }

        if ($remainingQty > 0) {

            $usedBinIds = array_map(fn($a) => $a['bin']->id, $allocations);

            $fallbackBins = BinLocation::with('zone')
                ->where('warehouse_id', $warehouseId)
                ->whereNotIn('id', $usedBinIds)
                ->whereRaw('(max_capacity - current_capacity) > 0')
                ->orderByRaw('(max_capacity - current_capacity) DESC')
                ->get();

            foreach ($fallbackBins as $bin) {
                if ($remainingQty <= 0) break;

                $availableSpace = $bin->max_capacity - $bin->current_capacity;
                if ($availableSpace <= 0) continue;

                $qtyToPut = min($availableSpace, $remainingQty);

                $reason = ($category === 'NEW_PRODUCT')
                    ? 'Hàng mới: chọn kệ còn trống nhiều nhất.'
                    : 'Zone ưu tiên đã đầy → dùng kệ dự phòng tốt nhất.';

                $allocations[] = [
                    'bin' => $bin,
                    'quantity' => $qtyToPut,
                    'category' => $category,
                    'is_consolidation' => false,
                    'reason' => $reason
                ];

                $remainingQty -= $qtyToPut;
                $bin->current_capacity += $qtyToPut;
            }
        }

        if ($remainingQty > 0) {
            throw new Exception("Kho đã ĐẦY! Còn thiếu {$remainingQty} chỗ chứa.");
        }

        return $allocations;
    }
}

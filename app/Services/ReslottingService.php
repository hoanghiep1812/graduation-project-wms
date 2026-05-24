<?php

namespace App\Services;

use App\Models\BinLocation;
use App\Models\Inventory;
use App\Models\ProductDemandMetric;
use App\Models\SlottingRecommendation;

class ReslottingService
{
    public function generateRecommendations($warehouseId)
    {
        $metrics = ProductDemandMetric::all()->keyBy('product_id');
        $recommendationsCreated = 0;

        $allBins = BinLocation::with('zone')
            ->where('warehouse_id', $warehouseId)
            ->whereRaw('(max_capacity - current_capacity) > 0')
            ->get();

        $binsByZoneDist = [];
        foreach ($allBins as $bin) {
            if ($bin->zone) {
                $dist = $bin->zone->distance_to_packing;
                $binsByZoneDist[$dist][] = $bin;
            }
        }
        ksort($binsByZoneDist);

        $existingPending = SlottingRecommendation::where('status', 'pending')
            ->select('product_id', 'current_bin_id')
            ->get()
            ->map(fn($r) => $r->product_id . '-' . $r->current_bin_id)
            ->flip();

        Inventory::with(['binLocation.zone'])
            ->where('warehouse_id', $warehouseId)
            ->where('on_hand_quantity', '>', 0)
            ->where('reserved_quantity', 0)
            ->chunk(500, function ($inventories) use (
                $metrics,
                &$recommendationsCreated,
                &$existingPending,
                $binsByZoneDist
            ) {

                foreach ($inventories as $inv) {

                    $metric = $metrics->get($inv->product_id);

                    $velocity = $metric ? $metric->sales_30_days : 0;
                    $category = $metric ? $metric->velocity_category : 'SLOW_MOVING';

                    $currentBin = $inv->binLocation;
                    if (!$currentBin || !$currentBin->zone) continue;

                    $currentDistance = $currentBin->zone->distance_to_packing;
                    $isSurging = ($velocity > 150);

                    $needsReslotting = false;
                    $impactText = "";

                    if (($category === 'FAST_MOVING' || $isSurging) && $currentDistance > 10) {
                        $needsReslotting = true;
                        $impactText = "Giảm thời gian picking";
                    } elseif ($category === 'SLOW_MOVING' && $currentDistance <= 10) {
                        $needsReslotting = true;
                        $impactText = "Đẩy ra xa để nhường chỗ cho hàng nhanh";
                    } else {
                        continue;
                    }

                    $bestBin = null;
                    $highestScore = -999999;

                    if ($category === 'FAST_MOVING' || $isSurging) {
                        foreach ($binsByZoneDist as $dist => $bins) {
                            if ($dist >= $currentDistance) break;

                            foreach ($bins as $bin) {
                                if ($bin->id == $currentBin->id) continue;

                                $availableCap = $bin->max_capacity - $bin->current_capacity;
                                if ($availableCap <= 0) continue;

                                $score = (500 / ($dist + 1)) + ($availableCap * 0.2);

                                if ($score > $highestScore) {
                                    $highestScore = $score;
                                    $bestBin = $bin;
                                }
                            }
                            if ($bestBin) break;
                        }
                    } elseif ($category === 'SLOW_MOVING') {
                        $reversed = array_reverse($binsByZoneDist, true);

                        foreach ($reversed as $dist => $bins) {
                            if ($dist <= $currentDistance) break;

                            foreach ($bins as $bin) {
                                if ($bin->id == $currentBin->id) continue;

                                $availableCap = $bin->max_capacity - $bin->current_capacity;
                                if ($availableCap <= 0) continue;

                                $score = ($dist * 10) + ($availableCap * 0.2);

                                if ($score > $highestScore) {
                                    $highestScore = $score;
                                    $bestBin = $bin;
                                }
                            }
                            if ($bestBin) break;
                        }
                    }

                    if (!$bestBin) continue;

                    $qtyToMove = min(
                        $inv->on_hand_quantity,
                        $bestBin->max_capacity - $bestBin->current_capacity
                    );

                    if ($qtyToMove <= 0) continue;

                    $bestBin->current_capacity += $qtyToMove;

                    $distanceDiff = abs(
                        $currentDistance - $bestBin->zone->distance_to_packing
                    );

                    $priority =
                        ($velocity * 2) +
                        $qtyToMove +
                        ($distanceDiff * 5);

                    if ($isSurging) $priority += 500;

                    $reasonArr = [
                        'category' => $isSurging ? 'SURGE_DEMAND' : $category,
                        'current_dist' => $currentDistance,
                        'new_dist' => $bestBin->zone->distance_to_packing,
                        'qty_to_move' => $qtyToMove,
                        'impact' => $impactText
                    ];

                    $checkKey = $inv->product_id . '-' . $currentBin->id;

                    if (!isset($existingPending[$checkKey])) {
                        SlottingRecommendation::create([
                            'product_id'         => $inv->product_id,
                            'current_bin_id'     => $currentBin->id,
                            'suggested_zone_id'  => $bestBin->zone_id,
                            'recommended_bin_id' => $bestBin->id,
                            'reason'             => json_encode($reasonArr),
                            'priority'           => $priority,
                            'status'             => 'pending'
                        ]);

                        $existingPending[$checkKey] = true;
                        $recommendationsCreated++;
                    }
                }
            });

        return $recommendationsCreated;
    }
}

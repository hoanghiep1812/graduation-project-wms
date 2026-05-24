<?php

namespace App\Services;

use App\Models\SalesOrder;
use App\Models\SalesOrderItemAllocation;
use App\Models\Inventory;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;

class SalesOrderService
{
    protected $stockMovementService;
    protected $pickingService;

    public function __construct(
        StockMovementService $stockMovementService,
        PickingOptimizationService $pickingService
    ) {
        $this->stockMovementService = $stockMovementService;
        $this->pickingService = $pickingService;
    }

    public function reserveStock(SalesOrder $so, int $warehouseId, $userId = null)
    {
        if ($so->status !== 'draft') {
            throw new Exception("Only draft orders can be reserved.");
        }

        DB::transaction(function () use ($so, $warehouseId, $userId) {

            foreach ($so->items as $item) {

                $pickingPlan = $this->pickingService->getOptimalPickingLocations(
                    $item->product_id,
                    $warehouseId,
                    $item->quantity
                );

                if (empty($pickingPlan)) {
                    throw new Exception("Không đủ tồn kho cho sản phẩm ID: {$item->product_id}");
                }

                foreach ($pickingPlan as $plan) {

                    $inventory = Inventory::lockForUpdate()
                        ->find($plan['inventory_id']);

                    if (!$inventory) {
                        throw new Exception("Inventory không tồn tại.");
                    }

                    $available = $inventory->on_hand_quantity - $inventory->reserved_quantity;

                    if ($available < $plan['pick_quantity']) {
                        throw new Exception("Hàng vừa bị lấy trước tại kệ {$inventory->binLocation->code}");
                    }

                    $inventory->reserved_quantity += $plan['pick_quantity'];
                    $inventory->save();

                    SalesOrderItemAllocation::create([
                        'sales_order_item_id' => $item->id,
                        'inventory_id'        => $inventory->id,
                        'allocated_quantity'  => $plan['pick_quantity'],
                    ]);
                }
            }

            $so->update([
                'status' => 'confirmed',
                'confirmed_by' => $userId,
                'confirmed_at' => Carbon::now(),
            ]);
        });
    }

    public function releaseStock(SalesOrder $so, $userId = null)
    {
        if (!in_array($so->status, ['confirmed', 'picking'])) {
            throw new Exception("Không thể nhả kho ở trạng thái này.");
        }

        DB::transaction(function () use ($so, $userId) {

            foreach ($so->items as $item) {

                $allocations = $item->allocations()->lockForUpdate()->get();

                foreach ($allocations as $allocation) {

                    $inventory = Inventory::lockForUpdate()
                        ->find($allocation->inventory_id);

                    if ($inventory) {
                        $inventory->reserved_quantity -= $allocation->allocated_quantity;
                        $inventory->reserved_quantity = max(0, $inventory->reserved_quantity);
                        $inventory->save();
                    }

                    $allocation->delete();
                }
            }

            $so->update([
                'status' => 'cancelled',
                'cancelled_by' => $userId,
                'cancelled_at' => Carbon::now(),
                'assigned_to' => null,
            ]);
        });
    }

    public function shipOrder(SalesOrder $so, $userId = null)
    {
        if ($so->status !== 'picked') {
            throw new Exception("Chỉ đơn đã PICKED mới được ship.");
        }

        DB::transaction(function () use ($so, $userId) {

            foreach ($so->items as $item) {

                $allocations = $item->allocations()->lockForUpdate()->get();

                if ($allocations->isEmpty()) {
                    throw new Exception("Không có allocation để xuất kho.");
                }

                $totalShipped = 0;

                foreach ($allocations as $allocation) {

                    $inventory = Inventory::lockForUpdate()
                        ->find($allocation->inventory_id);

                    if (!$inventory) {
                        throw new Exception("Inventory missing.");
                    }

                    if ($inventory->on_hand_quantity < $allocation->allocated_quantity) {
                        throw new Exception("Không đủ tồn kho để ship.");
                    }

                    $inventory->on_hand_quantity -= $allocation->allocated_quantity;
                    $inventory->reserved_quantity -= $allocation->allocated_quantity;
                    $inventory->reserved_quantity = max(0, $inventory->reserved_quantity);
                    $inventory->save();

                    $bin = \App\Models\BinLocation::lockForUpdate()
                        ->find($inventory->bin_location_id);

                    if ($bin) {
                        $bin->current_capacity -= $allocation->allocated_quantity;
                        $bin->current_capacity = max(0, $bin->current_capacity);
                        $bin->save();
                    }

                    $this->stockMovementService->recordMovement(
                        $inventory,
                        -$allocation->allocated_quantity,
                        'outbound',
                        $so,
                        $userId
                    );

                    $totalShipped += $allocation->allocated_quantity;
                }

                $item->shipped_quantity += $totalShipped;
                $item->save();
            }

            $so->update([
                'status' => 'shipped',
                'shipped_by' => $userId,
                'shipped_at' => Carbon::now(),
                'assigned_to' => null,
            ]);
        });
    }
}

<?php

namespace App\Services;

use App\Models\BinLocation;
use App\Models\InventoryAdjustment;
use App\Models\InventoryAdjustmentItem;
use App\Models\Inventory;
use Illuminate\Support\Facades\DB;
use Exception;

class InventoryAdjustmentService
{
    protected $stockMovementService;

    public function __construct(StockMovementService $stockMovementService)
    {
        $this->stockMovementService = $stockMovementService;
    }

    public function approve(InventoryAdjustment $adjustment, int $userId)
    {
        if (!in_array($adjustment->status, ['draft', 'counting'])) {
            throw new Exception("Chỉ phiếu kiểm kê ở trạng thái draft hoặc counting mới được duyệt.");
        }

        DB::transaction(function () use ($adjustment, $userId) {
            foreach ($adjustment->items as $item) {
                if (is_null($item->counted_quantity)) {
                    throw new Exception("Lỗi: Chưa nhập số lượng đếm thực tế (counted_quantity) cho Inventory ID {$item->inventory_id}");
                }

                $inventory = Inventory::where('id', $item->inventory_id)->lockForUpdate()->first();

                if (!$inventory) {
                    throw new Exception("Không tìm thấy dòng tồn kho ID: {$item->inventory_id}");
                }

                if ($item->counted_quantity < $inventory->reserved_quantity) {
                    throw new Exception("Lỗi nghiêm trọng: Đếm được {$item->counted_quantity} nhưng Kệ này đang bị Giữ chỗ (Reserved) {$inventory->reserved_quantity} cái chờ xuất. Hãy giải quyết Đơn hàng xuất kho trước khi điều chỉnh!");
                }

                $systemQty = $inventory->on_hand_quantity;
                $variance = $item->counted_quantity - $systemQty;

                if ($variance != 0) {
                    $inventory->on_hand_quantity = $item->counted_quantity;
                    $inventory->save();

                    $bin = BinLocation::lockForUpdate()->find($inventory->bin_location_id);
                    if ($bin) {
                        $bin->current_capacity += $variance;
                        if ($bin->current_capacity < 0) $bin->current_capacity = 0;
                        $bin->save();
                    }

                    $type = $variance > 0 ? 'adjustment_increase' : 'adjustment_decrease';
                    if ($adjustment->reason == 'Hàng mất cắp / Thất lạc') $type = 'loss';
                    elseif ($adjustment->reason == 'Hư hỏng / Rách vỡ') $type = 'damage';
                    elseif ($adjustment->reason == 'Kiểm kê định kỳ (Cycle Count)') $type = 'cycle_count';

                    $note = "Kiểm kê bởi: {$adjustment->counter_name} | Ghi chú: {$adjustment->reason}";

                    $this->stockMovementService->recordMovement(
                        $inventory,
                        $variance,
                        $type,
                        $adjustment,
                        $userId,
                        $note
                    );
                }

                $item->update([
                    'system_quantity' => $systemQty,
                    'variance' => $variance
                ]);
            }

            $adjustment->update([
                'status' => 'approved',
                'approved_by' => $userId,
            ]);
        });

        return $adjustment;
    }
}

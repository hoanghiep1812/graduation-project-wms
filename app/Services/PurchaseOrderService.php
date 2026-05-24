<?php

namespace App\Services;

use App\Models\PurchaseOrder;
use App\Models\Inventory;
use App\Models\BinLocation;
use App\Models\Batch;
use App\Models\PurchaseOrderItem;
use Illuminate\Support\Facades\DB;
use Exception;

class PurchaseOrderService
{
    protected $stockMovementService;

    public function __construct(StockMovementService $stockMovementService)
    {
        $this->stockMovementService = $stockMovementService;
    }

    public function approve(PurchaseOrder $po, int $userId)
    {
        if (!$po->isDraft()) {
            throw new Exception("Only draft PO can be approved.");
        }

        $po->update([
            'status' => PurchaseOrder::STATUS_APPROVED,
            'approved_by' => $userId,
            'approved_at' => now(),
        ]);

        return $po;
    }

    public function complete(PurchaseOrder $po, array $placements, int $userId)
    {
        DB::transaction(function () use ($po, $placements, $userId) {

            $lockedPo = PurchaseOrder::with('items.product')->lockForUpdate()->findOrFail($po->id);

            if ($lockedPo->isCompleted()) {
                throw new \Exception("Chậm tay rồi! Phiếu nhập này vừa được một nhân viên khác hoàn tất cất kho.");
            }

            if (!$lockedPo->isApproved()) {
                $this->approve($lockedPo, $userId);
            }

            $receivedQuantities = [];

            usort($placements, function ($a, $b) {
                return $a['bin_location_id'] <=> $b['bin_location_id'];
            });

            foreach ($placements as $place) {
                $itemId = $place['item_id'];
                $binId = $place['bin_location_id'];
                $putQty = (int) $place['quantity'];

                $item = $lockedPo->items->where('id', $itemId)->first();
                if (!$item) continue;

                $bin = BinLocation::lockForUpdate()->findOrFail($binId);
                $warehouseId = $bin->warehouse_id;

                if ($bin->current_capacity + $putQty > $bin->max_capacity) {
                    throw new \Exception("Kệ {$bin->code} không đủ sức chứa! Cần nhét {$putQty} nhưng kệ chỉ dư " . ($bin->max_capacity - $bin->current_capacity));
                }

                $product = $item->product;

                $mfgDate = now()->toDateString();
                $expDate = null;

                if ($product && $product->has_expiry) {
                    $expiryMethod = $place['expiry_method'] ?? 'manual';

                    if ($expiryMethod === 'manual') {
                        $expDate = $place['expiry_date_manual'] ?? null;
                        if (!$expDate) throw new \Exception("Vui lòng nhập Hạn Sử Dụng cho sản phẩm {$product->name}!");
                    } else {
                        $mfgDate = $place['manufactured_date'] ?? now()->toDateString();
                        $expDate = $place['expiry_date_auto'] ?? null;

                        if (!$expDate) {
                            $expDate = \Carbon\Carbon::parse($mfgDate)->addMonths($product->expiry_duration ?? 12)->toDateString();
                        }
                    }
                } else {
                    $mfgDate = $place['manufactured_date'] ?? now()->toDateString();
                }

                $batch = Batch::firstOrCreate(
                    [
                        'product_id'        => $item->product_id,
                        'manufactured_date' => $mfgDate,
                        'expiry_date'       => $expDate,
                    ],
                    [
                        'batch_number' => 'LOT-' . $item->product_id . '-' . date('Ymd') . '-' . rand(1000, 9999)
                    ]
                );

                $inventory = Inventory::where('product_id', $item->product_id)
                    ->where('bin_location_id', $binId)
                    ->where('batch_id', $batch->id)
                    ->lockForUpdate()
                    ->first();

                if (!$inventory) {
                    $inventory = Inventory::create([
                        'product_id'        => $item->product_id,
                        'warehouse_id'      => $warehouseId,
                        'bin_location_id'   => $binId,
                        'batch_id'          => $batch->id,
                        'on_hand_quantity'  => 0,
                        'reserved_quantity' => 0,
                    ]);
                }

                $inventory->on_hand_quantity += $putQty;
                $inventory->save();

                $bin->current_capacity += $putQty;
                $bin->save();

                $this->stockMovementService->recordMovement(
                    $inventory,
                    $putQty,
                    'inbound',
                    $lockedPo,
                    $userId
                );

                if (!isset($receivedQuantities[$itemId])) $receivedQuantities[$itemId] = 0;
                $receivedQuantities[$itemId] += $putQty;
            }

            foreach ($receivedQuantities as $id => $qty) {
                PurchaseOrderItem::where('id', $id)
                    ->update(['received_quantity' => DB::raw("received_quantity + $qty")]);
            }

            $lockedPo->update([
                'status' => PurchaseOrder::STATUS_COMPLETED,
                'completed_at' => now(),
                'assigned_to' => null,
            ]);
        });
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\BinLocation;
use App\Models\InventoryAdjustment;
use App\Models\InventoryAdjustmentItem;
use App\Services\InventoryAdjustmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryAdjustmentController extends Controller
{
    public function create(Request $request)
    {
        $selectedInventoryId = $request->query('inventory_id');

        $inventories = Inventory::with(['product', 'binLocation.zone', 'batch'])
            ->where('on_hand_quantity', '>', 0)
            ->get();

        return view('admin.adjustments.create', compact('inventories', 'selectedInventoryId'));
    }

    public function store(Request $request, InventoryAdjustmentService $adjustmentService)
    {
        $request->validate([
            'inventory_id'    => 'required|exists:inventories,id',
            'actual_quantity' => 'required|integer|min:0',
            'reason'          => 'required|string',
            'counter_name'    => 'required|string|max:100',
        ]);

        try {
            DB::transaction(function () use ($request, $adjustmentService) {
                $userId = auth()->id() ?? 1;

                $inventory = Inventory::lockForUpdate()->findOrFail($request->inventory_id);
                $systemQty = $inventory->on_hand_quantity;
                $actualQty = $request->actual_quantity;
                $variance = $actualQty - $systemQty;

                if ($variance == 0) {
                    throw new \Exception("Số đếm thực tế khớp với hệ thống, không cần tạo phiếu điều chỉnh!");
                }

                $adjustmentCode = 'ADJ-' . date('YmdHis') . '-' . rand(1000, 9999);

                $adjustment = InventoryAdjustment::create([
                    'code'         => $adjustmentCode,
                    'status'       => 'counting',
                    'reason'       => $request->reason,
                    'counter_name' => $request->counter_name,
                    'created_by'   => $userId
                ]);

                InventoryAdjustmentItem::create([
                    'inventory_adjustment_id' => $adjustment->id,
                    'inventory_id'            => $request->inventory_id,
                    'system_quantity'         => $systemQty,
                    'counted_quantity'        => $actualQty,
                    'variance'                => $variance
                ]);

                $adjustmentService->approve($adjustment, $userId);
            });

            return redirect()->route('admin.inventory.index')->with('success', 'Đã lưu và duyệt Phiếu điều chỉnh kho thành công! Thẻ kho và Thể tích kệ đã đồng bộ.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }
}

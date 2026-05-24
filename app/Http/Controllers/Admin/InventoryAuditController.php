<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\InventoryAudit;
use App\Models\InventoryAuditItem;
use App\Services\StockMovementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryAuditController extends Controller
{
    public function index()
    {
        $audits = InventoryAudit::with('creator')->orderBy('created_at', 'desc')->paginate(10);
        return view('admin.audits.index', compact('audits'));
    }

    public function store()
    {
        DB::transaction(function () {
            $audit = InventoryAudit::create([
                'audit_code' => 'AUDIT-' . date('Ymd-His'),
                'status' => 'pending',
                'created_by' => auth()->id()
            ]);

            $inventories = Inventory::where('on_hand_quantity', '>', 0)->get();

            foreach ($inventories as $inv) {
                InventoryAuditItem::create([
                    'inventory_audit_id' => $audit->id,
                    'inventory_id' => $inv->id,
                    'system_quantity' => $inv->on_hand_quantity,
                    'actual_quantity' => null
                ]);
            }
        });

        return redirect()->route('admin.audits.index')->with('success', 'Đã tạo phiếu kiểm kê mới!');
    }

    public function show($id)
    {
        $audit = InventoryAudit::with(['items.inventory.product', 'items.inventory.binLocation'])->findOrFail($id);
        return view('admin.audits.show', compact('audit'));
    }

    public function update(Request $request, $id)
    {
        $audit = InventoryAudit::findOrFail($id);
        if ($audit->status === 'completed') return back()->with('error', 'Phiếu đã chốt, không thể sửa!');

        $itemsData = $request->input('actual_quantity', []);

        foreach ($itemsData as $itemId => $qty) {
            if ($qty !== null) {
                InventoryAuditItem::where('id', $itemId)->update(['actual_quantity' => $qty]);
            }
        }

        return back()->with('success', 'Đã lưu kết quả đếm nháp!');
    }

    public function complete($id, StockMovementService $stockService)
    {
        $audit = InventoryAudit::with('items.inventory.binLocation')->findOrFail($id);

        if ($audit->status === 'completed') return back()->with('error', 'Phiếu này đã hoàn tất rồi!');

        try {
            DB::transaction(function () use ($audit, $stockService) {
                foreach ($audit->items as $item) {
                    if ($item->actual_quantity !== null && $item->actual_quantity != $item->system_quantity) {

                        $inventory = Inventory::lockForUpdate()->find($item->inventory_id);
                        $diff = $item->actual_quantity - $inventory->on_hand_quantity;
                        $inventory->on_hand_quantity = $item->actual_quantity;
                        $inventory->save();

                        $bin = $inventory->binLocation;
                        if ($bin) {
                            $bin->current_capacity += $diff;
                            $bin->save();
                        }

                        $stockService->recordMovement(
                            $inventory,
                            $diff,
                            'adjustment',
                            $audit,
                            auth()->id()
                        );
                    }
                }

                $audit->update(['status' => 'completed']);
            });

            return redirect()->route('admin.audits.index')->with('success', 'Đã chốt sổ kiểm kê và cập nhật thẻ kho!');
        } catch (\Exception $e) {
            return back()->with('error', 'Lỗi chốt sổ: ' . $e->getMessage());
        }
    }
}

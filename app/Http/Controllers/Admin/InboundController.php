<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Models\BinLocation;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Services\PurchaseOrderService;
use App\Services\SlottingService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InboundController extends Controller
{
    public function index(Request $request)
    {
        $query = PurchaseOrder::query();

        
        if ($request->filled('keyword')) {
            $keyword = $request->keyword;

            $query->where(function ($q) use ($keyword) {
                $q->where('po_number', 'like', "%$keyword%")
                    ->orWhere('supplier_name', 'like', "%$keyword%");
            });
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate(10);

        $orders->appends($request->all());

        return view('admin.inbound.index', compact('orders'));
    }

    public function create()
    {
        $products = Product::where('is_active', 1)->get();

        $suppliers = Supplier::where('status', 'active')->orderBy('name', 'asc')->get();
        $autoPoNumber = 'PO-' . date('Ymd') . '-' . rand(1000, 9999);
        return view('admin.inbound.create', compact('products', 'suppliers', 'autoPoNumber'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'po_number' => 'required|unique:purchase_orders,po_number',
            'supplier_id' => 'required|exists:suppliers,id',

            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();

        try {
            $supplier = Supplier::findOrFail($request->supplier_id);

            $order = PurchaseOrder::create([
                'po_number'     => $request->po_number,
                'supplier_id'   => $supplier->id,
                'supplier_name' => $supplier->name,
                'status'        => PurchaseOrder::STATUS_DRAFT,
                'expected_date' => now(),
                'created_by'    => auth()->id() ?? 1,
            ]);

            foreach ($request->items as $item) {
                PurchaseOrderItem::create([
                    'purchase_order_id' => $order->id,
                    'product_id'        => $item['product_id'],
                    'quantity'          => $item['quantity'],
                    'received_quantity' => 0,
                ]);
            }

            DB::commit();

            return redirect()->route('admin.inbound.index')
                ->with('success', 'Đã tạo PO nhiều sản phẩm thành công!');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Lỗi tạo PO: ' . $e->getMessage());
        }
    }

    public function putaway($id, SlottingService $slottingService)
    {

        try {
            DB::beginTransaction();

            $order = PurchaseOrder::with('items.product')->lockForUpdate()->findOrFail($id);

            if ($order->isCompleted()) {
                DB::rollBack();
                return redirect()->route('admin.inbound.index')->with('error', 'Phiếu nhập này đã hoàn tất.');
            }

            $userId = auth()->id() ?? 1;

            if ($order->assigned_to !== null && $order->assigned_to !== $userId) {
                DB::rollBack();
                return redirect()->route('admin.inbound.index')->with('error', 'Đơn này đang được nhân viên khác xử lý.');
            }

            if ($order->assigned_to === null) {
                $order->assigned_to = $userId;
                $order->save();
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('admin.inbound.index')->with('error', 'Lỗi hệ thống khi giành quyền xử lý: ' . $e->getMessage());
        }

        $warehouseId = 1;

        $putawayTasks = [];
        $taskIndex = 0;

        $allAvailableBins = BinLocation::with('zone')
            ->where('warehouse_id', $warehouseId)
            ->whereRaw('(max_capacity - current_capacity) > 0')
            ->orderBy('zone_id')
            ->get();

        try {
            foreach ($order->items as $item) {
                $allocations = $slottingService->suggestBinsForInbound(
                    $item->product_id,
                    $warehouseId,
                    $item->quantity
                );

                foreach ($allocations as $allo) {
                    $putawayTasks[] = [
                        'task_id'          => $taskIndex++,
                        'item_id'          => $item->id,
                        'product_id'       => $item->product_id,
                        'product_name'     => $item->product->name,
                        'sku'              => $item->product->sku,
                        'quantity'         => $allo['quantity'],
                        'has_expiry'       => $item->product->has_expiry,
                        'expiry_duration'  => $item->product->expiry_duration,
                        'suggested_zone'   => $allo['bin']->zone->code ?? 'N/A',
                        'suggested_bin'    => $allo['bin']->code ?? 'N/A',
                        'bin_location_id'  => $allo['bin']->id,
                        'category'         => $allo['category'],
                        'is_consolidation' => $allo['bin']->is_consolidation ?? false,
                        'reason'           => $allo['reason'] ?? 'AI đã gợi ý vị trí này.',
                    ];
                }
            }

            return view('admin.inbound.putaway', compact('order', 'putawayTasks', 'allAvailableBins'));
        } catch (\Exception $e) {
            return redirect()->route('admin.inbound.index')->with('error', 'AI Báo Lỗi: ' . $e->getMessage());
        }
    }

    public function completePutaway(Request $request, $id, PurchaseOrderService $poService)
    {
        try {
            $order = PurchaseOrder::with('items')->findOrFail($id);

            if ($order->isCompleted()) {
                return redirect()->route('admin.inbound.index')->with('error', 'Phiếu nhập này đã được cất kho xong từ trước rồi!');
            }

            $userId = auth()->id() ?? 1;
            $placements = $request->input('placements') ?? [];

            $poService->complete($order, $placements, $userId);

            return redirect()->route('admin.inbound.index')->with('success', 'Đã cất hàng xong.');
        } catch (\Exception $e) {
            if (strpos($e->getMessage(), 'Lock wait timeout') !== false || strpos($e->getMessage(), 'Deadlock') !== false) {
                return redirect()->route('admin.inbound.index')->with('error', 'Hệ thống đang xử lý dữ liệu của nhân viên khác. Vui lòng thử lại sau vài giây!');
            }

            return redirect()->route('admin.inbound.index')->with('error', 'Lỗi khi cất hàng: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $order = PurchaseOrder::lockForUpdate()->findOrFail($id);

            if ($order->isCompleted()) {
                DB::rollBack();
                return back()->with('error', 'Không thể hủy phiếu đã hoàn tất.');
            }

            $userId = auth()->id() ?? 1;

            if ($order->assigned_to && $order->assigned_to != $userId) {
                DB::rollBack();
                return back()->with('error', 'Đang bị người khác xử lý.');
            }

            $order->update([
                'status' => PurchaseOrder::STATUS_CANCELLED
            ]);

            DB::commit();

            return back()->with('success', 'Đã hủy phiếu nhập!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }


    public function exportPdf($id)
    {
        $order = PurchaseOrder::with(['items.product', 'creator'])->findOrFail($id);

        $pdf = Pdf::loadView('admin.inbound.pdf', compact('order'));

        return $pdf->stream('Phieu_Nhap_Kho_' . $order->po_number . '.pdf');
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use Illuminate\Support\Facades\DB;
use App\Models\Product;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\SalesOrderItemAllocation;
use App\Models\Partner;
use App\Services\SalesOrderService;
use Barryvdh\DomPDF\Facade\Pdf;
use Exception;
use Illuminate\Http\Request;

class SalesOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = SalesOrder::query();

        if ($request->filled('keyword')) {
            $keyword = $request->keyword;

            $query->where(function ($q) use ($keyword) {
                $q->where('so_number', 'like', "%$keyword%")
                    ->orWhere('customer_name', 'like', "%$keyword%");
            });
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate(10);

        $orders->appends($request->all());

        return view('admin.sales_orders.index', compact('orders'));
    }

    public function create()
    {
        $products = Product::where('is_active', 1)->get();
        $partners = Partner::where('status', 'active')->orderBy('name', 'asc')->get();
        $autoSoNumber = 'SO-' . date('Ymd') . '-' . rand(1000, 9999);

        return view('admin.sales_orders.create', compact('products', 'partners', 'autoSoNumber'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'so_number' => 'required|unique:sales_orders,so_number',
            'partner_id' => 'required|exists:partners,id',

            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        $partner = Partner::findOrFail($request->partner_id);
        $warehouseId = 1;

        try {
            DB::beginTransaction();

            foreach ($request->items as $item) {
                $inventories = Inventory::where('product_id', $item['product_id'])
                    ->where('warehouse_id', $warehouseId)
                    ->lockForUpdate()
                    ->get();

                $availableStock = $inventories->sum(
                    fn($inv) =>
                    $inv->on_hand_quantity - $inv->reserved_quantity
                );

                if ($item['quantity'] > $availableStock) {
                    DB::rollBack();
                    return back()->withInput()->with(
                        'error',
                        "Không đủ hàng cho sản phẩm ID {$item['product_id']} (còn {$availableStock})"
                    );
                }
            }

            $order = SalesOrder::create([
                'so_number'     => $request->so_number,
                'partner_id'    => $partner->id,
                'customer_name' => $partner->name,
                'status'        => 'draft',
                'created_by'    => auth()->id() ?? 1,
            ]);

            foreach ($request->items as $item) {
                SalesOrderItem::create([
                    'sales_order_id' => $order->id,
                    'product_id'     => $item['product_id'],
                    'quantity'       => $item['quantity'],
                    'shipped_quantity' => 0,
                ]);
            }

            DB::commit();

            return redirect()->route('admin.sales_orders.index')
                ->with('success', 'Tạo phiếu xuất nhiều sản phẩm thành công!');
        } catch (Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Lỗi: ' . $e->getMessage());
        }
    }

    public function startPicking($id, SalesOrderService $salesOrderService)
    {
        try {
            DB::beginTransaction();

            $order = SalesOrder::lockForUpdate()->findOrFail($id);
            $userId = auth()->id() ?? 1;

            if ($order->assigned_to !== null && $order->assigned_to !== $userId) {
                DB::rollBack();
                return redirect()->route('admin.sales_orders.index')->with('error', 'Chuyến hàng này đang được nhân viên khác xử lý.');
            }

            if ($order->status === 'draft') {
                $order->assigned_to = $userId;

                $warehouseId = 1;
                $salesOrderService->reserveStock($order, $warehouseId, $userId);

                $order->status = 'picking';
                $order->save();
            }

            DB::commit();
            return redirect()->route('admin.sales_orders.picking_route', $order->id);
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->route('admin.sales_orders.index')->with('error', $e->getMessage());
        }
    }

    public function pickingRoute($id)
    {
        $order = SalesOrder::with('items.product')->findOrFail($id);

        $allocations = SalesOrderItemAllocation::with([
            'salesOrderItem.product',
            'inventory.binLocation.zone',
            'inventory.batch'
        ])
            ->whereHas('salesOrderItem', function ($query) use ($order) {
                $query->where('sales_order_id', $order->id);
            })
            ->get();

        $allocations->transform(function ($allo) {
            $inventory = $allo->inventory;
            $zone = $inventory->binLocation->zone;

            $allo->reason = [
                'fefo' => true,
                'clear_bin' => ($allo->allocated_quantity == $inventory->on_hand_quantity),
                'near_packing' => ($zone && $zone->distance_to_packing <= 20)
            ];
            return $allo;
        });

        $allocations = $allocations->sortBy([
            ['inventory.binLocation.zone.distance_to_packing', 'asc'],
            ['inventory.on_hand_quantity', 'asc'],
        ])->values();

        return view('admin.sales_orders.picking', compact('order', 'allocations'));
    }

    public function completePicking($id)
    {
        $order = SalesOrder::findOrFail($id);

        if ($order->status !== 'picking') {
            return redirect()->route('admin.sales_orders.index')->with('error', 'Chuyến hàng chưa ở trạng thái đang nhặt!');
        }

        $order->status = 'picked';
        $order->save();

        return redirect()->route('admin.sales_orders.index')
            ->with('success', 'Đã xử lý xong! Hàng đang để ở khu vực đóng gói.');
    }

    public function confirmShipment($id, SalesOrderService $salesOrderService)
    {
        try {
            DB::beginTransaction();

            
            $order = SalesOrder::lockForUpdate()->findOrFail($id);

            if ($order->status === 'shipped') {
                DB::rollBack();
                return redirect()->route('admin.sales_orders.index')->with('error', 'Chuyến hàng này đã được nhân viên khác xuất kho.');
            }

            if ($order->status !== 'picked') {
                DB::rollBack();
                return redirect()->route('admin.sales_orders.index')->with('error', 'Trạng thái chuyến hàng không hợp lệ. Chỉ có thể xuất các đơn đã Đóng gói (Picked)!');
            }

            $userId = auth()->id() ?? 1;
            $salesOrderService->shipOrder($order, $userId);

            DB::commit();

            return redirect()->route('admin.sales_orders.index')
                ->with('success', "Chuyến hàng {$order->so_number} đã được xuất kho và giao cho ĐVVC.");
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->route('admin.sales_orders.index')->with('error', 'Lỗi khi xuất kho: ' . $e->getMessage());
        }
    }

    public function destroy($id, SalesOrderService $salesOrderService)
    {
        try {
            DB::beginTransaction();

            $order = SalesOrder::lockForUpdate()->findOrFail($id);

            if ($order->status === 'shipped') {
                DB::rollBack();
                return back()->with('error', 'Chuyến hàng đã xuất kho thì không thể hủy trực tiếp!');
            }

            if ($order->status === 'picking') {
                $userId = auth()->id() ?? 1;
                $salesOrderService->releaseStock($order, $userId);
            } else if ($order->status === 'draft') {
                $order->items()->delete();
                $order->delete();
            }

            DB::commit();
            return back()->with('success', 'Đã hủy bỏ và hoàn trả tồn kho thành công!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Lỗi khi hủy đơn: ' . $e->getMessage());
        }
    }

    public function exportPdf($id)
    {
        $order = SalesOrder::with(['items.product', 'creator'])->findOrFail($id);

        $pdf = Pdf::loadView('admin.sales_orders.pdf', compact('order'));

        return $pdf->stream('Phieu_Xuat_Kho_' . $order->so_number . '.pdf');
    }
}

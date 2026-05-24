<?php

namespace App\Http\Controllers\Admin;

use App\Exports\InventoryExport;
use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\Zone;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Inventory::with(['product', 'binLocation.zone', 'batch'])
            ->join('products', 'inventories.product_id', '=', 'products.id')
            ->select('inventories.*')
            ->where('inventories.on_hand_quantity', '>', 0);

        if ($request->has('zone_id') && $request->zone_id != '') {
            $query->whereHas('binLocation', function ($q) use ($request) {
                $q->where('zone_id', $request->zone_id);
            });
        }

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('product', function ($p) use ($search) {
                    $p->where('sku', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%");
                })->orWhereHas('binLocation', function ($b) use ($search) {
                    $b->where('code', 'like', "%{$search}%");
                });
            });
        }

        $query->orderByRaw('((inventories.on_hand_quantity - inventories.reserved_quantity) / COALESCE(NULLIF(products.minimum_stock, 0), 1)) ASC');
        $inventories = $query->paginate(15);

        $productIds = $inventories->getCollection()->pluck('product_id')->unique();
        $productTotals = Inventory::whereIn('product_id', $productIds)
            ->selectRaw('product_id, SUM(on_hand_quantity - COALESCE(reserved_quantity, 0)) as total_available')
            ->groupBy('product_id')
            ->pluck('total_available', 'product_id');

        $zones = Zone::all();

        return view('admin.inventory.index', compact('inventories', 'zones', 'productTotals'));
    }

    public function export(Request $request)
    {
        $fileName = 'Bao_Cao_Ton_Kho_' . date('Y_m_d_H_i') . '.xlsx';

        return Excel::download(new InventoryExport($request), $fileName);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\StockMovement;
use Illuminate\Http\Request;

class StockMovementController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search;
        $fromDate = $request->from_date;
        $toDate = $request->to_date;
        $type = $request->transaction_type;

        $query = StockMovement::with([
            'inventory.product',
            'inventory.binLocation.zone'
        ]);

        $query->when($fromDate, function ($q) use ($fromDate) {
            $q->whereDate('created_at', '>=', $fromDate);
        });

        $query->when($toDate, function ($q) use ($toDate) {
            $q->whereDate('created_at', '<=', $toDate);
        });

        $query->when($type, function ($q) use ($type) {
            if (in_array($type, ['inbound', 'Nhập kho'])) {
                $q->whereIn('transaction_type', ['inbound', 'Nhập kho', 'adjustment_increase']);
            } 
            elseif (in_array($type, ['outbound', 'Xuất kho'])) {
                $q->whereIn('transaction_type', ['outbound', 'Xuất kho', 'loss', 'damage']);
            } 
            else {
                $q->where('transaction_type', $type);
            }
        });

        $query->when($search, function ($q) use ($search) {
            $q->where(function ($subQ) use ($search) {
                $subQ->whereHas('inventory.product', function ($p) use ($search) {
                    $p->where('name', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%");
                })
                    ->orWhereHas('inventory.binLocation', function ($b) use ($search) {
                        $b->where('code', 'like', "%{$search}%");
                    })
                    ->orWhereHas('inventory.binLocation.zone', function ($z) use ($search) {
                        $z->where('code', 'like', "%{$search}%");
                    });
            });
        });

        $movements = $query->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        return view('admin.stock_movements.index', compact('movements'));
    }
    public function export(Request $request)
    {
        $fileName = 'TheKho_WMS_' . date('Y_m_d_His') . '.xlsx';
        return (new \App\Exports\StockMovementsExport($request))->download($fileName);
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\StockMovement;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function inventoryReport(Request $request)
    {        
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->toDateString());
        
        $products = Product::all();
        $reportData = [];

        foreach ($products as $product) {            
            $tonDau = StockMovement::whereHas('inventory', function($q) use ($product) {
                $q->where('product_id', $product->id);
            })->where('created_at', '<', $startDate . ' 00:00:00')
              ->sum('quantity_change');
            
            $nhapTrongKy = StockMovement::whereHas('inventory', function($q) use ($product) {
                $q->where('product_id', $product->id);
            })->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
              ->where('quantity_change', '>', 0)
              ->sum('quantity_change');
            
            $xuatTrongKy = abs(StockMovement::whereHas('inventory', function($q) use ($product) {
                $q->where('product_id', $product->id);
            })->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
              ->where('quantity_change', '<', 0)
              ->sum('quantity_change'));
            
            $tonCuoi = $tonDau + $nhapTrongKy - $xuatTrongKy;

            $reportData[] = [
                'sku'       => $product->sku,
                'name'      => $product->name,
                'ton_dau'   => (int) $tonDau,
                'nhap'      => (int) $nhapTrongKy,
                'xuat'      => (int) $xuatTrongKy,
                'ton_cuoi'  => (int) $tonCuoi,
            ];
        }

        return view('admin.reports.inventory', compact('reportData', 'startDate', 'endDate'));
    }

    public function exportInventoryReport(Request $request)
    {
        $fileName = 'Bao_Cao_NXT_' . date('Y_m_d') . '.xlsx';
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\InventoryReportExport($request), $fileName);
    }
}

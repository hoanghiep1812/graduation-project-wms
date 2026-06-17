<?php

namespace App\Exports;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\StockMovement;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class InventoryReportExport implements FromArray, WithHeadings, ShouldAutoSize, WithStyles
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function array(): array
    {
        $startDate = $this->request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate = $this->request->input('end_date', now()->toDateString());
        
        $products = Product::all();
        $data = [];

        foreach ($products as $product) {
            $tonDau = StockMovement::whereHas('inventory', fn($q) => $q->where('product_id', $product->id))
                ->where('created_at', '<', $startDate . ' 00:00:00')->sum('quantity_change');

            $nhap = StockMovement::whereHas('inventory', fn($q) => $q->where('product_id', $product->id))
                ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
                ->where('quantity_change', '>', 0)->sum('quantity_change');

            $xuat = abs(StockMovement::whereHas('inventory', fn($q) => $q->where('product_id', $product->id))
                ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
                ->where('quantity_change', '<', 0)->sum('quantity_change'));

            $tonCuoi = $tonDau + $nhap - $xuat;

            $data[] = [
                $product->sku,
                $product->name,
                $tonDau,
                $nhap,
                $xuat,
                $tonCuoi
            ];
        }

        return $data;
    }

    public function headings(): array
    {
        return ['Mã SKU', 'Tên Sản Phẩm', 'Tồn Đầu Kỳ', 'Tổng Nhập', 'Tổng Xuất', 'Tồn Cuối Kỳ'];
    }

    public function styles(Worksheet $sheet)
    {
        return [1 => ['font' => ['bold' => true, 'size' => 12]]];
    }
}
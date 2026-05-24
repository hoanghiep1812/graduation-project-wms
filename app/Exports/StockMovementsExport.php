<?php

namespace App\Exports;

use App\Models\StockMovement;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StockMovementsExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    use Exportable;

    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function query()
    {
        $search = $this->request->search;
        $fromDate = $this->request->from_date;
        $toDate = $this->request->to_date;
        $type = $this->request->transaction_type;

        $query = StockMovement::query()->with(['inventory.product', 'inventory.binLocation.zone']);

        $query->when($fromDate, fn($q) => $q->whereDate('created_at', '>=', $fromDate));
        $query->when($toDate, fn($q) => $q->whereDate('created_at', '<=', $toDate));
        $query->when($type, fn($q) => $q->where('transaction_type', $type));

        $query->when($search, function ($q) use ($search) {
            $q->where(function ($subQ) use ($search) {
                $subQ->whereHas('inventory.product', fn($p) => $p->where('name', 'like', "%{$search}%")->orWhere('sku', 'like', "%{$search}%"))
                    ->orWhereHas('inventory.binLocation', fn($b) => $b->where('code', 'like', "%{$search}%"))
                    ->orWhereHas('inventory.binLocation.zone', fn($z) => $z->where('code', 'like', "%{$search}%"));
            });
        });

        return $query->orderBy('created_at', 'desc');
    }

    public function headings(): array
    {
        return ['Ngày Giờ', 'Tên Sản Phẩm', 'Mã SKU', 'Vị Trí Kệ', 'Khu Vực', 'Loại Giao Dịch', 'Số Lượng', 'Tồn Sau', 'Chứng Từ (Ref ID)'];
    }

    public function map($movement): array
    {
        $inv = $movement->inventory;
        $typeMap = ['inbound' => 'Nhập Kho', 'outbound' => 'Xuất Kho', 'transfer' => 'Dời Kệ', 'adjustment' => 'Kiểm Kê'];

        return [
            $movement->created_at->format('d/m/Y H:i:s'),
            $inv->product->name ?? 'N/A',
            $inv->product->sku ?? 'N/A',
            $inv->binLocation->code ?? 'N/A',
            $inv->binLocation->zone->code ?? 'N/A',
            $typeMap[$movement->transaction_type] ?? strtoupper($movement->transaction_type),
            ($movement->quantity_change > 0 ? '+' : '') . $movement->quantity_change,
            $movement->balance_after,
            $movement->reference_type ? (class_basename($movement->reference_type) . ' #' . $movement->reference_id) : 'Hệ Thống',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [1 => ['font' => ['bold' => true, 'size' => 12]]];
    }
}

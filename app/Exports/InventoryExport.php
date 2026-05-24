<?php

namespace App\Exports;

use App\Models\Inventory;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class InventoryExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $query = Inventory::with(['product', 'binLocation.zone', 'batch'])
            ->where('on_hand_quantity', '>', 0);

        if ($this->request->filled('zone_id')) {
            $query->whereHas('binLocation', function ($q) {
                $q->where('zone_id', $this->request->zone_id);
            });
        }

        if ($this->request->filled('search')) {
            $search = $this->request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('product', function ($sub) use ($search) {
                    $sub->where('sku', 'like', "%$search%")
                        ->orWhere('name', 'like', "%$search%");
                })->orWhereHas('binLocation', function ($sub) use ($search) {
                    $sub->where('code', 'like', "%$search%");
                });
            });
        }

        return $query->orderBy('on_hand_quantity', 'asc')->get();
    }
    public function headings(): array
    {
        return [
            'Mã SKU',
            'Tên Sản Phẩm',
            'Khu Vực (Zone)',
            'Vị Trí Kệ (Bin)',
            'Số Lô (Batch)',
            'Hạn Sử Dụng',
            'Tồn Thực Tế',
            'Đang Giữ (Reserved)',
            'Khả Dụng (Available)',
            'Tình Trạng (Status)',
        ];
    }
    public function map($inv): array
    {
        $availableQty = $inv->on_hand_quantity - ($inv->reserved_quantity ?? 0);

        $expiryDate = $inv->batch && $inv->batch->expiry_date
            ? \Carbon\Carbon::parse($inv->batch->expiry_date)->format('d/m/Y')
            : 'Không quản lý lô';

        $minStock = $inv->product->minimum_stock ?? 0;
        $status = 'Ổn định';
        if ($inv->on_hand_quantity <= 0) {
            $status = 'Trống kho';
        } elseif ($inv->on_hand_quantity <= $minStock) {
            $status = 'Sắp cạn';
        }

        return [
            $inv->product->sku ?? 'N/A',
            $inv->product->name ?? 'N/A',
            $inv->binLocation->zone->code ?? 'N/A',
            $inv->binLocation->code ?? 'N/A',
            $inv->batch->batch_number ?? 'N/A',
            $expiryDate,
            (int) $inv->on_hand_quantity,
            (int) ($inv->reserved_quantity ?? 0),
            (int) $availableQty,
            $status,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF4F4F4F']
                ],
            ],
        ];
    }
}

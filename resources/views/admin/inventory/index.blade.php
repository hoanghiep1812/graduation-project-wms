@extends('layouts.master')

@section('title', 'Quản lý Tồn Kho')

@section('content')
    <div class="card card-flush">        
        <div class="card-header align-items-center py-5 gap-2 gap-md-5 flex-wrap">
            <div class="card-title w-100 w-md-auto m-0">                
                <form method="GET" action="{{ route('admin.inventory.index') }}"
                    class="d-flex flex-column flex-md-row align-items-md-center gap-3 w-100">
                    
                    <div class="position-relative w-100 w-md-250px">
                        <i class="ki-duotone ki-magnifier fs-3 position-absolute ms-4 top-50 translate-middle-y text-gray-500"><span
                                class="path1"></span><span class="path2"></span></i>
                        <input type="text" name="search" value="{{ request('search') }}"
                            class="form-control form-control-solid w-100 ps-12" placeholder="Tìm SKU, SP, Kệ..." />
                    </div>
                    
                    <div class="w-100 w-md-200px">
                        <select name="zone_id" class="form-select form-select-solid w-100" data-control="select2"
                            data-placeholder="Lọc theo Khu vực" onchange="this.form.submit()">
                            <option value="">Tất cả Khu vực (Zone)</option>
                            @foreach($zones as $zone)
                                <option value="{{ $zone->id }}" {{ request('zone_id') == $zone->id ? 'selected' : '' }}>
                                    {{ $zone->code }} ({{ $zone->name ?? 'Khu vực' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </form>
            </div>
            
            <div class="card-toolbar flex-row-fluid justify-content-end gap-5 w-100 w-md-auto">
                <a href="{{ route('admin.inventory.export') }}" class="btn btn-light-success fw-bold w-100 w-md-auto">
                    <i class="ki-duotone ki-document fs-2"><span class="path1"></span><span class="path2"></span></i> Xuất báo cáo Excel
                </a>
            </div>
        </div>
        
        <div class="card-body pt-0">
            <div class="table-responsive">                
                <table class="table align-middle table-row-dashed fs-6 gy-5 border-bottom text-nowrap">
                    <thead>
                        <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0 border-bottom border-gray-300">
                            <th class="ps-0 min-w-200px">Sản phẩm</th>
                            <th class="min-w-150px">Vị trí (Zone & Bin)</th>
                            <th class="min-w-175px">Lô / Hạn Sử Dụng</th>
                            <th class="text-end min-w-100px" title="Số lượng vật lý trên kệ">Tồn Thực Tế</th>
                            <th class="text-end min-w-100px text-warning" title="Đã có đơn đặt nhưng chưa xuất">Đang Giữ</th>
                            <th class="text-end min-w-100px text-success" title="Số lượng có thể đem bán">Khả Dụng</th>
                            <th class="text-center min-w-100px">Tình trạng Tồn</th>
                            <th class="text-end min-w-75px pe-0">Hành động</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-700 fw-semibold">
                        @forelse($inventories as $inv)
                            @php                                
                                $rowAvailableQty = $inv->on_hand_quantity - ($inv->reserved_quantity ?? 0);
                                
                                $totalProductQty = $productTotals[$inv->product_id] ?? 0;
                                $minStock = $inv->product->minimum_stock ?? 0;
                            @endphp
                            <tr>                                
                                <td class="ps-0">
                                    <div class="d-flex flex-column">
                                        <span class="text-gray-800 fw-bold fs-6 mb-1">{{ $inv->product->name ?? 'N/A' }}</span>
                                        <span class="text-muted fs-8">SKU: <span class="fw-bold">{{ $inv->product->sku ?? 'N/A' }}</span></span>
                                    </div>
                                </td>
                                
                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="badge badge-light-primary fw-bold px-2 py-1 mb-1 w-fit-content fs-8">
                                            Kệ: {{ $inv->binLocation->code ?? 'N/A' }}
                                        </span>
                                        <span class="text-muted fs-8">
                                            <i class="ki-duotone ki-geolocation fs-8 me-1"><span class="path1"></span><span class="path2"></span></i>
                                            {{ $inv->binLocation->zone->code ?? 'N/A' }}
                                        </span>
                                    </div>
                                </td>
                                
                                <td>
                                    @if($inv->batch)
                                        <div class="text-gray-800 fw-bold fs-7 mb-1">{{ $inv->batch->batch_number }}</div>
                                        @php
                                            $expiryDate = \Carbon\Carbon::parse($inv->batch->expiry_date);
                                            $daysLeft = now()->startOfDay()->diffInDays($expiryDate->startOfDay(), false);
                                        @endphp

                                        @if($daysLeft < 0)
                                            <span class="badge badge-light-danger fs-8 px-2 py-1">
                                                🔴 Đã quá hạn
                                            </span>
                                        @elseif($daysLeft <= 30)
                                            <span class="badge badge-light-warning fs-8 px-2 py-1 text-dark">
                                                ⚠️ Còn {{ intval($daysLeft) }} ngày
                                            </span>
                                        @else
                                            <span class="text-muted fs-8">HSD: {{ $expiryDate->format('d/m/Y') }}</span>
                                        @endif
                                    @else
                                        <span class="text-muted fs-8">Không quản lý Lô</span>
                                    @endif
                                </td>
                                
                                <td class="text-end fw-bold text-gray-800 fs-6">{{ $inv->on_hand_quantity }}</td>
                                <td class="text-end fw-bold text-warning fs-6">{{ $inv->reserved_quantity ?? 0 }}</td>
                                <td class="text-end fw-bolder text-success fs-5">{{ $rowAvailableQty }}</td>
                                
                                <td class="text-center">
                                    <div class="d-flex flex-column align-items-center">
                                        @if($totalProductQty <= 0)
                                            <span class="badge badge-light-danger fs-8 fw-bold mb-1 px-3 py-1">Trống kho</span>
                                        @elseif($totalProductQty <= $minStock)
                                            <span class="badge badge-light-warning fs-8 fw-bold mb-1 px-3 py-1">Sắp cạn</span>
                                        @else
                                            <span class="badge badge-light-success fs-8 fw-bold mb-1 px-3 py-1">Ổn định</span>
                                        @endif                                        
                                        <span class="text-muted fs-8">Tổng: {{ $totalProductQty }}</span>
                                    </div>
                                </td>
                                
                                <td class="text-end pe-0">
                                    <a href="{{ route('admin.adjustments.create', ['inventory_id' => $inv->id]) }}"
                                        class="btn btn-sm btn-icon btn-light-primary btn-active-primary" title="Kiểm kê / Điều chỉnh">
                                        <i class="ki-duotone ki-scan-barcode fs-3"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i>
                                    </a>
                                </td>
                            </tr>
                        @empty                            
                            <tr>
                                <td colspan="8" class="text-center py-10">
                                    <div class="d-flex flex-column align-items-center">
                                        <i class="ki-duotone ki-cube-2 fs-5x text-gray-400 mb-3"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                                        <span class="text-muted fs-5 fw-semibold">Chưa có dữ liệu tồn kho phù hợp.</span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="d-flex flex-stack flex-wrap mt-5">
                <div class="fs-6 fw-semibold text-gray-500 mb-2 mb-md-0">
                    Hiển thị từ {{ $inventories->firstItem() ?? 0 }} đến {{ $inventories->lastItem() ?? 0 }} trên tổng số {{ $inventories->total() ?? 0 }}
                </div>
                <div>
                    {{ $inventories->appends(request()->query())->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
@endsection
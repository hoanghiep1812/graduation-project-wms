@extends('layouts.master')

@section('title', 'Tổng Quan Vận Hành (WMS Command Center)')

@section('content')
    
    <div class="row g-5 g-xl-8 mb-8">
        
        <div class="col-xl-3">
            <div class="card card-xl-stretch mb-xl-8 border-start border-4 border-primary">
                <div class="card-body d-flex flex-column">
                    <div class="d-flex align-items-center mb-4">
                        <i class="ki-duotone ki-delivery-3 text-primary fs-2x me-3">
                            <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                        </i>
                        <div class="text-muted fw-bold fs-7 text-uppercase">Chờ Dỡ Hàng</div>
                    </div>
                    <div class="d-flex align-items-end">
                        <div class="text-gray-800 fw-bolder fs-1 me-2">{{ $pendingInbound ?? 0 }}</div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3">
            <div class="card card-xl-stretch mb-xl-8 border-start border-4 border-info">
                <div class="card-body d-flex flex-column">
                    <div class="d-flex align-items-center mb-4">
                        <i class="ki-duotone ki-parcel-tracking text-info fs-2x me-3">
                            <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                        </i>
                        <div class="text-muted fw-bold fs-7 text-uppercase">Chờ Nhặt Hàng</div>
                    </div>
                    <div class="d-flex align-items-end">
                        <div class="text-gray-800 fw-bolder fs-1 me-2">{{ $pendingOutbound ?? 0 }}</div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3">
            <div class="card card-xl-stretch mb-xl-8 border-start border-4 border-danger">
                <div class="card-body d-flex flex-column">
                    <div class="d-flex align-items-center mb-4">
                        <i class="ki-duotone ki-warning-2 text-danger fs-2x me-3">
                            <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                        </i>
                        <div class="text-muted fw-bold fs-7 text-uppercase">Cảnh báo cạn kho</div>
                    </div>
                    <div class="d-flex align-items-end">
                        <div class="text-danger fw-bolder fs-1 me-2">{{ $lowStockProductsCount ?? 0 }}</div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3">
            <div class="card card-xl-stretch mb-xl-8 border-start border-4 border-warning">
                <div class="card-body d-flex flex-column">
                    <div class="d-flex align-items-center mb-4">
                        <i class="ki-duotone ki-arrows-loop text-warning fs-2x me-3">
                            <span class="path1"></span><span class="path2"></span>
                        </i>
                        <div class="text-muted fw-bold fs-7 text-uppercase">Đề Xuất Dời kệ</div>
                    </div>
                    <div class="d-flex align-items-end">
                        <div class="text-gray-800 fw-bolder fs-1 me-2">{{ $pendingReslottingTasks ?? 0 }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row g-5 g-xl-8 mb-8">
        
        <div class="col-xl-6">
            <div class="card card-xl-stretch h-100">
                <div class="card-header border-0 pt-5">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bold fs-4 text-gray-800">Tỷ Lệ Lấp Đầy Kho</span>
                        <span class="text-muted fw-semibold fs-7 mt-1">
                            Đã sử dụng <span class="badge badge-light-danger fw-bolder">{{ $usagePercentage }}%</span>
                        </span>
                    </h3>
                </div>
                <div class="card-body d-flex flex-center flex-column pb-10">
                    <div class="position-relative w-100" style="height: 250px;">
                        <canvas id="capacityPieChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-6">
            <div class="card card-xl-stretch h-100">
                <div class="card-header border-0 pt-5">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bold fs-4 text-gray-800">Cơ Cấu Sản Phẩm (ABC)</span>
                        <span class="text-muted fw-semibold fs-7 mt-1">Pareto 80/20 - 30 ngày gần nhất</span>
                    </h3>
                </div>
                <div class="card-body d-flex flex-center flex-column pb-10">
                    <div class="position-relative w-100" style="height: 250px;">
                        <canvas id="abcPieChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row g-5 g-xl-8 mb-8">
        
        <div class="col-xl-6">
            <div class="card card-xl-stretch h-100">
                <div class="card-header border-0 pt-5">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bold fs-4 text-gray-800">Cảnh Báo Hết Hạn (FEFO)</span>
                    </h3>
                </div>
                <div class="card-body py-3">
                    <div class="table-responsive">
                        <table class="table table-row-dashed table-row-gray-200 align-middle gs-0 gy-4">
                            <thead>
                                <tr class="fw-bold text-muted fs-7 text-uppercase">
                                    <th class="ps-0">Sản phẩm</th>
                                    <th>Vị trí (Bin/Lô)</th>
                                    <th class="text-end pe-0">Hạn mức</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($expiringBatches as $inv)
                                    <tr>
                                        <td class="ps-0">
                                            <span class="text-gray-800 fw-bold d-block fs-6">{{ $inv->product->name }}</span>
                                            <span class="text-muted fw-semibold d-block fs-7">Tồn:
                                                {{ $inv->on_hand_quantity }}</span>
                                        </td>
                                        <td>
                                            <span class="text-gray-800 fw-bold fs-6">{{ $inv->binLocation->code }}</span>
                                            <span class="text-muted fw-semibold d-block fs-7">Lô:
                                                {{ $inv->batch->batch_number }}</span>
                                        </td>
                                        <td class="text-end pe-0">
                                            @php
                                                $daysLeft = \Carbon\Carbon::now()->diffInDays($inv->batch->expiry_date, false);
                                                $color = $daysLeft <= 30 ? 'danger' : 'warning';
                                            @endphp
                                            <span class="badge badge-light-{{ $color }} fw-bold">Còn {{ intval($daysLeft) }}
                                                ngày</span>
                                            <span
                                                class="text-muted fw-semibold d-block fs-8 mt-1">{{ \Carbon\Carbon::parse($inv->batch->expiry_date)->format('d/m/Y') }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted py-5">Khoảng trống an toàn. Không có hàng
                                            cận date.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        
        <div class="col-xl-6">
            <div class="card card-xl-stretch h-100">
                <div class="card-header border-0 pt-5">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bold fs-4 text-gray-800 cursor-pointer" data-bs-toggle="modal" data-bs-target="#lowStockModal" title="Bấm để xem toàn bộ danh sách">
                            Cần Nhập Khẩn (PO)
                            <span class="path1"></span><span class="path2"></span>
                        </span>
                        <span class="text-muted fw-semibold fs-7 mt-1">Hiển thị Top 5 / <a href="#" data-bs-toggle="modal" data-bs-target="#lowStockModal" class="text-primary">Xem tất cả ({{ $lowStockProductsCount }})</a></span>
                    </h3>
                </div>
                <div class="card-body py-3">
                    <div class="table-responsive">
                        <table class="table table-row-dashed table-row-gray-200 align-middle gs-0 gy-4">
                            <thead>
                                <tr class="fw-bold text-muted fs-7 text-uppercase">
                                    <th class="ps-0">SKU</th>
                                    <th>Sản phẩm</th>
                                    <th class="text-center pe-0">Tồn / Min</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($topCriticalLowStocks as $item)
                                    <tr>
                                        <td class="ps-0"><span class="text-gray-600 fw-bold fs-7">{{ $item->sku }}</span></td>
                                        <td><span class="text-gray-800 fw-bold fs-6">{{ $item->name }}</span></td>
                                        <td class="text-center pe-0">
                                            <span class="text-danger fw-bolder fs-6">{{ $item->total_available }}</span>
                                            <span class="text-muted mx-1">/</span>
                                            <span class="text-muted fw-semibold fs-6">{{ $item->minimum_stock }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted py-5">Tồn kho an toàn.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    
    <div class="row g-5 g-xl-8">
        
        <div class="col-xl-6">
            <div class="card card-xl-stretch mb-5 mb-xl-8">
                <div class="card-header border-0 pt-5">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bold fs-4 text-gray-800">Tốc Độ Xuất Kho</span>
                    </h3>
                </div>
                <div class="card-body py-3">
                    <div class="table-responsive">
                        <table class="table align-middle table-row-dashed table-row-gray-200 fs-6 gy-4">
                            <thead>
                                <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                    <th class="ps-0 min-w-150px">Sản phẩm</th>
                                    <th class="text-center">Phân nhóm</th>
                                    <th class="text-end pe-0">30 Ngày Qua</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($topSellers as $seller)
                                    <tr>
                                        <td class="ps-0 text-gray-800 fw-bold fs-6">{{ $seller->product->name ?? 'N/A' }}</td>
                                        <td class="text-center">
                                            @if($seller->velocity_category == 'FAST_MOVING')
                                                <span class="badge badge-light-success fs-8">Hạng A</span>
                                            @elseif($seller->velocity_category == 'MEDIUM_MOVING')
                                                <span class="badge badge-light-warning fs-8">Hạng B</span>
                                            @else
                                                <span class="badge badge-light-secondary fs-8">Hạng C</span>
                                            @endif
                                        </td>
                                        <td class="text-end pe-0">
                                            <span class="text-success fw-bolder fs-6">+{{ $seller->sales_30_days }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted py-5">Chưa có dữ liệu.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        
        <div class="col-xl-6">
            <div class="card card-xl-stretch mb-5 mb-xl-8">
                <div class="card-header border-0 pt-5">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bold fs-4 text-gray-800">Tiến Độ SO Gần Nhất</span>
                    </h3>
                </div>
                <div class="card-body py-3">
                    <div class="table-responsive">
                        <table class="table align-middle table-row-dashed table-row-gray-200 fs-6 gy-4">
                            <thead>
                                <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                    <th class="ps-0">Đơn hàng (SO)</th>
                                    <th class="text-center">Trạng Thái</th>
                                    <th class="text-end pe-0">Cập nhật</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentOrders as $order)
                                    <tr>
                                        <td class="ps-0">
                                            <span class="text-gray-800 fw-bold fs-6 d-block">{{ $order->so_number }}</span>
                                            <span class="text-muted fw-semibold fs-8">{{ $order->customer_name }}</span>
                                        </td>
                                        <td class="text-center">
                                            @if($order->status == 'draft')
                                                <span class="badge badge-light-warning">Chờ nhặt</span>
                                            @elseif($order->status == 'confirmed')
                                                <span class="badge badge-light-primary">Đang lấy</span>
                                            @else
                                                <span class="badge badge-light-success">Hoàn tất</span>
                                            @endif
                                        </td>
                                        <td class="text-end pe-0">
                                            <span
                                                class="text-muted fw-semibold fs-7">{{ $order->created_at->diffForHumans() }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted py-5">Chưa có hoạt động.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="lowStockModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="fw-bold">Báo Cáo Chi Tiết: Sản Phẩm Chạm Mốc Cạn Kho</h2>
                    <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                        <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                    </div>
                </div>
                
                <div class="modal-body py-10 px-lg-17" style="max-height: 60vh; overflow-y: auto;">
                    <div class="table-responsive">
                        <table class="table table-row-dashed table-row-gray-200 align-middle gs-0 gy-4">
                            <thead>
                                <tr class="fw-bold text-muted fs-7 text-uppercase bg-light p-3">
                                    <th class="ps-3 rounded-start">SKU</th>
                                    <th>Sản phẩm</th>
                                    <th class="text-center">Tồn Kho Hiện Tại</th>
                                    <th class="text-center pe-0">Mức Tối Thiểu (Min)</th>
                                    <th class="text-end pe-3 rounded-end">Trạng Thái</th>
                                </tr>
                            </thead>
                            <tbody>
                                @isset($criticalLowStocks)
                                    @forelse($criticalLowStocks as $item)
                                        <tr>
                                            <td class="ps-3"><span class="text-gray-600 fw-bold fs-7">{{ $item->sku }}</span></td>
                                            <td><span class="text-gray-800 fw-bold fs-6">{{ $item->name }}</span></td>
                                            <td class="text-center">
                                                <span class="text-danger fw-bolder fs-5">{{ $item->total_available }}</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted fw-semibold fs-6">{{ $item->minimum_stock }}</span>
                                            </td>
                                            <td class="text-end pe-3">
                                                @if($item->total_available <= 0)
                                                    <span class="badge badge-light-danger fs-7 fw-bold">Hết sạch</span>
                                                @else
                                                    <span class="badge badge-light-warning fs-7 fw-bold">Dưới định mức</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-5">Tồn kho an toàn, không có mặt hàng nào.</td>
                                        </tr>
                                    @endforelse
                                @else
                                    <tr>
                                        <td colspan="5" class="text-center text-danger py-5">Lỗi: Chưa truyền biến $criticalLowStocks từ Controller!</td>
                                    </tr>
                                @endisset
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="modal-footer flex-center">
                    <button type="button" class="btn btn-light me-3" data-bs-dismiss="modal">Đóng cửa sổ</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            Chart.defaults.color = '#A1A5B7';
            Chart.defaults.font.family = 'Inter, Helvetica, sans-serif';

            const emptySpaceColor = getComputedStyle(document.body).getPropertyValue('--bs-gray-200').trim() || '#E4E6EF';
            var capacityCtx = document.getElementById('capacityPieChart').getContext('2d');
            new Chart(capacityCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Đã lấp đầy', 'Còn trống'],
                    datasets: [{
                        data: [{{ $totalUsed }}, {{ $totalFree }}],
                        backgroundColor: ['#F1416C', emptySpaceColor],
                        borderWidth: 0,
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '75%',
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: { padding: 20, usePointStyle: true }
                        }
                    }
                }
            });

            var abcCtx = document.getElementById('abcPieChart').getContext('2d');
            var abcData = @json($abcStats);
            new Chart(abcCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Hạng A (Nhanh)', 'Hạng B (Vừa)', 'Hạng C (Chậm)'],
                    datasets: [{
                        data: [abcData.fast || 0, abcData.medium || 0, abcData.slow || 0],
                        backgroundColor: ['#50CD89', '#FFC700', '#7E8299'],
                        borderWidth: 0,
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '75%',
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: { padding: 20, usePointStyle: true }
                        }
                    }
                }
            });
        });
    </script>
@endpush
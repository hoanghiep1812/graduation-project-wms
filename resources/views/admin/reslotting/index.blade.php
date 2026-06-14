@extends('layouts.master')

@section('title', 'Đề Xuất Dời Kệ')

@section('content')
    
    @if(session('success'))
        <div class="alert alert-dismissible bg-light-success border border-success d-flex flex-column flex-sm-row p-5 mb-10">
            <i class="ki-duotone ki-check-circle fs-2hx text-success me-4 mb-5 mb-sm-0"><span class="path1"></span><span class="path2"></span></i>
            <div class="d-flex flex-column pe-0 pe-sm-10">
                <h4 class="mb-1 text-success">Thành công</h4>
                <span class="text-success">{{ session('success') }}</span>
            </div>
            <button type="button" class="position-absolute position-sm-relative m-2 m-sm-0 top-0 end-0 btn btn-icon ms-sm-auto" data-bs-dismiss="alert">
                <i class="ki-duotone ki-cross fs-1 text-success"><span class="path1"></span><span class="path2"></span></i>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-dismissible bg-light-danger border border-danger d-flex flex-column flex-sm-row p-5 mb-10">
            <i class="ki-duotone ki-shield-cross fs-2hx text-danger me-4 mb-5 mb-sm-0"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
            <div class="d-flex flex-column pe-0 pe-sm-10">
                <h4 class="mb-1 text-danger">Có lỗi xảy ra</h4>
                <span class="text-danger">{{ session('error') }}</span>
            </div>
            <button type="button" class="position-absolute position-sm-relative m-2 m-sm-0 top-0 end-0 btn btn-icon ms-sm-auto" data-bs-dismiss="alert">
                <i class="ki-duotone ki-cross fs-1 text-danger"><span class="path1"></span><span class="path2"></span></i>
            </button>
        </div>
    @endif

    <div class="card card-flush">
        <div class="card-header align-items-center py-5 gap-2 gap-md-5 flex-wrap">
            <h3 class="card-title align-items-start flex-column w-100 w-md-auto m-0">
                <span class="card-label fw-bold text-gray-800 fs-3">Tối Ưu Vị Trí Kệ (Adaptive Slotting)</span>
                <span class="text-muted mt-1 fw-semibold fs-7">AI phân tích và đề xuất luân chuyển hàng hóa để tối ưu quãng đường lấy hàng</span>
            </h3>

            <div class="card-toolbar flex-row-fluid justify-content-end gap-5 w-100 w-md-auto">
                <form action="{{ route('admin.reslotting.generate') }}" method="POST" class="w-100 w-md-auto m-0">
                    @csrf
                    
                    <button type="submit" class="btn btn-primary fw-bold w-100" onclick="return confirm('Hệ thống sẽ chạy thuật toán phân tích toàn bộ kho. Bạn có chắc chắn?');">
                        <i class="ki-duotone ki-cpu fs-2"><span class="path1"></span><span class="path2"></span></i> Kích Hoạt AI Quét Kho
                    </button>
                </form>
            </div>
        </div>

        <div class="card-body pt-0">
            <div class="table-responsive">
                
                <table class="table align-middle table-row-dashed table-row-gray-200 fs-6 gy-5 border-bottom text-nowrap">
                    <thead>
                        <tr class="text-start text-gray-500 fw-bolder fs-7 text-uppercase gs-0 border-bottom border-gray-300">
                            <th class="ps-0 min-w-200px">Sản Phẩm</th>
                            <th class="min-w-150px">Lý Do Đề Xuất</th>
                            <th class="min-w-250px text-center">Lộ Trình Dời Kệ</th>
                            <th class="text-end min-w-150px pe-0">Hành Động</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-700 fw-semibold">
                        @forelse($recommendations as $task)
                            <tr>
                                
                                <td class="ps-0">
                                    <div class="d-flex flex-column">
                                        <span class="text-gray-800 fw-bold fs-6 mb-1">{{ $task->product->name ?? 'N/A' }}</span>
                                        <span class="text-muted fs-8">SKU: <span class="fw-bold">{{ $task->product->sku ?? 'N/A' }}</span></span>
                                    </div>
                                </td>
                                
                                
                                <td>
                                    @php
                                        $aiReason = json_decode($task->reason, true);
                                    @endphp

                                    @if(is_array($aiReason))
                                        <div class="d-flex flex-column gap-2">
                                            
                                            <div>
                                                @if($aiReason['category'] == 'SURGE_DEMAND')
                                                    <span class="badge badge-light-danger fw-bold fs-8 px-2 py-1"><i class="ki-duotone ki-graph-up fs-8 text-danger me-1"><span class="path1"></span><span class="path2"></span></i> Hot Trend</span>
                                                @elseif($aiReason['category'] == 'FAST_MOVING')
                                                    <span class="badge badge-light-primary fw-bold fs-8 px-2 py-1">Xuất Chạy (A)</span>
                                                @elseif($aiReason['category'] == 'SLOW_MOVING')
                                                    <span class="badge badge-light-secondary fw-bold text-dark fs-8 px-2 py-1">Hàng Ế (C)</span>
                                                @else
                                                    <span class="badge badge-light-warning fw-bold fs-8 px-2 py-1">Trung bình (B)</span>
                                                @endif
                                            </div>

                                            <div class="text-gray-700 fs-8">
                                                Khoảng cách: <span class="text-danger fw-bold text-decoration-line-through">{{ $aiReason['current_dist'] }}m</span>
                                                <i class="ki-duotone ki-arrow-right fs-8 mx-1 text-muted"><span class="path1"></span><span class="path2"></span></i> 
                                                <span class="text-success fw-bolder">{{ $aiReason['new_dist'] }}m</span>
                                            </div>

                                            <div class="text-primary fs-8 fw-semibold text-wrap" style="max-width: 250px;">
                                                <i class="ki-duotone ki-information-5 fs-8 me-1 text-primary"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                                                {{ $aiReason['impact'] }}
                                            </div>
                                            
                                            <div class="text-muted fs-8">
                                                Số lượng dời: <span class="fw-bold text-gray-800">{{ $aiReason['qty_to_move'] }}</span>
                                            </div>
                                        </div>
                                    @else
                                        <span class="badge badge-light-warning fw-bold px-3 py-2 text-wrap text-start" style="max-width: 250px;">
                                            {{ $task->reason }}
                                        </span>
                                    @endif
                                </td>
                                
                                
                                <td class="text-center">
                                    
                                    <div class="d-flex flex-column flex-md-row align-items-center justify-content-center gap-2">
                                        
                                        <div class="border border-gray-300 border-dashed rounded py-2 px-3 text-center bg-light w-100 w-md-auto">
                                            <div class="text-gray-500 fs-8">Đang ở</div>
                                            <div class="fw-bolder text-gray-800 fs-6">{{ $task->currentBin->code ?? 'N/A' }}</div>
                                            <div class="text-muted fs-8">{{ $task->currentBin->zone->code ?? '' }}</div>
                                        </div>

                                        
                                        <i class="ki-duotone ki-arrow-right fs-2 text-muted d-none d-md-block"><span class="path1"></span><span class="path2"></span></i>
                                        <i class="ki-duotone ki-arrow-down fs-2 text-muted d-block d-md-none"><span class="path1"></span><span class="path2"></span></i>

                                        
                                        <div class="border border-success border-dashed rounded py-2 px-3 text-center bg-light-success w-100 w-md-auto">
                                            <div class="text-success fs-8">Chuyển tới</div>
                                            <div class="fw-bolder text-success fs-6">{{ $task->recommendedBin->code ?? 'N/A' }}</div>
                                            <div class="text-success opacity-75 fs-8">{{ $task->recommendedBin->zone->code ?? '' }}</div>
                                        </div>
                                    </div>
                                </td>
                                
                                
                                <td class="text-end pe-0">
                                    @if(auth()->user()->isAdmin())
                                        <div class="d-flex justify-content-end align-items-center gap-2">
                                            <form action="{{ route('admin.reslotting.approve', $task->id) }}" method="POST" class="m-0">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-light-primary btn-active-primary fw-bold" title="Duyệt đề xuất"
                                                    onclick="return confirm('Tạo lệnh dời hàng nội bộ theo đề xuất này?');">
                                                    <i class="ki-duotone ki-check fs-2"></i> Duyệt
                                                </button>
                                            </form>

                                            <form action="{{ route('admin.reslotting.reject', $task->id) }}" method="POST" class="m-0">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-icon btn-light-danger btn-active-danger" title="Từ chối / Bỏ qua"
                                                    onclick="return confirm('Bạn muốn bỏ qua đề xuất này?');">
                                                    <i class="ki-duotone ki-cross fs-2"><span class="path1"></span><span class="path2"></span></i>
                                                </button>
                                            </form>
                                        </div>
                                    @else
                                        <span class="badge badge-light-warning text-muted fs-8 fw-bold">Chờ Quản lý duyệt</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-10">
                                    <div class="d-flex flex-column align-items-center">
                                        <i class="ki-duotone ki-data fs-5x text-gray-400 mb-3"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>
                                        <span class="text-muted fs-5 fw-semibold">Kho đang tối ưu. Chưa có đề xuất mới từ AI.</span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            
            <div class="d-flex flex-stack flex-wrap mt-5">
                <div class="fs-6 fw-semibold text-gray-500 mb-2 mb-md-0">
                    Hiển thị từ {{ $recommendations->firstItem() ?? 0 }} đến {{ $recommendations->lastItem() ?? 0 }} trên tổng số {{ $recommendations->total() ?? 0 }}
                </div>
                <div>
                    {{ $recommendations->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function () {
        // KIỂM TRA PUSHER
        if (typeof window.Echo !== 'undefined') {
            window.Echo.channel('de-xuat-channel')
                .listen('.ai.completed', (e) => {
                    console.log("Đã nhận tín hiệu AI. Đang cập nhật lại bảng đề xuất...");
                    
                    fetch(window.location.href, {
                        headers: { "X-Requested-With": "XMLHttpRequest" }
                    })
                    .then(res => res.text())
                    .then(html => {
                        let parser = new DOMParser();
                        let doc = parser.parseFromString(html, "text/html");

                        // Chỉ đắp lại ruột bảng để không làm nháy cả trang
                        let newTableBody = doc.querySelector('.table-responsive tbody');
                        let newPagination = doc.querySelector('.card-body > .d-flex.flex-stack'); 

                        let currentTableBody = document.querySelector('.table-responsive tbody');
                        if (currentTableBody && newTableBody) {
                            currentTableBody.innerHTML = newTableBody.innerHTML;
                        }

                        let currentPagination = document.querySelector('.card-body > .d-flex.flex-stack');
                        if (currentPagination && newPagination) {
                            currentPagination.innerHTML = newPagination.innerHTML;
                        }
                        
                        // Hiệu ứng chớp sáng báo hiệu có data mới
                        if (currentTableBody) {
                            currentTableBody.style.transition = 'opacity 0.3s ease';
                            currentTableBody.style.opacity = '0.3';
                            setTimeout(() => currentTableBody.style.opacity = '1', 300);
                        }
                    })
                    .catch(err => console.log("Lỗi đồng bộ bảng:", err));
                });
        } else {
            console.error("Laravel Echo chưa được load, không thể real-time bảng này!");
        }
    });
</script>
@endpush
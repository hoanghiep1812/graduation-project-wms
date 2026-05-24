@extends('layouts.master')

@section('title', 'Quản lý Xuất Kho (Outbound)')

@section('content')
    
    @if(session('error'))
        <div class="alert alert-dismissible bg-light-danger border border-danger d-flex flex-column flex-sm-row p-5 mb-10">
            <i class="ki-duotone ki-shield-cross fs-2hx text-danger me-4 mb-5 mb-sm-0"><span class="path1"></span><span
                    class="path2"></span><span class="path3"></span></i>
            <div class="d-flex flex-column pe-0 pe-sm-10">
                <h4 class="mb-1 text-danger">Từ chối xuất kho</h4>
                <span class="text-danger">{{ session('error') }}</span>
            </div>
            <button type="button" class="position-absolute position-sm-relative m-2 m-sm-0 top-0 end-0 btn btn-icon ms-sm-auto"
                data-bs-dismiss="alert">
                <i class="ki-duotone ki-cross fs-1 text-danger"><span class="path1"></span><span class="path2"></span></i>
            </button>
        </div>
    @endif

    @if(session('success'))
        <div class="alert alert-dismissible bg-light-success border border-success d-flex flex-column flex-sm-row p-5 mb-10">
            <i class="ki-duotone ki-check-circle fs-2hx text-success me-4 mb-5 mb-sm-0"><span class="path1"></span><span
                    class="path2"></span></i>
            <div class="d-flex flex-column pe-0 pe-sm-10">
                <h4 class="mb-1 text-success">Thành công!</h4>
                <span class="text-success">{{ session('success') }}</span>
            </div>
            <button type="button" class="position-absolute position-sm-relative m-2 m-sm-0 top-0 end-0 btn btn-icon ms-sm-auto"
                data-bs-dismiss="alert">
                <i class="ki-duotone ki-cross fs-1 text-success"><span class="path1"></span><span class="path2"></span></i>
            </button>
        </div>
    @endif

    <div class="card card-flush">
        
        <div class="card-header align-items-center py-5 gap-2 gap-md-5 flex-wrap">
            
            <div class="card-title w-100 w-md-auto m-0">
                <form method="GET" action="{{ route('admin.sales_orders.index') }}" class="w-100">
                    <div class="d-flex align-items-center position-relative my-1 w-100">
                        <i class="ki-duotone ki-magnifier fs-3 position-absolute ms-4 text-gray-500"><span
                                class="path1"></span><span class="path2"></span></i>
                        <input type="text" name="keyword" value="{{ request('keyword') }}"
                            class="form-control form-control-solid w-100 w-md-250px ps-12" placeholder="Tìm kiếm..." />
                    </div>
                </form>
            </div>

            
            <div class="card-toolbar flex-row-fluid justify-content-end gap-5 w-100 w-md-auto">
                <a href="{{ route('admin.sales_orders.create') }}" class="btn btn-primary fw-bold w-100 w-md-auto">
                    Tạo Phiếu Xuất
                </a>
            </div>
        </div>

        
        <div class="card-body pt-0">
            
            <div class="table-responsive">
                
                <table class="table align-middle table-row-dashed table-row-gray-200 fs-6 gy-4 text-nowrap">
                    <thead>
                        <tr
                            class="text-start text-gray-500 fw-bolder fs-7 text-uppercase gs-0 border-bottom border-gray-300">
                            <th class="w-10px pe-2">#</th>
                            <th class="min-w-125px">Mã SO</th>
                            <th class="min-w-200px">Khách Hàng</th>
                            <th class="min-w-125px text-center">Trạng Thái</th>
                            <th class="min-w-125px text-end">Ngày Tạo</th>
                            <th class="text-end min-w-150px">Hành Động</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-700 fw-semibold">
                        @forelse($orders as $key => $order)
                            <tr>
                                <td class="text-muted">{{ $key + 1 }}</td>

                                <td>
                                    <span class="text-gray-800 fw-bold fs-6">{{ $order->so_number }}</span>
                                </td>

                                <td>
                                    
                                    <span class="d-inline-block text-truncate" style="max-width: 200px;">
                                        {{ $order->customer_name }}
                                    </span>
                                </td>

                                <td class="text-center">
                                    <span class="badge {{ $order->status_meta['class'] }} fs-8 fw-bold px-3 py-2">
                                        {{ $order->status_meta['label'] }}
                                    </span>
                                </td>

                                <td class="text-end text-muted">{{ $order->created_at->format('d/m/Y H:i') }}</td>

                                <td class="text-end">
                                    <div class="d-flex justify-content-end align-items-center gap-2">

                                        {{-- LOCK --}}
                                        @if($order->isLockedByOther())
                                            <span class="badge badge-light-secondary fs-8 px-3 py-2 text-muted">
                                                Đang khóa
                                            </span>

                                        @else

                                            {{-- DRAFT --}}
                                            @if($order->status == \App\Models\SalesOrder::STATUS_DRAFT)
                                                <form action="{{ route('admin.sales_orders.start_picking', $order->id) }}"
                                                    method="POST">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-light-primary fw-bold">
                                                        Lấy Hàng
                                                    </button>
                                                </form>

                                                {{-- PICKING --}}
                                            @elseif($order->status == \App\Models\SalesOrder::STATUS_PICKING)
                                                <a href="{{ route('admin.sales_orders.picking_route', $order->id) }}"
                                                    class="btn btn-sm btn-light-warning fw-bold">
                                                    Tiếp Tục
                                                </a>

                                                {{-- PICKED --}}
                                            @elseif($order->status == \App\Models\SalesOrder::STATUS_PICKED)
                                                <form action="{{ route('admin.sales_orders.confirm_shipment', $order->id) }}"
                                                    method="POST">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-light-success fw-bold"
                                                        onclick="return confirm('Xác nhận xuất kho?');">
                                                        Xuất Kho
                                                    </button>
                                                </form>

                                                {{-- SHIPPED --}}
                                            @elseif($order->status == \App\Models\SalesOrder::STATUS_SHIPPED)
                                                <a href="{{ route('admin.sales_orders.export_pdf', $order->id) }}" target="_blank"
                                                    class="btn btn-sm btn-light-info fw-bold">
                                                    In Phiếu
                                                </a>

                                            @else
                                                <button class="btn btn-sm btn-light text-muted fw-bold" disabled>Trống</button>
                                            @endif

                                            {{-- DELETE --}}
                                            @if(
                                                    in_array($order->status, [
                                                        \App\Models\SalesOrder::STATUS_DRAFT,
                                                        \App\Models\SalesOrder::STATUS_PICKING
                                                    ])
                                                )
                                                <form action="{{ route('admin.sales_orders.destroy', $order->id) }}" method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-icon btn-light-danger"
                                                        onclick="return confirm('Bạn có chắc chắn muốn hủy?');">
                                                        <i class="ki-duotone ki-trash fs-5"></i>
                                                    </button>
                                                </form>
                                            @endif

                                        @endif

                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-10">
                                    <div class="d-flex flex-column align-items-center">
                                        <i class="ki-duotone ki-file-deleted fs-5x text-gray-400 mb-3"><span
                                                class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                                        <span class="text-muted fs-5 fw-semibold">Không tìm thấy Phiếu Xuất (SO) nào.</span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            
            <div class="d-flex flex-stack flex-wrap mt-5">
                <div class="fs-6 fw-semibold text-gray-500 mb-2 mb-md-0">
                    Hiển thị từ {{ $orders->firstItem() ?? 0 }} đến {{ $orders->lastItem() ?? 0 }} trên tổng số
                    {{ $orders->total() ?? 0 }}
                </div>
                <div>
                    {{ $orders->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
@endsection
@extends('layouts.master')

@section('title', 'Quản lý Nhập Kho (Inbound)')

@section('content')
    
    @if (session('error'))
        <div class="alert alert-dismissible bg-light-danger border border-danger d-flex flex-column flex-sm-row p-5 mb-10">
            <i class="ki-duotone ki-shield-cross fs-2hx text-danger me-4 mb-5 mb-sm-0"><span class="path1"></span><span
                    class="path2"></span><span class="path3"></span></i>
            <div class="d-flex flex-column pe-0 pe-sm-10">
                <h4 class="mb-1 text-danger">Có lỗi xảy ra</h4>
                <span class="text-danger">{{ session('error') }}</span>
            </div>
            <button type="button" class="position-absolute position-sm-relative m-2 m-sm-0 top-0 end-0 btn btn-icon ms-sm-auto"
                data-bs-dismiss="alert">
                <i class="ki-duotone ki-cross fs-1 text-danger"><span class="path1"></span><span class="path2"></span></i>
            </button>
        </div>
    @endif

    @if (session('success'))
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
                <form method="GET" action="{{ route('admin.inbound.index') }}" class="w-100">
                    <div class="d-flex align-items-center position-relative my-1 w-100">
                        <i class="ki-duotone ki-magnifier fs-3 position-absolute ms-4 text-gray-500"></i>
                        <input type="text" name="keyword" value="{{ request('keyword') }}"
                            class="form-control form-control-solid w-100 w-md-250px ps-12" placeholder="Tìm kiếm..." />
                    </div>
                </form>
            </div>

            <div class="card-toolbar flex-row-fluid justify-content-end gap-5 w-100 w-md-auto">
                <a href="{{ route('admin.inbound.create') }}" class="btn btn-primary fw-bold w-100 w-md-auto">
                    Tạo Phiếu Nhập
                </a>
            </div>
        </div>

        <div class="card-body pt-0">
            <div class="table-responsive">
                <table class="table align-middle table-row-dashed table-row-gray-200 fs-6 gy-4 text-nowrap"
                    id="inbound_table">
                    <thead>
                        <tr
                            class="text-start text-gray-500 fw-bolder fs-7 text-uppercase gs-0 border-bottom border-gray-300">
                            <th class="w-10px pe-2">#</th>
                            <th class="min-w-125px">Mã PO</th>
                            <th class="min-w-200px">Nhà Cung Cấp</th>
                            <th class="min-w-125px text-center">Trạng Thái</th>
                            <th class="min-w-125px text-end">Ngày Nhập</th>
                            <th class="text-end min-w-150px">Hành Động</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-700 fw-semibold">
                        @forelse($orders as $key => $order)
                                        <tr>
                                            <td>{{ $key + 1 }}</td>

                                            <td>
                                                <span class="fw-bold">{{ $order->po_number }}</span>
                                            </td>

                                            <td>
                                                {{ $order->supplier_name }}
                                            </td>

                                            <td class="text-center">
                                                <span class="badge {{ $order->status_meta['class'] }} fs-8 fw-bold px-3 py-2">
                                                    {{ $order->status_meta['label'] }}
                                                </span>
                                            </td>

                                            <td class="text-end text-muted">
                                                {{ $order->expected_date
                            ? \Carbon\Carbon::parse($order->expected_date)->format('d/m/Y')
                            : now()->format('d/m/Y') }}
                                            </td>

                                            <td class="text-end">
                                                <div class="d-flex justify-content-end gap-2">

                                                    @if ($order->isLockedByOther())
                                                        <span class="badge badge-light-secondary">Đang khóa</span>
                                                    @else
                                                        @if (in_array($order->status, ['draft', 'approved']))
                                                            <a href="{{ route('admin.inbound.putaway', $order->id) }}"
                                                                class="btn btn-sm btn-light-primary fw-bold">
                                                                Cất Hàng
                                                            </a>
                                                        @elseif($order->status == 'completed')
                                                            <a href="{{ route('admin.inbound.export_pdf', $order->id) }}"
                                                                class="btn btn-sm btn-light-info fw-bold" target="_blank">
                                                                In Phiếu
                                                            </a>
                                                        @endif

                                                        @if (in_array($order->status, ['draft', 'approved']))
                                                            <form action="{{ route('admin.inbound.destroy', $order->id) }}" method="POST">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button class="btn btn-sm btn-light-danger"
                                                                    onclick="return confirm('Bạn chắc chắn muốn hủy?')">
                                                                    Hủy
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
                                        <span class="text-muted fs-5 fw-semibold">Không tìm thấy Phiếu Nhập (PO) nào.</span>
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
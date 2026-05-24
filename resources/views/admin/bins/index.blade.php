@extends('layouts.master')

@section('title', 'Quản lý Kệ Hàng (Bin Locations)')

@section('content')
    {{-- Hiển thị thông báo kiểu Enterprise, có icon và màu dịu --}}
    @if(session('success'))
        <div class="alert alert-success d-flex align-items-center p-4 mb-5 shadow-sm border-0">
            <i class="ki-duotone ki-check-circle fs-2hx text-success me-3"><span class="path1"></span><span
                    class="path2"></span></i>
            <div class="d-flex flex-column">
                <span class="fw-bold fs-6">{{ session('success') }}</span>
            </div>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger d-flex align-items-center p-4 mb-5 shadow-sm border-0">
            <i class="ki-duotone ki-cross-circle fs-2hx text-danger me-3"><span class="path1"></span><span
                    class="path2"></span></i>
            <div class="d-flex flex-column">
                <span class="fw-bold fs-6">{{ session('error') }}</span>
            </div>
        </div>
    @endif

    <div class="card card-flush shadow-sm border-0">
        {{-- Card Header: Tìm kiếm & Thêm mới tối giản --}}
        <div class="card-header align-items-center py-5 gap-2 gap-md-5">
            <div class="card-title">
                <form method="GET" action="{{ route('admin.bins.index') }}">
                    <div class="d-flex align-items-center position-relative my-1">
                        <i class="ki-duotone ki-magnifier fs-3 position-absolute ms-4"><span class="path1"></span><span
                                class="path2"></span></i>
                        <input type="text" name="search" value="{{ request('search') }}"
                            class="form-control form-control-solid w-250px ps-12 fs-7" placeholder="Tìm mã kệ..." />
                    </div>
                </form>
            </div>
            <div class="card-toolbar">
                <a href="{{ route('admin.bins.create') }}" class="btn btn-primary fw-bold fs-7">
                    Thêm Kệ Mới
                </a>
            </div>
        </div>

        {{-- Card Body: Bảng dữ liệu mật độ cao, monochrome style --}}
        <div class="card-body pt-0">
            <div class="table-responsive">
                <table class="table align-middle table-row-dashed fs-7 gy-4">
                    <thead>
                        <tr class="text-start text-gray-400 fw-bold fs-8 text-uppercase gs-0 bg-light">
                            <th class="ps-4 w-50px rounded-start">#</th>
                            <th class="min-w-150px">Mã Kệ (Bin Code)</th>
                            <th class="min-w-200px">Khu Vực (Zone)</th>
                            {{-- Căn giữa dữ liệu số sức chứa --}}
                            <th class="text-center min-w-150px">Sức chứa (Hiện tại/Tối đa)</th>
                            <th class="text-end min-w-100px pe-4 rounded-end">Hành Động</th>
                        </tr>
                    </thead>
                    <tbody class="fw-semibold text-gray-700">
                        @forelse($bins as $key => $bin)
                            <tr>
                                <td class="ps-4">{{ $key + 1 }}</td>
                                <td>
                                    {{-- Mã kệ là dữ liệu chính, dùng đậm màu đen --}}
                                    <span class="text-gray-900 fw-bold fs-7">{{ $bin->code }}</span>
                                </td>
                                <td>
                                    {{-- Giữ màu xanhPrimary doanh nghiệp cho liên kết Zone cốt lõi --}}
                                    <span
                                        class="badge badge-light-primary fw-bold px-3 py-2 fs-8">{{ $bin->zone->code ?? 'N/A' }}</span>
                                </td>
                                <td class="text-center">
                                    {{-- Khử màu, dùngGray text cho dữ liệu số để giảm nhiễu --}}
                                    <span class="text-gray-800 fw-medium">
                                        {{ $bin->current_capacity }} / {{ $bin->max_capacity }}
                                    </span>
                                </td>
                                <td class="text-end pe-4 action-column">
                                    <div class="d-flex justify-content-end align-items-center gap-2">
                                        <a href="{{ route('admin.bins.edit', $bin->id) }}"
                                            class="btn btn-sm btn-icon btn-light-primary"><i
                                                class="ki-duotone ki-pencil fs-4"><span class="path1"></span><span
                                                    class="path2"></span></i></a>
                                        <form action="{{ route('admin.bins.destroy', $bin->id) }}" method="POST"
                                            class="d-inline">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-icon btn-light-danger"
                                                onclick="return confirm('Bạn chắc chắn muốn xóa kệ này?');">
                                                <i class="ki-duotone ki-trash fs-4"><span class="path1"></span><span
                                                        class="path2"></span><span class="path3"></span><span
                                                        class="path4"></span><span class="path5"></span></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                {{-- Sửa colspan từ 4 thành 5 --}}
                                <td colspan="5" class="text-center text-muted py-10 fs-7">Hệ thống chưa ghi nhận Kệ hàng nào.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{-- Phân trang chuẩn Metronic --}}
            <div class="d-flex justify-content-between align-items-center mt-5">
                <div class="fs-8 fw-semibold text-gray-700">
                    Hiển thị {{ $bins->firstItem() ?? 0 }} đến {{ $bins->lastItem() ?? 0 }} của {{ $bins->total() }} kệ
                </div>
                <div>{{ $bins->appends(request()->query())->links('pagination::bootstrap-5') }}</div>
            </div>
        </div>
    </div>
@endsection
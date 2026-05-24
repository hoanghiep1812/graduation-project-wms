@extends('layouts.master')

@section('title', 'Quản lý Khu Vực')

@section('content')
    {{-- Hiển thị thông báo hệ thống kiểu Enterprise Blue/Gray --}}
    @if(session('success'))
        <div class="alert alert-success d-flex align-items-center p-4 mb-5 shadow-sm border-0">
            <i class="ki-duotone ki-check-circle fs-2hx text-success me-3"><span class="path1"></span><span class="path2"></span></i>
            <div class="d-flex flex-column">
                <span class="fw-bold fs-6">{{ session('success') }}</span>
            </div>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger d-flex align-items-center p-4 mb-5 shadow-sm border-0">
            <i class="ki-duotone ki-cross-circle fs-2hx text-danger me-3"><span class="path1"></span><span class="path2"></span></i>
            <div class="d-flex flex-column">
                <span class="fw-bold fs-6">{{ session('error') }}</span>
            </div>
        </div>
    @endif

    {{-- Card Layout chính, tương thích Dark Mode theo biến CSS layout --}}
    <div class="card card-flush shadow-sm border-0">
        {{-- Card Header: Tìm kiếm & Thêm mới tối giản --}}
        <div class="card-header align-items-center py-5 gap-2 gap-md-5">
            <div class="card-title">
                <form method="GET" action="{{ route('admin.zones.index') }}"
                    class="d-flex align-items-center position-relative my-1">
                    <i class="ki-duotone ki-magnifier fs-3 position-absolute ms-4">
                        <span class="path1"></span><span class="path2"></span>
                    </i>
                    <input type="text" name="search" value="{{ request('search') }}"
                        class="form-control form-control-solid w-250px ps-12 fs-7" placeholder="Tìm mã hoặc mô tả..." />
                </form>
            </div>
            <div class="card-toolbar">
                <a href="{{ route('admin.zones.create') }}" class="btn btn-primary fw-bold fs-7">
                     Thêm Khu Vực
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
                            <th class="min-w-150px">Mã Khu Vực</th>
                            <th class="min-w-200px">Mô tả</th>
                            {{-- Căn giữa dữ liệu số --}}
                            <th class="text-center min-w-150px">Khoảng cách Packing</th>
                            <th class="text-center min-w-150px">Số lượng Kệ</th>
                            <th class="text-end min-w-100px pe-4 rounded-end">Hành Động</th>
                        </tr>
                    </thead>
                    <tbody class="fw-semibold text-gray-700">
                        @forelse($zones as $key => $zone)
                            <tr>
                                <td class="ps-4">{{ $key + 1 }}</td>
                                <td>
                                    {{-- Nổi bật mã cốt lõi bằng màu Primary của doanh nghiệp --}}
                                    <span class="badge badge-light-primary fw-bold px-3 py-2 fs-7">{{ $zone->code }}</span>
                                </td>
                                <td class="text-gray-600">{{ $zone->description ?? 'N/A' }}</td>
                                <td class="text-center">
                                    {{-- Làm dịu dữ liệu số bằng huy hiệu màu Gray trung tính (Monochrome) --}}
                                    <span class="badge badge-light-secondary fw-bold px-3 py-2 text-gray-700">{{ $zone->distance_to_packing ?? 0 }} m</span>
                                </td>
                                <td class="text-center">
                                    {{-- Dùng Gray để khử nhiễuSuccess Green của Dashboard --}}
                                    <span class="badge badge-light-secondary fw-bold px-3 py-2 text-gray-700">{{ $zone->bin_locations_count }} Kệ</span>
                                </td>
                                <td class="text-end pe-4 action-column">
                                    <div class="d-flex justify-content-end align-items-center gap-2">
                                        <a href="{{ route('admin.zones.edit', $zone->id) }}"
                                            class="btn btn-sm btn-icon btn-light-primary"><i
                                                class="ki-duotone ki-pencil fs-4"><span class="path1"></span><span
                                                    class="path2"></span></i></a>
                                        <form action="{{ route('admin.zones.destroy', $zone->id) }}" method="POST" class="d-inline">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-icon btn-light-danger"
                                                onclick="return confirm('Xóa khu vực này sẽ ảnh hưởng đến các vị trí liên quan. Xác nhận?');">
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
                                <td colspan="6" class="text-center text-muted py-10 fs-7">Hệ thống chưa ghi nhận Khu Vực nào.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{-- Phân trang chuẩn Metronic --}}
            <div class="d-flex justify-content-between align-items-center mt-5">
                <div class="fs-8 fw-semibold text-gray-700">
                    Hiển thị {{ $zones->firstItem() ?? 0 }} đến {{ $zones->lastItem() ?? 0 }} của {{ $zones->total() }} khu vực
                </div>
                <div>{{ $zones->appends(request()->query())->links('pagination::bootstrap-5') }}</div>
            </div>
        </div>
    </div>
@endsection
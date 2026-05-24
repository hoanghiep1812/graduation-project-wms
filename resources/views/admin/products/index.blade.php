@extends('layouts.master')

@section('title', 'Quản lý Sản phẩm')

@section('content')
    {{-- Hiển thị thông báo kiểu Enterprise, mượt mà và liền mạch --}}
    @if(session('error'))
        <div class="alert alert-danger d-flex align-items-center p-4 mb-5 shadow-sm border-0">
            <i class="ki-duotone ki-shield-cross fs-2hx text-danger me-3"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
            <div class="d-flex flex-column">
                <span class="fw-bold fs-6">{{ session('error') }}</span>
            </div>
        </div>
    @endif

    @if(session('success'))
        <div class="alert alert-success d-flex align-items-center p-4 mb-5 shadow-sm border-0">
            <i class="ki-duotone ki-check-circle fs-2hx text-success me-3"><span class="path1"></span><span class="path2"></span></i>
            <div class="d-flex flex-column">
                <span class="fw-bold fs-6">{{ session('success') }}</span>
            </div>
        </div>
    @endif

    <div class="card card-flush shadow-sm border-0">
        {{-- Card Header: Tìm kiếm & Thêm mới tối giản --}}
        <div class="card-header align-items-center py-5 gap-2 gap-md-5">
            <div class="card-title">
                <form method="GET" action="{{ route('admin.products.index') }}" class="d-flex align-items-center position-relative my-1">
                    <i class="ki-duotone ki-magnifier fs-3 position-absolute ms-4"><span class="path1"></span><span class="path2"></span></i>
                    <input type="text" name="search" value="{{ request('search') }}"
                        class="form-control form-control-solid form-control-sm w-250px ps-12 fs-7" placeholder="Tìm kiếm..." />
                </form>
            </div>
            <div class="card-toolbar">
                <a href="{{ route('admin.products.create') }}" class="btn btn-primary btn-sm fw-bold fs-7">
                     Thêm Sản Phẩm
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
                            <th class="min-w-125px">Mã Sản Phẩm</th>
                            <th class="min-w-250px">Tên Sản Phẩm</th>
                            {{-- Căn giữa các dữ liệu số/đơn vị --}}
                            <th class="text-center min-w-80px">ĐVT</th>
                            <th class="text-center min-w-100px">Tồn tối thiểu</th>
                            <th class="text-center min-w-100px">HSD (Tháng)</th>
                            <th class="text-center min-w-100px">Trạng thái</th>
                            <th class="text-end min-w-100px pe-4 rounded-end">Hành Động</th>
                        </tr>
                    </thead>
                    <tbody class="fw-semibold text-gray-700">
                        @forelse($products as $key => $product)
                            <tr>
                                <td class="ps-4">{{ $key + 1 }}</td>
                                <td>
                                    {{-- Nổi bật SKU bằng màu Primary của doanh nghiệp --}}
                                    <span class="badge badge-light-primary fw-bold px-3 py-1 fs-8">{{ $product->sku }}</span>
                                </td>
                                <td>
                                    <span class="text-gray-900 fw-bold fs-7 text-hover-primary">{{ $product->name }}</span>
                                </td>
                                <td class="text-center text-gray-600">{{ $product->unit ?? 'Cái' }}</td>
                                <td class="text-center">
                                    {{-- Làm dịu màu thẻ, dùng Gray (Secondary) để khử nhiễu Danger Red --}}
                                    <span class="badge badge-light-secondary fw-bold px-3 py-1 text-gray-700">{{ $product->minimum_stock }}</span>
                                </td>
                                <td class="text-center">
                                    @if($product->has_expiry)
                                        {{-- Khử nhiễu Info Blue sang Gray trung tính --}}
                                        <span class="badge badge-light-secondary fw-bold px-3 py-1 text-gray-700">{{ $product->expiry_duration }} T</span>
                                    @else
                                        <span class="text-muted fs-8">N/A</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($product->is_active)
                                        {{-- Dùng màu doanh nghiệp (Primary) hoặc Gray cho active, khử nhiễu Green --}}
                                        <span class="badge badge-light-primary fw-bold px-3 py-1 fs-8">Kinh doanh</span>
                                    @else
                                        {{-- Chỉ dùng đỏ rực rất hạn chế cho ngừng bán --}}
                                        <span class=" badge badge-light-danger fw-bold px-3 py-1 fs-8">Ngừng bán</span>
                                    @endif
                                </td>
                                <td class="text-end pe-4 action-column">
                                    <div class="d-flex justify-content-end align-items-center gap-2">
                                        <a href="{{ route('admin.products.edit', $product->id) }}"
                                            class="btn btn-sm btn-icon btn-light-primary" title="Chỉnh sửa">
                                            <i class="ki-duotone ki-pencil fs-4"><span class="path1"></span><span
                                                    class="path2"></span></i>
                                        </a>
                                        <form action="{{ route('admin.products.destroy', $product->id) }}" method="POST" class="d-inline">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-icon btn-light-danger" title="Xóa"
                                                onclick="return confirm('Xác nhận xóa sản phẩm này khỏi hệ thống?');">
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
                                <td colspan="8" class="text-center text-muted py-10 fs-7">Hệ thống chưa ghi nhận sản phẩm nào.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Phân trang chuẩn Metronic/Bootstrap 5 --}}
            <div class="d-flex justify-content-between align-items-center mt-5">
                <div class="fs-8 fw-semibold text-gray-700">
                    Hiển thị {{ $products->firstItem() ?? 0 }} đến {{ $products->lastItem() ?? 0 }} của {{ $products->total() }} sản phẩm
                </div>
                <div>{{ $products->appends(request()->query())->links('pagination::bootstrap-5') }}</div>
            </div>
        </div>
    </div>
@endsection
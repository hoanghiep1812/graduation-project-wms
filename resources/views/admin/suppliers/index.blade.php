@extends('layouts.master')

@section('title', 'Danh Sách Nhà Cung Cấp')

@section('content')

    
    @if(session('success'))
        <div class="alert alert-dismissible bg-light-success border border-success d-flex flex-column flex-sm-row p-5 mb-10">
            <i class="ki-duotone ki-check-circle fs-2hx text-success me-4 mb-5 mb-sm-0"><span class="path1"></span><span
                    class="path2"></span></i>
            <div class="d-flex flex-column pe-0 pe-sm-10">
                <h4 class="mb-1 text-success">Thành công</h4>
                <span class="text-success">{{ session('success') }}</span>
            </div>
            <button type="button" class="position-absolute position-sm-relative m-2 m-sm-0 top-0 end-0 btn btn-icon ms-sm-auto"
                data-bs-dismiss="alert">
                <i class="ki-duotone ki-cross fs-1 text-success"><span class="path1"></span><span class="path2"></span></i>
            </button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-dismissible bg-light-danger border border-danger d-flex flex-column flex-sm-row p-5 mb-10">
            <i class="ki-duotone ki-information-5 fs-2hx text-danger me-4 mb-5 mb-sm-0"><span class="path1"></span><span
                    class="path2"></span><span class="path3"></span></i>
            <div class="d-flex flex-column pe-0 pe-sm-10">
                <h4 class="mb-1 text-danger">Dữ liệu không hợp lệ</h4>
                <ul class="mb-0 text-danger">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            <button type="button" class="position-absolute position-sm-relative m-2 m-sm-0 top-0 end-0 btn btn-icon ms-sm-auto"
                data-bs-dismiss="alert">
                <i class="ki-duotone ki-cross fs-1 text-danger"><span class="path1"></span><span class="path2"></span></i>
            </button>
        </div>
    @endif

    <div class="card card-flush shadow-sm border-0">
        
        <div class="card-header align-items-center py-5 gap-2 gap-md-5 flex-wrap border-bottom border-gray-200">

            <div class="card-title flex-column align-items-start m-0 w-100 w-md-auto">
                <span class="card-label fw-bold fs-3 mb-1 text-gray-800">Dữ Liệu Nhà Cung Cấp</span>
            </div>

            <div class="card-toolbar flex-row-fluid justify-content-end gap-3 w-100 w-md-auto">
                <form method="GET" action="{{ route('admin.suppliers.index') }}" class="w-100 w-md-auto m-0">
                    <div class="d-flex align-items-center position-relative my-1 w-100">
                        <i class="ki-duotone ki-magnifier fs-3 position-absolute ms-4 text-gray-500"><span
                                class="path1"></span><span class="path2"></span></i>
                        <input type="text" name="keyword" value="{{ request('keyword') }}"
                            class="form-control form-control-solid w-100 w-md-250px ps-12"
                            placeholder="Tìm tên, mã NCC..." />
                    </div>
                </form>

                <button type="button" class="btn btn-primary fw-bold w-100 w-md-auto shadow-sm" data-bs-toggle="modal"
                    data-bs-target="#modal_add_supplier">
                    <i class="ki-duotone ki-plus fs-2"></i> Thêm Mới
                </button>
            </div>
        </div>

        
        <div class="card-body pt-0">
            <div class="table-responsive">
                <table class="table align-middle table-row-dashed fs-6 gy-5 border-bottom text-nowrap"
                    id="kt_table_suppliers">
                    <thead>
                        <tr
                            class="text-start text-gray-500 fw-bolder fs-7 text-uppercase gs-0 border-bottom border-gray-300">
                            <th class="ps-0 min-w-100px">Mã NCC</th>
                            <th class="min-w-250px">Tên Nhà Cung Cấp</th>
                            <th class="min-w-150px">Liên Hệ</th>
                            <th class="text-center min-w-100px">Trạng thái</th>
                            <th class="text-end pe-0 min-w-100px">Hành động</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-700 fw-semibold">
                        @forelse($suppliers as $supplier)
                            <tr>
                                <td class="ps-0">
                                    <span
                                        class="badge badge-light-primary fw-bolder fs-7 px-3 py-2">{{ $supplier->code }}</span>
                                </td>

                                <td>
                                    <div class="d-flex flex-column">
                                        <a href="{{ route('admin.suppliers.show', $supplier->id) }}"
                                            class="text-gray-800 text-hover-primary fw-bolder fs-6 mb-1 d-block">
                                            {{ $supplier->name }}
                                        </a>
                                        <span class="text-muted fs-8 d-inline-block text-truncate" style="max-width: 250px;">
                                            <i class="ki-duotone ki-geolocation fs-8 me-1"><span class="path1"></span><span
                                                    class="path2"></span></i>
                                            {{ $supplier->address ?? 'Chưa cập nhật địa chỉ' }}
                                        </span>
                                    </div>
                                </td>

                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="text-gray-700 fs-7 mb-1"><span class="path1"></span><span
                                                class="path2"></span> {{ $supplier->phone ?? '---' }}</span>
                                        <span class="text-muted fs-8">MST: <span
                                                class="fw-bold text-gray-600">{{ $supplier->tax_code ?? '---' }}</span></span>
                                    </div>
                                </td>

                                <td class="text-center">
                                    @if($supplier->status === 'active')
                                        <span class="badge badge-light-success fw-bold px-3 py-2 fs-8">Đang giao dịch</span>
                                    @else
                                        <span class="badge badge-light-danger fw-bold px-3 py-2 fs-8">Ngừng giao dịch</span>
                                    @endif
                                </td>

                                <td class="text-end pe-0">
                                    <div class="d-flex justify-content-end gap-2">
                                        <a href="{{ route('admin.suppliers.show', $supplier->id) }}"
                                            class="btn btn-icon btn-light-info btn-active-info btn-sm" title="Xem chi tiết">
                                            <i class="ki-duotone ki-eye fs-4"><span class="path1"></span><span
                                                    class="path2"></span><span class="path3"></span></i>
                                        </a>
                                        <button class="btn btn-icon btn-light-primary btn-active-primary btn-sm"
                                            data-bs-toggle="modal" data-bs-target="#modal_edit_supplier_{{ $supplier->id }}"
                                            title="Chỉnh sửa">
                                            <i class="ki-duotone ki-pencil fs-4"><span class="path1"></span><span
                                                    class="path2"></span></i>
                                        </button>
                                        <form action="{{ route('admin.suppliers.destroy', $supplier->id) }}" method="POST"
                                            class="m-0">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-icon btn-light-danger btn-active-danger btn-sm"
                                                onclick="return confirm('Bạn có chắc chắn muốn ngưng giao dịch / xóa nhà cung cấp này?')"
                                                title="Xóa">
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
                                <td colspan="5" class="text-center py-10">
                                    <div class="d-flex flex-column align-items-center">
                                        <i class="ki-duotone ki-shop fs-5x text-gray-400 mb-3"><span class="path1"></span><span
                                                class="path2"></span><span class="path3"></span><span class="path4"></span><span
                                                class="path5"></span></i>
                                        <span class="text-muted fs-5 fw-semibold">Chưa có dữ liệu nhà cung cấp.</span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            
            @if(method_exists($suppliers, 'links'))
                <div class="d-flex flex-stack flex-wrap mt-5">
                    <div class="fs-6 fw-semibold text-gray-500 mb-2 mb-md-0">
                        Hiển thị từ {{ $suppliers->firstItem() ?? 0 }} đến {{ $suppliers->lastItem() ?? 0 }} trên tổng số
                        {{ $suppliers->total() ?? 0 }}
                    </div>
                    <div>
                        {{ $suppliers->appends(request()->query())->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            @endif

        </div>
    </div>

    
    <div class="modal fade" id="modal_add_supplier" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered mw-650px">
            <div class="modal-content shadow-sm">
                <form action="{{ route('admin.suppliers.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h3 class="fw-bold m-0">Thêm Nhà Cung Cấp Mới</h3>
                        <div class="btn btn-icon btn-sm btn-active-icon-danger" data-bs-dismiss="modal">
                            <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                        </div>
                    </div>

                    <div class="modal-body mx-3 mx-xl-5 my-3">
                        <div class="row g-6">
                            <div class="col-md-4 fv-row">
                                <label class="required fs-6 fw-semibold mb-2">Mã NCC</label>
                                <input type="text" class="form-control form-control-solid fw-bold" name="code"
                                    placeholder="VD: NCC001" required />
                            </div>
                            <div class="col-md-8 fv-row">
                                <label class="required fs-6 fw-semibold mb-2">Tên Nhà Cung Cấp</label>
                                <input type="text" class="form-control form-control-solid" name="name"
                                    placeholder="Tên công ty/đại lý..." required />
                            </div>
                            <div class="col-md-6 fv-row">
                                <label class="required fs-6 fw-semibold mb-2">Số điện thoại</label>
                                <input type="text" class="form-control form-control-solid" name="phone"
                                    placeholder="VD: 0987..." required />
                            </div>
                            <div class="col-md-6 fv-row">
                                <label class="fs-6 fw-semibold mb-2">Mã số thuế</label>
                                <input type="text" class="form-control form-control-solid" name="tax_code"
                                    placeholder="VD: 0101..." />
                            </div>
                            <div class="col-12 fv-row">
                                <label class="fs-6 fw-semibold mb-2">Địa chỉ</label>
                                <input type="text" class="form-control form-control-solid" name="address"
                                    placeholder="Nhập địa chỉ đầy đủ..." />
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer bg-light p-4">
                        <button type="button" class="btn btn-light fw-bold" data-bs-dismiss="modal">Hủy bỏ</button>
                        <button type="submit" class="btn btn-primary fw-bold"><i class="ki-duotone ki-save-2 fs-3"><span
                                    class="path1"></span><span class="path2"></span></i> Lưu thông tin</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    
    @foreach($suppliers as $supplier)
        <div class="modal fade" id="modal_edit_supplier_{{ $supplier->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered mw-650px">
                <div class="modal-content shadow-sm">
                    <form action="{{ route('admin.suppliers.update', $supplier->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="modal-header">
                            <h3 class="fw-bold m-0">Sửa: {{ $supplier->name }}</h3>
                            <div class="btn btn-icon btn-sm btn-active-icon-danger" data-bs-dismiss="modal">
                                <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                            </div>
                        </div>

                        <div class="modal-body mx-3 mx-xl-5 my-3">
                            <div class="row g-6">
                                <div class="col-md-4 fv-row">
                                    <label class="required fs-6 fw-semibold mb-2">Mã NCC</label>
                                    <input type="text" class="form-control form-control-solid text-muted fw-bold" name="code"
                                        value="{{ $supplier->code }}" required readonly title="Không được sửa mã" />
                                </div>
                                <div class="col-md-8 fv-row">
                                    <label class="required fs-6 fw-semibold mb-2">Tên Nhà Cung Cấp</label>
                                    <input type="text" class="form-control form-control-solid" name="name"
                                        value="{{ $supplier->name }}" required />
                                </div>
                                <div class="col-md-6 fv-row">
                                    <label class="fs-6 fw-semibold mb-2">Số điện thoại</label>
                                    <input type="text" class="form-control form-control-solid" name="phone"
                                        value="{{ $supplier->phone }}" />
                                </div>
                                <div class="col-md-6 fv-row">
                                    <label class="fs-6 fw-semibold mb-2">Mã số thuế</label>
                                    <input type="text" class="form-control form-control-solid" name="tax_code"
                                        value="{{ $supplier->tax_code }}" />
                                </div>
                                <div class="col-12 fv-row">
                                    <label class="fs-6 fw-semibold mb-2">Địa chỉ</label>
                                    <input type="text" class="form-control form-control-solid" name="address"
                                        value="{{ $supplier->address }}" />
                                </div>
                                <div class="col-12 fv-row border-top pt-5 mt-5">
                                    <label class="required fs-6 fw-semibold mb-2 text-gray-800">Tình trạng hợp tác</label>
                                    <select class="form-select form-select-solid fw-bold" name="status">
                                        <option value="active" {{ $supplier->status == 'active' ? 'selected' : '' }}>🟢 Đang giao
                                            dịch</option>
                                        <option value="inactive" {{ $supplier->status == 'inactive' ? 'selected' : '' }}>🔴 Ngừng
                                            giao dịch</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer bg-light p-4">
                            <button type="button" class="btn btn-light fw-bold" data-bs-dismiss="modal">Hủy bỏ</button>
                            <button type="submit" class="btn btn-primary fw-bold"><i class="ki-duotone ki-check fs-3"></i> Cập
                                nhật</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach

@endsection
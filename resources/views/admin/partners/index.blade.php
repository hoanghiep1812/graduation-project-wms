@extends('layouts.master')

@section('title', 'Danh Sách Đối Tác')

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
                <span class="card-label fw-bold fs-3 mb-1 text-gray-800">Dữ Liệu Đối Tác</span>
            </div>

            <div class="card-toolbar flex-row-fluid justify-content-end gap-3 w-100 w-md-auto">
                <form method="GET" action="{{ route('admin.partners.index') }}" class="w-100 w-md-auto m-0">
                    <div class="d-flex align-items-center position-relative my-1 w-100">
                        <i class="ki-duotone ki-magnifier fs-3 position-absolute ms-4 text-gray-500"><span
                                class="path1"></span><span class="path2"></span></i>
                        <input type="text" name="keyword" value="{{ request('keyword') }}"
                            class="form-control form-control-solid w-100 w-md-250px ps-12"
                            placeholder="Tìm tên, mã đối tác..." />
                    </div>
                </form>

                <button type="button" class="btn btn-primary fw-bold w-100 w-md-auto shadow-sm" data-bs-toggle="modal"
                    data-bs-target="#modal_add_partner">
                    Thêm Mới
                </button>
            </div>
        </div>

        
        <div class="card-body pt-0">
            <div class="table-responsive">
                <table class="table align-middle table-row-dashed fs-6 gy-5 border-bottom text-nowrap"
                    id="kt_table_partners">
                    <thead>
                        <tr
                            class="text-start text-gray-500 fw-bolder fs-7 text-uppercase gs-0 border-bottom border-gray-300">
                            <th class="ps-0 min-w-100px">Mã ĐT</th>
                            <th class="min-w-250px">Tên Đối Tác / Khách Hàng</th>
                            <th class="min-w-150px">Liên Hệ</th>
                            <th class="text-center min-w-100px">Trạng thái</th>
                            <th class="text-end pe-0 min-w-125px">Hành động</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-700 fw-semibold">
                        @forelse($partners as $partner)
                            <tr>
                                <td class="ps-0">
                                    <span class="badge badge-light-primary fw-bolder fs-7 px-3 py-2">{{ $partner->code }}</span>
                                </td>

                                <td>
                                    <div class="d-flex flex-column">
                                        <a href="{{ route('admin.partners.show', $partner->id) }}"
                                            class="text-gray-800 text-hover-primary mb-1 fw-bold fs-6">
                                            {{ $partner->name }}
                                        </a>
                                        <span class="text-muted fs-8 d-inline-block text-truncate" style="max-width: 250px;">
                                            <i class="ki-duotone ki-geolocation fs-8 me-1"><span class="path1"></span><span
                                                    class="path2"></span></i>
                                            {{ $partner->address ?? 'Chưa cập nhật địa chỉ' }}
                                        </span>
                                    </div>
                                </td>

                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="text-gray-700 fs-7 mb-1"><span class="path1"></span><span
                                                class="path2"></span>
                                            {{ $partner->phone ?? '---' }}</span>
                                        <span class="text-muted fs-8">MST: <span
                                                class="fw-bold text-gray-600">{{ $partner->tax_code ?? '---' }}</span></span>
                                    </div>
                                </td>

                                <td class="text-center">
                                    @if($partner->status === 'active')
                                        <span class="badge badge-light-success fw-bold px-3 py-2 fs-8">Hoạt động</span>
                                    @else
                                        <span class="badge badge-light-danger fw-bold px-3 py-2 fs-8">Ngừng HĐ</span>
                                    @endif
                                </td>

                                <td class="text-end pe-0">
                                    <div class="d-flex justify-content-end gap-2">
                                        
                                        <a href="{{ route('admin.partners.show', $partner->id) }}"
                                            class="btn btn-icon btn-light-info btn-active-info btn-sm" title="Xem hồ sơ">
                                            <i class="ki-duotone ki-eye fs-4"><span class="path1"></span><span
                                                    class="path2"></span><span class="path3"></span></i>
                                        </a>

                                        <button class="btn btn-icon btn-light-primary btn-active-primary btn-sm"
                                            data-bs-toggle="modal" data-bs-target="#modal_edit_partner_{{ $partner->id }}"
                                            title="Chỉnh sửa">
                                            <i class="ki-duotone ki-pencil fs-4"><span class="path1"></span><span
                                                    class="path2"></span></i>
                                        </button>

                                        <form action="{{ route('admin.partners.destroy', $partner->id) }}" method="POST"
                                            class="m-0">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-icon btn-light-danger btn-active-danger btn-sm"
                                                onclick="return confirm('Sếp có chắc chắn muốn ngưng giao dịch / xóa đối tác này?')"
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
                                        <i class="ki-duotone ki-profile-user fs-5x text-gray-400 mb-3"><span
                                                class="path1"></span><span class="path2"></span><span class="path3"></span><span
                                                class="path4"></span></i>
                                        <span class="text-muted fs-5 fw-semibold">Chưa có dữ liệu Khách hàng / Đối tác.</span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            
            @if(method_exists($partners, 'links'))
                <div class="d-flex flex-stack flex-wrap mt-5">
                    <div class="fs-6 fw-semibold text-gray-500 mb-2 mb-md-0">
                        Hiển thị từ {{ $partners->firstItem() ?? 0 }} đến {{ $partners->lastItem() ?? 0 }} trên tổng số
                        {{ $partners->total() ?? 0 }}
                    </div>
                    <div>
                        {{ $partners->appends(request()->query())->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            @endif

        </div>
    </div>

    
    <div class="modal fade" id="modal_add_partner" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered mw-650px">
            <div class="modal-content shadow-sm">
                <form action="{{ route('admin.partners.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h3 class="fw-bold m-0">Thêm Đối Tác Mới</h3>
                        <div class="btn btn-icon btn-sm btn-active-icon-danger" data-bs-dismiss="modal">
                            <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                        </div>
                    </div>

                    <div class="modal-body mx-3 mx-xl-5 my-3">
                        <div class="row g-6">
                            <div class="col-md-4 fv-row">
                                <label class="required fs-6 fw-semibold mb-2">Mã Đối Tác</label>
                                <input type="text" class="form-control form-control-solid fw-bold" name="code"
                                    placeholder="VD: KH001" required />
                            </div>
                            <div class="col-md-8 fv-row">
                                <label class="required fs-6 fw-semibold mb-2">Tên Đối Tác</label>
                                <input type="text" class="form-control form-control-solid" name="name"
                                    placeholder="Nhập tên..." required />
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
                        <button type="submit" class="btn btn-primary fw-bold"><span class="path1"></span><span
                                class="path2"></span> Lưu thông tin</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    
    @foreach($partners as $partner)
        <div class="modal fade" id="modal_edit_partner_{{ $partner->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered mw-650px">
                <div class="modal-content shadow-sm">
                    <form action="{{ route('admin.partners.update', $partner->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="modal-header">
                            <h3 class="fw-bold m-0">Sửa: {{ $partner->name }}</h3>
                            <div class="btn btn-icon btn-sm btn-active-icon-danger" data-bs-dismiss="modal">
                                <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                            </div>
                        </div>

                        <div class="modal-body mx-3 mx-xl-5 my-3">
                            <div class="row g-6">
                                <div class="col-md-4 fv-row">
                                    <label class="required fs-6 fw-semibold mb-2">Mã Khách</label>
                                    <input type="text" class="form-control form-control-solid text-muted fw-bold" name="code"
                                        value="{{ $partner->code }}" required readonly title="Không được sửa mã định danh" />
                                </div>
                                <div class="col-md-8 fv-row">
                                    <label class="required fs-6 fw-semibold mb-2">Tên Khách Hàng / Đối Tác</label>
                                    <input type="text" class="form-control form-control-solid" name="name"
                                        value="{{ $partner->name }}" required />
                                </div>
                                <div class="col-md-6 fv-row">
                                    <label class="fs-6 fw-semibold mb-2">Số điện thoại</label>
                                    <input type="text" class="form-control form-control-solid" name="phone"
                                        value="{{ $partner->phone }}" />
                                </div>
                                <div class="col-md-6 fv-row">
                                    <label class="fs-6 fw-semibold mb-2">Mã số thuế</label>
                                    <input type="text" class="form-control form-control-solid" name="tax_code"
                                        value="{{ $partner->tax_code }}" />
                                </div>
                                <div class="col-12 fv-row">
                                    <label class="fs-6 fw-semibold mb-2">Địa chỉ</label>
                                    <input type="text" class="form-control form-control-solid" name="address"
                                        value="{{ $partner->address }}" />
                                </div>
                                <div class="col-12 fv-row border-top pt-5 mt-5">
                                    <label class="required fs-6 fw-semibold mb-2 text-gray-800">Tình trạng hợp tác</label>
                                    <select class="form-select form-select-solid fw-bold" name="status">
                                        <option value="active" {{ $partner->status == 'active' ? 'selected' : '' }}>🟢 Hoạt động
                                        </option>
                                        <option value="inactive" {{ $partner->status == 'inactive' ? 'selected' : '' }}>🔴 Ngừng
                                            HĐ</option>
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
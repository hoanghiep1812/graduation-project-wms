@extends('layouts.master')

@section('title', 'Thêm Sản Phẩm Mới')

@section('content')
    <div class="row">
        <div class="col-lg-8 mx-auto">
            @if ($errors->any())
                <div class="alert alert-danger d-flex align-items-center p-5 mb-10">
                    <i class="ki-duotone ki-shield-cross fs-2hx text-danger me-4"><span class="path1"></span><span
                            class="path2"></span><span class="path3"></span></i>
                    <div class="d-flex flex-column">
                        <h4 class="mb-1 text-danger">Có lỗi xảy ra!</h4>
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <div class="card card-flush">
                <div class="card-header pt-5">
                    <h3 class="card-title text-gray-800 fw-bold">Thông Tin Sản Phẩm</h3>
                </div>

                <div class="card-body pt-5">
                    <form action="{{ route('admin.products.store') }}" method="POST">
                        @csrf

                        <div class="row mb-8">
                            <div class="col-md-4">
                                <label class="required fs-6 fw-semibold mb-2">Mã Sản Phẩm</label>
                                <input type="text" class="form-control form-control-solid" name="sku"
                                    value="{{ old('sku') }}" placeholder="VD: TL-PANA-01" required />
                            </div>
                            <div class="col-md-8">
                                <label class="required fs-6 fw-semibold mb-2">Tên Sản Phẩm</label>
                                <input type="text" class="form-control form-control-solid" name="name"
                                    value="{{ old('name') }}" placeholder="VD: Tủ lạnh Panasonic Inverter 234L" required />
                            </div>
                        </div>

                        <div class="row mb-8">
                            <div class="col-md-6">
                                <label class="required fs-6 fw-semibold mb-2">Đơn Vị Tính (Unit)</label>
                                <select name="unit" class="form-select form-select-solid" data-control="select2"
                                    data-hide-search="true" required>
                                    <option value="Cái" {{ old('unit') == 'Cái' ? 'selected' : '' }}>Cái</option>
                                    <option value="Chiếc" {{ old('unit') == 'Chiếc' ? 'selected' : '' }}>Chiếc</option>
                                    <option value="Bộ" {{ old('unit') == 'Bộ' ? 'selected' : '' }}>Bộ</option>
                                    <option value="Hộp" {{ old('unit') == 'Hộp' ? 'selected' : '' }}>Hộp</option>
                                    <option value="Thùng" {{ old('unit') == 'Thùng' ? 'selected' : '' }}>Thùng</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="fs-6 fw-semibold mb-2">Tồn Kho Tối Thiểu (Safety Stock)</label>
                                <input type="number" class="form-control form-control-solid" name="minimum_stock"
                                    value="{{ old('minimum_stock', 5) }}" min="0" />

                            </div>
                        </div>

                        <div class="separator separator-dashed my-8"></div>

                        <div class="row mb-8">
                            <div class="col-12">
                                <div class="d-flex flex-stack mb-4">
                                    <div class="me-5">
                                        <label class="fs-6 fw-semibold">Theo dõi Hạn Sử Dụng (FEFO)</label>
                                        <div class="fs-7 fw-semibold text-muted">Bật nếu sản phẩm là thực phẩm, hóa chất...
                                        </div>
                                    </div>
                                    <label class="form-check form-switch form-check-custom form-check-solid">
                                        <input class="form-check-input" type="checkbox" name="has_expiry"
                                            id="hasExpiryCheckbox" value="1" {{ old('has_expiry') ? 'checked' : '' }} />
                                    </label>
                                </div>
                                <div id="expiryDurationWrapper" class="bg-light-primary p-5 rounded" style="display: none;">
                                    <div class="row align-items-center">
                                        <div class="col-md-8">
                                            <label class="fs-6 fw-semibold mb-2">Hạn sử dụng mặc định (Tính theo
                                                THÁNG)</label>
                                            <input type="number" class="form-control form-control-solid"
                                                name="expiry_duration" placeholder="VD: 6, 12..." min="1"
                                                value="{{ old('expiry_duration') }}" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="separator separator-dashed my-8"></div>
                        <div class="d-flex flex-stack mb-8">
                            <div class="me-5">
                                <label class="fs-6 fw-semibold">Trạng thái kinh doanh</label>
                            </div>
                            <label class="form-check form-switch form-check-custom form-check-solid">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1"
                                    checked="checked" />
                                <span class="form-check-label fw-semibold text-muted">Đang bán</span>
                            </label>
                        </div>

                        <div class="text-end mt-10">
                            <a href="{{ route('admin.products.index') }}" class="btn btn-light me-3">Hủy bỏ</a>
                            <button type="submit" class="btn btn-primary">
                                <span class="path1"></span><span class="path2"></span> Lưu Sản Phẩm
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const checkbox = document.getElementById('hasExpiryCheckbox');
            const wrapper = document.getElementById('expiryDurationWrapper');
            const input = document.querySelector('input[name="expiry_duration"]');

            function toggleExpiry() {
                if (checkbox.checked) {
                    wrapper.style.display = 'block';
                    input.setAttribute('required', 'required');
                } else {
                    wrapper.style.display = 'none';
                    input.removeAttribute('required');
                    input.value = '';
                }
            }

            toggleExpiry();
            checkbox.addEventListener('change', toggleExpiry);
        });
    </script>
@endsection
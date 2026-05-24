@extends('layouts.master')

@section('title', 'Tạo Phiếu Xuất Kho (SO)')

@section('content')
    <div class="row">
        <div class="col-xl-9 mx-auto">

            
            @if ($errors->any())
                <div
                    class="alert alert-dismissible bg-light-danger border border-danger d-flex flex-column flex-sm-row p-5 mb-10">
                    <i class="ki-duotone ki-shield-cross fs-2hx text-danger me-4 mb-5 mb-sm-0"><span class="path1"></span><span
                            class="path2"></span><span class="path3"></span></i>
                    <div class="d-flex flex-column pe-0 pe-sm-10">
                        <h4 class="mb-1 text-danger">Dữ liệu không hợp lệ</h4>
                        <ul class="mb-0 text-danger">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    <button type="button"
                        class="position-absolute position-sm-relative m-2 m-sm-0 top-0 end-0 btn btn-icon ms-sm-auto"
                        data-bs-dismiss="alert">
                        <i class="ki-duotone ki-cross fs-1 text-danger"><span class="path1"></span><span
                                class="path2"></span></i>
                    </button>
                </div>
            @endif

            <div class="card card-flush border-0 shadow-sm">
                <div class="card-header pt-7">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bold text-gray-800 fs-3">Tạo Phiếu Xuất Mới</span>
                    </h3>
                </div>

                <div class="card-body pt-5">
                    <form action="{{ route('admin.sales_orders.store') }}" method="POST">
                        @csrf

                        
                        <div class="mb-8">
                            <h4 class="text-gray-800 fw-bold mb-5 fs-5">1. Thông tin chứng từ</h4>
                            <div class="row g-5">
                                <div class="col-md-6">
                                    <label class="required fs-6 fw-semibold mb-2">Mã Phiếu</label>
                                    <input type="text" class="form-control form-control-solid bg-light text-muted fw-bold"
                                        name="so_number" value="{{ $autoSoNumber }}" readonly />
                                </div>

                                <div class="col-md-6">
                                    <label class="required fs-6 fw-semibold mb-2"> Đối tác</label>
                                    <select name="partner_id" class="form-select form-select-solid" data-control="select2"
                                        required>
                                        <option value="">-- Chọn --</option>
                                        @foreach($partners as $partner)
                                            <option value="{{ $partner->id }}">
                                                [{{ $partner->code }}] - {{ $partner->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="separator separator-dashed border-gray-300 my-8"></div>

                        
                        <div class="mb-5">
                            <h4 class="text-gray-800 fw-bold mb-5 fs-5">2. Chi tiết hàng xuất</h4>

                            <div class="bg-light border border-gray-300 border-dashed rounded p-5">
                                
                                <div class="row mb-3 d-none d-md-flex">
                                    <div class="col-md-7"><label class="fs-7 fw-bold text-muted text-uppercase">Sản Phẩm
                                            (SKU)</label></div>
                                    <div class="col-md-4"><label class="fs-7 fw-bold text-muted text-uppercase">Số lượng
                                            xuất</label></div>
                                    <div class="col-md-1"></div>
                                </div>

                                
                                <div id="items-container">
                                    <div
                                        class="row mb-4 item-row align-items-center bg-body bg-md-transparent p-4 p-md-0 rounded shadow-sm shadow-md-none border border-gray-200 border-md-0">

                                        
                                        <div class="col-md-7 mb-3 mb-md-0">
                                            
                                            <label class="d-md-none required fs-7 fw-bold text-muted mb-2">Sản Phẩm
                                                (SKU)</label>
                                            <select name="items[0][product_id]"
                                                class="form-select form-select-solid item-select" data-control="select2"
                                                required>
                                                <option value="">-- Tìm và chọn mặt hàng --</option>
                                                @foreach($products as $prod)
                                                    <option value="{{ $prod->id }}">
                                                        [{{ $prod->sku }}] - {{ $prod->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        
                                        <div class="col-md-4 mb-4 mb-md-0">
                                            
                                            <label class="d-md-none required fs-7 fw-bold text-muted mb-2">Số lượng
                                                xuất</label>
                                            <div class="position-relative">
                                                <input type="number" name="items[0][quantity]"
                                                    class="form-control form-control-solid pe-12" value="1" min="1"
                                                    required>
                                            </div>
                                        </div>

                                        
                                        <div class="col-md-1 text-end">
                                            <button type="button"
                                                class="btn btn-light-danger remove-item w-100 w-md-auto p-md-3"
                                                title="Xóa dòng này">
                                                Xóa<span class="path1"></span><span class="path2"></span><span
                                                    class="path3"></span><span class="path4"></span><span
                                                    class="path5"></span>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                
                                <div class="mt-4">
                                    <button type="button" id="add-item"
                                        class="btn btn-sm btn-light-primary fw-bold w-100 w-md-auto">
                                        Thêm sản phẩm
                                    </button>
                                </div>
                            </div>
                        </div>

                        
                        <div class="d-flex justify-content-end pt-7 mt-10 border-top border-gray-200">
                            <a href="{{ route('admin.sales_orders.index') }}" class="btn btn-light fw-bold me-3">Hủy bỏ</a>
                            <button type="submit" class="btn btn-primary fw-bold">
                                Tạo Phiếu Xuất
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function () {
            let index = 1;

            $('#add-item').on('click', function () {
                // Phải đồng bộ HTML này với cục HTML ở trên
                let html = `
                                                                                                    <div class="row mb-4 item-row align-items-center bg-body bg-md-transparent p-4 p-md-0 rounded shadow-sm shadow-md-none border border-gray-200 border-md-0" style="display: none;">
                                                                                                        <div class="col-md-7 mb-3 mb-md-0">
                                                                                                            <label class="d-md-none required fs-7 fw-bold text-muted mb-2">Sản Phẩm (SKU)</label>
                                                                                                            <select name="items[${index}][product_id]" class="form-select form-select-solid item-select" required>
                                                                                                                <option value="">-- Tìm và chọn mặt hàng --</option>
                                                                                                                @foreach($products as $prod)
                                                                                                                    <option value="{{ $prod->id }}">
                                                                                                                        [{{ $prod->sku }}] - {{ $prod->name }}
                                                                                                                    </option>
                                                                                                                @endforeach
                                                                                                            </select>
                                                                                                        </div>

                                                                                                        <div class="col-md-4 mb-4 mb-md-0">
                                                                                                            <label class="d-md-none required fs-7 fw-bold text-muted mb-2">Số lượng xuất</label>
                                                                                                            <div class="position-relative">
                                                                                                                <input type="number" name="items[${index}][quantity]" class="form-control form-control-solid pe-12" value="1" min="1" required>                                                                                                                                        
                                                                                                            </div>
                                                                                                        </div>

                                                                                                        <div class="col-md-1 text-end">
                                                                                                            <button type="button" class="btn btn-light-danger remove-item w-100 w-md-auto p-md-3" title="Xóa dòng này">
                                                                                                                Xóa<span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span>
                                                                                                            </button>
                                                                                                        </div>
                                                                                                    </div>
                                                                                                `;

                // Thêm HTML vào DOM
                let $newRow = $(html);
                $('#items-container').append($newRow);
                $newRow.slideDown(200);

                // Kích hoạt Select2
                $newRow.find('.item-select').select2({
                    minimumResultsForSearch: 0
                });

                index++;
            });

            // Xóa dòng
            $('#items-container').on('click', '.remove-item', function () {
                if ($('.item-row').length <= 1) {
                    alert('Phải có ít nhất 1 sản phẩm trong Phiếu xuất!');
                    return;
                }

                $(this).closest('.item-row').slideUp(200, function () {
                    $(this).remove();
                });
            });
        });
    </script>
@endpush
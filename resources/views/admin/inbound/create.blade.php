@extends('layouts.master')

@section('title', 'Tạo Phiếu Nhập Kho (PO)')

@section('content')

    <div class="row">
        <div class="col-xl-9 mx-auto">
            <div class="card card-flush border-0 shadow-sm">

                <div class="card-header pt-7">
                    <h3 class="card-title flex-column">
                        <span class="fw-bold text-gray-800 fs-3">Tạo Phiếu Nhập Mới</span>
                    </h3>
                </div>

                
                <div class="card-body pt-5">
                    <form action="{{ route('admin.inbound.store') }}" method="POST">
                        @csrf

                        
                        <div class="mb-8">
                            <h4 class="fw-bold mb-5 fs-5">1. Thông tin chứng từ</h4>

                            <div class="row g-5">
                                
                                <div class="col-md-4">
                                    <label class="required fs-6 fw-semibold mb-2">Mã phiếu</label>
                                    <input type="text" class="form-control form-control-solid bg-light fw-bold text-muted"
                                        name="po_number" value="{{ $autoPoNumber }}" readonly>
                                </div>

                                
                                <div class="col-md-4">
                                    <label class="required fs-6 fw-semibold mb-2">Nhà Cung Cấp</label>
                                    <select name="supplier_id" class="form-select form-select-solid" data-control="select2"
                                        required>
                                        <option value="">-- Chọn --</option>
                                        @foreach($suppliers as $s)
                                            <option value="{{ $s->id }}">
                                                [{{ $s->code }}] - {{ $s->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="separator my-8"></div>

                        
                        <div class="mb-5">

                            <div class="d-flex justify-content-between align-items-center mb-5">
                                <h4 class="fw-bold fs-5">2. Danh sách hàng</h4>

                                <button type="button" id="addRow" class="btn btn-sm btn-light-primary">
                                    + Thêm dòng
                                </button>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-bordered align-middle" id="itemsTable">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>Sản phẩm</th>
                                            <th width="150">Số lượng</th>
                                            <th width="100" class="text-center">Hành động</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <tr>
                                            <td>
                                                <select name="items[0][product_id]" class="form-select form-select-solid"
                                                    data-control="select2" required>
                                                    <option value="">-- Chọn --</option>
                                                    @foreach($products as $p)
                                                        <option value="{{ $p->id }}">
                                                            [{{ $p->sku }}] - {{ $p->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </td>

                                            <td>
                                                <input type="number" name="items[0][quantity]"
                                                    class="form-control form-control-solid" min="1" value="1">
                                            </td>

                                            <td class="text-center">
                                                <button type="button" class="btn btn-sm btn-light-danger removeRow">
                                                    Xóa
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end pt-7 mt-10 border-top">
                            <a href="{{ route('admin.inbound.index') }}" class="btn btn-light fw-bold me-3">
                                Hủy
                            </a>

                            <button type="submit" class="btn btn-primary fw-bold">
                                Tạo Phiếu Nhập
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        ```

    </div>

    <script>
        let index = 1;

        document.getElementById('addRow').addEventListener('click', function () {
            const table = document.querySelector('#itemsTable tbody');

            let row = `
                                    <tr>
                                        <td>
                                            <select name="items[${index}][product_id]"
                                                    class="form-select form-select-solid"
                                                    data-control="select2"
                                                    required>
                                                <option value="">-- Chọn --</option>
                                                @foreach($products as $p)
                                                    <option value="{{ $p->id }}">
                                                        [{{ $p->sku }}] - {{ $p->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>

                                        <td>
                                            <input type="number"
                                                   name="items[${index}][quantity]"
                                                   class="form-control form-control-solid"
                                                   min="1"
                                                   value="1">
                                        </td>

                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-light-danger removeRow">
                                                Xóa
                                            </button>
                                        </td>
                                    </tr>
                                    `;

            table.insertAdjacentHTML('beforeend', row);
            index++;

            setTimeout(() => {
                $('[data-control="select2"]').select2();
            }, 0);
        });

        document.addEventListener('click', function (e) {
            if (e.target.classList.contains('removeRow')) {
                e.target.closest('tr').remove();
            }
        });
    </script>

@endsection
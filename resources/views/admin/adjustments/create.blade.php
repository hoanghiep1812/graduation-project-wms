@extends('layouts.master')

@section('title', 'Lập Phiếu Kiểm Kê')

@section('content')
    <div class="row">
        <div class="col-xl-8 col-lg-10 mx-auto">

            @if(session('error'))
                <div
                    class="alert alert-dismissible bg-light-danger border border-danger d-flex flex-column flex-sm-row p-5 mb-10">
                    <i class="ki-duotone ki-shield-cross fs-2hx text-danger me-4 mb-5 mb-sm-0"><span class="path1"></span><span
                            class="path2"></span><span class="path3"></span></i>
                    <div class="d-flex flex-column pe-0 pe-sm-10">
                        <h4 class="mb-1 text-danger">Từ chối điều chỉnh</h4>
                        <span class="text-danger">{{ session('error') }}</span>
                    </div>
                    <button type="button"
                        class="position-absolute position-sm-relative m-2 m-sm-0 top-0 end-0 btn btn-icon ms-sm-auto"
                        data-bs-dismiss="alert">
                        <i class="ki-duotone ki-cross fs-1 text-danger"><span class="path1"></span><span
                                class="path2"></span></i>
                    </button>
                </div>
            @endif

            @if ($errors->any())
                <div
                    class="alert alert-dismissible bg-light-danger border border-danger d-flex flex-column flex-sm-row p-5 mb-10">
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
                </div>
            @endif

            <div class="card card-flush border-0 shadow-sm">
                <div class="card-header pt-7">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bold text-gray-800 fs-3">Phiếu Điều Chỉnh Tồn Kho</span>
                    </h3>
                </div>

                <div class="card-body pt-5">
                    <form action="{{ route('admin.adjustments.store') }}" method="POST">
                        @csrf
                        <div class="mb-8">
                            <h4 class="text-gray-800 fw-bold mb-5 fs-5">1. Thông tin người kiểm đếm</h4>
                            
                            <div class="bg-light border border-gray-300 border-dashed rounded p-5">
                                <label class="required fs-6 fw-semibold mb-2">Họ & tên nhân viên đếm hàng</label>
                                <input type="text" name="counter_name" class="form-control form-control-solid"
                                    placeholder="Ví dụ: Nguyễn Văn A..." required value="{{ old('counter_name') }}">
                                <div class="text-muted fs-8 mt-2">
                                    <i class="ki-duotone ki-information-5 fs-8 text-muted me-1"><span
                                            class="path1"></span><span class="path2"></span><span class="path3"></span></i>

                                </div>
                            </div>
                        </div>

                        <div class="mb-5">
                            <h4 class="text-gray-800 fw-bold mb-5 fs-5">2. Dữ liệu chênh lệch</h4>
                            <div class="row g-5">
                                
                                <div class="col-12">
                                    <label class="required fs-6 fw-semibold mb-2">Xác định vị trí & Lô hàng cần điều
                                        chỉnh</label>
                                    <select name="inventory_id" class="form-select form-select-solid" data-control="select2"
                                        required>
                                        <option value="">-- Quét mã hoặc Chọn lô hàng --</option>
                                        @foreach($inventories as $inv)
                                            @php
                                                if ($inv->batch) {
                                                    $hsd = \Carbon\Carbon::parse($inv->batch->expiry_date)->format('d/m/Y');
                                                    $batchInfo = " - Lô: " . $inv->batch->batch_number . " (HSD: " . $hsd . ")";
                                                } else {
                                                    $batchInfo = " - Không có lô";
                                                }

                                                $isSelected = (isset($selectedInventoryId) && $selectedInventoryId == $inv->id) || old('inventory_id') == $inv->id;
                                            @endphp

                                            <option value="{{ $inv->id }}" {{ $isSelected ? 'selected' : '' }}>
                                                Kệ: {{ $inv->binLocation->code ?? 'N/A' }}
                                                | {{ $inv->product->name }}
                                                {{ $batchInfo }}
                                                (Tồn hệ thống: {{ $inv->on_hand_quantity }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                
                                <div class="col-md-6 mb-3 mb-md-0">
                                    <label class="required fs-6 fw-semibold text-gray-800 mb-2">Số lượng đếm tay thực
                                        tế</label>
                                    
                                    <div class="position-relative">
                                        <input type="number" name="actual_quantity"
                                            class="form-control form-control-solid pe-12 fw-bold text-primary fs-5"
                                            placeholder="Nhập số thực tế..." min="0" required
                                            value="{{ old('actual_quantity') }}">
                                        <span
                                            class="position-absolute top-50 end-0 translate-middle-y fw-bold text-muted pe-4">Cái</span>
                                    </div>
                                </div>

                                
                                <div class="col-md-6">
                                    <label class="required fs-6 fw-semibold mb-2">Lý do chênh lệch</label>
                                    <select name="reason" class="form-select form-select-solid" data-control="select2"
                                        data-hide-search="true" required>
                                        <option value="Hàng mất cắp / Thất lạc" {{ old('reason') == 'Hàng mất cắp / Thất lạc' ? 'selected' : '' }}>Hàng mất cắp / Thất lạc</option>
                                        <option value="Hư hỏng / Chuột cắn" {{ old('reason') == 'Hư hỏng / Chuột cắn' ? 'selected' : '' }}>Hư hỏng / Rách vỡ</option>
                                        <option value="Nhập sai số liệu từ trước" {{ old('reason') == 'Nhập sai số liệu từ trước' ? 'selected' : '' }}>Nhập sai số liệu từ trước</option>
                                        <option value="Khách trả hàng nhưng chưa nhập" {{ old('reason') == 'Khách trả hàng nhưng chưa nhập' ? 'selected' : '' }}>Khách trả hàng nhưng quên nhập</option>
                                        <option value="Khác" {{ old('reason') == 'Khác' ? 'selected' : '' }}>Khác (Ghi chú
                                            thêm ở thẻ kho)</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        
                        <div class="d-flex justify-content-end pt-7 mt-10 border-top border-gray-200">
                            <a href="{{ route('admin.inventory.index') }}" class="btn btn-light fw-bold me-3">Hủy bỏ</a>
                            
                            <button type="submit" class="btn btn-warning fw-bold"
                                onclick="return confirm('CẢNH BÁO: Tồn kho sẽ bị thay đổi vĩnh viễn và lưu vào Thẻ Kho. Bạn có chắc chắn với con số này?');">
                                Xác Nhận Cập Nhật
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
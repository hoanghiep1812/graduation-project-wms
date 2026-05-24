@extends('layouts.master')

@section('title', 'Thêm Khu Vực Mới')

@section('content')
    <div class="row">
        <div class="col-lg-6 mx-auto">
            <div class="card card-flush">
                <div class="card-header pt-5">
                    <h3 class="card-title text-gray-800 fw-bold">Thông tin Khu Vực</h3>
                </div>
                <div class="card-body pt-5">
                    <form action="{{ route('admin.zones.store') }}" method="POST">
                        @csrf
                        <div class="mb-8">
                            <label class="required fs-6 fw-semibold mb-2">Mã Khu Vực</label>
                            <input type="text" class="form-control form-control-solid" name="code"
                                placeholder="VD: ZONE_D, KHU_LANH..." required />
                            <div class="text-muted fs-7 mt-1">Viết liền không dấu, hệ thống sẽ tự động in hoa.</div>
                        </div>

                        <div class="mb-8">
                            <label class="required fs-6 fw-semibold mb-2">Khoảng cách tới khu Đóng gói (mét)</label>
                            <input type="number" class="form-control form-control-solid" name="distance_to_packing"
                                placeholder="VD: 15" min="0" value="0" required />
                        </div>

                        <div class="mb-8">
                            <label class="fs-6 fw-semibold mb-2">Mô tả mục đích sử dụng</label>
                            <textarea class="form-control form-control-solid" name="description" rows="3"
                                placeholder="VD: Khu vực dành riêng cho hàng cồng kềnh..."></textarea>
                        </div>
                        <div class="text-end">
                            <a href="{{ route('admin.zones.index') }}" class="btn btn-light me-3">Hủy</a>
                            <button type="submit" class="btn btn-primary">Lưu Khu Vực</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
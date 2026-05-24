@extends('layouts.master')
@section('title', 'Sửa Khu Vực: ' . $zone->code)
@section('content')
    <div class="row">
        <div class="col-lg-6 mx-auto">
            <div class="card card-flush">
                <div class="card-header pt-5">
                    <h3 class="card-title text-gray-800 fw-bold">Cập nhật Khu Vực</h3>
                </div>
                <div class="card-body pt-5">
                    <form action="{{ route('admin.zones.update', $zone->id) }}" method="POST">
                        @csrf @method('PUT')
                        <div class="mb-8">
                            <label class="required fs-6 fw-semibold mb-2">Mã Khu Vực</label>
                            <input type="text" class="form-control form-control-solid" name="code" value="{{ $zone->code }}"
                                required />
                        </div>

                        <div class="mb-8">
                            <label class="required fs-6 fw-semibold mb-2">Khoảng cách tới khu Đóng gói (mét)</label>
                            <input type="number" class="form-control form-control-solid" name="distance_to_packing"
                                value="{{ $zone->distance_to_packing }}" min="0" required />
                        </div>

                        <div class="mb-8">
                            <label class="fs-6 fw-semibold mb-2">Mô tả mục đích sử dụng</label>
                            <textarea class="form-control form-control-solid" name="description"
                                rows="3">{{ $zone->description }}</textarea>
                        </div>
                        <div class="text-end">
                            <a href="{{ route('admin.zones.index') }}" class="btn btn-light me-3">Hủy</a>
                            <button type="submit" class="btn btn-primary">Cập Nhật</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
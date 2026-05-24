@extends('layouts.master')
@section('title', 'Thêm Kệ Hàng Mới')
@section('content')
    <div class="row">
        <div class="col-lg-6 mx-auto">
            @if ($errors->any())
                <div class="alert alert-danger p-5 mb-5">
                    <ul>@foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach</ul>
            </div> @endif

            <div class="card card-flush">
                <div class="card-header pt-5">
                    <h3 class="card-title text-gray-800 fw-bold">Thông tin Kệ Hàng</h3>
                </div>
                <div class="card-body pt-5">
                    <form action="{{ route('admin.bins.store') }}" method="POST">
                        @csrf
                        <div class="mb-8">
                            <label class="required fs-6 fw-semibold mb-2">Chọn Khu Vực chứa Kệ này</label>
                            <select name="zone_id" class="form-select form-select-solid" data-control="select2" required>
                                <option value="">-- Chọn Zone --</option>
                                @foreach($zones as $zone)
                                    <option value="{{ $zone->id }}">{{ $zone->code }}
                                        {{ $zone->description ? '(' . $zone->description . ')' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-8">
                            <label class="required fs-6 fw-semibold mb-2">Mã Kệ </label>
                            <input type="text" class="form-control form-control-solid" name="code"
                                placeholder="VD: A1-WH4, B2-WH4..." required />
                        </div>
                        <div class="mb-8">
                            <label class="required fs-6 fw-semibold mb-2">Sức chứa tối đa</label>
                            <input type="number" class="form-control form-control-solid" name="max_capacity"
                                placeholder="VD: 100" min="1" required />
                        </div>
                        <div class="text-end">
                            <a href="{{ route('admin.bins.index') }}" class="btn btn-light me-3">Hủy</a>
                            <button type="submit" class="btn btn-primary">Lưu Kệ Hàng</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
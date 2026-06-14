@extends('layouts.master')

@section('title', 'Chi tiết Đối Tác: ' . $partner->name)

@section('content')

    
    <div class="mb-5">
        <a href="{{ route('admin.partners.index') }}" class="btn btn-sm btn-light fw-bold">
            <span class="path1"></span><span class="path2"></span> Quay lại danh sách
        </a>
    </div>

    
    <div class="d-flex flex-column flex-lg-row gap-5 gap-xl-8">

        
        <div class="flex-column flex-lg-row-auto w-100 w-lg-300px w-xl-350px">
            <div class="card card-flush border-0 shadow-sm mb-5 mb-xl-8">
                <div class="card-body pt-15">

                    
                    <div class="d-flex flex-center flex-column mb-8">
                        <div class="symbol symbol-100px symbol-circle mb-5">
                            <span class="symbol-label fs-2x fw-bolder bg-light-primary text-primary">
                                {{ mb_strtoupper(mb_substr($partner->name, 0, 1)) }}
                            </span>
                        </div>
                        <h3 class="fs-3 text-gray-800 fw-bold mb-1 text-center">{{ $partner->name }}</h3>
                        <div class="fs-6 fw-semibold text-muted mb-4">Mã: {{ $partner->code }}</div>

                        @if($partner->status === 'active')
                            <span class="badge badge-light-success fw-bold px-3 py-2">Đang giao dịch</span>
                        @else
                            <span class="badge badge-light-danger fw-bold px-3 py-2">Ngừng giao dịch</span>
                        @endif
                    </div>

                    <div class="separator separator-dashed my-5"></div>

                    
                    <div class="pb-5 fs-6">
                        <div class="fw-bolder mt-5 text-gray-800 text-uppercase fs-7"><span class="path1"></span><span
                                class="path2"></span> Mã số thuế</div>
                        <div class="text-gray-600 mt-1">{{ $partner->tax_code ?? 'Chưa cập nhật' }}</div>

                        <div class="fw-bolder mt-5 text-gray-800 text-uppercase fs-7"><span class="path1"></span><span
                                class="path2"></span> Số điện thoại</div>
                        <div class="text-gray-600 mt-1">{{ $partner->phone ?? '---' }}</div>

                        <div class="fw-bolder mt-5 text-gray-800 text-uppercase fs-7"><span class="path1"></span><span
                                class="path2"></span> Địa chỉ</div>
                        <div class="text-gray-600 mt-1">{{ $partner->address ?? '---' }}</div>
                    </div>

                </div>
            </div>
        </div>

        
        <div class="flex-lg-row-fluid">
            <div class="card card-flush border-0 shadow-sm h-100">

                <div class="card-header pt-7">
                    <div class="card-title flex-column m-0">
                        <h3 class="fw-bold m-0 text-gray-800">Lịch sử Đơn Xuất Kho</h3>
                    </div>
                </div>

                <div class="card-body pt-5">
                    <div class="table-responsive">
                        
                        <table class="table align-middle table-row-dashed table-row-gray-200 fs-6 gy-5 text-nowrap">
                            <thead>
                                <tr
                                    class="text-start text-gray-500 fw-bolder fs-7 text-uppercase gs-0 border-bottom border-gray-300">
                                    <th class="ps-0 min-w-125px">Mã Đơn (SO)</th>
                                    <th class="min-w-150px">Ngày Xuất</th>
                                    <th class="text-center min-w-125px">Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody class="fw-semibold text-gray-600">
                                @forelse($partner->salesOrders ?? [] as $order)
                                    <tr>
                                        
                                        <td class="ps-0">
                                            <span
                                                class="text-gray-800 fw-bolder d-block fs-6">{{ $order->so_number ?? 'N/A' }}</span>
                                        </td>

                                        
                                        <td>
                                            <span class="d-block">{{ $order->created_at->format('d/m/Y') }}</span>
                                            <span class="text-muted fs-8">{{ $order->created_at->format('H:i') }}</span>
                                        </td>

                                        <td class="text-center">
                                            <span class="badge {{ $order->status_meta['class'] }} fs-8 fw-bold px-3 py-2">
                                                {{ $order->status_meta['label'] }}
                                            </span>
                                        </td>

                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-10">
                                            <div class="d-flex flex-column align-items-center">
                                                <i class="ki-duotone ki-delivery-3 fs-5x text-gray-400 mb-3"><span
                                                        class="path1"></span><span class="path2"></span><span
                                                        class="path3"></span></i>
                                                <span class="text-muted fs-5 fw-semibold">Đối tác này chưa có đơn xuất kho
                                                    nào.</span>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection
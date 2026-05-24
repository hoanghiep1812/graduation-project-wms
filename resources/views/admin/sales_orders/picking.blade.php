@extends('layouts.master')

@section('title', 'Lộ trình Lấy hàng - Đơn: ' . $order->so_number)

@section('content')

    
    @if(session('error'))
        <div class="alert alert-dismissible bg-light-danger border border-danger d-flex flex-column flex-sm-row p-5 mb-10">
            <i class="ki-duotone ki-shield-cross fs-2hx text-danger me-4 mb-5 mb-sm-0"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
            <div class="d-flex flex-column pe-0 pe-sm-10">
                <h4 class="mb-1 text-danger">Lỗi xuất kho</h4>
                <span class="text-danger">{{ session('error') }}</span>
            </div>
            <button type="button" class="position-absolute position-sm-relative m-2 m-sm-0 top-0 end-0 btn btn-icon ms-sm-auto" data-bs-dismiss="alert">
                <i class="ki-duotone ki-cross fs-1 text-danger"><span class="path1"></span><span class="path2"></span></i>
            </button>
        </div>
    @endif

    <div class="row g-5 g-xl-8">
        
        <div class="col-xl-3">
            <div class="card border-0 shadow-sm mb-xl-8">
                <div class="card-header border-0 pt-5">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bold fs-4 mb-1 text-gray-800">Lệnh Lấy Hàng</span>
                    </h3>
                </div>
                <div class="card-body pt-3 pb-8">
                    <div class="d-flex flex-column mb-5">
                        <span class="text-muted fw-semibold fs-7 text-uppercase">Mã Phiếu (SO)</span>
                        <span class="text-gray-900 fw-bolder fs-3">{{ $order->so_number }}</span>
                    </div>
                    
                    <div class="d-flex flex-column mb-5">
                        <span class="text-muted fw-semibold fs-7 text-uppercase">Khách hàng</span>
                        <span class="text-gray-800 fw-bold fs-5">{{ $order->customer_name }}</span>
                    </div>

                    <div class="d-flex flex-column mb-5">
                        <span class="text-muted fw-semibold fs-7 text-uppercase">Ngày tạo lệnh</span>
                        <span class="text-gray-800 fw-bold fs-6">{{ $order->created_at->format('d/m/Y H:i') }}</span>
                    </div>

                    <div class="d-flex flex-column">
                        <span class="text-muted fw-semibold fs-7 text-uppercase">Phụ trách duyệt</span>
                        <span class="text-gray-800 fw-bold fs-6">Admin (ID: {{ $order->confirmed_by }})</span>
                    </div>
                </div>
            </div>
        </div>

        
        <div class="col-xl-9">
            <div class="card border-0 shadow-sm mb-5 mb-xl-8">
                <div class="card-header border-0 pt-5">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bold fs-3 mb-1 text-gray-800">Lộ Trình Tối Ưu (Picking Route)</span>
                        <span class="text-muted mt-1 fw-semibold fs-7">WMS AI đã sắp xếp lộ trình đi ngắn nhất. Vui lòng lấy hàng theo đúng thứ tự.</span>
                    </h3>
                </div>
                
                <div class="card-body pt-5">
                    <div class="timeline">
                        
                        @foreach($allocations as $index => $allo)
                            @php
                                $inventory = $allo->inventory;
                                $bin = $inventory->binLocation;
                                $zone = $bin->zone ? $bin->zone->code : 'N/A';
                                $batch = $inventory->batch;
                                $product = $allo->salesOrderItem->product;
                            @endphp

                            <div class="timeline-item">
                                
                                <div class="timeline-line w-40px"></div>
                                
                                
                                <div class="timeline-icon symbol symbol-circle symbol-40px me-4">
                                    <div class="symbol-label bg-light-primary">
                                        <span class="fs-5 fw-bolder text-primary">{{ $index + 1 }}</span>
                                    </div>
                                </div>
                                
                                
                                <div class="timeline-content mb-10 mt-n1">
                                    <div class="pe-3 mb-4">
                                        
                                        <div class="fs-5 fw-semibold mb-3 d-flex align-items-center flex-wrap">
                                            <span class="text-gray-600 me-2">Đi đến:</span> 
                                            <span class="badge badge-light-info fw-bold px-3 py-2 fs-6 me-2">Khu Vực {{ $zone }}</span> 
                                            <i class="ki-duotone ki-arrow-right fs-5 text-muted mx-2 d-none d-sm-block"><span class="path1"></span><span class="path2"></span></i> 
                                            <span class="badge badge-light-primary fw-bold px-3 py-2 fs-6 mt-2 mt-sm-0">Kệ {{ $bin->code }}</span>
                                        </div>
                                        
                                        
                                        <div class="border border-dashed border-gray-300 rounded px-5 py-4 bg-light">
                                            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center">
                                                
                                                
                                                <div class="mb-4 mb-sm-0 me-3">
                                                    <span class="text-gray-800 fw-bold fs-4 d-block mb-1">{{ $product->name }}</span>
                                                    <span class="text-muted fw-semibold fs-7 d-block">Mã SKU: {{ $product->sku }}</span>
                                                </div>
                                                
                                                
                                                <div class="text-start text-sm-end bg-body rounded border border-gray-200 p-3 w-100 w-sm-auto">
                                                    <span class="text-primary fw-bolder fs-2hx d-block lh-1 mb-2">{{ $allo->allocated_quantity }} <span class="fs-5 text-muted fw-semibold">cái</span></span>
                                                    <span class="badge badge-light-warning fw-bold fs-8">
                                                        Lô: {{ $batch->batch_number }} | HSD: {{ \Carbon\Carbon::parse($batch->expiry_date)->format('d/m/Y') }}
                                                    </span>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach

                        
                        <div class="timeline-item">
                            <div class="timeline-line w-40px"></div>
                            <div class="timeline-icon symbol symbol-circle symbol-40px me-4">
                                <div class="symbol-label bg-light-success">
                                    <i class="ki-duotone ki-check text-success fs-2"></i>
                                </div>
                            </div>
                            <div class="timeline-content mb-2 mt-n1">
                                <div class="fs-4 fw-bold text-success mb-1">HOÀN TẤT TUYẾN ĐƯỜNG</div>
                                <div class="fs-6 fw-semibold text-gray-600">Kiểm tra lại số lượng và mang hàng ra khu vực Đóng Gói.</div>
                            </div>
                        </div>

                    </div>

                    
                    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center mt-10 border-top border-gray-200 pt-7">
                        <a href="{{ route('admin.sales_orders.index') }}" class="btn btn-light fw-bold w-100 w-sm-auto mb-3 mb-sm-0">Hủy & Trở lại</a>
                        
                        <form action="{{ route('admin.sales_orders.complete_picking', $order->id) }}" method="POST" class="w-100 w-sm-auto m-0">
                            @csrf
                            <button type="submit" class="btn btn-primary fw-bold w-100 w-sm-auto shadow-sm" onclick="return confirm('Xác nhận bạn đã lấy đủ hàng và đem ra khu vực đóng gói?');">
                                <i class="ki-duotone ki-check-square fs-2"><span class="path1"></span><span class="path2"></span></i> Xác Nhận Lấy Xong
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
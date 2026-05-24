@extends('layouts.master')
@section('title', 'Chi tiết Kiểm Kê - ' . $audit->audit_code)

@section('content')
    
    @if(session('success'))
        <div class="alert alert-dismissible bg-light-success border border-success d-flex flex-column flex-sm-row p-5 mb-10">
            <i class="ki-duotone ki-check-circle fs-2hx text-success me-4 mb-5 mb-sm-0"><span class="path1"></span><span class="path2"></span></i>
            <div class="d-flex flex-column pe-0 pe-sm-10">
                <h4 class="mb-1 text-success">Thành công</h4>
                <span class="text-success">{{ session('success') }}</span>
            </div>
            <button type="button" class="position-absolute position-sm-relative m-2 m-sm-0 top-0 end-0 btn btn-icon ms-sm-auto" data-bs-dismiss="alert">
                <i class="ki-duotone ki-cross fs-1 text-success"><span class="path1"></span><span class="path2"></span></i>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-dismissible bg-light-danger border border-danger d-flex flex-column flex-sm-row p-5 mb-10">
            <i class="ki-duotone ki-shield-cross fs-2hx text-danger me-4 mb-5 mb-sm-0"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
            <div class="d-flex flex-column pe-0 pe-sm-10">
                <h4 class="mb-1 text-danger">Có lỗi xảy ra</h4>
                <span class="text-danger">{{ session('error') }}</span>
            </div>
            <button type="button" class="position-absolute position-sm-relative m-2 m-sm-0 top-0 end-0 btn btn-icon ms-sm-auto" data-bs-dismiss="alert">
                <i class="ki-duotone ki-cross fs-1 text-danger"><span class="path1"></span><span class="path2"></span></i>
            </button>
        </div>
    @endif

    <div class="card card-flush border-0 shadow-sm">
        
        <div class="card-header align-items-center py-5 gap-2 gap-md-5 flex-wrap">
            <h3 class="card-title align-items-start flex-column m-0">
                <span class="card-label fw-bold fs-3 mb-1 text-gray-800">Chi Tiết Phiếu Đếm Kho: {{ $audit->audit_code }}</span>                
            </h3>
            <div class="card-toolbar flex-row-fluid justify-content-end gap-3 w-100 w-md-auto">
                <a href="{{ route('admin.audits.index') }}" class="btn btn-light fw-bold w-100 w-md-auto">Trở lại</a>
                
                @if($audit->status == 'completed')
                    <span class="badge badge-light-success fs-6 fw-bold px-4 py-2 w-100 w-md-auto text-center">Đã Chốt Sổ</span>
                @else
                    <span class="badge badge-light-warning text-dark fs-6 fw-bold px-4 py-2 w-100 w-md-auto text-center">Đang Đếm</span>
                @endif
            </div>
        </div>

        <div class="card-body pt-5">
            
            <form id="form_audit_update" action="{{ route('admin.audits.update', $audit->id) }}" method="POST">
                @csrf
                <div class="table-responsive">
                    
                    <table class="table align-middle table-row-dashed table-row-gray-200 fs-6 gy-5 text-nowrap">
                        <thead>
                            <tr class="text-start text-gray-500 fw-bolder fs-7 text-uppercase gs-0 border-bottom border-gray-300">
                                <th class="ps-0 min-w-200px">Sản phẩm</th>
                                <th class="min-w-150px">Vị trí kệ</th>
                                <th class="text-center min-w-125px">Tồn Hệ Thống</th>
                                <th class="text-center min-w-150px">Thực Tế Đếm</th>
                                <th class="text-end pe-0 min-w-125px">Chênh Lệch</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-700 fw-semibold">
                            @foreach($audit->items as $item)
                                @php
                                    // Logic tính chênh lệch tự động
                                    $hasCounted = $item->actual_quantity !== null;
                                    $diff = $hasCounted ? ($item->actual_quantity - $item->system_quantity) : 0;
                                @endphp
                                <tr>
                                    
                                    <td class="ps-0">
                                        <div class="d-flex flex-column">
                                            <span class="text-gray-800 fw-bold fs-6 mb-1">{{ $item->inventory->product->name ?? 'N/A' }}</span>
                                            <span class="text-muted fs-8">SKU: <span class="fw-bold">{{ $item->inventory->product->sku ?? 'N/A' }}</span></span>
                                        </div>
                                    </td>
                                    
                                    
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="badge badge-light-primary fw-bold px-2 py-1 mb-1 w-fit-content fs-8">Kệ: {{ $item->inventory->binLocation->code ?? 'N/A' }}</span>
                                            <span class="text-muted fs-8">Khu vực: {{ $item->inventory->binLocation->zone->code ?? '' }}</span>
                                        </div>
                                    </td>
                                    
                                    
                                    <td class="text-center">
                                        <span class="fw-bolder text-gray-600 fs-5">{{ $item->system_quantity }}</span>
                                    </td>
                                    
                                    
                                    <td class="text-center">
                                        @if($audit->status == 'pending')
                                            
                                            <input type="number" name="actual_quantity[{{ $item->id }}]"
                                                value="{{ $item->actual_quantity }}" 
                                                class="form-control form-control-solid text-center fw-bolder text-primary w-100px mx-auto" 
                                                placeholder="0" min="0">
                                        @else
                                            <span class="fw-bolder fs-4 {{ $item->actual_quantity == $item->system_quantity ? 'text-success' : 'text-danger' }}">
                                                {{ $item->actual_quantity ?? 'Không đếm' }}
                                            </span>
                                        @endif
                                    </td>

                                    
                                    <td class="text-end pe-0">
                                        @if(!$hasCounted && $audit->status == 'pending')
                                            <span class="text-muted fs-8 fst-italic">Chưa nhập</span>
                                        @elseif($diff == 0)
                                            <span class="badge badge-light-success fs-8 fw-bold px-3 py-2"><i class="ki-duotone ki-check fs-6 text-success me-1"></i> Khớp</span>
                                        @elseif($diff > 0)
                                            <span class="badge badge-light-info fs-8 fw-bold px-3 py-2">Thừa (+{{ $diff }})</span>
                                        @else
                                            <span class="badge badge-light-danger fs-8 fw-bold px-3 py-2">Thiếu ({{ $diff }})</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </form>

            
            <div class="mt-10 border-top border-gray-200 pt-7">
                @if($audit->status == 'pending')
                    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center gap-4">
                        
                        
                        <div class="w-100 w-sm-auto text-start">
                            <button type="submit" form="form_audit_update" class="btn btn-primary fw-bold w-100 w-sm-auto">
                               <span class="path1"></span><span class="path2"></span>Lưu Nháp 
                            </button>
                            <div class="text-muted fs-8 mt-2 d-none d-sm-block">Có thể lưu nhiều lần trước khi Chốt.</div>
                        </div>

                        
                        <form action="{{ route('admin.audits.complete', $audit->id) }}" method="POST" class="w-100 w-sm-auto m-0">
                            @csrf
                            <button type="submit" class="btn btn-danger fw-bold w-100 w-sm-auto shadow-sm"
                                onclick="return confirm('CẢNH BÁO MẠNH: Xác nhận chốt sổ? Tồn kho trên hệ thống sẽ bị GHI ĐÈ bằng số bạn đếm thực tế và KHÔNG THỂ HOÀN TÁC!');">
                                <i class="ki-duotone ki-check-square fs-2"><span class="path1"></span><span class="path2"></span></i> CHỐT SỔ KHỚP KHO
                            </button>
                        </form>

                    </div>
                @else
                    
                    <div class="alert bg-light-success border border-success d-flex align-items-center p-5 m-0">
                        <i class="ki-duotone ki-shield-tick fs-2hx text-success me-4"><span class="path1"></span><span class="path2"></span></i>
                        <div class="d-flex flex-column">
                            <h4 class="mb-1 text-success">Đã đối soát hoàn tất</h4>
                            <span class="text-success">Phiếu kiểm kê này đã được chốt sổ. Tồn kho hệ thống đã được cập nhật chuẩn xác.</span>
                        </div>
                    </div>
                @endif
            </div>

        </div>
    </div>
@endsection
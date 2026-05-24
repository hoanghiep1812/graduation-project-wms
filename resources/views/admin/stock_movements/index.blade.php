@extends('layouts.master')

@section('title', 'Lịch sử thẻ Kho')

@section('content')
    <div class="card card-flush border-0 shadow-sm">
        
        
        <div class="card-header align-items-center py-5 gap-2 gap-md-5 flex-wrap border-bottom border-gray-200">
            <div class="card-title flex-column align-items-start m-0 w-100 w-md-auto">
                <span class="card-label fw-bold fs-3 mb-1 text-gray-800">Thẻ Kho (Biến Động Tồn)</span>
                <span class="text-muted mt-1 fw-semibold fs-7">Theo dõi mọi giao dịch nhập, xuất, dời kệ và kiểm kê</span>
            </div>
            
            <div class="card-toolbar flex-row-fluid justify-content-end gap-5 w-100 w-md-auto">
                <a href="{{ route('admin.stock_movements.export', request()->all()) }}" class="btn btn-light-success fw-bold w-100 w-md-auto shadow-sm">
                    <i class="ki-duotone ki-document fs-2"><span class="path1"></span><span class="path2"></span></i> Xuất báo cáo
                </a>
            </div>
        </div>

        <div class="card-body pt-5">
            
            
            <div class="bg-light rounded p-4 mb-7 border border-gray-300 border-dashed">
                <form method="GET" action="{{ route('admin.stock_movements.index') }}">
                    <div class="row g-3 align-items-center">
                        
                        
                        <div class="col-6 col-md-auto">
                            <div class="input-group input-group-solid input-group-sm">
                                <span class="input-group-text text-muted border-end-0"><i class="ki-duotone ki-calendar-8 fs-6"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span><span class="path6"></span></i></span>
                                <input type="date" name="from_date" value="{{ request('from_date') }}" class="form-control form-control-solid border-start-0 ps-0" title="Từ ngày" />
                            </div>
                        </div>

                        
                        <div class="col-6 col-md-auto">
                            <div class="input-group input-group-solid input-group-sm">
                                <span class="input-group-text text-muted border-end-0"><i class="ki-duotone ki-calendar-8 fs-6"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span><span class="path6"></span></i></span>
                                <input type="date" name="to_date" value="{{ request('to_date') }}" class="form-control form-control-solid border-start-0 ps-0" title="Đến ngày" />
                            </div>
                        </div>

                        
                        <div class="col-12 col-md-auto">
                            <select name="transaction_type" class="form-select form-select-sm form-select-solid" data-control="select2" data-hide-search="true">
                                <option value="">-- Tất cả Giao dịch --</option>
                                <option value="inbound" {{ request('transaction_type') == 'inbound' ? 'selected' : '' }}>Nhập kho (In)</option>
                                <option value="outbound" {{ request('transaction_type') == 'outbound' ? 'selected' : '' }}>Xuất kho (Out)</option>
                                <option value="transfer" {{ request('transaction_type') == 'transfer' ? 'selected' : '' }}>Dời kệ (Move)</option>
                                <option value="adjustment" {{ request('transaction_type') == 'adjustment' ? 'selected' : '' }}>Kiểm kê (Adjust)</option>
                            </select>
                        </div>

                        
                        <div class="col-12 col-md-auto flex-grow-1">
                            <div class="position-relative">
                                <i class="ki-duotone ki-magnifier fs-4 position-absolute ms-3 top-50 translate-middle-y text-gray-500"><span class="path1"></span><span class="path2"></span></i>
                                <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm form-control-solid ps-10" placeholder="Tìm tên SP, SKU, Vị trí..." />
                            </div>
                        </div>

                        
                        <div class="col-12 col-md-auto text-end">
                            <a href="{{ route('admin.stock_movements.index') }}" class="btn btn-sm btn-light fw-bold me-2">Xóa lọc</a>
                            <button type="submit" class="btn btn-sm btn-primary fw-bold"><i class="ki-duotone ki-filter fs-5"></i> Lọc dữ liệu</button>
                        </div>
                    </div>
                </form>
            </div>

            
            <div class="table-responsive">
                <table class="table align-middle table-row-dashed fs-6 gy-4 text-nowrap border-bottom">
                    <thead>
                        <tr class="text-start text-gray-500 fw-bolder fs-8 text-uppercase gs-0 border-bottom border-gray-300">
                            <th class="ps-0 min-w-100px">Thời gian</th>
                            <th class="min-w-200px">Sản Phẩm & Vị Trí</th>
                            <th class="text-center min-w-125px">Loại GD</th>
                            <th class="text-end min-w-100px">Biến Động</th>
                            <th class="text-end min-w-100px">Tồn Cuối</th>
                            <th class="text-end pe-0 min-w-150px">Tham Chiếu (Ref)</th>
                        </tr>
                    </thead>
                    <tbody class="fw-semibold text-gray-600">
                        @forelse($movements as $movement)
                            @php
                                $inventory = $movement->inventory;
                                $product = $inventory->product ?? null;
                                $bin = $inventory->binLocation ?? null;
                                $zoneCode = $bin && $bin->zone ? $bin->zone->code : 'N/A';

                                $isPositive = $movement->quantity_change > 0;
                                $colorClass = $isPositive ? 'success' : 'danger';
                                $sign = $isPositive ? '+' : '';
                                
                                // Dịch Tên Model thành Tiếng Việt thân thiện
                                $refName = 'Hệ thống';
                                if($movement->reference_type) {
                                    $modelClass = class_basename($movement->reference_type);
                                    $refName = match($modelClass) {
                                        'PurchaseOrder' => 'Phiếu Nhập',
                                        'SalesOrder' => 'Phiếu Xuất',
                                        'Audit' => 'Phiếu Kiểm Kê',
                                        'Reslotting' => 'Dời Kệ AI',
                                        default => $modelClass
                                    };
                                }
                            @endphp
                            <tr>
                                <td class="ps-0 align-top pt-5">
                                    <span class="text-gray-800 fw-bold d-block fs-7">{{ $movement->created_at->format('d/m/Y') }}</span>
                                    <span class="text-muted fw-semibold d-block fs-8">{{ $movement->created_at->format('H:i:s') }}</span>
                                </td>

                                <td class="align-top pt-5">
                                    <div class="d-flex flex-column">
                                        <span class="text-gray-800 fw-bold mb-1 fs-6">
                                            {{ $product ? $product->name : 'Sản phẩm đã xóa' }}
                                        </span>
                                        <div class="text-muted fs-8 mb-1">
                                            SKU: <span class="fw-bold">{{ $product ? $product->sku : 'N/A' }}</span>
                                        </div>
                                        <div class="text-muted fs-8">
                                            Vị trí: <span class="badge badge-light-secondary fs-8 px-2 py-1 text-dark">{{ $zoneCode }} - {{ $bin ? $bin->code : 'N/A' }}</span>
                                        </div>
                                    </div>
                                </td>

                                <td class="text-center align-top pt-5">
                                    @if($movement->transaction_type == 'inbound')
                                        <span class="badge badge-light-success fs-8 fw-bold px-3 py-2">Nhập Kho</span>
                                    @elseif($movement->transaction_type == 'outbound')
                                        <span class="badge badge-light-danger fs-8 fw-bold px-3 py-2">Xuất Kho</span>
                                    @elseif($movement->transaction_type == 'transfer')
                                        <span class="badge badge-light-info fs-8 fw-bold px-3 py-2">Dời Kệ</span>
                                    @elseif($movement->transaction_type == 'adjustment')
                                        <span class="badge badge-light-warning text-dark fs-8 fw-bold px-3 py-2">Kiểm Kê</span>
                                    @else
                                        <span class="badge badge-light-secondary fs-8 fw-bold px-3 py-2">{{ strtoupper($movement->transaction_type) }}</span>
                                    @endif
                                </td>

                                <td class="text-end align-top pt-5">
                                    <span class="badge badge-light-{{ $colorClass }} text-{{ $colorClass }} fs-5 fw-bolder px-3 py-1">
                                        {{ $sign }}{{ $movement->quantity_change }}
                                    </span>
                                </td>

                                <td class="text-end align-top pt-5">
                                    <span class="text-gray-800 fw-bolder fs-5">{{ $movement->balance_after }}</span>
                                </td>

                                <td class="text-end pe-0 align-top pt-5">
                                    @if($movement->reference_type && $movement->reference_id)
                                        <span class="text-primary fw-bold d-block fs-7">{{ $refName }}</span>
                                        <span class="text-muted fs-8">#ID: {{ $movement->reference_id }}</span>
                                    @else
                                        <span class="text-muted fs-8 fst-italic">Hệ thống</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-10">
                                    <div class="d-flex flex-column align-items-center">
                                        <i class="ki-duotone ki-time fs-4x text-gray-400 mb-3"><span class="path1"></span><span class="path2"></span></i>
                                        <span class="text-muted fs-5 fw-semibold">Chưa có giao dịch nào được ghi nhận.</span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            
            <div class="d-flex flex-stack flex-wrap mt-5">
                <div class="fs-6 fw-semibold text-gray-500 mb-2 mb-md-0">
                    Hiển thị từ {{ $movements->firstItem() ?? 0 }} đến {{ $movements->lastItem() ?? 0 }} trên tổng số {{ $movements->total() ?? 0 }}
                </div>
                <div>
                    {{ $movements->appends(request()->query())->links('pagination::bootstrap-5') }}
                </div>
            </div>

        </div>
    </div>
@endsection
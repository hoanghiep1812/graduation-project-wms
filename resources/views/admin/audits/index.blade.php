@extends('layouts.master')

@section('title', 'Quản Lý Kiểm Kê Kho ')

@section('content')

    
    @if(session('success'))
        <div class="alert alert-dismissible bg-light-success border border-success d-flex flex-column flex-sm-row p-5 mb-10">
            <i class="ki-duotone ki-check-circle fs-2hx text-success me-4 mb-5 mb-sm-0"><span class="path1"></span><span
                    class="path2"></span></i>
            <div class="d-flex flex-column pe-0 pe-sm-10">
                <h4 class="mb-1 text-success">Thành công</h4>
                <span class="text-success">{{ session('success') }}</span>
            </div>
            <button type="button" class="position-absolute position-sm-relative m-2 m-sm-0 top-0 end-0 btn btn-icon ms-sm-auto"
                data-bs-dismiss="alert">
                <i class="ki-duotone ki-cross fs-1 text-success"><span class="path1"></span><span class="path2"></span></i>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-dismissible bg-light-danger border border-danger d-flex flex-column flex-sm-row p-5 mb-10">
            <i class="ki-duotone ki-shield-cross fs-2hx text-danger me-4 mb-5 mb-sm-0"><span class="path1"></span><span
                    class="path2"></span><span class="path3"></span></i>
            <div class="d-flex flex-column pe-0 pe-sm-10">
                <h4 class="mb-1 text-danger">Có lỗi xảy ra</h4>
                <span class="text-danger">{{ session('error') }}</span>
            </div>
            <button type="button" class="position-absolute position-sm-relative m-2 m-sm-0 top-0 end-0 btn btn-icon ms-sm-auto"
                data-bs-dismiss="alert">
                <i class="ki-duotone ki-cross fs-1 text-danger"><span class="path1"></span><span class="path2"></span></i>
            </button>
        </div>
    @endif

    <div class="card card-flush shadow-sm border-0">
        
        <div class="card-header align-items-center py-5 gap-2 gap-md-5 flex-wrap">
            <div class="card-title w-100 w-md-auto m-0">
                <h3 class="card-title align-items-start flex-column m-0">
                    <span class="card-label fw-bold fs-3 mb-1 text-gray-800">Danh Sách Phiếu Kiểm Kê</span>
                    <span class="text-muted mt-1 fw-semibold fs-7">Quản lý và đối soát tồn kho định kỳ</span>
                </h3>
            </div>
            
            <div class="card-toolbar flex-row-fluid justify-content-end gap-5 w-100 w-md-auto">
                <form action="{{ route('admin.audits.store') }}" method="POST" class="w-100 w-md-auto m-0">
                    @csrf
                    <button type="submit" class="btn btn-primary fw-bold w-100 shadow-sm"
                        onclick="return confirm('Hệ thống sẽ lấy toàn bộ số liệu tồn kho HIỆN TẠI (Snapshot) để đem đi đối soát. Xác nhận tạo đợt kiểm kê?');">
                        <i class="ki-duotone ki-plus fs-2"></i> Tạo Phiếu Kiểm Kê
                    </button>
                </form>
            </div>
        </div>

        <div class="card-body pt-0">
            <div class="table-responsive">
                
                <table class="table align-middle table-row-dashed table-row-gray-200 fs-6 gy-5 border-bottom text-nowrap">
                    <thead>
                        <tr
                            class="text-start text-gray-500 fw-bolder fs-7 text-uppercase gs-0 border-bottom border-gray-300">
                            <th class="ps-0 min-w-150px">Mã Phiếu</th>
                            <th class="min-w-150px">Người Tạo</th>
                            <th class="min-w-150px">Ngày Lập</th>
                            <th class="text-center min-w-150px">Trạng Thái</th>
                            <th class="text-end min-w-150px pe-0">Hành Động</th>
                        </tr>
                    </thead>
                    <tbody class="fw-semibold text-gray-600">
                        @forelse($audits as $audit)
                            <tr>
                                
                                <td class="ps-0">
                                    <a href="{{ route('admin.audits.show', $audit->id) }}"
                                        class="text-gray-800 text-hover-primary fw-bolder fs-6 d-block mb-1">
                                        {{ $audit->audit_code }}
                                    </a>
                                </td>

                                <td>
                                    <span class="text-gray-700 fw-bold">{{ $audit->creator->name ?? 'Hệ thống' }}</span>
                                </td>

                                <td class="text-muted">{{ $audit->created_at->format('d/m/Y H:i') }}</td>

                                
                                <td class="text-center">
                                    @if($audit->status == 'completed')
                                        <span class="badge badge-light-success fw-bold px-3 py-2 fs-8">Đã Chốt Sổ</span>
                                    @else
                                        <span class="badge badge-light-warning text-dark fw-bold px-3 py-2 fs-8">Đang Đếm</span>
                                    @endif
                                </td>

                                
                                <td class="text-end pe-0">
                                    @if($audit->status == 'completed')
                                        <a href="{{ route('admin.audits.show', $audit->id) }}"
                                            class="btn btn-sm btn-light btn-active-light-info fw-bold">
                                            <i class="ki-duotone ki-eye fs-5"><span class="path1"></span><span
                                                    class="path2"></span><span class="path3"></span></i> Xem kết quả
                                        </a>
                                    @else
                                        <a href="{{ route('admin.audits.show', $audit->id) }}"
                                            class="btn btn-sm btn-light-primary btn-active-primary fw-bold">
                                            <i class="ki-duotone ki-pencil fs-5"><span class="path1"></span><span
                                                    class="path2"></span></i> Nhập Số
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            
                            <tr>
                                <td colspan="5" class="text-center py-10">
                                    <div class="d-flex flex-column align-items-center">
                                        <i class="ki-duotone ki-folder-empty fs-5x text-gray-400 mb-3"><span
                                                class="path1"></span><span class="path2"></span><span class="path3"></span><span
                                                class="path4"></span></i>
                                        <span class="text-muted fs-5 fw-semibold">Chưa có đợt kiểm kê nào. Hãy bấm "Tạo Phiếu
                                            Kiểm Kê" để bắt đầu!</span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex flex-stack flex-wrap mt-5">
                <div class="fs-6 fw-semibold text-gray-500 mb-2 mb-md-0">
                    Hiển thị từ {{ $audits->firstItem() ?? 0 }} đến {{ $audits->lastItem() ?? 0 }} trên tổng số
                    {{ $audits->total() ?? 0 }}
                </div>
                <div>
                    {{ $audits->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
@endsection
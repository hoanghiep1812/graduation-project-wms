@extends('layouts.master')

@section('title', 'Báo Cáo Nhập - Xuất - Tồn')

@section('content')
    <div class="card card-flush border-0 shadow-sm">
        
        <div class="card-header align-items-center py-5 gap-2 gap-md-5 flex-wrap border-bottom border-gray-200">
            <div class="card-title flex-column align-items-start m-0 w-100 w-md-auto">
                <span class="card-label fw-bold fs-3 mb-1 text-gray-800">Báo Cáo Tổng Hợp Nhập - Xuất - Tồn</span>
                <span class="text-muted mt-1 fw-semibold fs-7">Số liệu tổng hợp theo khoảng thời gian</span>
            </div>
            
            <div class="card-toolbar flex-row-fluid justify-content-end gap-5 w-100 w-md-auto">
                <a href="{{ route('admin.reports.inventory.export', request()->all()) }}" class="btn btn-light-success fw-bold shadow-sm">
                    <i class="ki-duotone ki-file-down fs-2"><span class="path1"></span><span class="path2"></span></i> Xuất File Excel N-X-T
                </a>
            </div>
        </div>

        <div class="card-body pt-5">
            <div class="bg-light rounded p-4 mb-7 border border-gray-300 border-dashed">
                <form method="GET" action="{{ route('admin.reports.inventory') }}">
                    <div class="d-flex flex-column flex-md-row align-items-md-center gap-3">
                        
                        <div class="fw-bold text-gray-700 text-nowrap">Kỳ báo cáo:</div>
                        
                        <div class="w-100 w-md-200px">
                            <div class="input-group input-group-solid input-group-sm cursor-pointer" id="kt_datepicker_start_wrapper">
                                <span class="input-group-text text-muted border-end-0"><i class="ki-duotone ki-calendar-8 fs-6"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span><span class="path6"></span></i></span>
                                <input type="text" id="kt_datepicker_start" name="start_date" value="{{ $startDate }}" class="form-control form-control-solid border-start-0 ps-0 cursor-pointer" placeholder="Từ ngày (dd/mm/yyyy)" readonly required/>
                            </div>
                        </div>

                        <div class="fw-bold text-gray-500 text-center d-none d-md-block">-</div>

                        <div class="w-100 w-md-200px">
                            <div class="input-group input-group-solid input-group-sm cursor-pointer" id="kt_datepicker_end_wrapper">
                                <span class="input-group-text text-muted border-end-0"><i class="ki-duotone ki-calendar-8 fs-6"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span><span class="path6"></span></i></span>
                                <input type="text" id="kt_datepicker_end" name="end_date" value="{{ $endDate }}" class="form-control form-control-solid border-start-0 ps-0 cursor-pointer" placeholder="Đến ngày (dd/mm/yyyy)" readonly required/>
                            </div>
                        </div>

                        <div class="mt-3 mt-md-0 ms-md-auto text-end">
                            <button type="button"
                                    id="btn-clear-date"
                                    class="btn btn-sm btn-light fw-bold">
                                Xóa lọc
                            </button>
                            <button type="submit" class="btn btn-sm btn-primary fw-bold w-100 w-md-auto"><i class="ki-duotone ki-filter fs-5"></i> Xem Báo Cáo</button>
                        </div>
                    </div>
                </form>
            </div>

            <div class="table-responsive">
                <table class="table align-middle table-row-dashed fs-6 gy-4 text-nowrap border-bottom">
                    <thead>
                        <tr class="text-start text-gray-500 fw-bolder fs-7 text-uppercase gs-0 bg-light">
                            <th class="ps-3 min-w-100px rounded-start">Mã SKU</th>
                            <th class="min-w-200px">Tên Sản Phẩm</th>
                            <th class="text-center min-w-100px text-warning">Tồn Đầu Kỳ</th>
                            <th class="text-center min-w-100px text-success">Nhập Trong Kỳ</th>
                            <th class="text-center min-w-100px text-danger">Xuất Trong Kỳ</th>
                            <th class="text-center min-w-100px text-primary rounded-end">Tồn Cuối Kỳ</th>
                        </tr>
                    </thead>
                    <tbody class="fw-semibold text-gray-600">
                        @forelse($reportData as $row)
                            <tr>
                                <td class="ps-3 fw-bold text-gray-800">{{ $row['sku'] }}</td>
                                <td>{{ $row['name'] }}</td>
                                <td class="text-center fw-bolder text-warning fs-5">{{ $row['ton_dau'] }}</td>
                                <td class="text-center fw-bolder text-success fs-5">+{{ $row['nhap'] }}</td>
                                <td class="text-center fw-bolder text-danger fs-5">-{{ $row['xuat'] }}</td>
                                <td class="text-center fw-bolder text-primary fs-4">{{ $row['ton_cuoi'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-10">Không có dữ liệu cho khoảng thời gian này.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function () {    
        const flatpickrConfig = {
            altInput: true,
            altFormat: "d/m/Y",
            dateFormat: "Y-m-d",
            allowInput: false, 
            locale: {
                firstDayOfWeek: 1, 
                weekdays: {
                    shorthand: ["CN", "T2", "T3", "T4", "T5", "T6", "T7"],
                    longhand: ["Chủ nhật", "Thứ hai", "Thứ ba", "Thứ tư", "Thứ năm", "Thứ sáu", "Thứ bảy"]
                },
                months: {
                    shorthand: ["Th1", "Th2", "Th3", "Th4", "Th5", "Th6", "Th7", "Th8", "Th9", "Th10", "Th11", "Th12"],
                    longhand: ["Tháng 1", "Tháng 2", "Tháng 3", "Tháng 4", "Tháng 5", "Tháng 6", "Tháng 7", "Tháng 8", "Tháng 9", "Tháng 10", "Tháng 11", "Tháng 12"]
                }
            }
        };
        
        const startPicker = flatpickr("#kt_datepicker_start", flatpickrConfig);
        const endPicker = flatpickr("#kt_datepicker_end", flatpickrConfig);
                
        document.getElementById('kt_datepicker_start_wrapper').addEventListener('click', function() {
            startPicker.open();
        });
        
        document.getElementById('kt_datepicker_end_wrapper').addEventListener('click', function() {
            endPicker.open();
        });

        document.getElementById('btn-clear-date').addEventListener('click', function () {

            startPicker.clear();
            endPicker.clear();

            document.getElementById('kt_datepicker_start').value = '';
            document.getElementById('kt_datepicker_end').value = '';
        });
    });
</script>
@endpush
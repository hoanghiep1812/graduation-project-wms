@extends('layouts.master')

@section('title', 'Thực thi Cất hàng - ' . $order->po_number)

@section('content')
    
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

    <div class="row g-5 g-xl-8">        
        <div class="col-xl-3">
            <div class="card border-0 shadow-sm mb-xl-8">
                <div class="card-header border-0 pt-5">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bold fs-4 mb-1 text-gray-800">Thông Tin Lệnh</span>
                    </h3>
                </div>
                <div class="card-body pt-3 pb-8">                    
                    <div class="d-flex flex-column mb-5">
                        <span class="text-muted fw-semibold fs-7 text-uppercase">Mã Phiếu</span>
                        <span class="text-gray-900 fw-bolder fs-3">{{ $order->po_number }}</span>
                    </div>
                                    
                    <div class="d-flex flex-column mb-5">
                        <span class="text-muted fw-semibold fs-7 text-uppercase">Nhà cung cấp</span>
                        <span class="text-gray-800 fw-bold fs-5">{{ $order->supplier_name }}</span>
                    </div>
                    
                    <div class="d-flex flex-column">
                        <span class="text-muted fw-semibold fs-7 text-uppercase">Ngày cập bến</span>
                        <span class="text-gray-800 fw-bold fs-6">
                            {{ $order->expected_date ? \Carbon\Carbon::parse($order->expected_date)->format('d/m/Y') : now()->format('d/m/Y') }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-9">
            <div class="card border-0 shadow-sm mb-5 mb-xl-8">
                <div class="card-header border-0 pt-5">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bold fs-4 mb-1 text-gray-800">Chi Tiết Cất Hàng</span>                        
                    </h3>
                </div>

                <div class="card-body pt-3">
                    <form action="{{ route('admin.inbound.complete_putaway', $order->id) }}" method="POST">
                        @csrf
                        <div class="table-responsive">                            
                            <table class="table align-middle table-row-dashed table-row-gray-200 fs-6 gy-5">
                                <thead>
                                    <tr class="text-start text-gray-500 fw-bolder fs-7 text-uppercase gs-0 border-bottom border-gray-300">
                                        <th class="ps-0 min-w-200px">Sản phẩm</th>
                                        <th class="text-center min-w-100px">SL Cất</th>
                                        <th class="min-w-300px">Vị trí & Dữ liệu Lô</th>
                                        <th class="text-end pe-0 min-w-100px">Trạng thái</th>
                                    </tr>
                                </thead>
                                <tbody class="text-gray-700 fw-semibold">
                                    @forelse($putawayTasks as $task)
                                        <tr>                                            
                                            <td class="ps-0 align-top pt-6">
                                                <div class="d-flex flex-column">
                                                    <span class="text-gray-800 fw-bold fs-6 mb-1">{{ $task['product_name'] }}</span>
                                                    <span class="text-muted fw-semibold fs-7">SKU: {{ $task['sku'] }}</span>
                                                </div>
                                            </td>
                                                                                        
                                            <td class="text-center align-top pt-6">
                                                <span class="badge badge-light-primary fs-6 fw-bold px-3 py-2">{{ $task['quantity'] }}</span>
                                            </td>
                                            
                                            
                                            <td class="align-top pt-6">
                                                
                                                <div class="mb-3">
                                                    @if($task['category'] == 'NEW_PRODUCT')
                                                        <span class="badge badge-light-info fs-8 px-2 py-1">SP Mới (Tự chọn kệ)</span>
                                                    @elseif($task['is_consolidation'])
                                                        <span class="badge badge-light-success fs-8 px-2 py-1">Ưu tiên Gom hàng</span>
                                                    @elseif($task['category'] == 'FAST_MOVING')
                                                        <span class="badge badge-light-primary fs-8 px-2 py-1">Xuất Chạy (A)</span>
                                                    @elseif($task['category'] == 'MEDIUM_MOVING')
                                                        <span class="badge badge-light-warning fs-8 px-2 py-1">Trung Bình (B)</span>
                                                    @else
                                                        <span class="badge badge-light-secondary fs-8 px-2 py-1 text-dark">Xuất Chậm (C)</span>
                                                    @endif
                                                </div>
                                                                                                
                                                <select name="placements[{{ $task['task_id'] }}][bin_location_id]" 
                                                        class="form-select form-select-sm form-select-solid border-gray-300 bin-select mb-2" 
                                                        data-control="select2" 
                                                        data-row-qty="{{ $task['quantity'] }}" 
                                                        required>
                                                    @if($task['category'] == 'NEW_PRODUCT')
                                                        <option value="">-- Chọn vị trí kệ --</option>
                                                    @endif

                                                    @php
                                                        $groupedBins = $allAvailableBins->groupBy(function($b) {
                                                            return $b->zone->name ?? $b->zone->code ?? 'Zone Khác';
                                                        });
                                                    @endphp

                                                    @foreach($groupedBins as $zoneName => $binsInZone)
                                                        <optgroup label="Khu vực: {{ $zoneName }}">
                                                            @foreach($binsInZone as $bin)
                                                                @php
                                                                    $availableSpace = $bin->max_capacity - $bin->current_capacity;
                                                                    $isDisabled = $availableSpace < $task['quantity']; 
                                                                    $isEmpty = $bin->current_capacity == 0;
                                                                    $statusText = $isEmpty ? 'Trống hoàn toàn' : "Trống: $availableSpace";
                                                                @endphp

                                                                <option value="{{ $bin->id }}" 
                                                                    data-original-capacity="{{ $availableSpace }}"
                                                                    {{ ($bin->id == $task['bin_location_id'] && $task['category'] != 'NEW_PRODUCT') ? 'selected' : '' }}
                                                                    {{ $isDisabled ? 'disabled' : '' }}>
                                                                    Kệ {{ $bin->code }}  [{{ $statusText }}]
                                                                </option>
                                                            @endforeach
                                                        </optgroup>
                                                    @endforeach
                                                </select>      

                                                
                                                <div class="mb-4">
                                                    <span class="text-muted fs-8">
                                                        <i class="ki-duotone ki-information-5 fs-8 me-1"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i> 
                                                        {{ $task['reason'] }}
                                                    </span>
                                                </div>
                                                
                                                @if($task['has_expiry'])
                                                <div class="bg-light border border-gray-300 border-dashed rounded p-4 expiry-wrapper" data-duration="{{ $task['expiry_duration'] }}">
                                                    <label class="fs-8 fw-bold text-gray-700 mb-3">Dữ liệu Hạn sử dụng (Lô) <span class="text-danger">*</span></label>
                                                    
                                                    <div class="d-flex gap-5 mb-3">
                                                        <div class="form-check form-check-custom form-check-solid form-check-sm">
                                                            <input class="form-check-input radio-expiry-method" type="radio" name="placements[{{ $task['task_id'] }}][expiry_method]" value="manual" id="method_manual_{{ $task['task_id'] }}" checked />
                                                            <label class="form-check-label text-dark fw-semibold fs-8" for="method_manual_{{ $task['task_id'] }}">
                                                                Nhập HSD
                                                            </label>
                                                        </div>
                                                        <div class="form-check form-check-custom form-check-solid form-check-sm">
                                                            <input class="form-check-input radio-expiry-method" type="radio" name="placements[{{ $task['task_id'] }}][expiry_method]" value="auto" id="method_auto_{{ $task['task_id'] }}" />
                                                            <label class="form-check-label text-dark fw-semibold fs-8" for="method_auto_{{ $task['task_id'] }}">
                                                                NSX (Tự cộng)
                                                            </label>
                                                        </div>
                                                    </div>

                                                    <div class="manual-input-zone">
                                                        <input type="text" class="form-control form-control-sm bg-white date-picker input-hsd-manual border-gray-300" 
                                                            name="placements[{{ $task['task_id'] }}][expiry_date_manual]" 
                                                            placeholder="Chọn ngày hết hạn" required />
                                                    </div>

                                                    <div class="auto-input-zone" style="display: none;">
                                                        <input type="text" class="form-control form-control-sm bg-white date-picker input-nsx border-gray-300 mb-2" 
                                                            name="placements[{{ $task['task_id'] }}][manufactured_date]" 
                                                            placeholder="Chọn ngày sản xuất" />
                                                        
                                                        <div class="result-display px-3 py-2 bg-light-info rounded border border-info border-dashed w-100" style="display: none !important;">
                                                            <span class="fs-8 text-info fw-bold">HSD tính ra: <span class="calc-result-text">...</span></span>
                                                        </div>
                                                        <input type="hidden" name="placements[{{ $task['task_id'] }}][expiry_date_auto]" class="input-hsd-auto" />
                                                    </div>
                                                </div>
                                                @endif
                                                
                                                <input type="hidden" name="placements[{{ $task['task_id'] }}][product_id]" value="{{ $task['product_id'] ?? $task['item_id'] }}">
                                                <input type="hidden" name="placements[{{ $task['task_id'] }}][item_id]" value="{{ $task['item_id'] }}">
                                                <input type="hidden" name="placements[{{ $task['task_id'] }}][quantity]" value="{{ $task['quantity'] }}">
                                            </td>

                                            <td class="text-end pe-0 align-top pt-6">
                                                <span class="badge badge-light-secondary text-muted fs-8 fw-bold px-3 py-2">Chờ cất</span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center py-10">
                                                <span class="text-muted fs-6 fw-semibold">Đơn hàng rỗng.</span>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    
                        <div class="d-flex justify-content-between align-items-center mt-8 border-top border-gray-200 pt-7">
                            <a href="{{ route('admin.inbound.index') }}" class="btn btn-light fw-bold">Hủy & Quay lại</a>
                            <button type="submit" class="btn btn-primary fw-bold" onclick="return confirm('Bạn xác nhận hàng đã lên kệ an toàn?');">
                                 Hoàn Tất Cất Hàng
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $(".date-picker").flatpickr({
            dateFormat: "Y-m-d", 
            allowInput: true,    
            locale: "vn"         
        });

        $('.expiry-wrapper').each(function() {
            let $wrapper = $(this);
            let durationMonths = parseInt($wrapper.data('duration')) || 0;
            
            let $radioMethods = $wrapper.find('.radio-expiry-method');
            let $manualZone = $wrapper.find('.manual-input-zone');
            let $autoZone = $wrapper.find('.auto-input-zone');
            
            let $manualHsdInput = $wrapper.find('.input-hsd-manual');
            let $nsxInput = $wrapper.find('.input-nsx');
            let $autoHsdInputHidden = $wrapper.find('.input-hsd-auto');
            
            let $resultDisplay = $wrapper.find('.result-display');
            let $resultText = $wrapper.find('.calc-result-text');

            $radioMethods.on('change', function() {
                if ($(this).val() === 'manual') {
                    $manualZone.slideDown(200);
                    $autoZone.slideUp(200);
                    $manualHsdInput.prop('required', true);
                    $nsxInput.prop('required', false);
                    if($nsxInput[0]._flatpickr) $nsxInput[0]._flatpickr.clear();
                    $autoHsdInputHidden.val('');
                    $resultDisplay.hide();
                } else {
                    $manualZone.slideUp(200);
                    $autoZone.slideDown(200);
                    $manualHsdInput.prop('required', false);
                    $nsxInput.prop('required', true);
                    if($manualHsdInput[0]._flatpickr) $manualHsdInput[0]._flatpickr.clear();
                }
            });

            $nsxInput.on('change', function() {
                let nsxVal = $(this).val();
                if (nsxVal && durationMonths > 0) {
                    let date = new Date(nsxVal);
                    date.setMonth(date.getMonth() + durationMonths);
                    let y = date.getFullYear();
                    let m = String(date.getMonth() + 1).padStart(2, '0');
                    let d = String(date.getDate()).padStart(2, '0');
                    
                    let formattedDate = `${y}-${m}-${d}`;
                    let displayDate = `${d}/${m}/${y}`;
                    
                    $autoHsdInputHidden.val(formattedDate);
                    $resultText.text(displayDate);
                    $resultDisplay.hide().fadeIn(300);
                }
            });
        });

        function calculateRealtimeCapacity() {
            let binUsage = {}; 
            $('.bin-select').each(function() {
                let selectedBinId = $(this).val();
                if (selectedBinId) {
                    let qty = parseInt($(this).data('row-qty'));
                    if (!binUsage[selectedBinId]) binUsage[selectedBinId] = 0;
                    binUsage[selectedBinId] += qty; 
                }
            });

            $('.bin-select').each(function() {
                let $currentSelect = $(this);
                let currentVal = $currentSelect.val();
                let rowQty = parseInt($currentSelect.data('row-qty'));

                $currentSelect.find('option').each(function() {
                    let $option = $(this);
                    let binId = $option.val();
                    if (!binId) return;

                    let originalCapacity = parseInt($option.data('original-capacity'));
                    let usageByOtherRows = (binUsage[binId] || 0);
                    if (currentVal == binId) {
                        usageByOtherRows -= rowQty; 
                    }

                    let realtimeRemainingCap = originalCapacity - usageByOtherRows;

                    if (realtimeRemainingCap < rowQty) {
                        $option.prop('disabled', true);
                        if ($option.text().indexOf('(Hết chỗ)') === -1) {
                            $option.text($option.text().replace('', '(Hết chỗ) -'));
                        }
                    } else {
                        $option.prop('disabled', false);
                        $option.text($option.text().replace('(Hết chỗ) -', ''));
                    }
                });
            });
            $('.bin-select').select2();
        }

        $('.bin-select').on('change', function() {
            calculateRealtimeCapacity();
        });

        calculateRealtimeCapacity();
    });
</script>
@endpush
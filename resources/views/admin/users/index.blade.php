@extends('layouts.master')

@section('title', 'Quản Lý Nhân Sự')

@section('content')
    {{-- Hiển thị thông báo kiểu Enterprise --}}
    @if(session('success'))
        <div class="alert alert-success d-flex align-items-center p-4 mb-5 shadow-sm border-0">
            <i class="ki-duotone ki-check-circle fs-2hx text-success me-3"><span class="path1"></span><span class="path2"></span></i>
            <div class="d-flex flex-column">
                <span class="fw-bold fs-6">{{ session('success') }}</span>
            </div>
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger d-flex align-items-center p-4 mb-5 shadow-sm border-0">
            <i class="ki-duotone ki-cross-circle fs-2hx text-danger me-3"><span class="path1"></span><span class="path2"></span></i>
            <div class="d-flex flex-column">
                <span class="fw-bold fs-6 mb-1">Thao tác không thành công</span>
                <ul class="mb-0 fs-7 text-danger">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <div class="row g-5 g-xl-8">        
        {{-- BÊN TRÁI: FORM CẤP TÀI KHOẢN MỚI --}}
        <div class="col-xl-4">
            <div class="card card-flush shadow-sm border-0 h-xl-100">
                <div class="card-header pt-7 border-0">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bold text-gray-900 fs-4 mb-1">Cấp Tài Khoản Mới</span>                        
                        <span class="text-muted mt-1 fw-semibold fs-7">Cấp quyền truy cập hệ thống WMS</span>
                    </h3>
                </div>
                <div class="card-body pt-5">
                    <form action="{{ route('admin.users.store') }}" method="POST">
                        @csrf
                        <div class="mb-6">
                            <label class="form-label required fs-7 fw-semibold text-gray-700 mb-2">Họ và Tên</label>
                            <input type="text" name="name" class="form-control form-control-solid form-control-sm fs-7"
                                placeholder="VD: Nguyễn Văn A" required>
                        </div>

                        {{-- ĐÃ ĐỔI TỪ EMAIL SANG USERNAME/MÃ NV --}}
                        <div class="mb-6">
                            <label class="form-label required fs-7 fw-semibold text-gray-700 mb-2">Tên đăng nhập (Mã NV)</label>
                            <input type="text" name="username" class="form-control form-control-solid form-control-sm fs-7"
                                placeholder="VD: NV001" required>
                        </div>

                        <div class="mb-6">
                            <label class="form-label required fs-7 fw-semibold text-gray-700 mb-2">Mật khẩu</label>
                            <input type="password" name="password"
                                class="form-control form-control-solid form-control-sm fs-7"
                                placeholder="Nhập ít nhất 6 ký tự" required minlength="6">
                        </div>

                        <div class="mb-8">
                            <label class="form-label required fs-7 fw-semibold text-gray-700 mb-2">Vai Trò</label>
                            <select name="role" class="form-select form-select-solid form-select-sm fs-7" required>
                                <option value="staff" selected>Nhân Viên</option>
                                <option value="admin">Quản Trị Viên</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary btn-sm w-100 fw-bold fs-7 hover-elevate-up">
                            <i class="ki-duotone ki-plus fs-3"></i> Cấp Tài Khoản
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- BÊN PHẢI: DANH SÁCH NHÂN SỰ & QUẢN LÝ --}}
        <div class="col-xl-8">
            <div class="card card-flush shadow-sm border-0 h-xl-100">
                <div class="card-header pt-7 border-0">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bold text-gray-900 fs-4 mb-1">Danh Sách Nhân Sự</span>
                        <span class="text-muted mt-1 fw-semibold fs-7">Tra cứu và quản lý bảo mật</span>
                    </h3>
                </div>
                <div class="card-body pt-5">
                    <div class="table-responsive">
                        <table class="table align-middle table-row-dashed fs-7 gy-4 border-0">
                            <thead>
                                <tr class="text-start text-gray-400 fw-bold fs-8 text-uppercase gs-0 bg-light">
                                    <th class="ps-4 w-50px rounded-start">#</th>
                                    <th class="min-w-150px">Họ Tên</th>
                                    <th class="min-w-150px">Tên đăng nhập</th>
                                    <th class="text-center min-w-100px">Vai Trò</th>
                                    <th class="text-end pe-4 min-w-100px rounded-end">Hành động</th>
                                </tr>
                            </thead>
                            <tbody class="fw-semibold text-gray-700">
                                @forelse($users as $key => $user)
                                    <tr>
                                        <td class="ps-4 text-muted fs-8">{{ $key + 1 }}</td>
                                        <td class="text-gray-900 fw-bold fs-7">{{ $user->name }}</td>
                                        <td class="text-gray-600 fs-7">{{ $user->username }}</td>
                                        <td class="text-center">
                                            @if($user->role === 'admin')
                                                <span class="badge badge-light-danger fw-bold px-3 py-1 fs-8">Admin</span>
                                            @else
                                                <span class="badge badge-light-secondary fw-bold px-3 py-1 fs-8 text-gray-700">Staff</span>
                                            @endif
                                        </td>
                                        <td class="text-end pe-4">
                                            <div class="d-flex justify-content-end gap-2">
                                                {{-- Nút Đổi mật khẩu (Chỉ Admin mới thao tác được trên Staff) --}}
                                                @if(auth()->user()->isAdmin() && $user->role === 'staff')
                                                    <button type="button" class="btn btn-sm btn-icon btn-light-warning" 
                                                        data-bs-toggle="modal" data-bs-target="#resetPasswordModal"
                                                        data-userid="{{ $user->id }}" data-username="{{ $user->name }}"
                                                        title="Cấp lại mật khẩu">
                                                        <i class="ki-duotone ki-key fs-4"><span class="path1"></span><span class="path2"></span></i>
                                                    </button>
                                                @endif

                                                {{-- Nút Vô hiệu hóa (Dùng Icon Lock thay vì Trash, sửa lại lỗi HTML thẻ button) --}}
                                                @if(auth()->id() !== $user->id && $user->role !== 'admin')
                                                    <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" class="d-inline">
                                                        @csrf @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-icon btn-light-danger" 
                                                            onclick="return confirm('Xác nhận vô hiệu hóa (khóa) tài khoản này?');" title="Vô hiệu hóa">
                                                            <i class="ki-duotone ki-lock fs-4"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-10 fs-7">Chưa có nhân sự.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{-- Phân trang --}}
                    <div class="d-flex justify-content-between align-items-center mt-5 pagination-sm">
                        <div class="fs-8 fw-semibold text-gray-700">
                            Hiển thị {{ $users->firstItem() ?? 0 }} đến {{ $users->lastItem() ?? 0 }} của
                            {{ $users->total() }} nhân sự
                        </div>
                        <div>{{ $users->links('pagination::bootstrap-5') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL CẤP LẠI MẬT KHẨU (BẢO MẬT 2 LỚP) --}}
    <div class="modal fade" id="resetPasswordModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered mw-400px">
            <div class="modal-content shadow-sm border-0">
                <div class="modal-header border-0 pb-0 justify-content-end">
                    <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                        <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                    </div>
                </div>
                <div class="modal-body scroll-y px-10 px-lg-15 pt-0 pb-15">
                    <form action="{{ route('admin.users.reset-password') }}" method="POST">
                        @csrf
                        <div class="text-center mb-10">
                            <h1 class="text-gray-900 fw-bold mb-3 fs-3">Cấp Lại Mật Khẩu</h1>
                            <div class="text-muted fw-semibold fs-7">
                                Nhân viên: <span id="resetUserName" class="text-gray-800 fw-bold"></span>
                            </div>
                        </div>

                        <input type="hidden" name="user_id" id="resetUserId">

                        <div class="mb-6">
                            <label class="required fw-semibold fs-7 mb-2 text-gray-700">Mật khẩu mới</label>
                            <input type="text" name="new_password" class="form-control form-control-solid form-control-sm fs-7" placeholder="Nhập mật khẩu mới" required minlength="6">
                        </div>

                        <div class="mb-8">
                            <label class="required fw-semibold fs-7 mb-2 text-danger">Mã bảo mật của Admin</label>
                            {{-- Input viền đỏ để cảnh báo hành động nhạy cảm --}}
                            <input type="password" name="admin_security_code" class="form-control form-control-solid form-control-sm fs-7 border border-danger border-opacity-50" placeholder="Nhập mật khẩu của bạn" required>                            
                        </div>

                        <div class="text-center">
                            <button type="button" class="btn btn-light btn-sm me-3" data-bs-dismiss="modal">Hủy bỏ</button>
                            <button type="submit" class="btn btn-danger btn-sm fw-bold">Xác nhận Đổi</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const resetModal = document.getElementById('resetPasswordModal');
        if(resetModal) {
            resetModal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                const userId = button.getAttribute('data-userid');
                const userName = button.getAttribute('data-username');
                
                resetModal.querySelector('#resetUserId').value = userId;
                resetModal.querySelector('#resetUserName').textContent = userName;
            });
        }
    });
</script>
@endpush
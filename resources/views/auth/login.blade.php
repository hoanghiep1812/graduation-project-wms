<!DOCTYPE html>
<html lang="vi">

<head>
    <title>WMS System - Đăng Nhập</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="shortcut icon" href="{{ asset('assets/media/logos/logo.svg') }}" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700" />

    <link href="{{ asset('assets/plugins/global/plugins.bundle.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/style.bundle.css') }}" rel="stylesheet" type="text/css" />

    <style>
        .btn-primary:hover {
            background-color: #007ac2 !important;
            transition: all 0.3s ease;
        }

        .bgi-position-center {
            background-position: center center !important;
        }

        .password-toggle-btn {
            cursor: pointer;
            color: #94a3b8;
            transition: color 0.2s ease;
        }

        .password-toggle-btn:hover {
            color: #0f172a;
        }

        [data-bs-theme="dark"] .password-toggle-btn:hover {
            color: #f8fafc;
        }
    </style>
</head>

<body id="kt_body" class="app-blank bgi-size-cover bgi-attachment-fixed bgi-position-center bgi-no-repeat"
    style="background-image: url('{{ asset('assets/media/auth/bg4.jpg') }}')">

    <script>
        var defaultThemeMode = "light";
        var themeMode = defaultThemeMode;
        if (document.documentElement) {
            if (document.documentElement.hasAttribute("data-bs-theme-mode")) {
                themeMode = document.documentElement.getAttribute("data-bs-theme-mode");
            } else {
                if (localStorage.getItem("data-bs-theme") !== null) {
                    themeMode = localStorage.getItem("data-bs-theme");
                }
            }
            if (themeMode === "system") {
                themeMode = window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light";
            }
            document.documentElement.setAttribute("data-bs-theme", themeMode);
        }
    </script>

    <div class="d-flex flex-column flex-root" id="kt_app_root">
        <div class="d-flex flex-column flex-column-fluid flex-lg-row">

            <div class="d-flex flex-center w-lg-50 pt-15 pt-lg-0 px-10">
                <div class="d-flex flex-center flex-lg-start flex-column">
                    <a href="#" class="mb-7">
                        <h1 class="text-white fs-2hx fw-bolder mb-3">WMS System</h1>
                    </a>
                    <h2 class="text-white fw-normal m-0 fs-5">
                        Hệ thống Quản lý Kho thông minh tích hợp trí tuệ nhân tạo
                    </h2>
                </div>
            </div>

            <div
                class="d-flex flex-column-fluid flex-lg-row-auto justify-content-center justify-content-lg-end p-12 p-lg-20">
                <div
                    class="bg-body d-flex flex-column align-items-stretch flex-center rounded-4 w-md-600px p-15 p-lg-20 shadow-lg border border-gray-200">
                    <div class="d-flex flex-center flex-column flex-column-fluid px-lg-10 pb-10 pb-lg-15">

                        <form class="form w-100" method="POST" action="{{ route('login') }}" autocomplete="off">
                            @csrf

                            <div class="text-center mb-11">
                                <h1 class="text-gray-900 fw-bolder mb-3 fs-2hx">Đăng Nhập</h1>
                                <div class="text-gray-500 fw-semibold fs-6">Vui lòng nhập thông tin tài khoản WMS</div>
                            </div>

                            @if ($errors->any())
                                <div class="alert alert-danger d-flex align-items-center p-4 mb-10 border-0 shadow-sm">
                                    <i class="ki-duotone ki-shield-cross fs-2hx text-danger me-4"><span
                                            class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                                    <div class="d-flex flex-column">
                                        <ul class="mb-0 fs-7 fw-semibold text-danger">
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            @endif

                            <div class="fv-row mb-8">
                                <input type="text" placeholder="Tên đăng nhập / Mã NV" name="username"
                                    value="{{ old('username') }}" autocomplete="off"
                                    class="form-control form-control-solid bg-transparent fw-semibold text-gray-800"
                                    required autofocus />
                            </div>

                            <div class="fv-row mb-10 position-relative">
                                <input type="password" placeholder="Mật khẩu" name="password" id="passwordField"
                                    autocomplete="current-password"
                                    class="form-control form-control-solid bg-transparent fw-semibold text-gray-800 pe-12"
                                    required />
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary py-4 fs-5 fw-bold hover-elevate-up">
                                    Đăng Nhập
                                </button>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('assets/plugins/global/plugins.bundle.js') }}"></script>
    <script src="{{ asset('assets/js/scripts.bundle.js') }}"></script>

    // <script>
    //     function togglePasswordVisibility() {
    //         const passwordField = document.getElementById('passwordField');
    //         const toggleIcon = document.getElementById('togglePasswordIcon');
    //         if (passwordField.type === 'password') {
    //             passwordField.type = 'text';
    //             toggleIcon.classList.remove('ki-eye-slash');
    //             toggleIcon.classList.add('ki-eye');
    //         } else {
    //             passwordField.type = 'password';
    //             toggleIcon.classList.remove('ki-eye');
    //             toggleIcon.classList.add('ki-eye-slash');
    //         }
    //     }
    // </script>
</body>

</html>
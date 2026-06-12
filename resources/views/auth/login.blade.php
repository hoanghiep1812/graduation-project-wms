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
            z-index: 10;
        }

        .password-toggle-btn:hover {
            color: #0f172a;
        }

        [data-bs-theme="dark"] .password-toggle-btn:hover {
            color: #f8fafc;
        }
        
        .glass-container {
            border-radius: 1.25rem;
            transition: all 0.3s ease;
            width: 100%;
            max-width: 420px; 
        }
        
        @media (max-width: 575.98px) {
            .glass-container {
                margin-left: 1.5rem;
                margin-right: 1.5rem;
                padding: 2rem 1.5rem !important; 
            }
        }

        /* --- CHẾ ĐỘ SÁNG (LIGHT MODE) --- */
        [data-bs-theme="light"] .glass-container {
            background-color: rgba(255, 255, 255, 1) !important;
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.6) !important;
            box-shadow: 0px 10px 30px rgba(0, 0, 0, 0.05);
        }

        [data-bs-theme="light"] .form-control-solid {
            background-color: rgba(243, 246, 249, 0.8) !important;
            border: 1px solid #e4e6ef !important;
        }

        /* --- CHẾ ĐỘ TỐI (DARK MODE - GLASS EFFECT) --- */
        [data-bs-theme="dark"] .glass-container {
            background-color: rgba(30, 30, 45, 1) !important; 
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.08) !important; 
            box-shadow: 0px 15px 40px rgba(0, 0, 0, 0.4);
        }
        
        [data-bs-theme="dark"] .form-control-solid {
            background-color: rgba(15, 15, 20, 0.6) !important;
            border: 1px solid rgba(255, 255, 255, 0.05) !important;
            color: #ffffff !important;
        }

        [data-bs-theme="dark"] .form-control-solid:focus {
            background-color: rgba(15, 15, 20, 0.9) !important;
            border: 1px solid rgba(255, 255, 255, 0.15) !important;
        }
        
        [data-bs-theme="dark"] .text-gray-900 { color: #ffffff !important; }
        [data-bs-theme="dark"] .text-gray-700 { color: #e4e6ef !important; }
        [data-bs-theme="dark"] .text-gray-500 { color: #a1a5b7 !important; }
        
        .theme-toggle-floating {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            z-index: 99;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: rgba(30, 30, 45, 0.8);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255,255,255,0.1);
            color: #ffffff;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            transition: all 0.3s ease;
        }

        [data-bs-theme="dark"] .theme-toggle-floating {
            background-color: rgba(255, 255, 255, 0.85);
            color: #1e1e2d;
            border: 1px solid rgba(0,0,0,0.1);
        }

        .theme-toggle-floating:hover {
            transform: scale(1.1);
        }
    </style>
</head>

<body id="kt_body" class="app-blank bgi-size-cover bgi-attachment-fixed bgi-position-center bgi-no-repeat"
    style="background-image: url('{{ asset('assets/media/auth/bg4.jpg') }}')">

    <script>
        var defaultThemeMode = "light";
        var themeMode = defaultThemeMode;
        if (document.documentElement) {
            if (localStorage.getItem("data-bs-theme") !== null) {
                themeMode = localStorage.getItem("data-bs-theme");
            } else if (document.documentElement.hasAttribute("data-bs-theme-mode")) {
                themeMode = document.documentElement.getAttribute("data-bs-theme-mode");
            }
            if (themeMode === "system") {
                themeMode = window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light";
            }
            document.documentElement.setAttribute("data-bs-theme", themeMode);
        }
    </script>

    <div class="d-flex flex-column flex-root flex-center min-vh-100" id="kt_app_root">
        
        <div class="glass-container p-10 mx-auto">
            <div class="d-flex flex-center flex-column flex-column-fluid">

                <form class="form w-100" method="POST" action="{{ route('login') }}" autocomplete="off">
                    @csrf

                    <div class="text-center mb-10">
                        <div class="mb-5 d-inline-block bg-light-primary p-3 rounded-4">
                            <i class="ki-duotone ki-badge fs-3x text-primary">
                                <span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span>
                            </i>
                        </div>
                        <h1 class="text-gray-900 fw-bolder mb-2 fs-2">Hệ thống WMS nội bộ</h1>                                                
                    </div>

                    @if ($errors->any())
                        <div class="alert alert-danger d-flex align-items-center p-4 mb-8 border-0 shadow-sm rounded-3">
                            <i class="ki-duotone ki-shield-cross fs-3 text-danger me-3"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                            <div class="d-flex flex-column">
                                <ul class="mb-0 fs-8 fw-semibold text-danger ps-0" style="list-style: none;">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif

                    <div class="fv-row mb-6 text-start">
                        <label class="form-label fs-8 fw-bold text-gray-700">Tên đăng nhập <span class="text-danger">*</span></label>
                        <input type="text" placeholder="Ví dụ: NV001" name="username"
                            value="{{ old('username') }}" autocomplete="off"
                            class="form-control form-control-solid bg-transparent fw-semibold text-gray-800"
                            required autofocus />
                    </div>

                    <div class="fv-row mb-6 text-start position-relative">
                        <label class="form-label fs-8 fw-bold text-gray-700">Mật khẩu <span class="text-danger">*</span></label>
                        <div class="position-relative">
                            <input type="password" placeholder="Nhập mật khẩu..." name="password" id="passwordField"
                                autocomplete="current-password"
                                class="form-control form-control-solid bg-transparent fw-semibold text-gray-800 pe-12"
                                required />
                            
                            <span class="position-absolute top-50 end-0 translate-middle-y me-4 password-toggle-btn" onclick="togglePasswordVisibility()">
                                <i class="ki-duotone ki-eye-slash fs-4" id="togglePasswordIcon">
                                    <span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span>
                                </i>
                            </span>
                        </div>
                    </div>

                    <div class="d-flex flex-stack flex-wrap gap-3 fs-base fw-semibold mb-8 text-start">
                        <div>
                            <label class="form-check form-check-custom form-check-inline form-check-solid d-flex align-items-center cursor-pointer m-0">
                                
                                <input class="form-check-input h-15px w-15px cursor-pointer m-0" type="checkbox" name="remember" value="1"/>
                                
                                <span class="form-check-label fs-8 text-gray-700 ms-2">Ghi nhớ đăng nhập</span>
                                
                            </label>
                        </div>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary py-3 fs-6 fw-bold hover-elevate-up rounded-3">
                            Đăng nhập hệ thống
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>

    <div class="theme-toggle-floating" id="btnToggleTheme" onclick="toggleCustomTheme()">
        <i class="ki-duotone ki-night-day fs-2x theme-light-icon d-none">
            <span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span><span class="path6"></span><span class="path7"></span><span class="path8"></span><span class="path9"></span><span class="path10"></span>
        </i>
        <i class="ki-duotone ki-moon fs-2x theme-dark-icon">
            <span class="path1"></span><span class="path2"></span>
        </i>
    </div>

    <script src="{{ asset('assets/plugins/global/plugins.bundle.js') }}"></script>
    <script src="{{ asset('assets/js/scripts.bundle.js') }}"></script>

    <script>        
        function togglePasswordVisibility() {
            const passwordField = document.getElementById('passwordField');
            const toggleIcon = document.getElementById('togglePasswordIcon');
            
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                toggleIcon.classList.remove('ki-eye-slash');
                toggleIcon.classList.add('ki-eye');
            } else {
                passwordField.type = 'password';
                toggleIcon.classList.remove('ki-eye');
                toggleIcon.classList.add('ki-eye-slash');
            }
        }
        
        function updateThemeIcon() {
            const currentTheme = document.documentElement.getAttribute("data-bs-theme");
            const lightIcon = document.querySelector('.theme-light-icon');
            const darkIcon = document.querySelector('.theme-dark-icon');
            
            if (currentTheme === 'dark') {
                lightIcon.classList.remove('d-none');
                darkIcon.classList.add('d-none');
            } else {
                lightIcon.classList.add('d-none');
                darkIcon.classList.remove('d-none');
            }
        }
        
        function toggleCustomTheme() {
            let currentTheme = document.documentElement.getAttribute("data-bs-theme");
            let newTheme = currentTheme === "dark" ? "light" : "dark";
                        
            document.documentElement.setAttribute("data-bs-theme", newTheme);
                        
            localStorage.setItem("data-bs-theme", newTheme);                        
            updateThemeIcon();
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            updateThemeIcon();
        });
    </script>
</body>

</html>
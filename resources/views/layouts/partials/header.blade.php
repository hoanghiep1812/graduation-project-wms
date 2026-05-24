<div id="kt_app_header" class="app-header" data-kt-sticky="true" data-kt-sticky-activate="{default: true, lg: true}"
    data-kt-sticky-name="app-header-minimize" data-kt-sticky-offset="{default: '200px', lg: '0'}"
    data-kt-sticky-animation="false">

    <div class="app-container container-fluid d-flex align-items-stretch justify-content-between"
        id="kt_app_header_container">

        <div class="d-flex align-items-center d-lg-none ms-n3 me-1 me-md-2" title="Show sidebar menu">
            <div class="btn btn-icon btn-active-color-primary w-35px h-35px" id="kt_app_sidebar_mobile_toggle">
                <i class="ki-duotone ki-abstract-14 fs-2 fs-md-1"><span class="path1"></span><span
                        class="path2"></span></i>
            </div>
        </div>
        <div class="d-flex align-items-center flex-grow-1 flex-lg-grow-0">
            <a href="{{ route('admin.dashboard.index') }}" class="d-lg-none text-decoration-none">
                <h2 class="fw-bolder text-body m-0 fs-1">
                    Easy<span class="text-primary">WMS</span>
                </h2>
            </a>
        </div>
        <div class="d-flex align-items-stretch justify-content-end flex-lg-grow-1" id="kt_app_header_wrapper">

            <div class="app-navbar flex-shrink-0">

                <div class="app-navbar-item ms-1 ms-md-4">
                    <div class="btn btn-icon btn-custom btn-icon-muted btn-active-light btn-active-color-primary w-35px h-35px position-relative"
                        data-kt-menu-trigger="{default: 'click', lg: 'hover'}" data-kt-menu-attach="parent"
                        data-kt-menu-placement="bottom-end">
                        <i class="ki-duotone ki-notification-status fs-2">
                            <span class="path1"></span><span class="path2"></span><span class="path3"></span><span
                                class="path4"></span>
                        </i>
                        @if(isset($unreadCount) && $unreadCount > 0)
                            <span
                                class="bullet badge-danger position-absolute translate-middle top-0 start-100 animation-blink"></span>
                        @endif
                    </div>

                    <div class="menu menu-sub menu-sub-dropdown menu-column w-350px w-lg-375px" data-kt-menu="true">
                        <div class="d-flex flex-column bgi-no-repeat rounded-top"
                            style="background-image: url('{{ asset('assets/media/misc/menu-header-bg.jpg') }}')">
                            <h3 class="text-white fw-semibold px-9 mt-10 mb-6">
                                Thông báo
                                @if(isset($unreadCount) && $unreadCount > 0)
                                    <span class="fs-8 opacity-75 ps-3" id="unread_text_header">{{ $unreadCount }} chưa
                                        đọc</span>
                                @endif
                            </h3>
                        </div>
                        <div class="tab-content">
                            <div class="scroll-y mh-325px my-5 px-8" id="notifications_list_container">
                                @include('layouts.partials.notifications_list')
                            </div>
                        </div>
                    </div>
                </div>

                <div class="app-navbar-item ms-1 ms-md-4">
                    <a href="#"
                        class="btn btn-icon btn-custom btn-icon-muted btn-active-light btn-active-color-primary w-35px h-35px"
                        data-kt-menu-trigger="{default:'click', lg: 'hover'}" data-kt-menu-attach="parent"
                        data-kt-menu-placement="bottom-end">
                        <i class="ki-duotone ki-night-day theme-light-show fs-1"><span class="path1"></span><span
                                class="path2"></span><span class="path3"></span><span class="path4"></span><span
                                class="path5"></span><span class="path6"></span><span class="path7"></span><span
                                class="path8"></span><span class="path9"></span><span class="path10"></span></i>
                        <i class="ki-duotone ki-moon theme-dark-show fs-1"><span class="path1"></span><span
                                class="path2"></span></i>
                    </a>
                    <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-title-gray-700 menu-icon-gray-500 menu-active-bg menu-state-color fw-semibold py-4 fs-base w-150px"
                        data-kt-menu="true" data-kt-element="theme-mode-menu">
                        <div class="menu-item px-3 my-0">
                            <a href="#" class="menu-link px-3 py-2" data-kt-element="mode" data-kt-value="light">
                                <span class="menu-icon" data-kt-element="icon"><i
                                        class="ki-duotone ki-night-day fs-2"></i></span>
                                <span class="menu-title">Sáng (Light)</span>
                            </a>
                        </div>
                        <div class="menu-item px-3 my-0">
                            <a href="#" class="menu-link px-3 py-2" data-kt-element="mode" data-kt-value="dark">
                                <span class="menu-icon" data-kt-element="icon"><i
                                        class="ki-duotone ki-moon fs-2"></i></span>
                                <span class="menu-title">Tối (Dark)</span>
                            </a>
                        </div>
                        <div class="menu-item px-3 my-0">
                            <a href="#" class="menu-link px-3 py-2" data-kt-element="mode" data-kt-value="system">
                                <span class="menu-icon" data-kt-element="icon"><i
                                        class="ki-duotone ki-screen fs-2"></i></span>
                                <span class="menu-title">Hệ thống</span>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="app-navbar-item ms-1 ms-md-4" id="kt_header_user_menu_toggle">
                    <div class="cursor-pointer symbol symbol-35px"
                        data-kt-menu-trigger="{default: 'click', lg: 'hover'}" data-kt-menu-attach="parent"
                        data-kt-menu-placement="bottom-end">
                        <div class="symbol-label bg-light-primary text-primary fw-bold rounded-3">
						    {{ strtoupper(mb_substr(auth()->user()->name ?? 'U', 0, 1)) }}
						</div>
                    </div>
                    <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-800 menu-state-bg menu-state-color fw-semibold py-4 fs-6 w-275px"
                        data-kt-menu="true">

                        <div class="menu-item px-3">
                            <div class="menu-content d-flex align-items-center px-3">
                                <div class="symbol symbol-50px me-5">
                                    <div class="symbol-label bg-light-primary text-primary fw-bold rounded-3">
									    {{ strtoupper(mb_substr(auth()->user()->name ?? 'U', 0, 1)) }}
									</div>
                                </div>
                                <div class="d-flex flex-column">
                                    <div class="fw-bold d-flex align-items-center fs-5">
                                        {{ auth()->check() ? auth()->user()->name : 'Khách' }}

                                        @if(auth()->check() && auth()->user()->isAdmin())
                                            <span class="badge badge-light-danger fw-bold fs-8 px-2 py-1 ms-2">Admin</span>
                                        @else
                                            <span class="badge badge-light-primary fw-bold fs-8 px-2 py-1 ms-2">Staff</span>
                                        @endif
                                    </div>
                                    <a href="#" class="fw-semibold text-muted text-hover-primary fs-7"
                                        style="pointer-events: none;">{{ auth()->check() ? auth()->user()->email : '' }}</a>
                                </div>
                            </div>
                        </div>

                        <div class="separator my-2"></div>

                        <div class="menu-item px-5">
                            <form action="{{ route('logout') }}" method="POST" id="logout-form">
                                @csrf
                                <a href="#" class="menu-link px-5 text-danger fw-bold"
                                    onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                    Đăng xuất
                                </a>
                            </form>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
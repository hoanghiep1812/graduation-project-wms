<div id="kt_app_sidebar" class="app-sidebar flex-column user-select-none" data-bs-theme="dark" data-kt-drawer="true"
    data-kt-drawer-name="app-sidebar" data-kt-drawer-activate="{default: true, lg: false}" data-kt-drawer-overlay="true"
    data-kt-drawer-width="225px" data-kt-drawer-direction="start" data-kt-drawer-toggle="#kt_app_sidebar_mobile_toggle">

    
    <div class="app-sidebar-logo px-6" id="kt_app_sidebar_logo">
        <a href="{{ route('admin.dashboard.index') }}" class="text-decoration-none d-flex align-items-center">
            
            <h2 class="fw-bolder text-white m-0 app-sidebar-logo-default fs-2 tracking-tight">
                Easy<span class="text-primary">WMS</span>
            </h2>
            
            <h2 class="fw-bolder text-white m-0 app-sidebar-logo-minimize fs-3">
                E<span class="text-primary">W</span>
            </h2>
        </a>

        
        <div id="kt_app_sidebar_toggle"
            class="app-sidebar-toggle btn btn-icon btn-shadow btn-sm bg-gray-800 text-white btn-active-color-primary h-30px w-30px position-absolute top-50 start-100 translate-middle rotate {{ request()->cookie('sidebar_minimize_state') === 'on' ? 'active' : '' }}"
            data-kt-toggle="true" data-kt-toggle-state="active" data-kt-toggle-target="body"
            data-kt-toggle-name="app-sidebar-minimize">
            <i class="ki-duotone ki-black-left-line fs-3 rotate-180 text-white">
                <span class="path1"></span><span class="path2"></span>
            </i>
        </div>
    </div>

    
    <div class="app-sidebar-menu overflow-hidden flex-column-fluid">
        <div id="kt_app_sidebar_menu_wrapper" class="app-sidebar-wrapper">
            <div id="kt_app_sidebar_menu_scroll" class="scroll-y my-5 mx-3" data-kt-scroll="true"
                data-kt-scroll-activate="true" data-kt-scroll-height="auto"
                data-kt-scroll-dependencies="#kt_app_sidebar_logo, #kt_app_sidebar_footer"
                data-kt-scroll-wrappers="#kt_app_sidebar_menu" data-kt-scroll-offset="5px"
                data-kt-scroll-save-state="true">

                <div class="menu menu-column menu-rounded menu-sub-indention fw-semibold fs-6" id="#kt_app_sidebar_menu"
                    data-kt-menu="true" data-kt-menu-expand="false">

                    
                    @if(auth()->check() && auth()->user()->isAdmin())
                        <div class="menu-item">
                            <a class="menu-link {{ request()->routeIs('admin.dashboard.index') ? 'active' : '' }}"
                                href="{{ route('admin.dashboard.index') }}">
                                <span class="menu-icon">
                                    <i class="ki-duotone ki-element-11 fs-2"><span class="path1"></span><span
                                            class="path2"></span><span class="path3"></span><span class="path4"></span></i>
                                </span>
                                <span class="menu-title">Tổng Quan</span>
                            </a>
                        </div>
                    @endif

                    
                    <div class="menu-item pt-5">
                        <div class="menu-content"><span
                                class="menu-heading fw-bold text-uppercase fs-7 text-gray-500">Vận Hành</span></div>
                    </div>

                    
                    <div data-kt-menu-trigger="click"
                        class="menu-item menu-accordion {{ request()->routeIs('admin.inbound.*', 'admin.sales_orders.*') ? 'here show' : '' }}">
                        <span class="menu-link">
                            <span class="menu-icon"><i class="ki-duotone ki-delivery-3 fs-2"><span
                                        class="path1"></span><span class="path2"></span><span
                                        class="path3"></span></i></span>
                            <span class="menu-title">Nhập / Xuất Hàng</span>
                            <span class="menu-arrow"></span>
                        </span>
                        <div class="menu-sub menu-sub-accordion">
                            <div class="menu-item">
                                <a class="menu-link {{ request()->routeIs('admin.inbound.*') ? 'active' : '' }}"
                                    href="{{ route('admin.inbound.index') }}">
                                    <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                    <span class="menu-title">Phiếu Nhập</span>
                                </a>
                            </div>
                            <div class="menu-item">
                                <a class="menu-link {{ request()->routeIs('admin.sales_orders.*') ? 'active' : '' }}"
                                    href="{{ route('admin.sales_orders.index') }}">
                                    <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                    <span class="menu-title">Phiếu Xuất</span>
                                </a>
                            </div>
                        </div>
                    </div>

                    
                    <div data-kt-menu-trigger="click"
                        class="menu-item menu-accordion {{ request()->routeIs('admin.inventory.*', 'admin.reslotting.*', 'admin.stock_movements.*', 'admin.audits.*') ? 'here show' : '' }}">
                        <span class="menu-link">
                            <span class="menu-icon"><i class="ki-duotone ki-abstract-26 fs-2"><span
                                        class="path1"></span><span class="path2"></span></i></span>
                            <span class="menu-title">Tồn Kho</span>
                            <span class="menu-arrow"></span>
                        </span>
                        <div class="menu-sub menu-sub-accordion">
                            <div class="menu-item">
                                <a class="menu-link {{ request()->routeIs('admin.inventory.*') ? 'active' : '' }}"
                                    href="{{ route('admin.inventory.index') }}">
                                    <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                    <span class="menu-title">Danh Sách Tồn Kho</span>
                                </a>
                            </div>
                            <div class="menu-item">
                                <a class="menu-link {{ request()->routeIs('admin.reslotting.*') ? 'active' : '' }}"
                                    href="{{ route('admin.reslotting.index') }}">
                                    <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                    <span class="menu-title">Đề Xuất Dời Kệ</span>
                                </a>
                            </div>

                            @if(auth()->check() && auth()->user()->isAdmin())
                                <div class="menu-item">
                                    <a class="menu-link {{ request()->routeIs('admin.audits.*') ? 'active' : '' }}"
                                        href="{{ route('admin.audits.index') }}">
                                        <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                        <span class="menu-title">Kiểm Kê Kho</span>
                                    </a>
                                </div>
                                <div class="menu-item">
                                    <a class="menu-link {{ request()->routeIs('admin.stock_movements.*') ? 'active' : '' }}"
                                        href="{{ route('admin.stock_movements.index') }}">
                                        <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                        <span class="menu-title">Thẻ Kho</span>
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>

                    @if(auth()->check() && auth()->user()->isAdmin())
                        
                        <div class="menu-item pt-5">
                            <div class="menu-content">
                                <span class="menu-heading fw-bold text-uppercase fs-7 text-gray-500">Cấu Hình</span>
                            </div>
                        </div>

                        
                        <div data-kt-menu-trigger="click"
                            class="menu-item menu-accordion {{ request()->routeIs('admin.zones.*', 'admin.bins.*', 'admin.products.*', 'admin.suppliers.*', 'admin.partners.*') ? 'here show' : '' }}">
                            <span class="menu-link">
                                <span class="menu-icon"><i class="ki-duotone ki-setting-2 fs-2"><span
                                            class="path1"></span><span class="path2"></span></i></span>
                                <span class="menu-title">Dữ Liệu Chủ</span>
                                <span class="menu-arrow"></span>
                            </span>
                            <div class="menu-sub menu-sub-accordion">
                                <div class="menu-item">
                                    <a class="menu-link {{ request()->routeIs('admin.suppliers.*') ? 'active' : '' }}"
                                        href="{{ route('admin.suppliers.index') }}">
                                        <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                        <span class="menu-title">Nhà Cung Cấp</span>
                                    </a>
                                </div>
                                <div class="menu-item">
                                    <a class="menu-link {{ request()->routeIs('admin.partners.*') ? 'active' : '' }}"
                                        href="{{ route('admin.partners.index') }}">
                                        <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                        <span class="menu-title">Đối Tác</span>
                                    </a>
                                </div>
                                <div class="menu-item">
                                    <a class="menu-link {{ request()->routeIs('admin.zones.*') ? 'active' : '' }}"
                                        href="{{ route('admin.zones.index') }}">
                                        <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                        <span class="menu-title">Khu Vực</span>
                                    </a>
                                </div>
                                <div class="menu-item">
                                    <a class="menu-link {{ request()->routeIs('admin.bins.*') ? 'active' : '' }}"
                                        href="{{ route('admin.bins.index') }}">
                                        <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                        <span class="menu-title">Kệ Hàng</span>
                                    </a>
                                </div>
                                <div class="menu-item">
                                    <a class="menu-link {{ request()->routeIs('admin.products.*') ? 'active' : '' }}"
                                        href="{{ route('admin.products.index') }}">
                                        <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                        <span class="menu-title">Sản Phẩm</span>
                                    </a>
                                </div>
                            </div>
                        </div>

                        
                        <div data-kt-menu-trigger="click"
                            class="menu-item menu-accordion {{ request()->routeIs('admin.users.*') ? 'here show' : '' }}">
                            <span class="menu-link">
                                <span class="menu-icon"><i class="ki-duotone ki-user fs-2"><span class="path1"></span><span
                                            class="path2"></span></i></span>
                                <span class="menu-title">Phân Quyền</span>
                                <span class="menu-arrow"></span>
                            </span>
                            <div class="menu-sub menu-sub-accordion">
                                <div class="menu-item">
                                    <a class="menu-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}"
                                        href="{{ route('admin.users.index') }}">
                                        <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                        <span class="menu-title">Danh Sách Nhân Sự</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
</div>
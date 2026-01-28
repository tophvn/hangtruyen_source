<!doctype html>
<html lang="vi">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <meta name="color-scheme" content="light dark">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Panel') - HangTruyen</title>

    <!-- Fonts -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css"
        crossorigin="anonymous" media="print" onload="this.media='all'">

    <!-- OverlayScrollbars -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/styles/overlayscrollbars.min.css"
        crossorigin="anonymous">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css"
        crossorigin="anonymous">

    <!-- AdminLTE CSS -->
    <link rel="stylesheet" href="{{ asset('adminlte/css/adminlte.min.css') }}">

    @stack('styles')
    <style>
        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
        }

        .custom-toast {
            min-width: 250px;
            padding: 15px 20px;
            margin-bottom: 10px;
            border-radius: 8px;
            color: #fff;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            display: flex;
            align-items: center;
            justify-content: space-between;
            animation: slideIn 0.3s ease-out;
            border: none;
        }

        .custom-toast.bg-success {
            background: #28a745 !important;
        }

        .custom-toast.bg-danger {
            background: #dc3545 !important;
        }

        .custom-toast.bg-info {
            background: #17a2b8 !important;
        }

        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes fadeOut {
            from {
                opacity: 1;
            }

            to {
                opacity: 0;
            }
        }
    </style>
</head>

<body class="layout-fixed sidebar-expand-lg sidebar-open bg-body-tertiary">
    <div class="app-wrapper">
        <!-- Header -->
        <nav class="app-header navbar navbar-expand bg-body">
            <div class="container-fluid">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button">
                            <i class="bi bi-list"></i>
                        </a>
                    </li>
                    <li class="nav-item d-none d-md-block">
                        <a href="{{ url('/') }}" class="nav-link" target="_blank">Trang chủ</a>
                    </li>
                </ul>
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown user-menu">
                        <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                            <span>{{ auth()->user()->name ?? 'Admin' }}</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
                            <li class="user-header text-bg-primary">
                                <p>
                                    {{ auth()->user()->name ?? 'Admin' }}
                                    <small>{{ auth()->user()->email ?? '' }}</small>
                                </p>
                            </li>
                            <li class="user-footer">
                                <a href="{{ route('account.index') }}" class="btn btn-default btn-flat">Tài khoản</a>
                                <a href="#" class="btn btn-default btn-flat float-end"
                                    onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Đăng
                                    xuất</a>
                                <form id="logout-form" action="{{ route('auth.logout') }}" method="POST"
                                    style="display: none;">
                                    @csrf
                                </form>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </nav>

        <!-- Sidebar -->
        <aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
            <div class="sidebar-brand">
                <a href="{{ route('admin.index') }}" class="brand-link">
                    <img src="/images/mini-logo.png" alt="HangTruyen Logo" class="brand-image opacity-75 shadow"
                        style="opacity: 0.8; max-height: 33px;" />
                    <span class="brand-text fw-light">HangTruyen Admin</span>
                </a>
            </div>
            <div class="sidebar-wrapper">
                <nav class="mt-2">
                    <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="navigation"
                        aria-label="Main navigation" data-accordion="false">
                        <li class="nav-item">
                            <a href="{{ route('admin.index') }}"
                                class="nav-link {{ request()->routeIs('admin.index') ? 'active' : '' }}">
                                <i class="nav-icon bi bi-speedometer"></i>
                                <p>Dashboard</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.analytics') }}"
                                class="nav-link {{ request()->routeIs('admin.analytics*') ? 'active' : '' }}">
                                <i class="nav-icon bi bi-graph-up-arrow"></i>
                                <p>Thống kê & Phân tích</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.users') }}"
                                class="nav-link {{ request()->routeIs('admin.users*') ? 'active' : '' }}">
                                <i class="nav-icon bi bi-people-fill"></i>
                                <p>Quản lý Users</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.comments') }}"
                                class="nav-link {{ request()->routeIs('admin.comments*') ? 'active' : '' }}">
                                <i class="nav-icon bi bi-chat-dots-fill"></i>
                                <p>Quản lý Bình luận</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.mangas') }}"
                                class="nav-link {{ request()->routeIs('admin.mangas*') ? 'active' : '' }}">
                                <i class="nav-icon bi bi-book"></i>
                                <p>Quản lý Truyện</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.categories') }}"
                                class="nav-link {{ request()->routeIs('admin.categories*') ? 'active' : '' }}">
                                <i class="nav-icon bi bi-tags-fill"></i>
                                <p>Thể loại</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.posts') }}"
                                class="nav-link {{ request()->routeIs('admin.posts*') ? 'active' : '' }}">
                                <i class="nav-icon bi bi-newspaper"></i>
                                <p>Tin tức</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.reports') }}"
                                class="nav-link {{ request()->routeIs('admin.reports*') ? 'active' : '' }}">
                                <i class="nav-icon bi bi-exclamation-triangle-fill"></i>
                                <p>Báo lỗi</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.settings') }}"
                                class="nav-link {{ request()->routeIs('admin.settings*') ? 'active' : '' }}">
                                <i class="nav-icon bi bi-gear-fill"></i>
                                <p>Cài đặt</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.ads-seo') }}"
                                class="nav-link {{ request()->routeIs('admin.ads-seo*') ? 'active' : '' }}">
                                <i class="nav-icon bi bi-megaphone-fill"></i>
                                <p>Quảng cáo</p>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="app-main">
            <!-- App Content Header -->
            <div class="app-content-header">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-sm-6">
                            <h3 class="mb-0">@yield('page-title', 'Dashboard')</h3>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-end">
                                <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Home</a></li>
                                <li class="breadcrumb-item active">@yield('page-title', 'Dashboard')</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <!-- App Content -->
            <div class="app-content">
                <div class="container-fluid">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @yield('content')
                </div>
            </div>
        </main>

        <!-- Footer -->
        </footer>
    </div>

    <!-- Toast Container -->
    <div id="toast-container" class="toast-container"></div>

    <!-- jQuery (required for AdminLTE and some plugins) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>

    <!-- OverlayScrollbars -->
    <script src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/browser/overlayscrollbars.browser.es6.min.js"
        crossorigin="anonymous"></script>

    <!-- Popper.js for Bootstrap 5 -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
        crossorigin="anonymous"></script>

    <!-- Bootstrap 5 -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.min.js"
        crossorigin="anonymous"></script>

    <!-- AdminLTE JS -->
    <script src="{{ asset('adminlte/js/adminlte.min.js') }}"></script>

    <!-- OverlayScrollbars Configure -->
    <script>
        const SELECTOR_SIDEBAR_WRAPPER = '.sidebar-wrapper';
        const Default = {
            scrollbarTheme: 'os-theme-light',
            scrollbarAutoHide: 'leave',
            scrollbarClickScroll: true,
        };
        document.addEventListener('DOMContentLoaded', function () {
            const sidebarWrapper = document.querySelector(SELECTOR_SIDEBAR_WRAPPER);
            if (sidebarWrapper && OverlayScrollbarsGlobal?.OverlayScrollbars !== undefined) {
                OverlayScrollbarsGlobal.OverlayScrollbars(sidebarWrapper, {
                    scrollbars: {
                        theme: Default.scrollbarTheme,
                        autoHide: Default.scrollbarAutoHide,
                        clickScroll: Default.scrollbarClickScroll,
                    },
                });
            }
        });
    </script>

    <script>
        function showToast(message, type = 'success') {
            const container = document.getElementById('toast-container');
            const toast = document.createElement('div');
            toast.className = `custom-toast bg-${type}`;

            let icon = 'bi-check-circle-fill';
            if (type === 'danger') icon = 'bi-exclamation-triangle-fill';
            if (type === 'info') icon = 'bi-info-circle-fill';

            toast.innerHTML = `
                <div class="d-flex align-items-center">
                    <i class="bi ${icon} me-2"></i>
                    <span>${message}</span>
                </div>
                <button type="button" class="btn-close btn-close-white ms-3" onclick="this.parentElement.remove()"></button>
            `;

            container.appendChild(toast);

            setTimeout(() => {
                toast.style.animation = 'fadeOut 0.5s forwards';
                setTimeout(() => toast.remove(), 500);
            }, 3000);
        }

        @if(session('success')) showToast("{{ session('success') }}", 'success'); @endif
        @if(session('error')) showToast("{{ session('error') }}", 'danger'); @endif
    </script>

    @stack('scripts')
</body>

</html>
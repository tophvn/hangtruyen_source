<!doctype html>
<html lang="vi">
<head>
    <title>@yield('title', 'Hangtruyen - Trang web đọc truyện tranh Online')</title>
    <meta name="description" content="@yield('description', 'Đọc truyện tranh manga, manhua, manhwa miễn phí được cập nhật liên tục hàng ngày.')">
    <meta name="keywords" content="@yield('keywords', 'đọc truyện, truyện tranh, hangtruyen')">
    <link rel="canonical" href="@yield('canonical', url('/'))" />

    <meta name="robots" content="index, follow" />

    <script type="application/ld+json">
        {
            "@context": "https://schema.org",
            "@type": "WebSite",
            "name": "HangTruyen",
            "alternateName": "Hang Truyện",
            "url": "{{ url('/') }}"
        }
    </script>

    <!-- Required meta tags -->
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta property="og:locale" content="vi_VN" />
    <meta property="og:url" content="@yield('og:url', url('/'))" />
    <meta property="og:type" content="website" />
    <meta property="og:title" content="@yield('og:title', 'Hangtruyen - Trang web đọc truyện tranh Online')" />
    <meta property="og:description" content="@yield('og:description', 'Đọc truyện tranh manga, manhua, manhwa miễn phí được cập nhật liên tục hàng ngày.')" />
    <meta property="og:image" content="@yield('og:image', asset('images/logo-dark.png'))" />
    <meta property="og:image:width" content="1200" />
    <meta property="og:image:height" content="630" />
    <meta property="og:site_name" content="HangTruyen" />
    
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="@yield('og:title', 'Hangtruyen - Trang web đọc truyện tranh Online')" />
    <meta name="twitter:description" content="@yield('og:description', 'Đọc truyện tranh manga, manhua, manhwa miễn phí được cập nhật liên tục hàng ngày.')" />
    <meta name="twitter:image" content="@yield('og:image', asset('images/logo-dark.png'))" />

    @stack('head')

    <meta http-equiv="content-language" content="vi" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" href="{{ asset('images/favicon.png') }}" type="image/png" />
    <link rel="shortcut icon" href="{{ asset('images/favicon.png') }}" type="image/png" />
    
    <!-- PWA Manifest -->
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#596FB7">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="HangTruyen">
    <link rel="apple-touch-icon" href="{{ asset('images/favicon.png') }}">
    
    <!-- Preconnect to CDN for faster image loading -->
    <link rel="preconnect" href="https://img.otruyenapi.com" crossorigin>
    <link rel="dns-prefetch" href="https://img.otruyenapi.com">
    <link rel="preconnect" href="https://otruyenapi.com" crossorigin>
    <link rel="dns-prefetch" href="https://otruyenapi.com">
    
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="{{ asset('css/bootstrap.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/icon-font.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/splide-core.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/style.css') }}?v=1.13" />
    <link rel="stylesheet" href="{{ asset('css/responsive.css') }}?v=1.13" />
    
    @php
        $gtagCode = \App\Models\Setting::get('gtag_code', '');
    @endphp
    @if(!empty($gtagCode))
        {!! $gtagCode !!}
    @endif
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="{{ asset('js/utils/cookie.js') }}"></script>
    <script src="{{ asset('js/utils/common.js') }}"></script>
    @include('components.header-scripts')
</head>

<body>
    <div id="auth"></div>
    <script>
        async function getUser() {
            var response = await $.ajax({
                type: 'GET',
                url: '/api/auth/user',
                contentType: 'application/json',
            }).catch(() => {
                console.log('getUser error');
                return null;
            });

            if (response && response.status === 1) {
                return response.user;
            }
            return null;
        }

        async function refreshToken(newToken = false) {
            // Không cần refresh token cho local, chỉ cần getUser
            return await getUser();
        }

        async function logout(newToken = false) {
            await $.ajax({
                type: 'POST',
                url: '/api/auth/logout',
                contentType: 'application/json',
                error: function(data) {
                    console.log('logout error');
                    return null;
                },
            });
        }

        function handleSaveUserToSessionStorage(user) {
            sessionStorage.setItem(
                'user',
                JSON.stringify({
                    ...user,
                }),
            );
        }

        function handleRemoveUserFromSessionStorage() {
            sessionStorage.removeItem('user');
        }

        function getUserFromSessionStorage() {
            try {
                return JSON.parse(sessionStorage.getItem('user'));
            } catch {
                return sessionStorage.getItem('user');
            }
        }
    </script>
    <script src="{{ asset('js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('js/splide.min.js') }}"></script>
    <script>
        // Check darkmode config
        function checkDarkModeConfig() {
            const lightMode = localStorage.getItem('lm');
            if (lightMode === 'true') {
                document.body.classList.remove('darkmode');
                $('.dark-mode').removeClass('on');
            } else {
                document.body.classList.add('darkmode');
                $('.dark-mode').addClass('on');
            }
        }

        function toggleDarkModeConfig(mode) {
            const darkMode = mode !== undefined ? mode : $('body').hasClass('darkmode');
            if (!darkMode) {
                document.body.classList.add('darkmode');
            } else {
                document.body.classList.remove('darkmode');
            }

            localStorage.setItem('lm', darkMode);
            checkDarkModeConfig();
        }
        checkDarkModeConfig();
    </script>
    <div class="wrapper">
        @include('components.header')

        <div id="vote_noti">
            <p></p>
            <img src="{{ asset('images/details/img-vote-noti.png') }}" width="64" height="92" alt="" />
        </div>

        <main>
            <div id="main-content">
                @yield('content')
            </div>
        </main>
        
        @include('components.footer')
        
        <div href="javascript:void(0)" id="back-to-top" style="display: flex; cursor: pointer;">
            <i class="icon-arrow-up"></i>
        </div>
    </div>
    @include('components.mobile-menu')
    @include('components.login-modal')
    <script src="{{ asset('js/splide-extension-grid.min.js') }}"></script>
    @stack('scripts')
    <script src="{{ asset('js/custom.js') }}?v=1.06"></script>
    
    <script>
        $(document).ready(function() {
            setTimeout(function() {
                if (typeof getUser === 'function' && typeof handleHeaderLoginSuccess === 'function') {
                    getUser().then(function(user) {
                        if (user) {
                            if (typeof handleSaveUserToSessionStorage === 'function') {
                                handleSaveUserToSessionStorage(user);
                            }
                            handleHeaderLoginSuccess(user);
                        } else {
                            if (typeof handleHeaderLogout === 'function') {
                                handleHeaderLogout();
                            }
                        }
                    }).catch(function(error) {
                        console.log('Error getting user:', error);
                    });
                }
            }, 100);
        });
    </script>
</body>
</html>

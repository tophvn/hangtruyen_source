<!doctype html>
<html lang="vi">

<head>
    <title>@yield('title', 'HangTruyen - Đọc Truyện Tranh Online Manga, Manhua, Manhwa Miễn Phí | Cập Nhật Mới Nhất')</title>
    <meta name="description" content="@yield('description', 'HangTruyen - Website đọc truyện tranh online miễn phí hàng đầu Việt Nam. Cập nhật truyện manga, manhua, manhwa mới nhất mỗi ngày. Đọc truyện full, không quảng cáo, chất lượng cao.')">
    <meta name="keywords" content="@yield('keywords', 'đọc truyện tranh, truyện tranh online, manga online, manhua online, manhwa online, đọc truyện miễn phí, truyện tranh mới nhất, hangtruyen, đọc manga, đọc manhua, truyện full, truyện hot, truyện trending')">
    <link rel="canonical" href="@yield('canonical', url('/'))" />

    <meta name="robots" content="index, follow" />
    <meta name="author" content="HangTruyen">
    <meta name="language" content="Vietnamese">
    <meta name="revisit-after" content="1 days">
    <meta name="distribution" content="global">
    <meta name="rating" content="general">

    <script type="application/ld+json">
        {
            "@context": "https://schema.org",
            "@type": "WebSite",
            "name": "HangTruyen",
            "alternateName": "Hang Truyện",
            "url": "{{ url('/') }}",
            "potentialAction": {
                "@type": "SearchAction",
                "target": "{{ url('/tim-kiem') }}?q={search_term_string}",
                "query-input": "required name=search_term_string"
            }
        }
    </script>
    @php
        $facebookUrl = \App\Models\Setting::get('facebook_url', '');
        $twitterUrl = \App\Models\Setting::get('twitter_url', '');
        $youtubeUrl = \App\Models\Setting::get('youtube_url', '');
        $githubUrl = \App\Models\Setting::get('github_url', '');
        $socialLinks = array_values(array_filter([$facebookUrl, $twitterUrl, $youtubeUrl, $githubUrl]));
    @endphp
    <script type="application/ld+json">
        {
            "@context": "https://schema.org",
            "@type": "Organization",
            "name": "HangTruyen",
            "url": "{{ url('/') }}",
            "logo": "{{ asset('images/logo-dark.png') }}"@if(count($socialLinks) > 0),
            "sameAs": {!! json_encode($socialLinks, JSON_UNESCAPED_SLASHES) !!}
        @endif
}
    </script>

    <!-- Required meta tags -->
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    
    <!-- Open Graph / Facebook -->
    <meta property="og:locale" content="vi_VN" />
    <meta property="og:url" content="@yield('og:url', url('/'))" />
    <meta property="og:type" content="@yield('og:type', 'website')" />
    <meta property="og:title" content="@yield('og:title', 'HangTruyen - Đọc Truyện Tranh Online Manga, Manhua, Manhwa Miễn Phí')" />
    <meta property="og:description" content="@yield('og:description', 'HangTruyen - Website đọc truyện tranh online miễn phí hàng đầu Việt Nam. Cập nhật truyện manga, manhua, manhwa mới nhất mỗi ngày.')" />
    <meta property="og:image" content="@yield('og:image', asset('images/logo-dark.png'))" />
    <meta property="og:image:width" content="1200" />
    <meta property="og:image:height" content="630" />
    <meta property="og:site_name" content="HangTruyen" />
    
    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="@yield('og:title', 'HangTruyen - Đọc Truyện Tranh Online Miễn Phí')" />
    <meta name="twitter:description" content="@yield('og:description', 'HangTruyen - Website đọc truyện tranh online miễn phí hàng đầu Việt Nam.')" />
    <meta name="twitter:image" content="@yield('og:image', asset('images/logo-dark.png'))" />

    @stack('head')

    <meta http-equiv="content-language" content="vi" />
    <link rel="icon" href="{{ asset('images/favicon.png') }}" type="image/png" />
    <link rel="shortcut icon" href="{{ asset('images/favicon.png') }}" type="image/png" />
    
    <!-- PWA Manifest -->
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#596FB7">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="HangTruyen">
    <link rel="apple-touch-icon" href="{{ asset('images/favicon.png') }}">
    
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
    <script src="{{ asset('js/utils/cookie.js') }}"></script>
    <script src="{{ asset('js/utils/common.js') }}"></script>
    <script src="{{ asset('js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('js/splide.min.js') }}"></script>
    <script>
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
    <script src="{{ asset('js/custom/suggest.js') }}"></script>
    <script src="{{ asset('js/custom/home/index.js') }}"></script>
    <script src="{{ asset('js/custom.js') }}?v=1.06"></script>
    @stack('scripts')
    
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

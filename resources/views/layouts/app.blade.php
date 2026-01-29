<!doctype html>
<html lang="vi">

<head>
    @php
        $siteName = trim((string) \App\Models\Setting::get('site_name', 'HangTruyen'));
        if ($siteName === '') {
            $siteName = 'HangTruyen';
        }

        $defaultDescription = $siteName . ' - Website đọc truyện tranh online miễn phí hàng đầu Việt Nam. Cập nhật truyện manga, manhua, manhwa mới nhất mỗi ngày. Đọc truyện full, không quảng cáo, chất lượng cao. Hàng nghìn truyện tranh hot trending đang chờ bạn khám phá.';
        $defaultKeywords = 'đọc truyện tranh, truyện tranh online, manga online, manhua online, manhwa online, đọc truyện miễn phí, truyện tranh mới nhất, hangtruyen, đọc manga, đọc manhua, truyện full, truyện hot, truyện trending, truyện tranh việt nam';

        $settingsDescription = trim((string) \App\Models\Setting::get('site_description', ''));
        $settingsKeywords = trim((string) \App\Models\Setting::get('site_keywords', ''));

        $globalDescription = $settingsDescription !== '' ? $settingsDescription : $defaultDescription;
        $globalKeywords = $settingsKeywords !== '' ? $settingsKeywords : $defaultKeywords;

        $replaceNames = ['HangTruyen', 'Hang Truyện', 'Hangtruyen'];

        $pageTitle = trim((string) $__env->yieldContent('title', $siteName . ' - Đọc Truyện Tranh Online Manga, Manhua, Manhwa Miễn Phí | Cập Nhật Mới Nhất'));
        $pageTitle = str_ireplace($replaceNames, $siteName, $pageTitle);

        $pageDescription = trim((string) $__env->yieldContent('description', $globalDescription));
        $pageDescription = str_ireplace($replaceNames, $siteName, $pageDescription);

        $pageKeywords = trim((string) $__env->yieldContent('keywords', $globalKeywords));
        $pageKeywords = str_ireplace($replaceNames, $siteName, $pageKeywords);

        $canonicalUrl = trim((string) $__env->yieldContent('canonical', url()->current()));
        $ogUrl = trim((string) $__env->yieldContent('og:url', url()->current()));

        $ogTitle = trim((string) $__env->yieldContent('og:title', $siteName . ' - Đọc Truyện Tranh Online Manga, Manhua, Manhwa Miễn Phí'));
        $ogTitle = str_ireplace($replaceNames, $siteName, $ogTitle);

        $ogDescription = trim((string) $__env->yieldContent('og:description', $globalDescription));
        $ogDescription = str_ireplace($replaceNames, $siteName, $ogDescription);
    @endphp

    <title>{{ $pageTitle }}</title>
    <meta name="description" content="{{ $pageDescription }}">
    <meta name="keywords" content="{{ $pageKeywords }}">
    <link rel="canonical" href="{{ $canonicalUrl }}" />

    <meta name="robots" content="index, follow" />
    <meta name="author" content="{{ $siteName }}">
    <meta name="language" content="Vietnamese">
    <meta name="revisit-after" content="1 days">
    <meta name="distribution" content="global">
    <meta name="rating" content="general">

    <script type="application/ld+json">
        {
            "@context": "https://schema.org",
            "@type": "WebSite",
            "name": "{{ $siteName }}",
            "alternateName": "{{ $siteName }}",
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
            "name": "{{ $siteName }}",
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
    <meta property="og:url" content="{{ $ogUrl }}" />
    <meta property="og:type" content="@yield('og:type', 'website')" />
    <meta property="og:title" content="{{ $ogTitle }}" />
    <meta property="og:description" content="{{ $ogDescription }}" />
    <meta property="og:image" content="@yield('og:image', asset('images/logo-dark.png'))" />
    <meta property="og:image:width" content="1200" />
    <meta property="og:image:height" content="630" />
    <meta property="og:site_name" content="{{ $siteName }}" />

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="{{ $ogTitle }}" />
    <meta name="twitter:description" content="{{ $ogDescription }}" />
    <meta name="twitter:image" content="@yield('og:image', asset('images/logo-dark.png'))" />

    @stack('head')

    <meta http-equiv="content-language" content="vi" />
    <link rel="icon" href="{{ asset('images/favicon.png') }}" type="image/png" />
    <link rel="shortcut icon" href="{{ asset('images/favicon.png') }}" type="image/png" />

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

    {{-- Advertisement Head --}}
    @php
        $adsEnabled = \App\Models\Setting::get('ads_enabled', '0') == '1';
    @endphp
    @if($adsEnabled)
        {{-- Custom Head Script --}}
        {!! \App\Models\Setting::get('ad_script_header', '') !!}

        {{-- Popunder --}}
        @if(\App\Models\Setting::get('ads_popunder_enabled', '0') == '1')
            {!! \App\Models\Setting::get('ads_popunder_code', '') !!}
        @endif

        {{-- Push Notifications --}}
        @if(\App\Models\Setting::get('ads_push_enabled', '0') == '1')
            {!! \App\Models\Setting::get('ads_push_code', '') !!}
        @endif
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
                error: function (data) {
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

        {{-- Ad Banner Top --}}
        @if($adsEnabled && \App\Models\Setting::get('ads_top_enabled', '0') == '1')
            <div class="container ad-banner-top my-3 text-center">
                {!! \App\Models\Setting::get('ad_banner_top', '') !!}
            </div>
        @endif

        <div id="vote_noti">
            <p></p>
            <img src="{{ asset('images/details/img-vote-noti.png') }}" width="64" height="92" alt="" />
        </div>

        <main>
            <div id="main-content">
                @yield('content')
            </div>
        </main>

        {{-- Ad Banner Bottom --}}
        @if($adsEnabled && \App\Models\Setting::get('ads_bottom_enabled', '0') == '1')
            <div class="container ad-banner-bottom my-4 text-center">
                {!! \App\Models\Setting::get('ad_banner_bottom', '') !!}
            </div>
        @endif

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
        $(document).ready(function () {
            setTimeout(function () {
                if (typeof getUser === 'function' && typeof handleHeaderLoginSuccess === 'function') {
                    getUser().then(function (user) {
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
                    }).catch(function (error) {
                        console.log('Error getting user:', error);
                    });
                }
            }, 100);
        });
    </script>
    @php
        $activeEffect = \App\Models\Setting::get('site_effect', 'none');
        $effectHtml = '';
        if ($activeEffect && $activeEffect !== 'none') {
            $effectPath = resource_path('effects/' . $activeEffect . '.html');
            if (is_file($effectPath)) {
                $effectHtml = file_get_contents($effectPath);
            }
        }
    @endphp
    {!! $effectHtml !!}

    {{-- Sticky Ad and Additional scripts --}}
    @if($adsEnabled && \App\Models\Setting::get('ads_sticky_enabled', '0') == '1')
        <div class="sticky-ad-container"
            style="position: fixed; bottom: 0; left: 0; width: 100%; z-index: 999; text-align: center; background: rgba(0,0,0,0.1);">
            {!! \App\Models\Setting::get('ads_sticky_code', '') !!}
        </div>
    @endif
</body>

</html>
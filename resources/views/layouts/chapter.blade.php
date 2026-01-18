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
    <div class="wrapper reading-page">
        <!-- Reading Header -->
        <header class="reading-header" id="readingHeader">
            <div class="r-left">
                <a href="{{ url('/') }}" class="logo">
                    <img src="/images/mini-logo.png" width="48" alt="Logo" style="min-width: 48px;">
                </a>
                <h1 class="manga-name">
                    Đọc <a href="/truyen-tranh/{{ isset($mangaSlug) ? $mangaSlug : 'gto-fury-of-death-yamada' }}">{{ isset($mangaTitle) ? $mangaTitle : 'GTO: Fury of Death Yamada' }}</a> - {{ isset($chapterName) ? $chapterName : 'Chapter 13' }}
                </h1>
            </div>

            <div class="r-right">
                <a href="" class="report m-0" data-bs-toggle="modal" data-bs-target="#ReportModal">
                    <i class="icon-info-circle"></i>
                </a>
                <a href="#" class="show-cmt d-flex" data-bs-toggle="offcanvas" data-bs-target="#offcanvasComment" aria-controls="offcanvasComment">
                    <i class="icon-messages"></i>
                </a>
                <a href="#" class="show-setting d-flex" data-bs-toggle="offcanvas" data-bs-target="#offcanvasSetting">
                    <i class="icon-setting-2"></i>
                </a>
            </div>
            <div class="r-navigation navigation navi-chap position-relative">
                @if(isset($prevChapter) && $prevChapter)
                    <button type="button" class="navi prev" onclick="window.location.href='{{ $prevChapter['url'] }}'" title="Chapter Trước">
                        <i class="icon-arrow-left"></i>
                    </button>
                @else
                    <button type="button" class="navi prev disabled" title="Chapter Trước">
                        <i class="icon-arrow-left"></i>
                    </button>
                @endif
                <div id="dd-chapters" class="dropdown" data-value="{{ $chapterSlug ?? '' }}">
                    <a href="" class="dropdown-toggle" id="dropdownChaps" data-bs-toggle="dropdown" aria-expanded="false">
                        <span>{{ isset($chapterName) ? $chapterName : 'Chapter 13' }}</span>
                        <sub>{{ isset($chapterName) ? $chapterName : 'Chapter 13' }}</sub>
                        <i class="icon-arrow-down-1"></i>
                    </a>
                    <div class="dropdown-menu" aria-labelledby="dropdownChaps">
                        <form class="form-search" id="form-search-chap">
                            <input class="form-control" type="text" placeholder="Tìm kiếm" aria-label="Tìm kiếm">
                            <i class="icon-search-normal"></i>
                        </form>
                        <div class="list-chap">
                            @php
                                $mangaChapters = $manga['chapters'] ?? [];
                                $currentChapterSlug = $chapterSlug ?? '';
                            @endphp
                            @if(count($mangaChapters) > 0)
                                @foreach($mangaChapters as $chapter)
                                    @php
                                        $chapterDisplayName = 'Chapter ' . ($chapter['name'] ?? '');
                                        $isActive = ($chapter['slug'] ?? '') === $currentChapterSlug;
                                    @endphp
                                    <span class="l-chapter {{ $isActive ? 'selected' : '' }}">
                                        <a class="dropdown-item" data-value="{{ $chapter['name'] ?? '' }}" href="{{ route('manga.chapter', ['mangaSlug' => $mangaSlug ?? '', 'chapterSlug' => $chapter['slug'] ?? '']) }}" title="{{ $chapterDisplayName }}">
                                            {{ $chapterDisplayName }}
                                        </a>
                                    </span>
                                @endforeach
                            @else
                                <span class="l-chapter">
                                    <span class="dropdown-item">Đang cập nhật danh sách chapter</span>
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
                @if(isset($nextChapter) && $nextChapter)
                    <button type="button" class="navi next" onclick="window.location.href='{{ $nextChapter['url'] }}'" title="Chapter Sau">
                        <i class="icon-arrow-right"></i>
                    </button>
                @else
                    <button type="button" class="navi next disabled" title="Chapter Sau">
                        <i class="icon-arrow-right"></i>
                    </button>
                @endif
            </div>
            <button class="reading-header-btn" id="reading-header-btn">
                <i class="icon-arrow-down-1"></i>
            </button>
        </header>

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
    
    <!-- Settings Offcanvas -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasSetting" aria-labelledby="offcanvasSettingLabel">
        <div class="offcanvas-body">
            <div class="offcanvas-header">
                <button class="btn-close" type="button" data-bs-dismiss="offcanvas" aria-label="Close">
                    <i class="icon-close-circle"></i>
                </button>
                <h4 class="title">Cài đặt</h4>
            </div>
            <div class="list-settings">
                <div class="reading-mode disabled">
                    <div id="dd-mode" class="dropdown">
                        <a href="" class="dropdown-toggle" id="dropdownMode" data-bs-toggle="dropdown" aria-expanded="false">
                            <span>Chế độ đọc</span><sub>Dọc</sub>
                        </a>
                        <div class="dropdown-menu" aria-labelledby="dropdownMode">
                            <div class="list-mode">
                                <span><a class="dropdown-item" data-mode="default" data-value="dọc" href="#">Dọc</a></span>
                                <span><a class="dropdown-item" data-mode="horizon-single" data-value="Ngang 1 trang" href="#">Ngang 1 trang</a></span>
                                <span><a class="dropdown-item" data-mode="horizon-double" data-value="Ngang 2 trang" href="#">Ngang 2 trang</a></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="set-pages">
                    <div id="dd-pages" class="dropdown">
                        <a href="" class="dropdown-toggle" id="dropdownPages" data-bs-toggle="dropdown" aria-expanded="false">
                            <span>Trang 1</span><i class="icon-arrow-down-1"></i>
                        </a>
                        <div class="dropdown-menu" aria-labelledby="dropdownPages">
                            <div class="list-pages">
                                @php
                                    $totalPages = isset($chapterImages) && is_array($chapterImages) ? count($chapterImages) : 5;
                                @endphp
                                @for($i = 1; $i <= $totalPages; $i++)
                                    <span>
                                        <a class="dropdown-item chose-img chose-img-{{ $i }}" data-id="{{ $i }}" href="#">Trang {{ $i }}</a>
                                    </span>
                                @endfor
                            </div>
                        </div>
                    </div>
                    <div class="navigation navi-page">
                        <button type="button" class="navi prev">
                            <i class="icon-arrow-left"></i>
                        </button>
                        <button type="button" class="navi next">
                            <i class="icon-arrow-right"></i>
                        </button>
                    </div>
                </div>
                <div class="zoom-mode">
                    <div id="dd-zoom" class="dropdown">
                        <a href="" class="dropdown-toggle" id="dropdownZoom" data-bs-toggle="dropdown" aria-expanded="false">
                            <span>Chế độ Zoom</span><sub>Không Zoom</sub>
                        </a>
                        <div class="dropdown-menu" aria-labelledby="dropdownZoom">
                            <div class="list-mode">
                                <span><a class="dropdown-item" data-value="Không Zoom" href="#">Không Zoom</a></span>
                                <span><a class="dropdown-item" data-value="Fit Width" href="#">Fit Width</a></span>
                                <span><a class="dropdown-item" data-value="Fit Height" href="#">Fit Height</a></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="lightmode" class="dropdown">
                    <a href="" class="dropdown-toggle" id="dropdownLightmode" data-bs-toggle="dropdown" aria-expanded="false">
                        <span>LightMode</span><sub>Tối</sub>
                    </a>
                    <div class="dropdown-menu" aria-labelledby="dropdownLightmode">
                        <div class="list-mode">
                            <span><a class="dropdown-item dl-mode" data-value="false" href="#">Sáng</a></span>
                            <span><a class="dropdown-item dl-mode" data-value="true" href="#">Tối</a></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Report Modal -->
    <div class="modal fade" id="ReportModal" tabindex="-1" aria-labelledby="ReportModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="ReportModalLabel">Báo cáo lỗi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="icon-close-circle"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="reportForm">
                        <div class="mb-3">
                            <label for="reportType" class="form-label">Loại lỗi</label>
                            <select class="form-select" id="reportType" required>
                                <option value="">Chọn loại lỗi</option>
                                <option value="broken-image">Ảnh bị lỗi</option>
                                <option value="wrong-chapter">Sai chương</option>
                                <option value="missing-page">Thiếu trang</option>
                                <option value="other">Khác</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="reportDescription" class="form-label">Mô tả chi tiết</label>
                            <textarea class="form-control" id="reportDescription" rows="4" placeholder="Mô tả chi tiết về lỗi..." required></textarea>
                        </div>
                        <div class="d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                            <button type="submit" class="btn btn-primary">Gửi báo cáo</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Comments Offcanvas -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasComment" aria-labelledby="offcanvasCommentLabel">
        <div class="offcanvas-body" style="padding: 0; overflow-y: auto; height: 100vh;">
            @if(isset($comments) && isset($mangaSlug))
                @include('manga.components.comments', [
                    'mangaSlug' => $mangaSlug ?? '',
                    'comments' => $comments ?? collect(),
                    'likedCommentIds' => $likedCommentIds ?? [],
                    'commentsCount' => $commentsCount ?? 0,
                ])
            @else
                <div class="comments-section">
                    <p class="text-muted">Bình luận sẽ được hiển thị ở đây</p>
                </div>
            @endif
        </div>
    </div>

    @include('components.mobile-menu')
    @include('components.login-modal')
    <script src="{{ asset('js/splide-extension-grid.min.js') }}"></script>
    @stack('scripts')
    <script src="{{ asset('js/custom.js') }}?v=1.06"></script>
    
    <script>
        // Ensure jQuery is loaded
        if (typeof jQuery === 'undefined') {
            console.error('jQuery is not loaded');
        }
        let lastScrollTop = 0;
        const readingHeader = document.getElementById('readingHeader');
        const readingHeaderBtn = document.getElementById('reading-header-btn');
        let isHeaderExpanded = false;
        
        if (readingHeader && readingHeaderBtn) {
            window.addEventListener('scroll', function() {
                const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
                
                if (isHeaderExpanded) {
                    return;
                }
                
                if (scrollTop > lastScrollTop && scrollTop > 100) {
                    readingHeader.style.top = '-100%';
                    readingHeaderBtn.style.opacity = '1';
                    readingHeaderBtn.style.visibility = 'visible';
                } else if (scrollTop < lastScrollTop) {
                    readingHeader.style.top = '0';
                    readingHeaderBtn.style.opacity = '0';
                    readingHeaderBtn.style.visibility = 'hidden';
                }
                
                lastScrollTop = scrollTop <= 0 ? 0 : scrollTop;
            });
            
            readingHeaderBtn.addEventListener('click', function() {
                isHeaderExpanded = !isHeaderExpanded;
                readingHeader.classList.toggle('expanded');
                if (isHeaderExpanded) {
                    readingHeader.style.top = '0';
                    readingHeaderBtn.style.opacity = '0';
                    readingHeaderBtn.style.visibility = 'hidden';
                } else {
                    const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
                    if (scrollTop > 100) {
                        readingHeader.style.top = '-100%';
                        readingHeaderBtn.style.opacity = '1';
                        readingHeaderBtn.style.visibility = 'visible';
                    }
                }
            });
        }

        // Chapter search functionality
        const formSearchChap = document.getElementById('form-search-chap');
        if (formSearchChap) {
            const searchInput = formSearchChap.querySelector('input');
            if (searchInput) {
                searchInput.addEventListener('input', function(e) {
                    const searchTerm = e.target.value.toLowerCase();
                    const chapterItems = document.querySelectorAll('#dd-chapters .list-chap .l-chapter');
                    
                    chapterItems.forEach(function(item) {
                        const chapterText = item.textContent.toLowerCase();
                        if (chapterText.includes(searchTerm)) {
                            item.style.display = '';
                        } else {
                            item.style.display = 'none';
                        }
                    });
                });
            }
        }

        // Lightmode toggle functionality - must be inside document ready
        $(document).ready(function() {
            // Handle lightmode dropdown clicks
            $(document).on('click', '#lightmode .dl-mode', function(e) {
                e.preventDefault();
                const isLightMode = $(this).attr('data-value') === 'false';
                toggleDarkModeConfig(isLightMode);
                handleUpdateDarkmodeConfig();
            });

            function handleUpdateDarkmodeConfig() {
                const darkMode = $('body').hasClass('darkmode');
                const subElement = $('#dropdownLightmode > sub');
                if (subElement.length) {
                    subElement.text(darkMode ? 'Tối' : 'Sáng');
                }
            }

            // Update on page load and when darkmode changes
            handleUpdateDarkmodeConfig();
            
            // Also update when body class changes (in case darkmode is toggled elsewhere)
            const observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                        handleUpdateDarkmodeConfig();
                    }
                });
            });
            observer.observe(document.body, { attributes: true, attributeFilter: ['class'] });
        });
    </script>
</body>
</html>

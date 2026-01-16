@extends('layouts.app')

@section('title', 'Tài khoản - HangTruyen')
@section('description', 'Quản lý tài khoản, lịch sử đọc và truyện đang theo dõi')
@section('keywords', 'tài khoản, lịch sử đọc, truyện đang theo dõi')

@section('content')
<main>
    <div id="main-content" class="container">
        <div class="page-breadcrumb">
            <span class="item"><a href="{{ url('/') }}">Trang chủ</a></span>
            <span class="item breadcrumb_last" aria-current="page">Cá nhân</span>
        </div>
        <ul class="nav nav-tabs nav-account" role="tablist">
            <li class="nav-item" role="presentation">
                <a href="" class="nav-link active" id="general-tab" data-bs-toggle="tab" data-bs-target="#general" role="tab" aria-controls="general" aria-selected="true">Thông tin</a>
            </li>
            <li class="nav-item" role="presentation">
                <a href="" class="nav-link" id="mg-continue-tab" data-bs-toggle="tab" data-bs-target="#mg-continue" role="tab" aria-controls="mg-continue" aria-selected="false">Đang đọc</a>
            </li>
            <li class="nav-item" role="presentation">
                <a href="" class="nav-link" id="mg-save-tab" data-bs-toggle="tab" data-bs-target="#mg-save" role="tab" aria-controls="mg-save" aria-selected="false">Đã lưu</a>
            </li>
        </ul>
        <div class="tab-content" id="TopFollow">
            <div class="tab-pane fade show active" id="general" role="tabpanel" aria-labelledby="general-tab">
                <h3 class="m-title title">
                    Thông tin chung<span class="sub">Truyện mới được cập nhật.</span>
                </h3>
                <form id="form-prf" class="user-page" method="POST" action="{{ route('account.update') }}">
                    @csrf
                    @method('PUT')
                    <div class="avatar-user position-relative">
                        @php
                            $avatar = $user->avatar ?? asset('images/favicon.png');
                            if ($user->avatar && !filter_var($user->avatar, FILTER_VALIDATE_URL)) {
                                $avatar = asset('storage/' . $user->avatar);
                            }
                            $avatarInitial = strtoupper(substr($user->name ?? 'U', 0, 1));
                        @endphp
                        <div id="avatar-temp-edit" class="avatar-temp user-avatar-img" data-name="{{ $avatarInitial }}" style="@if($user->avatar && filter_var($user->avatar, FILTER_VALIDATE_URL)) background-image: url('{{ $avatar }}'); background-size: cover; background-position: center; @endif">
                            @if(!$user->avatar || !filter_var($user->avatar, FILTER_VALIDATE_URL))
                                {{ $avatarInitial }}
                            @endif
                        </div>
                        <span class="color position-absolute">
                            <input type="file" id="changeAvatar" name="uploadAvatar" accept=".jpg,.jpeg,.gif,.png" />
                            <span class="icon-edit">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                                    <path d="M17.4752 0.833984H15.0252C13.9668 0.833984 13.3335 1.46732 13.3335 2.52565V4.97565C13.3335 6.03398 13.9668 6.66732 15.0252 6.66732H17.4752C18.5335 6.66732 19.1668 6.03398 19.1668 4.97565V2.52565C19.1668 1.46732 18.5335 0.833984 17.4752 0.833984ZM15.8418 5.47565C15.8168 5.50065 15.7585 5.53398 15.7168 5.53398L14.8502 5.65898C14.8252 5.66732 14.7918 5.66732 14.7668 5.66732C14.6418 5.66732 14.5335 5.62565 14.4585 5.54232C14.3585 5.44232 14.3168 5.30065 14.3418 5.15065L14.4668 4.28398C14.4752 4.24232 14.5002 4.18398 14.5252 4.15898L15.9418 2.74232C15.9668 2.80065 15.9918 2.86732 16.0168 2.93398C16.0502 3.00065 16.0835 3.05898 16.1168 3.11732C16.1418 3.16732 16.1752 3.21732 16.2085 3.25065C16.2418 3.30065 16.2752 3.35065 16.3002 3.37565C16.3168 3.40065 16.3252 3.40898 16.3335 3.41732C16.4085 3.50898 16.4918 3.59232 16.5668 3.65065C16.5835 3.66732 16.6002 3.68398 16.6085 3.68398C16.6502 3.71732 16.7002 3.75898 16.7335 3.78398C16.7835 3.81732 16.8252 3.85065 16.8752 3.87565C16.9335 3.90898 17.0002 3.94232 17.0668 3.97565C17.1335 4.00898 17.2002 4.03398 17.2585 4.05065L15.8418 5.47565ZM17.8335 3.48398L17.5668 3.75065C17.5502 3.77565 17.5252 3.78398 17.5002 3.78398C17.4918 3.78398 17.4835 3.78398 17.4752 3.78398C16.8752 3.60898 16.4002 3.13398 16.2252 2.53398C16.2168 2.50065 16.2252 2.46732 16.2502 2.44232L16.5252 2.16732C16.9752 1.71732 17.4002 1.72565 17.8418 2.16732C18.0668 2.39232 18.1752 2.60898 18.1752 2.82565C18.1668 3.04232 18.0585 3.25898 17.8335 3.48398Z" fill="white"/>
                                    <path d="M7.49994 8.65026C8.5953 8.65026 9.48327 7.76229 9.48327 6.66693C9.48327 5.57156 8.5953 4.68359 7.49994 4.68359C6.40457 4.68359 5.5166 5.57156 5.5166 6.66693C5.5166 7.76229 6.40457 8.65026 7.49994 8.65026Z" fill="white"/>
                                    <path d="M17.4748 6.66602H17.0832V10.5077L16.9748 10.416C16.3248 9.85768 15.2748 9.85768 14.6248 10.416L11.1582 13.391C10.5082 13.9493 9.45817 13.9493 8.80817 13.391L8.52484 13.1577C7.93317 12.641 6.9915 12.591 6.32484 13.041L3.20817 15.1327C3.02484 14.666 2.9165 14.1243 2.9165 13.491V6.50768C2.9165 4.15768 4.15817 2.91602 6.50817 2.91602H13.3332V2.52435C13.3332 2.19102 13.3915 1.90768 13.5248 1.66602H6.50817C3.47484 1.66602 1.6665 3.47435 1.6665 6.50768V13.491C1.6665 14.3994 1.82484 15.191 2.13317 15.8577C2.84984 17.441 4.38317 18.3327 6.50817 18.3327H13.4915C16.5248 18.3327 18.3332 16.5244 18.3332 13.491V6.47435C18.0915 6.60768 17.8082 6.66602 17.4748 6.66602Z" fill="white"/>
                                </svg>
                            </span>
                        </span>
                    </div>
                    <div class="info-user">
                        <div class="info-user-item">
                            <input name="avatar" type="text" class="d-none" value="{{ $user->avatar ?? '' }}" />
                            <div>
                                <label for="txtEmail" class="form-label">Địa chỉ email</label>
                                <input name="txtEmail" type="text" value="{{ $user->email ?? '' }}" maxlength="100" id="txtEmail" disabled="disabled" tabindex="10" class="disabled form-control" />
                            </div>
                            <div>
                                <label for="userName" class="form-label">Tên hiển thị</label>
                                <input name="name" type="text" value="{{ $user->name ?? '' }}" maxlength="100" id="userName" class="form-control" />
                            </div>
                        </div>
                    </div>
                </form>
                <button type="submit" id="save-prf" form="form-prf" class="btn">
                    Lưu thay đổi
                </button>
            </div>
            <div class="tab-pane fade" id="mg-continue" role="tabpanel" aria-labelledby="mg-continue-tab">
                <div class="list-managas row list-current-managas">
                    @if(count($readingHistory) > 0)
                        @foreach($readingHistory as $manga)
                            @php
                                $viewsCount = (int)($manga['views_count'] ?? 0);
                                $formattedViews = '';
                                if ($viewsCount >= 1000000) {
                                    $formattedViews = number_format($viewsCount / 1000000, 1) . 'M';
                                } elseif ($viewsCount >= 1000) {
                                    $formattedViews = number_format($viewsCount / 1000, 1) . 'K';
                                } else {
                                    $formattedViews = number_format($viewsCount);
                                }
                            @endphp
                            <div class="m-post col-md-6 col-xl-4">
                                <div class="p-thumb flex-shrink-0">
                                    <a title="{{ $manga['title'] }}" href="{{ route('manga.detail', ['slug' => $manga['slug']]) }}" rel="nofollow">
                                        <span class="img-poster">
                                            <img class="lzl" data-src="{{ $manga['cover_url'] }}" rel="nofollow"
                                                data-original="{{ $manga['cover_url'] }}" alt="{{ $manga['title'] }}" src="{{ asset('images/pre-load1.png') }}" width="100%" height="100%">
                                        </span>
                                    </a>
                                </div>
                                <div class="p-content flex-grow-1">
                                    <h3 class="m-name">
                                        <a href="{{ route('manga.detail', ['slug' => $manga['slug']]) }}">{{ $manga['title'] }}</a>
                                    </h3>
                                    <div class="group-star">
                                        <div class="m-star">
                                            <span class="star-rating">
                                                <span style="width: {{ ($manga['rating'] / 5) * 100 }}%;"></span>
                                            </span>
                                            <span>{{ number_format($manga['rating'], 1) }}</span>
                                        </div>
                                        @if($viewsCount > 0)
                                            <span class="num-view">{{ $formattedViews }} lượt xem</span>
                                        @endif
                                    </div>
                                    @if($manga['last_chapter'])
                                        <ul class="list-chaps">
                                            <li class="chapter">
                                                <a href="{{ route('manga.detail', ['slug' => $manga['slug']]) }}/{{ $manga['last_chapter']['slug'] }}" title="{{ $manga['last_chapter']['name'] }}">
                                                    {{ $manga['last_chapter']['name'] }}
                                                </a>
                                            </li>
                                        </ul>
                                    @endif
                                    @if(isset($manga['progress']) && $manga['progress']['total'] > 0)
                                        <div class="progress">
                                            <div class="progress-bar" style="width: {{ $manga['progress']['percent'] }}%"></div>
                                            <span class="progress-text">{{ $manga['progress']['current'] }}/{{ $manga['progress']['total'] }}</span>
                                        </div>
                                    @endif
                                </div>
                                <a href="#" class="s-clear">
                                    <i class="icon-close-circle clear-reading-manga-btn" data-id="{{ $manga['id'] }}"></i>
                                </a>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center fst-italic mt-5 col-12">Bạn chưa đọc truyện nào. Tìm <a href="/hot-nhat?type=all" class="text-decoration-underline">truyện hay nhất</a></div>
                    @endif
                </div>
                @if($totalReadingPages > 1)
                    <ul class="pagination" data-count-page="{{ $totalReadingPages }}">
                        @if($readingPage > 1)
                            <li data-page="-1">
                                <a class="prev-page" href="{{ url('/tai-khoan?reading_page=' . ($readingPage - 1)) }}#reading" title="" data-page="{{ $readingPage - 1 }}">
                                    <i class="icon-arrow-left"></i>
                                </a>
                            </li>
                        @endif
                        @php
                            $startPage = max(1, $readingPage - 2);
                            $endPage = min($totalReadingPages, $readingPage + 2);
                        @endphp
                        @for($i = $startPage; $i <= $endPage; $i++)
                            <li data-page="{{ $i }}">
                                <a class="{{ $i == $readingPage ? 'active' : '' }}" href="{{ url('/tai-khoan?reading_page=' . $i) }}#reading" title="" data-page="{{ $i }}">
                                    {{ $i }}
                                </a>
                            </li>
                        @endfor
                        @if($readingPage < $totalReadingPages)
                            <li data-page="+1">
                                <a class="next-page" href="{{ url('/tai-khoan?reading_page=' . ($readingPage + 1)) }}#reading" title="" data-page="{{ $readingPage + 1 }}">
                                    <i class="icon-arrow-right"></i>
                                </a>
                            </li>
                        @endif
                    </ul>
                @endif
            </div>
            <div class="tab-pane fade" id="mg-save" role="tabpanel" aria-labelledby="mg-save-tab">
                <div class="list-managas row list-following-managas">
                    @if(count($followingMangas) > 0)
                        @foreach($followingMangas as $manga)
                            <div class="col-12 col-md-6 col-lg-4 col-xl-3">
                                <div class="m-post">
                                    <div class="p-thumb flex-shrink-0">
                                        <a title="{{ $manga['title'] }}" href="{{ route('manga.detail', ['slug' => $manga['slug']]) }}">
                                            <span class="img-poster">
                                                <img class="lzl" data-src="{{ $manga['cover_url'] }}" rel="nofollow"
                                                    data-original="{{ $manga['cover_url'] }}" alt="{{ $manga['title'] }}" src="{{ asset('images/pre-load1.png') }}" width="100%" height="100%">
                                            </span>
                                        </a>
                                    </div>
                                    <div class="p-content flex-grow-1">
                                        <h3 class="m-name">
                                            <a href="{{ route('manga.detail', ['slug' => $manga['slug']]) }}">{{ $manga['title'] }}</a>
                                        </h3>
                                        <div class="group-star">
                                            <div class="m-star">
                                                <span class="star-rating">
                                                    <span style="width: {{ ($manga['rating'] / 5) * 100 }}%;"></span>
                                                </span>
                                                <span>{{ number_format($manga['rating'], 1) }}</span>
                                            </div>
                                        </div>
                                        @if($manga['last_chapter'])
                                            <ul class="list-chaps">
                                                <li class="chapter">
                                                    <a href="{{ route('manga.detail', ['slug' => $manga['slug']]) }}/{{ $manga['last_chapter']['slug'] }}" title="{{ $manga['last_chapter']['name'] }}">
                                                        {{ $manga['last_chapter']['name'] }}<span>{{ $manga['last_chapter']['updated_at'] }}</span>
                                                    </a>
                                                </li>
                                            </ul>
                                        @endif
                                        <button class="unfollow-manga-btn btn btn-sm btn-outline-danger mt-2" data-id="{{ $manga['id'] }}">Bỏ theo dõi</button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center fst-italic mt-5">Bạn chưa theo dõi truyện nào.</div>
                    @endif
                </div>
                @if($totalFollowingPages > 1)
                    <ul class="pagination" data-count-page="{{ $totalFollowingPages }}">
                        @if($followingPage > 1)
                            <li data-page="-1">
                                <a class="prev-page" href="{{ url('/tai-khoan?following_page=' . ($followingPage - 1)) }}#following" title="" data-page="{{ $followingPage - 1 }}">
                                    <i class="icon-arrow-left"></i>
                                </a>
                            </li>
                        @endif
                        @php
                            $startPage = max(1, $followingPage - 2);
                            $endPage = min($totalFollowingPages, $followingPage + 2);
                        @endphp
                        @for($i = $startPage; $i <= $endPage; $i++)
                            <li data-page="{{ $i }}">
                                <a class="{{ $i == $followingPage ? 'active' : '' }}" href="{{ url('/tai-khoan?following_page=' . $i) }}#following" title="" data-page="{{ $i }}">
                                    {{ $i }}
                                </a>
                            </li>
                        @endfor
                        @if($followingPage < $totalFollowingPages)
                            <li data-page="+1">
                                <a class="next-page" href="{{ url('/tai-khoan?following_page=' . ($followingPage + 1)) }}#following" title="" data-page="{{ $followingPage + 1 }}">
                                    <i class="icon-arrow-right"></i>
                                </a>
                            </li>
                        @endif
                    </ul>
                @endif
            </div>
        </div>
    </div>
</main>
@endsection

@push('scripts')
<script>
    // Handle tab switching with hash
    let page = new URLSearchParams(window.location.search).get('page') || 1;

    $('.nav-item').on('click', function (e) {
        const newUrl = new URL(window.location.href);
        // Reset pagination when switching tabs
        newUrl.searchParams.delete('reading_page');
        newUrl.searchParams.delete('following_page');

        let hash;
        switch ($(this).find('a').attr('data-bs-target')) {
            case '#mg-continue':
                hash = '#reading';
                break;
            case '#mg-save':
                hash = '#following';
                break;
            default:
                hash = '#general';
                break;
        }

        newUrl.hash = hash;
        window.history.pushState({}, document.title, newUrl);
    });

    function handleChooseTab() {
        let hash = window.location.hash;

        switch (hash) {
            case '#reading':
                hash = 'mg-continue';
                break;
            case '#following':
                hash = 'mg-save';
                break;
            default:
                hash = 'general';
                break;
        }

        $('#general').removeClass('active').removeClass('show');
        $('#mg-save').removeClass('active').removeClass('show');
        $('#mg-continue').removeClass('active').removeClass('show');

        $('#general-tab').removeClass('active');
        $('#mg-save-tab').removeClass('active');
        $('#mg-continue-tab').removeClass('active');

        $(`#${hash}-tab`).addClass('active');
        $(`#${hash}`).addClass('active').addClass('show');
    }

    handleChooseTab();

    window.onhashchange = function(event) {
        handleChooseTab();
    };

    // Handle avatar upload
    $('input#changeAvatar').on('change', async function (e) {
        e.preventDefault();
        const file = this.files[0];

        if (file && !file.type.startsWith('image/')) {
            if (typeof alertNoti === 'function') {
                alertNoti('Vui lòng chọn file hình ảnh');
            } else {
                alert('Vui lòng chọn file hình ảnh');
            }
            return;
        }

        if (file) {
            const formData = new FormData();
            formData.append('avatar', file);
            formData.append('_token', '{{ csrf_token() }}');

            try {
                const response = await $.ajax({
                    type: 'POST',
                    url: '{{ route("account.upload-avatar") }}',
                    data: formData,
                    processData: false,
                    contentType: false,
                });

                if (response && response.status === 'success') {
                    $('#avatar-temp-edit')
                        .css({
                            'background-image': `url(${response.data.url})`,
                            'background-size': 'cover',
                            'background-position': 'center',
                            'background-repeat': 'no-repeat',
                        })
                        .empty();
                    $('input[name="avatar"]').val(response.data.url);
                } else {
                    if (typeof alertNoti === 'function') {
                        alertNoti('Lỗi! Vui lòng thử ảnh khác');
                    } else {
                        alert('Lỗi! Vui lòng thử ảnh khác');
                    }
                }
            } catch (error) {
                console.error('Upload error:', error);
                if (typeof alertNoti === 'function') {
                    alertNoti('Có lỗi xảy ra khi upload ảnh');
                } else {
                    alert('Có lỗi xảy ra khi upload ảnh');
                }
            }
        }
    });

    // Handle profile update
    $('#form-prf').on('submit', async function (e) {
        e.preventDefault();
        const formData = Object.fromEntries(new FormData(e.target).entries());
        delete formData.uploadAvatar;

        try {
            const response = await $.ajax({
                type: 'PUT',
                url: '{{ route("account.update") }}',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                data: formData,
                dataType: 'json',
            });

            if (response && response.status === 'success') {
                if (typeof alertNoti === 'function') {
                    alertNoti('Cập nhật thông tin thành công');
                } else {
                    alert('Cập nhật thông tin thành công');
                }
                if (response.data && response.data.user) {
                    // Update session if needed
                    location.reload();
                }
            }
        } catch (error) {
            console.error('Update error:', error);
            if (error.responseJSON && error.responseJSON.message) {
                if (typeof alertNoti === 'function') {
                    alertNoti(error.responseJSON.message);
                } else {
                    alert(error.responseJSON.message);
                }
            } else {
                if (typeof alertNoti === 'function') {
                    alertNoti('Có lỗi xảy ra khi cập nhật thông tin');
                } else {
                    alert('Có lỗi xảy ra khi cập nhật thông tin');
                }
            }
        }
    });

    // Handle clear reading history
    $('.clear-reading-manga-btn').on('click', async function (e) {
        e.preventDefault();
        const mangaId = $(this).data('id');

        try {
            const response = await $.ajax({
                type: 'POST',
                url: '{{ route("account.clear-reading") }}',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                data: { manga_id: mangaId },
                dataType: 'json',
            });

            if (response && response.status === 'success') {
                if (typeof alertNoti === 'function') {
                    alertNoti('Xóa lịch sử đọc thành công');
                } else {
                    alert('Xóa lịch sử đọc thành công');
                }
                location.reload();
            }
        } catch (error) {
            console.error('Clear reading error:', error);
            if (error.responseJSON && error.responseJSON.message) {
                if (typeof alertNoti === 'function') {
                    alertNoti(error.responseJSON.message);
                } else {
                    alert(error.responseJSON.message);
                }
            } else {
                if (typeof alertNoti === 'function') {
                    alertNoti('Có lỗi xảy ra');
                } else {
                    alert('Có lỗi xảy ra');
                }
            }
        }
    });

    // Handle unfollow
    $('.unfollow-manga-btn').on('click', async function (e) {
        e.preventDefault();
        const mangaSlug = $(this).data('slug');

        try {
            const response = await $.ajax({
                type: 'POST',
                url: '/truyen-tranh/' + mangaSlug + '/follow',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                data: {},
                dataType: 'json',
            });

            if (response && response.status === 'success') {
                if (typeof alertNoti === 'function') {
                    alertNoti('Bỏ theo dõi thành công');
                } else {
                    alert('Bỏ theo dõi thành công');
                }
                location.reload();
            }
        } catch (error) {
            console.error('Unfollow error:', error);
            if (error.responseJSON && error.responseJSON.message) {
                if (typeof alertNoti === 'function') {
                    alertNoti(error.responseJSON.message);
                } else {
                    alert(error.responseJSON.message);
                }
            } else {
                if (typeof alertNoti === 'function') {
                    alertNoti('Có lỗi xảy ra');
                } else {
                    alert('Có lỗi xảy ra');
                }
            }
        }
    });
</script>
@endpush

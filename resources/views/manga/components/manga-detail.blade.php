<div id="manga-detail">
    <div class="main-info">
        <div class="row">
            <div class="col-12 col-lg-7 col-xxl-8">
                <div class="left-info d-block d-lg-flex">
                    <div class="col-image-wrapper flex-shrink-0">
                        @php
                            $coverUrl = $manga['cover_url'] ?? asset('images/pre-load1.png');
                            $mangaName = $manga['name'] ?? 'Đang cập nhật';
                        @endphp
                        <div class="col-image" style="background-image: url('{{ $coverUrl }}');">
                            <img src="{{ $coverUrl }}" alt="{{ $mangaName }}">
                        </div>
                        <a href="" class="report" data-bs-toggle="modal" data-bs-target="#ReportModal" rel="nofollow">
                            <i class="icon-info-circle"></i>
                            Báo cáo lỗi</a>
                    </div>
                    <div class="col-main-info flex-grow-1">
                        <h1 class="title title-detail"><a href="/truyen-tranh/{{ $mangaSlug }}">{{ $mangaName }}</a></h1>
                        <div class="m-star">
                            @php
                                $rating = (float)($avgRating ?? 0);
                                $ratingPercent = ($rating / 5) * 100;
                            @endphp
                            <span class="star-rating">
                                <span style="width: {{ $ratingPercent }}%;"></span>
                            </span>
                            <span>{{ number_format($rating, 1) }}</span>
                        </div>
                        <div class="kind">
                            <span class="label">Thể loại: </span>
                            @if(isset($manga['type']))
                                @php
                                    $typeName = strtolower($manga['type']['name'] ?? '');
                                    $flagImage = 'jp.png';
                                    if (strpos($typeName, 'manhua') !== false || strpos($typeName, 'truyện màu') !== false) {
                                        $flagImage = 'cn.png';
                                    } elseif (strpos($typeName, 'manhwa') !== false) {
                                        $flagImage = 'kr.png';
                                    }
                                @endphp
                                <a href="/the-loai/{{ $manga['type']['slug'] ?? 'manga' }}">
                                    <img src="{{ asset('images/' . $flagImage) }}" class="m-flag" /> {{ $manga['type']['name'] ?? 'Manga' }}</a>
                            @else
                                <a href="/the-loai/manga">
                                    <img src="{{ asset('images/jp.png') }}" class="m-flag" /> Manga</a>
                            @endif
                        </div>
                        <div class="m-tags">
                            <span class="label">Tags:</span>
                            @if(isset($manga['tags']) && count($manga['tags']) > 0)
                                @foreach($manga['tags'] as $tag)
                                    <a href="/genre/{{ $tag['slug'] }}" class="" title="" tabindex="-1">{{ $tag['name'] }}</a>
                                @endforeach
                            @else
                                <span>Đang cập nhật</span>
                            @endif
                        </div>
                        <div class="list-info">
                            <span class="label">Thông tin:</span>
                            <div class="info-group">
                                <div class="status">
                                    <span>Tình trạng: </span>
                                    <p class="">{{ $manga['status'] ?? 'Đang cập nhật' }}</p>
                                </div>
                                <div class="author">
                                    <span>Tác giả: </span>
                                    <p class="">{{ is_array($manga['author'] ?? []) ? implode(', ', $manga['author']) : ($manga['author'] ?? 'Đang cập nhật') }}</p>
                                </div>
                                <div class="update">
                                    <span>Cập nhật: </span>
                                    <p class="">{{ $manga['updated_at'] ? \Carbon\Carbon::parse($manga['updated_at'])->format('H:i d/m/Y') : 'Đang cập nhật' }}</p>
                                </div>
                                <div class="view">
                                    <span>Lượt xem: </span>
                                    @php
                                        $tv = (int)($totalViews ?? 0);
                                        $tvText = $tv >= 1000000 ? number_format($tv / 1000000, 2) . 'M'
                                            : ($tv >= 1000 ? number_format($tv / 1000, 2) . 'K' : number_format($tv));
                                    @endphp
                                    <p class="">{{ $tvText }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="sort-des">
                            <h2 class="label">Tóm tắt nội dung truyện {{ $mangaName }} tại HangTruyen</h2>
                            <div class="line-clamp">{!! $manga['description'] ?? '<p>Đang cập nhật</p>' !!}</div>
                            <a class="show-full-des color" href="#" data-bs-toggle="modal" data-bs-target="#fullDescriptionModal">Đọc thêm</a>
                        </div>
                        <div class="read-action">
                            @php
                                $chapters = $manga['chapters'] ?? [];
                                $firstChapter = count($chapters) > 0 ? $chapters[0] : null;
                                $firstChapterUrl = $firstChapter ? route('manga.chapter', ['mangaSlug' => $mangaSlug, 'chapterSlug' => $firstChapter['slug']]) : '#';
                            @endphp
                            @if($firstChapter)
                                <a id="btn-read_next" href="{{ $firstChapterUrl }}" class="btn btn-read" hidden>Đọc tiếp</a>
                                <a href="{{ $firstChapterUrl }}" class="btn btn-read">Đọc ngay</a>
                            @else
                                <a href="#" class="btn btn-read" disabled>Đang cập nhật</a>
                            @endif
                            <a href="#" class="manga-save{{ ($isFollowing ?? false) ? ' active' : '' }}{{ auth()->check() ? '' : ' login-required' }}" title="Theo dõi" rel="nofollow" {{ auth()->check() ? '' : 'data-bs-toggle="modal" data-bs-target="#loginModal"' }}>
                                <i class="icon-bookmark"></i>
                            </a>
                            @php
                                $followsCountText = ($followsCount ?? 0) >= 1000000 
                                    ? number_format(($followsCount ?? 0) / 1000000, 1) . 'M'
                                    : (($followsCount ?? 0) >= 1000 
                                        ? number_format(($followsCount ?? 0) / 1000, 1) . 'K'
                                        : number_format($followsCount ?? 0));
                            @endphp
                            <span class="num-follow">{{ $followsCountText }} lượt theo dõi</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-5 col-xxl-4">
                <div class="right-info">
                    <div class="rating">
                        <div class="top-rating">
                            <p>Cho chúng mình biết cảm nhận của bạn về truyện này nhé!</p>
                            <img src="{{ asset('images/details/bg-vote-rate.png') }}">
                        </div>
                        <div class="vote-rate">
                            <svg xmlns="http://www.w3.org/2000/svg" class="bg-vote" width="416" height="190" viewBox="0 0 416 190" fill="none">
                                <path d="M0 42.5C0 35.8726 5.37258 30.5 12 30.5H225.29C228.613 30.5 231.788 29.1215 234.057 26.6929L255.443 3.80706C257.712 1.37854 260.887 0 264.21 0H404C410.627 0 416 5.37258 416 12V178C416 184.627 410.627 190 404 190H12C5.37258 190 0 184.627 0 178V42.5Z" fill="#596FB7"></path>
                            </svg>
                            <div class="options">
                                <span class="so-bad">
                                    <a href="javascript:void(0)" data-vote="1" class="manga-vote-btn{{ auth()->check() ? '' : ' login-required' }}{{ ($userRating && $userRating != 1) ? ' un-select' : '' }}" {{ auth()->check() ? '' : 'data-bs-toggle="modal" data-bs-target="#loginModal"' }}>
                                        <span class="bg">
                                            {!! file_get_contents(public_path('images/details/vote-1.svg')) !!}
                                        </span>
                                        Tệ
                                    </a>
                                </span>
                                <span class="bad">
                                    <a href="javascript:void(0)" data-vote="2" class="manga-vote-btn{{ auth()->check() ? '' : ' login-required' }}{{ ($userRating && $userRating != 2) ? ' un-select' : '' }}" {{ auth()->check() ? '' : 'data-bs-toggle="modal" data-bs-target="#loginModal"' }}>
                                        <span class="bg">
                                            {!! file_get_contents(public_path('images/details/vote-2.svg')) !!}
                                        </span>
                                        Hơi tệ
                                    </a>
                                </span>
                                <span class="normal">
                                    <a href="javascript:void(0)" data-vote="3" class="manga-vote-btn{{ auth()->check() ? '' : ' login-required' }}{{ ($userRating && $userRating != 3) ? ' un-select' : '' }}" {{ auth()->check() ? '' : 'data-bs-toggle="modal" data-bs-target="#loginModal"' }}>
                                        <span class="bg">
                                            {!! file_get_contents(public_path('images/details/vote-3.svg')) !!}
                                        </span>
                                        Bình thường
                                    </a>
                                </span>
                                <span class="good">
                                    <a href="javascript:void(0)" data-vote="4" class="manga-vote-btn{{ auth()->check() ? '' : ' login-required' }}{{ ($userRating && $userRating != 4) ? ' un-select' : '' }}" {{ auth()->check() ? '' : 'data-bs-toggle="modal" data-bs-target="#loginModal"' }}>
                                        <span class="bg">
                                            {!! file_get_contents(public_path('images/details/vote-4.svg')) !!}
                                        </span>
                                        Hay
                                    </a>
                                </span>
                                <span class="very-good">
                                    <a href="javascript:void(0)" data-vote="5" class="manga-vote-btn{{ auth()->check() ? '' : ' login-required' }}{{ ($userRating && $userRating != 5) ? ' un-select' : '' }}" {{ auth()->check() ? '' : 'data-bs-toggle="modal" data-bs-target="#loginModal"' }}>
                                        <span class="bg">
                                            {!! file_get_contents(public_path('images/details/vote-5.svg')) !!}
                                        </span>
                                        Tuyệt vời
                                    </a>
                                </span>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

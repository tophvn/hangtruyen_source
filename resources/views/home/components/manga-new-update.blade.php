<section id="manga-new_update">
    <div class="list-manga splide">
        <div class="group-title">
            <h2 class="m-title title">Mới cập nhật
                <span class="sub">Truyện mới được cập nhật.</span>
            </h2>
            <div class="swiper-btn__group">
                <a href="/new?page=2" class="view-all">Xem tất cả</a>
                <div class="splide__arrows position-relative">
                    <button class="splide__arrow splide__arrow--prev">
                        <i class="icon-arrow-left"></i>
                    </button>
                    <button class="splide__arrow splide__arrow--next">
                        <i class="icon-arrow-right"></i>
                    </button>
                </div>
            </div>
        </div>
        <div class="splide__track">
            <div class="splide__list">
                @forelse($recentlyUpdated ?? [] as $manga)
                    @php
                        $mangaUrl = route('manga.detail', ['slug' => $manga['slug']]);
                        $chapters = $manga['chapters'] ?? [];
                        $views = $manga['views'] ?? null;
                        $rating = $manga['rating'] ?? 0;
                    @endphp
                    <div class="m-post splide__slide">
                        <div class="p-thumb flex-shrink-0">
                            <a title="{{ $manga['name'] }}" href="{{ $mangaUrl }}">
                                <span class="img-poster">
                                    <img class="lzl" 
                                        data-src="{{ $manga['cover_url'] }}" 
                                        rel="nofollow"
                                        data-original="{{ $manga['cover_url'] }}" 
                                        alt="{{ $manga['name'] }}" 
                                        src="{{ asset('images/pre-load1.png') }}" 
                                        width="100%" 
                                        height="100%">
                                </span>
                            </a>
                        </div>
                        <div class="p-content flex-grow-1">
                            <h3 class="m-name">
                                <a href="{{ $mangaUrl }}">{{ $manga['name'] }}</a>
                            </h3>
                            <div class="group-star">
                                <div class="m-star">
                                    <span class="star-rating">
                                        <span style="width: {{ ($rating / 5) * 100 }}%;"></span>
                                    </span>
                                    <span>{{ number_format($rating, 1) }}</span>
                                </div>
                                @php
                                    $viewsCount = (int)($views ?? 0);
                                    if ($viewsCount >= 1000000) {
                                        $formattedViews = number_format($viewsCount / 1000000, 2) . 'M';
                                    } elseif ($viewsCount >= 1000) {
                                        $formattedViews = number_format($viewsCount / 1000, 2) . 'K';
                                    } else {
                                        $formattedViews = number_format($viewsCount);
                                    }
                                @endphp
                                <span class="num-view">{{ $formattedViews }} lượt xem</span>
                            </div>
                            @if(count($chapters) > 0)
                                <ul class="list-chaps">
                                    @foreach(array_slice($chapters, 0, 2) as $chapter)
                                        @php
                                            $chapterTime = $chapter['updated_at'] 
                                                ? formatVietnameseTime($chapter['updated_at'])
                                                : '';
                                            $chapterNumber = $chapter['number'] ?? '';
                                            $chapterSlug = 'chapter-' . $chapterNumber;
                                            $chapterUrl = route('manga.detail', ['slug' => $manga['slug']]) . '/' . $chapterSlug;
                                        @endphp
                                        <li class="chapter">
                                            <a href="{{ $chapterUrl }}" title="{{ $chapter['name'] }}">
                                                {{ $chapter['name'] }}<span>{{ $chapterTime }}</span>
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="m-post splide__slide">
                        <p>Đang tải dữ liệu...</p>
                    </div>
                @endforelse
            </div>
        </div>
        @if(isset($recentlyUpdatedMetadata) && $recentlyUpdatedMetadata)
            <ul class="pagination">
                @for($i = 1; $i <= min(5, $recentlyUpdatedMetadata['total_pages'] ?? 1); $i++)
                    <li class="{{ $i == 1 ? 'active' : '' }}" data-page="{{ $i }}">
                        <a href="{{ $i == 1 ? url('/') : "/new?page={$i}" }}">{{ $i }}</a>
                    </li>
                @endfor
            </ul>
        @endif
    </div>
</section>

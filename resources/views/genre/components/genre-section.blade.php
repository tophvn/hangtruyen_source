@php
    $genre = $genre ?? [];
    $mangas = $genre['mangas'] ?? [];
@endphp

<section class="mb-3">
    <div class="m-suggest splide splide-navtop">
        <div class="group-title">
            <h2 class="m-title title">{{ $genre['name'] }}<span class="sub">Gợi ý theo sở thích của bạn.</span></h2>
            <div class="swiper-btn__group">
                <a href="{{ url('/the-loai/' . $genre['slug']) }}" class="view-all">Xem tất cả</a>
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
                @foreach($mangas as $manga)
                    <div class="m-post horizontal splide__slide">
                        <div class="p-thumb flex-shrink-0">
                            <a title="{{ $manga['title'] }}" href="/truyen-tranh/{{ $manga['slug'] }}">
                                <span class="img-poster">
                                    <img class="lzl" data-src="{{ $manga['posterPath'] }}" rel="nofollow"
                                        data-original="{{ $manga['posterPath'] }}" alt="{{ $manga['title'] }}" src="{{ asset('images/pre-load1.png') }}" width="100%" height="100%">
                                </span>
                            </a>
                        </div>
                        <div class="p-content flex-grow-1">
                            <h3 class="m-name">
                                <a href="/truyen-tranh/{{ $manga['slug'] }}">{{ $manga['title'] }}</a>
                            </h3>
                            <div class="group-star">
                                <div class="m-star">
                                    <span class="star-rating">
                                        <span style="width: {{ ($manga['avgVote'] ?? 0) * 20 }}%;"></span>
                                    </span>
                                    <span>{{ number_format($manga['avgVote'] ?? 0, 1) }}</span>
                                </div>
                            </div>
                            <ul class="list-chaps">
                                @if(isset($manga['chapters']) && is_array($manga['chapters']) && count($manga['chapters']) > 0)
                                    @foreach(array_slice($manga['chapters'], 0, 1) as $chapter)
                                        <li class="chapter">
                                            <a data-id="{{ $chapter['id'] ?? '' }}" href="{{ route('manga.chapter', ['mangaSlug' => $manga['slug'], 'chapterSlug' => $chapter['slug']]) }}" title="{{ $chapter['name'] }}">
                                                {{ $chapter['name'] }}<span>{{ $chapter['releasedAt'] ?? '' }}</span>
                                            </a>
                                        </li>
                                    @endforeach
                                @else
                                    <li class="chapter">
                                        <span>Đang cập nhật</span>
                                    </li>
                                @endif
                            </ul>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>

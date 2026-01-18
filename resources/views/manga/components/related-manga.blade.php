<div id="cm-related" class="pb-3">
    <h3 class="m-title title">TRUYỆN LIÊN QUAN<span class="sub">Truyện được mọi người yêu thích.</span></h3>
    <div class="tab-content">
        <ul class="list-unstyled">
            @if(isset($relatedMangas) && $relatedMangas->count() > 0)
                @foreach($relatedMangas as $relatedManga)
                    @php
                        $mangaUrl = route('manga.detail', ['slug' => $relatedManga['slug']]);
                        $ratingPercent = ($relatedManga['rating'] / 5) * 100;
                        $coverUrl = $relatedManga['cover_url'] ?? asset('images/pre-load1.png');
                    @endphp
                    <li>
                        <div class="p-thumb flex-shrink-0">
                            <a title="{{ $relatedManga['title'] }}" href="{{ $mangaUrl }}">
                                <span class="img-poster">
                                    <img class="lzl" data-src="{{ $coverUrl }}" rel="nofollow"
                                        data-original="{{ $coverUrl }}" alt="{{ $relatedManga['title'] }}" src="{{ asset('images/pre-load1.png') }}" 
                                        loading="lazy" decoding="async" width="100%" height="100%">
                                </span>
                            </a>
                        </div>
                        <div class="p-content flex-grow-1">
                            <h3 class="m-name">
                                <a href="{{ $mangaUrl }}">{{ $relatedManga['title'] }}</a>
                            </h3>
                            <div class="group-star">
                                <div class="m-star">
                                    <span class="star-rating">
                                        <span style="width: {{ $ratingPercent }}%;"></span>
                                    </span>
                                    <span>{{ number_format($relatedManga['rating'], 1) }}</span>
                                </div>
                                <span class="num-view">{{ $relatedManga['views_count'] }}</span>
                            </div>
                            @if(isset($relatedManga['last_chapter']) && $relatedManga['last_chapter'])
                                <ul class="list-chaps">
                                    <li class="chapter">
                                        <a href="{{ route('manga.chapter', ['mangaSlug' => $relatedManga['slug'], 'chapterSlug' => $relatedManga['last_chapter']['slug']]) }}" title="{{ $relatedManga['last_chapter']['name'] }}">
                                            {{ $relatedManga['last_chapter']['name'] }}<span>{{ $relatedManga['last_chapter']['updated_at'] ?? '' }}</span>
                                        </a>
                                    </li>
                                </ul>
                            @endif
                        </div>
                    </li>
                @endforeach
            @else
                <li class="text-center py-3">
                    <p class="text-muted">Chưa có truyện liên quan</p>
                </li>
            @endif
        </ul>
    </div>
</div>

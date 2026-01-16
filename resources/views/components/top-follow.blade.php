<section id="top-follow">
    <h2 class="m-title title">Top theo dõi<span class="sub">Truyện mới được cập nhật.</span></h2>
    <ul class="nav nav-tabs" role="tablist">
        <li class="nav-item" role="presentation">
            <a href="" class="nav-link active" id="day-tab" data-bs-toggle="tab" data-bs-target="#day" role="tab" aria-controls="day" aria-selected="true">Ngày</a>
        </li>
        <li class="nav-item" role="presentation">
            <a href="" class="nav-link" id="week-tab" data-bs-toggle="tab" data-bs-target="#week" role="tab" aria-controls="week" aria-selected="false">Tuần</a>
        </li>
        <li class="nav-item" role="presentation">
            <a href="" class="nav-link" id="month-tab" data-bs-toggle="tab" data-bs-target="#month" role="tab" aria-controls="month" aria-selected="false">Tháng</a>
        </li>
    </ul>
    <div class="tab-content" id="TopFollow">
        <div class="tab-pane fade show active" id="day" role="tabpanel" aria-labelledby="day-tab">
            <ul class="list-unstyled">
                @forelse($topFollowDay ?? [] as $manga)
                    <li>
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
                                <span class="num-view">{{ $manga['views_formatted'] }} lượt xem</span>
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
                        </div>
                        <span class="rank rank-{{ $manga['rank'] }}">{{ str_pad($manga['rank'], 2, '0', STR_PAD_LEFT) }}</span>
                    </li>
                @empty
                    <li class="text-center fst-italic py-3">Chưa có dữ liệu</li>
                @endforelse
            </ul>
        </div>
        <div class="tab-pane fade" id="week" role="tabpanel" aria-labelledby="week-tab">
            <ul class="list-unstyled">
                @forelse($topFollowWeek ?? [] as $manga)
                    <li>
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
                                <span class="num-view">{{ $manga['views_formatted'] }} lượt xem</span>
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
                        </div>
                        <span class="rank rank-{{ $manga['rank'] }}">{{ str_pad($manga['rank'], 2, '0', STR_PAD_LEFT) }}</span>
                    </li>
                @empty
                    <li class="text-center fst-italic py-3">Chưa có dữ liệu</li>
                @endforelse
            </ul>
        </div>
        <div class="tab-pane fade" id="month" role="tabpanel" aria-labelledby="month-tab">
            <ul class="list-unstyled">
                @forelse($topFollowMonth ?? [] as $manga)
                    <li>
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
                                <span class="num-view">{{ $manga['views_formatted'] }} lượt xem</span>
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
                        </div>
                        <span class="rank rank-{{ $manga['rank'] }}">{{ str_pad($manga['rank'], 2, '0', STR_PAD_LEFT) }}</span>
                    </li>
                @empty
                    <li class="text-center fst-italic py-3">Chưa có dữ liệu</li>
                @endforelse
            </ul>
        </div>
    </div>
</section>

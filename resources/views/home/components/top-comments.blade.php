<section class="container">
    <div class="row">
        <div class="col-12 col-xl-3">
            <div class="banner-cmt d-none d-xl-block">
                <img class="" src="{{ asset('images/home/banner-cmt.png') }}" alt="Banner bình luận">
            </div>
        </div>
        <div class="col-12 col-xl-9">
            <div class="top-comments splide">
                <h3 class="title" style="display: block !important;">Bình luận mới nhất</h3>
                <div class="splide__track">
                    <ul class="splide__list">
                        @forelse($topComments ?? [] as $comment)
                            @php
                                $mangaUrl = $comment['manga']['slug'] 
                                    ? route('manga.detail', ['slug' => $comment['manga']['slug']])
                                    : '#';
                                $commentUrl = $mangaUrl . '#cmt-' . $comment['id'];
                                $timeAgo = formatVietnameseTime($comment['created_at']);
                            @endphp
                            <li class="splide__slide">
                                <div class="tc-item">
                                    <div class="tc-v">
                                        <div class="tc-header">
                                            <div class="user-avatar">
                                                <img alt="{{ $comment['user']['name'] }}" 
                                                     rel="nofollow" 
                                                     src="{{ $comment['user']['avatar'] }}" />
                                            </div>
                                            <div class="info">
                                                <div class="user-name">{{ $comment['user']['name'] }}</div>
                                                <span class="tc-time">{{ $timeAgo }}</span>
                                            </div>
                                        </div>
                                        <div class="tc-thumb">
                                            <a class="tc-thumbnail" 
                                               title="{{ $comment['manga']['title'] }}" 
                                               href="{{ $mangaUrl }}">
                                                <img class="lzl" 
                                                     data-src="{{ $comment['manga']['cover_url'] }}" 
                                                     rel="nofollow"
                                                     alt="{{ $comment['manga']['title'] }}" 
                                                     src="{{ asset('images/pre-load1.png') }}" 
                                                     width="100%" 
                                                     height="100%">
                                            </a>
                                        </div>
                                    </div>
                                    <div class="cmt-description">
                                        {{ Str::limit($comment['content'], 100) }}
                                    </div>
                                    <div class="tc-footer">
                                        <a href="{{ $mangaUrl }}" class="tc-name">{{ $comment['manga']['title'] }}</a>
                                    </div>
                                    <a class="tc-link" href="{{ $commentUrl }}"></a>
                                </div>
                            </li>
                        @empty
                            <li class="splide__slide">
                                <div class="tc-item">
                                    <p class="text-center p-3">Chưa có bình luận nào</p>
                                </div>
                            </li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

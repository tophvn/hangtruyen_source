@php($demoMangaUrl = route('manga.detail', ['slug' => 'gto-fury-of-death-yamada']))
<section id="m-finish" class="container">
    <div class="m-suggest splide splide-navtop">
        <div class="group-title">
            <h2 class="m-title title">ĐÃ HOÀN THÀNH<span class="sub">Gợi ý theo sở thích của bạn.</span></h2>
            <div class="swiper-btn__group">
                <a href="/da-hoan-thanh" class="view-all">Xem tất cả</a>
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
                <!-- Truyện 1 -->
                <div class="m-post horizontal splide__slide">
                    <div class="p-thumb flex-shrink-0">
                        <a title="Springtime for Blossom" href="{{ $demoMangaUrl }}">
                            <span class="img-poster">
                                <img class="lzl" data-src="https://prvhtr.mgbucket.xyz/posters/db/e8/springtime-for-blossom.png" rel="nofollow"
                                    data-original="https://prvhtr.mgbucket.xyz/posters/db/e8/springtime-for-blossom.png" alt="Springtime for Blossom" src="{{ asset('images/pre-load1.png') }}" width="100%" height="100%">
                            </span>
                        </a>
                    </div>
                    <div class="p-content flex-grow-1">
                        <h3 class="m-name">
                            <a href="{{ $demoMangaUrl }}">Springtime for Blossom</a>
                        </h3>
                        <div class="group-star">
                            <div class="m-star">
                                <span class="star-rating">
                                    <span style="width: 0%;"></span>
                                </span>
                                <span>0</span>
                            </div>
                        </div>
                        <ul class="list-chaps">
                            <li class="chapter">
                                <a data-id="2065132" href="{{ $demoMangaUrl }}" title="Chapter #34 - END">
                                    Chapter #34 - END<span>5 tháng trước</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Truyện 2 -->
                <div class="m-post horizontal splide__slide">
                    <div class="p-thumb flex-shrink-0">
                        <a title="Toàn Chức Pháp Sư" href="{{ $demoMangaUrl }}">
                            <span class="img-poster">
                                <img class="lzl" data-src="https://prvhtr.mgbucket.xyz/posters/5a/7e/toan-chuc-phap-su.png" rel="nofollow"
                                    data-original="https://prvhtr.mgbucket.xyz/posters/5a/7e/toan-chuc-phap-su.png" alt="Toàn Chức Pháp Sư" src="{{ asset('images/pre-load1.png') }}" width="100%" height="100%">
                            </span>
                        </a>
                    </div>
                    <div class="p-content flex-grow-1">
                        <h3 class="m-name">
                            <a href="{{ $demoMangaUrl }}">Toàn Chức Pháp Sư</a>
                        </h3>
                        <div class="group-star">
                            <div class="m-star">
                                <span class="star-rating">
                                    <span style="width: 74%;"></span>
                                </span>
                                <span>3.7</span>
                            </div>
                        </div>
                        <ul class="list-chaps">
                            <li class="chapter">
                                <a data-id="1484811" href="{{ $demoMangaUrl }}" title="Chapter 1181">
                                    Chapter 1181<span>2 năm trước</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

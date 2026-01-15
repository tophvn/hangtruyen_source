@php($demoMangaUrl = route('manga.detail', ['slug' => 'gto-fury-of-death-yamada']))
<section class="container">
    <div class="row">
        <div class="col-12 col-xl-3">
            <div class="banner-cmt d-none d-xl-block">
                <img class="" src="{{ asset('images/home/banner-cmt.png') }}" alt="Banner bình luận">
            </div>
        </div>
        <div class="col-12 col-xl-9">
            <div class="top-comments splide">
                <h3 class="title">Bình luận mới nhất</h3>
                <div class="splide__track">
                    <ul class="splide__list">
                        <!-- Bình luận 1 -->
                        <li class="splide__slide">
                            <div class="tc-item">
                                <div class="tc-v">
                                    <div class="tc-header">
                                        <div class="user-avatar"><img alt="" rel="nofollow" src="{{ asset('images/avatars/type3/8.png') }}" /></div>
                                        <div class="info">
                                            <div class="user-name">GunGoo </div>
                                            <span class="tc-time">3 giờ trước</span>
                                        </div>
                                    </div>
                                    <div class="tc-thumb">
                                        <a class="tc-thumbnail" title="" href="{{ $demoMangaUrl }}">
                                            <img class="lzl" data-src="https://img.htrcdn.com/fast/0x150/oss.cdnfastest.com/90htr/posters/01/c5/hoan-doi-dieu-ky.png" rel="nofollow"
                                                alt="Hoán Đổi Diệu Kỳ" src="{{ asset('images/pre-load1.png') }}" width="100%" height="100%">
                                        </a>
                                    </div>
                                </div>
                                <div class="cmt-description">
                                    A Jay ở sạch quá r=)))) a Vasco mà bt Dan đớp 1 đóng calo chắc tức xỉu:))))
                                </div>
                                <div class="tc-footer">
                                    <a href="{{ $demoMangaUrl }}" class="tc-name">Hoán Đổi Diệu Kỳ</a>
                                </div>
                                <a class="tc-link" href="{{ $demoMangaUrl }}#cmt-3744"></a>
                            </div>
                        </li>

                        <!-- Bình luận 2 -->
                        <li class="splide__slide">
                            <div class="tc-item">
                                <div class="tc-v">
                                    <div class="tc-header">
                                        <div class="user-avatar"><img alt="" rel="nofollow" src="{{ asset('images/avatars/type2/3.png') }}" /></div>
                                        <div class="info">
                                            <div class="user-name">tuongvinhbaolong</div>
                                            <span class="tc-time">21 giờ trước</span>
                                        </div>
                                    </div>
                                    <div class="tc-thumb">
                                        <a class="tc-thumbnail" title="" href="{{ $demoMangaUrl }}">
                                            <img class="lzl" data-src="https://img.htrcdn.com/fast/0x150/oss.cdnfastest.com/90htr/posters/5f/cf/blue-lock.jpg" rel="nofollow"
                                                alt="Blue Lock" src="{{ asset('images/pre-load1.png') }}" width="100%" height="100%">
                                        </a>
                                    </div>
                                </div>
                                <div class="cmt-description">
                                    quá ảo
                                </div>
                                <div class="tc-footer">
                                    <a href="{{ $demoMangaUrl }}" class="tc-name">Blue Lock</a>
                                </div>
                                <a class="tc-link" href="{{ $demoMangaUrl }}#cmt-3739"></a>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

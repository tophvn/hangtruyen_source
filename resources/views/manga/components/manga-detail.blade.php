<div id="manga-detail">
    <div class="main-info">
        <div class="row">
            <div class="col-12 col-lg-7 col-xxl-8">
                <div class="left-info d-block d-lg-flex">
                    <div class="col-image-wrapper flex-shrink-0">
                        <div class="col-image" style="background-image: url('{{ $mangaImage ?? 'https://prvhtr.mgbucket.xyz/posters/bd/f3/gto-fury-of-death-yamada.jpeg' }}');">
                            <img src="{{ $mangaImage ?? 'https://prvhtr.mgbucket.xyz/posters/bd/f3/gto-fury-of-death-yamada.jpeg' }}" alt="{{ $mangaTitle ?? 'GTO: Fury of Death Yamada' }}">
                        </div>
                        <a href="" class="report" data-bs-toggle="modal" data-bs-target="#ReportModal" rel="nofollow">
                            <i class="icon-info-circle"></i>
                            Báo cáo lỗi</a>
                    </div>
                    <div class="col-main-info flex-grow-1">
                        <h1 class="title title-detail"><a href="/truyen-tranh/{{ $mangaSlug ?? 'gto-fury-of-death-yamada' }}">{{ $mangaTitle ?? 'GTO: Fury of Death Yamada' }}</a></h1>
                        <div class="m-star">
                            <span class="star-rating">
                                <span style="width: {{ ($rating ?? 0) * 20 }}%;"></span>
                            </span>
                            <span>{{ $rating ?? 0 }}</span>
                        </div>
                        <div class="kind">
                            <span class="label">Thể loại: </span>
                            <a href="/the-loai/manga">
                                <img src="{{ asset('images/jp.png') }}" class="m-flag" /> Manga</a>
                        </div>
                        <div class="m-tags">
                            <span class="label">Tags:</span>
                            <a href="/genre/school-life" class="" title="" tabindex="-1">School Life</a>
                            <a href="/genre/comedy" class="" title="" tabindex="-1">Comedy</a>
                            <a href="/genre/romance" class="" title="" tabindex="-1">Romance</a>
                            <a href="/genre/action" class="" title="" tabindex="-1">Action</a>
                            <a href="/genre/hangtruyen" class="" title="" tabindex="-1">HangTruyen</a>
                        </div>
                        <div class="list-info">
                            <span class="label">Thông tin:</span>
                            <div class="info-group">
                                <div class="status">
                                    <span>Tình trạng: </span>
                                    <p class="">{{ $status ?? 'Đang tiến hành' }}</p>
                                </div>
                                <div class="author">
                                    <span>Tác giả: </span>
                                    <p class="">{{ $author ?? 'Tohru Fujisawa' }}</p>
                                </div>
                                <div class="update">
                                    <span>Cập nhật: </span>
                                    <p class="">{{ $updatedAt ?? '17h29 01/01/2026' }}</p>
                                </div>
                                <div class="view">
                                    <span>Lượt xem: </span>
                                    <p class="">{{ $views ?? '284' }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="sort-des">
                            <h2 class="label">Tóm tắt nội dung truyện {{ $mangaTitle ?? 'GTO: Fury of Death Yamada' }} tại HangTruyen</h2>
                            <div class="line-clamp"><p><span style="font-size: 16px">{{ $description ?? 'Bộ phim kể về Phó Hiệu trưởng Hiroshi Uchiyamada, người vô tình lạc vào một cơn ác mộng xuyên không gian sau khi đến Kabukicho để tìm kiếm nữ sinh mất tích Nanami.' }}</span></p></div>
                            <a class="show-full-des color" href="#" data-bs-toggle="modal" data-bs-target="#fullDescriptionModal">Đọc thêm</a>
                        </div>
                        <div class="read-action">
                            <a id="btn-read_next" href="/truyen-tranh/{{ $mangaSlug ?? 'gto-fury-of-death-yamada' }}/chapter-1" class="btn btn-read" hidden>Đọc tiếp</a>
                            <a href="/truyen-tranh/{{ $mangaSlug ?? 'gto-fury-of-death-yamada' }}/chapter-1" class="btn btn-read">Đọc ngay</a>
                            <a href="#" class="manga-save" title="Theo dõi" rel="nofollow">
                                <i class="icon-bookmark"></i>
                            </a>
                            <span class="num-follow">{{ $followCount ?? 0 }} lượt theo dõi</span>
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
                                    <a href="" data-vote="1" class="manga-vote-btn" data-bs-toggle="modal" data-bs-target="#loginModal">
                                        <span class="bg">
                                            {!! file_get_contents(public_path('images/details/vote-1.svg')) !!}
                                        </span>
                                        Tệ
                                    </a>
                                </span>
                                <span class="bad">
                                    <a href="" data-vote="2" class="manga-vote-btn" data-bs-toggle="modal" data-bs-target="#loginModal">
                                        <span class="bg">
                                            {!! file_get_contents(public_path('images/details/vote-2.svg')) !!}
                                        </span>
                                        Hơi tệ
                                    </a>
                                </span>
                                <span class="normal">
                                    <a href="" data-vote="3" class="manga-vote-btn" data-bs-toggle="modal" data-bs-target="#loginModal">
                                        <span class="bg">
                                            {!! file_get_contents(public_path('images/details/vote-3.svg')) !!}
                                        </span>
                                        Bình thường
                                    </a>
                                </span>
                                <span class="good">
                                    <a href="" data-vote="4" class="manga-vote-btn" data-bs-toggle="modal" data-bs-target="#loginModal">
                                        <span class="bg">
                                            {!! file_get_contents(public_path('images/details/vote-4.svg')) !!}
                                        </span>
                                        Hay
                                    </a>
                                </span>
                                <span class="very-good">
                                    <a href="" data-vote="5" class="manga-vote-btn" data-bs-toggle="modal" data-bs-target="#loginModal">
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

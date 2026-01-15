<div class="row">
    <div class="col-12 col-lg-8">
        <div class="list-chapters-wrapper">
            <h3 class="m-title">DANH SÁCH CHƯƠNG</h3>
            <div class="top-list">
                <div class="topleft-list">
                    <div class="sort-chapter a-z user-select-none">
                        Chap
                        <i class="icon-arrow-3" role="button"></i>
                    </div>
                    <form class="d-xl-flex form-search">
                        <input class="form-control" type="text" placeholder="Tìm kiếm" aria-label="Tìm kiếm">
                        <i class="icon-search-normal"></i>
                    </form>
                </div>
                <div class="tl-update">Ngày cập nhật</div>
                <div class="tl-update">Lượt xem</div>
                <div class="tl-update">Người đăng</div>
            </div>
            <div class="list-chapters">
                <!-- Chapter 1 -->
                <div class="l-chapter">
                    <a href="/truyen-tranh/{{ $mangaSlug ?? 'gto-fury-of-death-yamada' }}/chapter-13" title="Chapter 13" data-cid="2169687" class="ll-chap">
                        <img src="{{ asset('images/book.svg') }}" /> Chapter 13
                    </a>
                    <span class="ll-update">14 ngày trước</span>
                    <span class="ll-update">12</span>
                    <span class="ll-trans">Hang truyện</span>
                </div>
                <!-- Chapter 2 -->
                <div class="l-chapter">
                    <a href="/truyen-tranh/{{ $mangaSlug ?? 'gto-fury-of-death-yamada' }}/chapter-12" title="Chapter 12" data-cid="2169688" class="ll-chap">
                        <img src="{{ asset('images/book.svg') }}" /> Chapter 12
                    </a>
                    <span class="ll-update">14 ngày trước</span>
                    <span class="ll-update">4</span>
                    <span class="ll-trans">Hang truyện</span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-4">
        @include('manga.components.related-manga')
    </div>
</div>

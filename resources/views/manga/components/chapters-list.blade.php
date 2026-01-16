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
                @if(isset($manga['chapters']) && count($manga['chapters']) > 0)
                    @foreach($manga['chapters'] as $chapter)
                        @php
                            $chapterUrl = route('manga.chapter', ['mangaSlug' => $mangaSlug, 'chapterSlug' => $chapter['slug']]);
                            $chapterName = 'Chapter ' . ($chapter['name'] ?? '');
                            $cv = (int)(($chapterViews[$chapter['slug']] ?? 0));
                        @endphp
                        <div class="l-chapter">
                            <a href="{{ $chapterUrl }}" title="{{ $chapterName }}" class="ll-chap">
                                <img src="{{ asset('images/book.svg') }}" /> {{ $chapterName }}
                            </a>
                            <span class="ll-update">Đang cập nhật</span>
                            <span class="ll-update">{{ number_format($cv) }}</span>
                            <span class="ll-trans">HangTruyen</span>
                        </div>
                    @endforeach
                @else
                    <div class="l-chapter">
                        <span class="ll-chap">Đang cập nhật danh sách chapter</span>
                    </div>
                @endif
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-4">
        @include('manga.components.related-manga')
    </div>
</div>

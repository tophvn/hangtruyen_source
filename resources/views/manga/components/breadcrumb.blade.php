<div class="page-breadcrumb">
    <span class="item"><a href="{{ url('/') }}">Trang chủ</a></span>
    @if(isset($chapterName))
        <span class="item breadcrumb_last" aria-current="page">{{ $chapterName }}</span>
    @else
        <span class="item breadcrumb_last" aria-current="page">{{ $mangaTitle ?? 'Truyện tranh' }}</span>
    @endif
</div>

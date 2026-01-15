<div class="page-breadcrumb">
    <span class="item"><a href="{{ url('/') }}">Trang chủ</a></span>
    <span class="item"><a href="/truyen-tranh/{{ $mangaSlug ?? 'gto-fury-of-death-yamada' }}">{{ $mangaTitle ?? 'Truyện tranh' }}</a></span>
    @if(isset($chapterName))
        <span class="item breadcrumb_last" aria-current="page">{{ $chapterName }}</span>
    @else
        <span class="item breadcrumb_last" aria-current="page">{{ $mangaTitle ?? 'Truyện tranh' }}</span>
    @endif
</div>

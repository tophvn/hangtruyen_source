@extends('layouts.app')

@php
    $slug = $slug ?? 'manga';
    $category = $category ?? ['name' => 'Manga', 'title' => 'Truyện tranh Manga', 'description' => ''];
    $results = $results ?? [];
    $currentPage = $currentPage ?? 1;
    $totalPages = $totalPages ?? 1;
@endphp

@section('title', 'Đọc Truyện ' . $category['name'] . ' Hay Mới Nhất - Truyện Tranh ' . $category['name'] . ' Full | HangTruyen' . ($currentPage > 1 ? ' - Trang ' . $currentPage : ''))
@section('description', 'Kho truyện tranh ' . $category['name'] . ' hay nhất: Cập nhật các top trending truyện ' . $category['name'] . ' hot nhiều người xem nhất. Truyện ' . $category['name'] . ' full tại HangTruyen. ' . ($currentPage > 1 ? 'Trang ' . $currentPage . '.' : ''))
@section('keywords', $category['name'] . ', đọc truyện ' . $category['name'] . ', truyện tranh ' . $category['name'] . ', hangtruyen, truyện tranh, truyện full, manga online, manhua online, manhwa online')
@section('canonical', url('/the-loai/' . $slug . ($currentPage > 1 ? '?page=' . $currentPage : '')))
@section('og:url', url('/the-loai/' . $slug . ($currentPage > 1 ? '?page=' . $currentPage : '')))
@section('og:type', 'website')
@section('og:title', 'Đọc Truyện ' . $category['name'] . ' Hay Mới Nhất | HangTruyen')
@section('og:description', 'Kho truyện tranh ' . $category['name'] . ' hay nhất tại HangTruyen')
@section('og:image', asset('images/logo-dark.png'))

@push('head')
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "CollectionPage",
    "name": "{{ $category['title'] }}",
    "description": "{{ $category['description'] }}",
    "url": "{{ url('/the-loai/' . $slug) }}"
}
</script>
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "BreadcrumbList",
    "itemListElement": [
        {
            "@type": "ListItem",
            "position": 1,
            "name": "Trang chủ",
            "item": "{{ url('/') }}"
        },
        {
            "@type": "ListItem",
            "position": 2,
            "name": "{{ $category['name'] }}",
            "item": "{{ url('/the-loai/' . $slug) }}"
        }
    ]
}
</script>
@endpush

@section('content')
<div class="container">
    <div class="page-breadcrumb">
        <span class="item"><a href="{{ url('/') }}">Trang chủ</a></span>
        <span class="item breadcrumb_last" aria-current="page">Thể loại - {{ $category['name'] }}</span>
    </div>
    <section class="mb-3">
        <div class="group-title">
            <div class="only-title">
                <h1 class="m-title title">{{ $category['title'] }}</h1>
                <h2 class="sub">{{ $category['description'] }}</h2>
            </div>
        </div>
        <div class="list-genre">
            @if(count($results) > 0)
                @foreach($results as $manga)
                    <div class="m-post horizontal">
                        <div class="p-thumb flex-shrink-0">
                            <a title="{{ $manga['title'] }}" href="/truyen-tranh/{{ $manga['slug'] }}">
                                <span class="img-poster">
                                    <img class="lzl" data-src="{{ $manga['posterPath'] }}" rel="nofollow"
                                        data-original="{{ $manga['posterPath'] }}" alt="{{ $manga['title'] }}" src="{{ asset('images/pre-load1.png') }}" width="100%" height="100%">
                                </span>
                            </a>
                        </div>
                        <div class="p-content flex-grow-1">
                            <h3 class="m-name">
                                <a href="/truyen-tranh/{{ $manga['slug'] }}">{{ $manga['title'] }}</a>
                            </h3>
                            <div class="group-star">
                                <div class="m-star">
                                    <span class="star-rating">
                                        <span style="width: {{ ($manga['avgVote'] ?? 0) * 20 }}%;"></span>
                                    </span>
                                    <span>{{ number_format($manga['avgVote'] ?? 0, 1) }}</span>
                                </div>
                            </div>
                            <ul class="list-chaps">
                                @if(isset($manga['chapters']) && is_array($manga['chapters']) && count($manga['chapters']) > 0)
                                    @foreach(array_slice($manga['chapters'], 0, 1) as $chapter)
                                        <li class="chapter">
                                            <a data-id="{{ $chapter['id'] ?? '' }}" href="{{ route('manga.chapter', ['mangaSlug' => $manga['slug'], 'chapterSlug' => $chapter['slug']]) }}" title="{{ $chapter['name'] }}">
                                                {{ $chapter['name'] }}<span>{{ $chapter['releasedAt'] ?? '' }}</span>
                                            </a>
                                        </li>
                                    @endforeach
                                @else
                                    <li class="chapter">
                                        <span>Đang cập nhật</span>
                                    </li>
                                @endif
                            </ul>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="col-12">
                    <p class="text-center text-muted">Không tìm thấy truyện nào</p>
                </div>
            @endif
        </div>

        @if($totalPages > 1)
            <ul class="pagination" data-count-page="{{ $totalPages }}">
                @if($currentPage > 1)
                    <li data-page="0">
                        <a class="prev-page" href="{{ url('/the-loai/' . $slug) }}{{ $currentPage > 2 ? '?page=' . ($currentPage - 1) : '' }}" title="Chuyển đến trang {{ $currentPage - 1 }}" data-page="{{ $currentPage - 1 }}" rel="prev">
                            <i class="icon-arrow-left"></i>
                        </a>
                    </li>
                @endif

                @php
                    $startPage = max(1, $currentPage - 2);
                    $endPage = min($totalPages, $currentPage + 2);
                    if ($endPage - $startPage < 4) {
                        if ($startPage == 1) {
                            $endPage = min($totalPages, $startPage + 4);
                        } else {
                            $startPage = max(1, $endPage - 4);
                        }
                    }
                @endphp

                @for($i = $startPage; $i <= $endPage; $i++)
                    <li class="{{ $i == $currentPage ? 'active' : 'false' }}" data-page="{{ $i }}">
                        @if($i == 1)
                            <a href="{{ url('/the-loai/' . $slug) }}" data-page="{{ $i }}">{{ $i }}</a>
                        @else
                            <a href="{{ url('/the-loai/' . $slug) }}?page={{ $i }}" data-page="{{ $i }}">{{ $i }}</a>
                        @endif
                    </li>
                @endfor

                @if($currentPage < $totalPages)
                    <li data-page="0">
                        <a class="next-page" href="{{ url('/the-loai/' . $slug) }}?page={{ $currentPage + 1 }}" title="Chuyển đến trang {{ $currentPage + 1 }}" data-page="{{ $currentPage + 1 }}" rel="next">
                            <i class="icon-arrow-right"></i>
                        </a>
                    </li>
                @endif
            </ul>
        @endif
    </section>
</div>
@endsection

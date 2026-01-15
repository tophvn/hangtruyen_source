@extends('layouts.app')

@section('title', 'Hangtruyen - Trang web đọc truyện tranh Online' . ($currentPage > 1 ? ' - page ' . $currentPage : ''))
@section('description', 'Đọc truyện tranh manga, manhua, manhwa miễn phí được cập nhật liên tục hàng ngày.' . ($currentPage > 1 ? ' - page ' . $currentPage : ''))
@section('keywords', 'đọc truyện, truyện tranh, hangtruyen')

@push('meta')
    <link rel="canonical" href="{{ url('/new' . ($currentPage > 1 ? '?page=' . $currentPage : '')) }}" />
    @if($currentPage > 1)
        <link rel="prev" href="{{ url('/new' . ($currentPage > 2 ? '?page=' . ($currentPage - 1) : '')) }}" />
    @endif
    @if($currentPage < $totalPages)
        <link rel="next" href="{{ url('/new?page=' . ($currentPage + 1)) }}" />
    @endif
@endpush

@section('content')
<main>
    <div class="container">
        <div class="page-breadcrumb">
            <span class="item"><a href="/">Trang chủ</a></span>
            <span class="item breadcrumb_last" aria-current="page">Truyện mới nhất</span>
        </div>
        <section class="mb-3">
            <div class="group-title">
                <div class="only-title">
                    <h1 class="m-title title">Truyện tranh Truyện mới nhất</h1>
                    <h2 class="sub">Danh sách Truyện mới nhất hot nhất được gợi ý</h2>
                </div>
            </div>
            <div class="list-genre">
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
                                    <span style="width: {{ ($manga['avgVote'] / 5) * 100 }}%;"></span>
                                </span>
                                <span>{{ $manga['avgVote'] }}</span>
                            </div>
                        </div>
                        <ul class="list-chaps">
                            @foreach($manga['chapters'] as $chapter)
                            <li class="chapter">
                                <a data-id="{{ $chapter['id'] }}" href="/truyen-tranh/{{ $manga['slug'] }}/{{ $chapter['slug'] }}" title="{{ $chapter['name'] }}">
                                    {{ $chapter['name'] }}<span>{{ $chapter['releasedAt'] }}</span>
                                </a>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                @endforeach
            </div>

            @if($totalPages > 1)
            <ul class="pagination" data-count-page="{{ $totalPages }}">
                @if($currentPage > 1)
                <li data-page="-1">
                    <a class="prev-page" href="{{ url('/new' . ($currentPage > 2 ? '?page=' . ($currentPage - 1) : '')) }}" title="" data-page="{{ $currentPage - 1 }}">
                        <i class="icon-arrow-left"></i>
                    </a>
                </li>
                @endif

                @php
                    $startPage = max(1, $currentPage - 2);
                    $endPage = min($totalPages, $currentPage + 2);
                    if($currentPage == 1) {
                        $endPage = min($totalPages, 5);
                    }
                @endphp

                @for($i = $startPage; $i <= $endPage; $i++)
                <li class="{{ $i == $currentPage ? 'active' : 'false' }}" data-page="{{ $i }}">
                    <a href="{{ url('/new' . ($i > 1 ? '?page=' . $i : '')) }}" data-page="{{ $i }}">{{ $i }}</a>
                </li>
                @endfor

                @if($currentPage < $totalPages)
                <li data-page="0">
                    <a class="next-page" href="{{ url('/new?page=' . ($currentPage + 1)) }}" title="Chuyển đến trang {{ $currentPage + 1 }}" data-page="{{ $currentPage + 1 }}">
                        <i class="icon-arrow-right"></i>
                    </a>
                </li>
                @endif
            </ul>
            @endif
        </section>
    </div>
</main>
@endsection

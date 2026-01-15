@extends('layouts.app')

@php
    $slug = $slug ?? 'action';
    $genre = $genre ?? ['name' => 'Action', 'title' => 'Truyện tranh Action', 'description' => ''];
    $results = $results ?? [];
    $currentPage = $currentPage ?? 1;
    $totalPages = $totalPages ?? 1;
@endphp

@section('title', $genre['title'] . ' hay mới nhất, đọc truyện ' . $genre['name'] . ' miễn phí - HangTruyen' . ($currentPage > 1 ? ' - page ' . $currentPage : ''))
@section('description', 'Đọc truyện ' . $genre['name'] . ' hay mới nhất. Top truyện tranh ' . $genre['name'] . ' full và hot được nhiều người đọc trending nhất. Đọc ' . $genre['name'] . ' online miễn phí tại HangTruyen' . ($currentPage > 1 ? ' - page ' . $currentPage : ''))
@section('keywords', $genre['name'] . ', truyện tranh ' . $genre['name'] . ', đọc truyện ' . $genre['name'])
@section('canonical', url('/genre/' . $slug . ($currentPage > 1 ? '?page=' . $currentPage : '')))

@section('content')
<div class="container">
    <div class="page-breadcrumb">
        <span class="item"><a href="{{ url('/') }}">Trang chủ</a></span>
        <span class="item breadcrumb_last" aria-current="page">Tag - {{ $genre['name'] }}</span>
    </div>
    <section class="mb-3">
        <div class="group-title">
            <div class="only-title">
                <h1 class="m-title title">{{ $genre['title'] }}</h1>
                <h2 class="sub">{{ $genre['description'] }}</h2>
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
                                @if(isset($manga['chapters']) && is_array($manga['chapters']))
                                    @foreach(array_slice($manga['chapters'], 0, 1) as $chapter)
                                        <li class="chapter">
                                            <a data-id="{{ $chapter['id'] }}" href="/truyen-tranh/{{ $manga['slug'] }}/{{ $chapter['slug'] }}" title="{{ $chapter['name'] }}">
                                                {{ $chapter['name'] }}<span>{{ $chapter['releasedAt'] ?? '' }}</span>
                                            </a>
                                        </li>
                                    @endforeach
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
                        <a class="prev-page" href="{{ url('/genre/' . $slug) }}{{ $currentPage > 2 ? '?page=' . ($currentPage - 1) : '' }}" title="Chuyển đến trang {{ $currentPage - 1 }}" data-page="{{ $currentPage - 1 }}">
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
                            <a href="{{ url('/genre/' . $slug) }}" data-page="{{ $i }}">{{ $i }}</a>
                        @else
                            <a href="{{ url('/genre/' . $slug) }}?page={{ $i }}" data-page="{{ $i }}">{{ $i }}</a>
                        @endif
                    </li>
                @endfor

                @if($currentPage < $totalPages)
                    <li data-page="0">
                        <a class="next-page" href="{{ url('/genre/' . $slug) }}?page={{ $currentPage + 1 }}" title="Chuyển đến trang {{ $currentPage + 1 }}" data-page="{{ $currentPage + 1 }}">
                            <i class="icon-arrow-right"></i>
                        </a>
                    </li>
                @endif
            </ul>
        @endif
    </section>
</div>
@endsection

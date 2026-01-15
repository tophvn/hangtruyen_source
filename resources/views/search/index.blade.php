@extends('layouts.app')

@php
    $keyword = $keyword ?? '';
    $totalResults = $totalResults ?? 0;
    $results = $results ?? [];
    $currentPage = $currentPage ?? 1;
    $totalPages = $totalPages ?? 1;
@endphp

@section('title', ($keyword ? $keyword . ' - ' : '') . 'Tìm kiếm - Đọc truyện mới nhất | HangTruyen')
@section('description', 'Tìm kiếm truyện tranh ' . ($keyword ? $keyword . ' ' : '') . 'mới nhất, truyện full chap đầy đủ. Thông tin về truyện hay cập nhật mới nhất tại HangTruyen')
@section('keywords', $keyword . ',hangtruyen,đọc truyện,truyện tranh,truyện full')
@section('canonical', url('/tim-kiem' . ($keyword ? '?keyword=' . urlencode($keyword) : '')))

@section('content')
<div class="search-wrapper">
    <div class="container">
        <div class="page-breadcrumb">
            <span class="item"><a href="{{ url('/') }}">Trang chủ</a></span>
            <span class="item breadcrumb_last" aria-current="page">Tìm kiếm</span>
        </div>
        <div class="row flex-lg-row-reverse">
            <div class="col-lg-4">
                @include('search.components.sidebar-filter', ['keyword' => $keyword])
            </div>
            <div class="col-lg-8">
                <div class="group-title">
                    <div class="only-title">
                        <h1 class="m-title title">Kết quả tìm kiếm</h1>
                        <h2 class="sub">Kết quả được lọc theo mong muốn của bạn</h2>
                    </div>
                    <span>Có <strong class="color">{{ number_format($totalResults) }}</strong> kết quả liên quan</span>
                </div>

                <div class="search-result">
                    <div class="row">
                        @if(count($results) > 0)
                            @foreach($results as $manga)
                                <div class="m-post col-md-6">
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
                                            <span class="num-view">
                                                @if(($manga['countView'] ?? 0) >= 1000000)
                                                    {{ number_format(($manga['countView'] ?? 0) / 1000000, 2) }}M lượt xem
                                                @elseif(($manga['countView'] ?? 0) >= 1000)
                                                    {{ number_format(($manga['countView'] ?? 0) / 1000, 2) }}K lượt xem
                                                @else
                                                    {{ number_format($manga['countView'] ?? 0) }} lượt xem
                                                @endif
                                            </span>
                                        </div>
                                        <ul class="list-chaps">
                                            @if(isset($manga['chapters']) && is_array($manga['chapters']))
                                                @foreach(array_slice($manga['chapters'], 0, 2) as $chapter)
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
                                <p class="text-center text-muted">Không tìm thấy kết quả nào</p>
                            </div>
                        @endif
                    </div>

                    @if($totalPages > 1)
                        <ul class="pagination" data-count-page="{{ $totalPages }}">
                            @if($currentPage > 1)
                                <li data-page="0">
                                    <a class="prev-page" href="{{ url('/tim-kiem') }}?page={{ $currentPage - 1 }}{{ $keyword ? '&keyword=' . urlencode($keyword) : '' }}" title="Chuyển đến trang {{ $currentPage - 1 }}" data-page="{{ $currentPage - 1 }}">
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
                                    <a href="{{ url('/tim-kiem') }}?page={{ $i }}{{ $keyword ? '&keyword=' . urlencode($keyword) : '' }}" data-page="{{ $i }}">{{ $i }}</a>
                                </li>
                            @endfor

                            @if($currentPage < $totalPages)
                                <li data-page="0">
                                    <a class="next-page" href="{{ url('/tim-kiem') }}?page={{ $currentPage + 1 }}{{ $keyword ? '&keyword=' . urlencode($keyword) : '' }}" title="Chuyển đến trang {{ $currentPage + 1 }}" data-page="{{ $currentPage + 1 }}">
                                        <i class="icon-arrow-right"></i>
                                    </a>
                                </li>
                            @endif
                        </ul>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        // Toggle hiển thị tags khi bấm "Xem tất cả"
        $('#view-all-tags').on('click', function(e) {
            e.preventDefault();
            const $hiddenTags = $('.list-genres .d-none');
            const $link = $(this);
            
            if ($hiddenTags.length > 0) {
                // Hiển thị các tags ẩn
                $hiddenTags.removeClass('d-none');
                $link.text('Ẩn bớt');
            } else {
                // Ẩn lại các tags (trừ 2 tags đầu tiên)
                $('.list-genres span').each(function(index) {
                    if (index >= 2) {
                        $(this).addClass('d-none');
                    }
                });
                $link.text('Xem tất cả');
            }
        });
    });
</script>
@endpush
@endsection

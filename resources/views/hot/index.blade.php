@extends('layouts.app')

@php
    $type = $type ?? 'all';
    $results = $results ?? [];
    $typeMap = [
        'all' => 'All',
        'day' => 'Ngày',
        'week' => 'Tuần',
        'month' => 'Tháng',
    ];
@endphp

@section('title', 'Hang Truyện tranh hot trending đang được xem nhiều nhất | HangTruyen')
@section('description', 'Hang Truyện cập nhật truyện hot mới nhất trên thị trường. Đọc truyện tranh online đang top trending cập nhật mới nhất tại HangTruyen')
@section('keywords', 'Truyện Hot, HangTruyen')
@section('canonical', url('/hot-nhat' . ($type !== 'all' ? '?type=' . $type : '')))

@section('content')
<div class="container">
    <div class="page-breadcrumb">
        <span class="item"><a href="{{ url('/') }}">Trang chủ</a></span>
        <span class="item breadcrumb_last" aria-current="page">Truyện hot nhất</span>
    </div>
    <section class="mb-3">
        <div>
            <div class="group-title">
                <div class="only-title">
                    <h1 class="m-title title">Truyện hot nhất</h1>
                    <h2 class="sub">Danh sách truyện Hot nhất được gợi ý</h2>
                </div>
            </div>
            <ul class="nav nav-tabs nav-account" role="tablist">
                <li class="nav-item" role="presentation">
                    <a href="{{ url('/hot-nhat?type=all') }}" class="nav-link {{ $type === 'all' ? 'active' : 'false' }}">All</a>
                </li>
                <li class="nav-item" role="presentation">
                    <a href="{{ url('/hot-nhat?type=day') }}" class="nav-link {{ $type === 'day' ? 'active' : 'false' }}">Ngày</a>
                </li>
                <li class="nav-item" role="presentation">
                    <a href="{{ url('/hot-nhat?type=week') }}" class="nav-link {{ $type === 'week' ? 'active' : 'false' }}">Tuần</a>
                </li>
                <li class="nav-item" role="presentation">
                    <a href="{{ url('/hot-nhat?type=month') }}" class="nav-link {{ $type === 'month' ? 'active' : 'false' }}">Tháng</a>
                </li>
            </ul>
            <div class="list-managas row">
                @if(count($results) > 0)
                    @foreach($results as $manga)
                        <div class="m-post col-md-6 col-xl-4">
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
                        <p class="text-center text-muted">Không tìm thấy truyện nào</p>
                    </div>
                @endif
            </div>
        </div>
    </section>
</div>
@endsection

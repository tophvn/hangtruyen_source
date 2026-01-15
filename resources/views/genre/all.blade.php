@extends('layouts.app')

@php
    $genres = $genres ?? [];
@endphp

@section('title', 'Truyện tranh Tất cả genres hay mới nhất, đọc truyện Tất cả genres miễn phí - HangTruyen')
@section('description', 'Đọc truyện Tất cả genres hay mới nhất. Top truyện tranh Tất cả genres full và hot được nhiều người đọc trending nhất. Đọc Tất cả genres online miễn phí tại HangTruyen')
@section('keywords', 'Tất cả genres, truyện tranh Tất cả genres, đọc truyện Tất cả genres')
@section('canonical', url('/genre'))

@section('content')
<div class="container">
    <div class="page-breadcrumb">
        <span class="item"><a href="{{ url('/') }}">Trang chủ</a></span>
        <span class="item breadcrumb_last" aria-current="page">Tags</span>
    </div>

    @foreach($genres as $genre)
        @include('genre.components.genre-section', ['genre' => $genre])
    @endforeach
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        // Initialize Splide for each genre section
        $('.m-suggest.splide').each(function() {
            const splideElement = this;
            new Splide(splideElement, {
                type: 'slide',
                perPage: 4,
                perMove: 1,
                gap: '1rem',
                pagination: false,
                arrows: true,
                breakpoints: {
                    1200: {
                        perPage: 3,
                    },
                    768: {
                        perPage: 2,
                    },
                    576: {
                        perPage: 1,
                    },
                },
            }).mount();
        });
    });
</script>
@endpush
@endsection

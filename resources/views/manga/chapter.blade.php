@extends('layouts.chapter')

@php
    $mangaTitle = $mangaTitle ?? 'GTO: Fury of Death Yamada';
    $chapterName = $chapterName ?? 'Chapter 13';
    $mangaSlug = $mangaSlug ?? 'gto-fury-of-death-yamada';
    $chapterSlug = $chapterSlug ?? 'chapter-13';
    $nextChapterName = isset($nextChapter) ? $nextChapter['name'] : null;
    $title = 'Đọc ' . $mangaTitle . ' ' . $chapterName;
    if ($nextChapterName) {
        $title .= ' next ' . $nextChapterName;
    }
    $title .= ', ' . $mangaTitle . ' chương mới nhất | HangTruyen';
    $description = 'Đọc truyện ' . $mangaTitle . ' cập nhật chương ' . $chapterName . ' mới nhất, bản full đầy đủ chap với hình ảnh sắc nét, truyện tải nhanh, không quảng cáo tại website đọc truyện tranh online HangTruyen';
@endphp
@section('title', $title)
@section('description', $description)
@section('keywords', $mangaTitle . ',' . $chapterName . ',đọc truyện,truyện tranh,hangtruyen')
@section('canonical', url('/truyen-tranh/' . $mangaSlug . '/' . $chapterSlug))
@section('og:url', url('/truyen-tranh/' . $mangaSlug . '/' . $chapterSlug))
@section('og:title', $title)
@section('og:description', $description)

@section('content')
<div id="manga-images" data-mode="vertical">
    <div class="main-images">
        @if(isset($chapterImages) && is_array($chapterImages))
            @foreach($chapterImages as $index => $imageUrl)
                <div class="mi-item" data-page="{{ $index + 1 }}">
                    <img 
                        class="lzl" 
                        data-src="{{ $imageUrl }}" 
                        data-original="{{ $imageUrl }}" 
                        alt="Trang {{ $index + 1 }}" 
                        src="{{ asset('images/pre-load1.png') }}"
                        loading="lazy"
                    />
                </div>
            @endforeach
        @else
            <!-- Demo images -->
            @for($i = 1; $i <= 5; $i++)
                <div class="mi-item" data-page="{{ $i }}">
                    <img 
                        class="lzl" 
                        data-src="https://prvhtr.mgbucket.xyz/chapters/1277938/2169687/{{ $i }}.jpg" 
                        data-original="https://prvhtr.mgbucket.xyz/chapters/1277938/2169687/{{ $i }}.jpg" 
                        alt="Trang {{ $i }}" 
                        src="{{ asset('images/pre-load1.png') }}"
                        loading="lazy"
                    />
                </div>
            @endfor
        @endif
    </div>
</div>

<div class="navi-bottom navi-chap">
    @if(isset($prevChapter))
        <button type="button" onclick="window.location.href='{{ $prevChapter['url'] }}'">
            <i class="icon-arrow-left"></i>
            <span>{{ $prevChapter['name'] }}</span>
        </button>
    @else
        <button type="button" class="disabled">
            <i class="icon-arrow-left"></i>
            <span>Không có</span>
        </button>
    @endif
    <button type="button" onclick="window.location.href='/truyen-tranh/{{ $mangaSlug ?? 'gto-fury-of-death-yamada' }}'">
        <i class="icon-list"></i>
        <span>Danh sách chương</span>
    </button>
    @if(isset($nextChapter))
        <button type="button" onclick="window.location.href='{{ $nextChapter['url'] }}'">
            <span>{{ $nextChapter['name'] }}</span>
            <i class="icon-arrow-right"></i>
        </button>
    @else
        <button type="button" class="disabled">
            <span>Không có</span>
            <i class="icon-arrow-right"></i>
        </button>
    @endif
</div>

@include('manga.components.modals')
@endsection

@push('scripts')
<script>
    const chapterDetail = {
        "id": {{ $chapterId ?? 2169687 }},
        "mangaId": 1277938,
        "name": "{{ $chapterName ?? 'Chapter 13' }}",
        "slug": "{{ $chapterSlug ?? 'chapter-13' }}",
        "mangaSlug": "{{ $mangaSlug ?? 'gto-fury-of-death-yamada' }}",
        "pages": [
            @if(isset($chapterImages) && is_array($chapterImages))
                @foreach($chapterImages as $index => $imageUrl)
                    {
                        "page": {{ $index + 1 }},
                        "url": "{{ $imageUrl }}"
                    }{{ !$loop->last ? ',' : '' }}
                @endforeach
            @else
                @for($i = 1; $i <= 5; $i++)
                    {
                        "page": {{ $i }},
                        "url": "https://prvhtr.mgbucket.xyz/chapters/1277938/2169687/{{ $i }}.jpg"
                    }{{ $i < 5 ? ',' : '' }}
                @endfor
            @endif
        ]
    };
    
    const mangaDetail = {
        "id": 1277938,
        "title": "{{ $mangaTitle ?? 'GTO: Fury of Death Yamada' }}",
        "slug": "/truyen-tranh/{{ $mangaSlug ?? 'gto-fury-of-death-yamada' }}"
    };
</script>

<script src="{{ asset('js/custom/comment/comment.js') }}"></script>
<script src="{{ asset('js/custom/manga-detail/index.js') }}"></script>
@endpush

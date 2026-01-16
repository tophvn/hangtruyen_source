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
    <div class="main-images text-center position-relative">
        @if(isset($chapterImages) && is_array($chapterImages))
            @foreach($chapterImages as $index => $imageUrl)
                <div class="mi-item" data-page="{{ $index + 1 }}">
                    <div class="loaded i-right">
                        <img 
                            class="lzl reading-img" 
                            data-src="{{ $imageUrl }}" 
                            data-original="{{ $imageUrl }}" 
                            alt="Trang {{ $index + 1 }}" 
                            src="{{ asset('images/pre-load1.png') }}"
                            loading="lazy"
                        />
                    </div>
                </div>
            @endforeach
        @else
            <!-- Demo images -->
            @for($i = 1; $i <= 5; $i++)
                <div class="mi-item" data-page="{{ $i }}">
                    <div class="loaded i-right">
                        <img 
                            class="lzl reading-img" 
                            data-src="https://prvhtr.mgbucket.xyz/chapters/1277938/2169687/{{ $i }}.jpg" 
                            data-original="https://prvhtr.mgbucket.xyz/chapters/1277938/2169687/{{ $i }}.jpg" 
                            alt="Trang {{ $i }}" 
                            src="{{ asset('images/pre-load1.png') }}"
                            loading="lazy"
                        />
                    </div>
                </div>
            @endfor
        @endif
    </div>
</div>

<div class="navi-bottom navi-chap navigation">
    <button type="button" class="navi prev {{ !isset($prevChapter) || !$prevChapter ? 'disabled' : '' }}" onclick="handlePrevChapter()" title="Chapter Trước">
        <i class="icon-arrow-left"></i>
        <span>Chapter Trước</span>
    </button>
    <button type="button" class="navi next {{ !isset($nextChapter) || !$nextChapter ? 'disabled' : '' }}" onclick="handleNextChapter()" title="Chapter Sau">
        <span>Chapter Sau</span>
        <i class="icon-arrow-right"></i>
    </button>
</div>

<div class="container comment-chapter">
    @include('manga.components.comments', [
        'mangaSlug' => $mangaSlug ?? '',
        'comments' => $comments ?? collect(),
        'likedCommentIds' => $likedCommentIds ?? [],
        'commentsCount' => $commentsCount ?? 0,
    ])
</div>

@include('manga.components.modals')
@endsection

@push('scripts')
<script>
    window.chapterDetail = {
        "id": {{ $chapterId ?? 'null' }},
        "mangaId": {{ isset($mangaMetadata) && $mangaMetadata->id ? $mangaMetadata->id : 'null' }},
        "name": "{{ $chapterName ?? 'Chapter' }}",
        "slug": "{{ $chapterSlug ?? '' }}",
        "mangaSlug": "{{ $mangaSlug ?? '' }}",
        "pages": [
            @if(isset($chapterImages) && is_array($chapterImages) && count($chapterImages) > 0)
                @foreach($chapterImages as $index => $imageUrl)
                    {
                        "page": {{ $index + 1 }},
                        "url": "{{ $imageUrl }}"
                    }{{ !$loop->last ? ',' : '' }}
                @endforeach
            @else
                []
            @endif
        ]
    };
    
    window.mangaDetail = {
        "id": {{ isset($mangaMetadata) && $mangaMetadata->id ? $mangaMetadata->id : 'null' }},
        "title": {!! json_encode($mangaTitle ?? 'Đang cập nhật') !!},
        "slug": "/truyen-tranh/{{ $mangaSlug ?? '' }}",
        "rawSlug": {!! json_encode($mangaSlug ?? '') !!},
    };
    
    // Đảm bảo các biến có thể truy cập được từ comment.js
    if (typeof chapterDetail === 'undefined') {
        var chapterDetail = window.chapterDetail;
    }
    if (typeof mangaDetail === 'undefined') {
        var mangaDetail = window.mangaDetail;
    }
</script>

    <script>
        function handlePrevChapter() {
            @if(isset($prevChapter) && $prevChapter)
                window.location.href = '{{ $prevChapter['url'] }}';
            @endif
        }

        function handleNextChapter() {
            @if(isset($nextChapter) && $nextChapter)
                window.location.href = '{{ $nextChapter['url'] }}';
            @endif
        }
    </script>

    <script>
        async function postComment(mangaId, chapterId, content, parentCommentId) {
            try {
                const response = await $.ajax({
                    type: 'POST',
                    url: '/truyen-tranh/{{ $mangaSlug }}/comments',
                    headers: {
                        'Accept': 'application/json'
                    },
                    data: {
                        content: content,
                        chapter_id: chapterId,
                        parent_id: parentCommentId,
                    },
                    dataType: 'json',
                });

                if (response && response.status === 'success') {
                    return response.data;
                }
                return null;
            } catch (error) {
                if (error.responseJSON && error.responseJSON.message) {
                    alert(error.responseJSON.message);
                } else if (error.status === 401) {
                    alert('Vui lòng đăng nhập để bình luận');
                } else {
                    alert('Có lỗi xảy ra khi đăng bình luận. Vui lòng thử lại.');
                }
                return null;
            }
        }

        async function getComment(mangaId, chapterId, page, pageSize, orderBy) {
            try {
                const response = await $.ajax({
                    type: 'GET',
                    url: '/truyen-tranh/{{ $mangaSlug }}/comments',
                    headers: {
                        'Accept': 'application/json'
                    },
                    data: {
                        chapter_id: chapterId,
                        page: page,
                        order: orderBy || 'latest',
                    },
                    dataType: 'json',
                });

                if (response && response.status === 'success') {
                    return response.data.html;
                }
                return '';
            } catch (error) {
                console.error('Error loading comments:', error);
                return '';
            }
        }

        async function getLikedCommentIds(mangaId) {
            try {
                const response = await $.ajax({
                    type: 'GET',
                    url: '/truyen-tranh/{{ $mangaSlug }}/comments/liked-ids',
                    headers: {
                        'Accept': 'application/json'
                    },
                    dataType: 'json',
                });

                if (response && response.status === 'success') {
                    return response.data;
                }
                return [];
            } catch (error) {
                return [];
            }
        }

        async function likeComment(commentId) {
            try {
                const response = await $.ajax({
                    type: 'POST',
                    url: '/truyen-tranh/{{ $mangaSlug }}/comments/' + commentId + '/like',
                    headers: {
                        'Accept': 'application/json'
                    },
                    data: {},
                    dataType: 'json',
                });

                if (response && response.status === 'success') {
                    return response.data;
                }
                return null;
            } catch (error) {
                if (error.responseJSON && error.responseJSON.message) {
                    alert(error.responseJSON.message);
                } else if (error.status === 401) {
                    alert('Vui lòng đăng nhập để thích bình luận');
                } else {
                    alert('Có lỗi xảy ra. Vui lòng thử lại.');
                }
                return null;
            }
        }

        async function postReport(mangaId, reasons, chapterId) {
            try {
                const content = Array.isArray(reasons) ? reasons.join('\n') : reasons;
                
                const response = await $.ajax({
                    type: 'POST',
                    url: '/truyen-tranh/{{ $mangaSlug }}/report',
                    headers: {
                        'Accept': 'application/json'
                    },
                    data: {
                        content: content,
                        chapter_slug: chapterId || '{{ $chapterSlug ?? '' }}'
                    },
                    dataType: 'json',
                });

                if (response && response.status === 'success') {
                    return response;
                }
                return null;
            } catch (error) {
                if (error.responseJSON && error.responseJSON.message) {
                    alert(error.responseJSON.message);
                } else {
                    alert('Có lỗi xảy ra khi gửi báo cáo. Vui lòng thử lại.');
                }
                return null;
            }
        }
    </script>

    <script>
        // Handle report form in chapter
        $(document).ready(function() {
            $('#reportForm').on('submit', async function(e) {
                e.preventDefault();
                
                const reportType = $('#reportType').val();
                const reportDescription = $('#reportDescription').val();
                
                if (!reportType) {
                    alert('Vui lòng chọn loại lỗi');
                    return;
                }
                
                if (!reportDescription || reportDescription.trim().length < 10) {
                    alert('Mô tả chi tiết phải có ít nhất 10 ký tự');
                    return;
                }
                
                const reportTypeLabels = {
                    'broken-image': 'Ảnh bị lỗi',
                    'wrong-chapter': 'Sai chương',
                    'missing-page': 'Thiếu trang',
                    'other': 'Khác'
                };
                
                const content = `${reportTypeLabels[reportType] || reportType}:\n${reportDescription}`;
                
                const currentMangaDetail = window.mangaDetail || (typeof mangaDetail !== 'undefined' ? mangaDetail : null);
                if (!currentMangaDetail || !currentMangaDetail.id) {
                    alert('Không thể xác định truyện để báo cáo');
                    return;
                }
                
                const chapterSlug = window.chapterDetail ? window.chapterDetail.slug : '{{ $chapterSlug ?? '' }}';
                
                const response = await postReport(currentMangaDetail.id, content, chapterSlug);
                
                if (response && response.status === 'success') {
                    const message = response.message || 'Cảm ơn bạn đã báo cáo. Chúng tôi sẽ xem xét và xử lý sớm nhất có thể.';
                    alert(message);
                    
                    // Reset form
                    $('#reportForm')[0].reset();
                    $('#ReportModal').modal('hide');
                }
            });
        });
    </script>

    <script src="{{ asset('js/custom/comment/comment.js') }}"></script>
    <script src="{{ asset('js/custom/manga-detail/index.js') }}"></script>
@endpush

@extends('layouts.manga')

@section('title', 'Truyện tranh ' . ($manga['name'] ?? 'Đang cập nhật') . ' mới nhất miễn phí - HangTruyen')
@section('description', 'Đọc ' . ($manga['name'] ?? 'Đang cập nhật') . ' mới update full miễn phí tại HangTruyen cập nhật chương nhanh nhất')
@section('keywords', ($manga['name'] ?? 'Đang cập nhật') . ' truyện tranh, ' . ($manga['name'] ?? 'Đang cập nhật') . ' full, đọc truyện tranh ' . ($manga['name'] ?? 'Đang cập nhật') . ' online')
@section('canonical', url('/truyen-tranh/' . ($mangaSlug ?? '')))
@section('og:url', url('/truyen-tranh/' . ($mangaSlug ?? '')))
@section('og:title', 'Truyện tranh ' . ($manga['name'] ?? 'Đang cập nhật') . ' mới nhất miễn phí - HangTruyen')
@section('og:description', 'Đọc ' . ($manga['name'] ?? 'Đang cập nhật') . ' mới update full miễn phí tại HangTruyen cập nhật chương nhanh nhất')

@section('content')
<div class="container">
        @include('manga.components.breadcrumb', [
            'mangaTitle' => $manga['name'] ?? 'Đang cập nhật',
            'mangaSlug' => $mangaSlug ?? ''
        ])
        
        @include('manga.components.manga-detail')
        
        @include('manga.components.chapters-list')
        
        <div class="row">
            <div class="col-12 col-lg-8">
                @include('manga.components.comments')
            </div>
            <div class="col-12 col-lg-4">
                @include('components.top-follow')
            </div>
        </div>
    </div>

    @include('manga.components.modals')
@endsection

@push('scripts')
<script>
    window.userVote = {{ $userRating ? $userRating : 'null' }};
    var userVote = window.userVote;
    @php
        $chaptersForJs = [];
        foreach ($manga['chapters'] ?? [] as $ch) {
            $chaptersForJs[] = [
                'id' => null,
                'name' => 'Chapter ' . $ch['name'],
                'index' => preg_match('/^(\d+)/', $ch['name'], $m) ? (float)$m[1] : 0,
                'slug' => $ch['slug'],
                'countView' => $chapterViews[$ch['slug']] ?? 0,
                'releasedAt' => null,
                'translators' => []
            ];
        }
    @endphp
    @php
        $mangaId = $mangaMetadata && $mangaMetadata->id ? $mangaMetadata->id : null;
    @endphp
    @if($mangaId)
    window.mangaDetail = {
        "id": {{ $mangaId }},
            "title": {!! json_encode($manga['name'] ?? 'Đang cập nhật') !!},
            "slug": "/truyen-tranh/{{ $mangaSlug }}",
            "avgVote": {{ $avgRating ?? 0 }},
            "posterPath": {!! json_encode($manga['cover_url'] ?? '') !!},
            "overview": {!! json_encode($manga['description'] ?? 'Đang cập nhật') !!},
            "countView": 0,
            "categoryId": {{ isset($manga['type']['id']) && $manga['type']['id'] ? (int)$manga['type']['id'] : 'null' }},
            "status": 0,
            "author": {!! json_encode(is_array($manga['author'] ?? []) ? implode(', ', $manga['author']) : ($manga['author'] ?? 'Đang cập nhật')) !!},
            "sourceAllView": 0,
            "sourceFollow": {{ $followsCount ?? 0 }},
            "isFollowing": {{ ($isFollowing ?? false) ? 'true' : 'false' }},
            "recentlyUpdatedAt": {!! json_encode($manga['updated_at'] ?? '') !!},
            "isWebtoon": false,
            "isIndexing": 1,
            "genres": {!! json_encode($manga['tags'] ?? []) !!},
            "chapters": {!! json_encode($chaptersForJs) !!},
            "category": {!! json_encode($manga['type'] ?? null) !!},
            "rawSlug": {!! json_encode($mangaSlug) !!},
        "sourceAllViewString": "0",
        "sourceFollowString": "0"
    };
    const mangaDetail = window.mangaDetail;
    const chapterDetail = null;
    @else
    window.mangaDetail = null;
    @endif
    
    // Check if URL has comment fragment and verify it belongs to current manga
    (function() {
        const hash = window.location.hash;
        const commentMatch = hash.match(/cmt-(\d+)/);
        if (commentMatch && window.mangaDetail && window.mangaDetail.id) {
            const commentId = parseInt(commentMatch[1]);
            // Fetch comment info to verify it belongs to current manga
            fetch('/api/comment/' + commentId + '/manga-id')
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data && data.manga_id) {
                        if (data.manga_id !== window.mangaDetail.id) {
                            // Comment belongs to different manga, redirect to correct manga
                            if (data.manga_slug) {
                                window.location.replace('/truyen-tranh/' + data.manga_slug + hash);
                                return;
                            }
                        }
                    }
                    // If comment belongs to current manga, scroll to it
                    setTimeout(() => {
                        const commentElement = document.getElementById('cmt-' + commentId);
                        if (commentElement) {
                            commentElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        }
                    }, 100);
                })
                .catch(error => {
                    console.error('Error checking comment:', error);
                    // If API fails, try to check from DOM
                    setTimeout(() => {
                        const commentElement = document.getElementById('cmt-' + commentId);
                        if (!commentElement) {
                            // Comment not found on this page, might be wrong manga
                            console.warn('Comment #' + commentId + ' not found on current page');
                        } else {
                            commentElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        }
                    }, 500);
                });
        } else if (hash && hash.match(/cmt-(\d+)/)) {
            // If we have a comment hash but no mangaDetail, try to get comment's manga
            const commentMatch = hash.match(/cmt-(\d+)/);
            if (commentMatch) {
                const commentId = parseInt(commentMatch[1]);
                fetch('/api/comment/' + commentId + '/manga-id')
                    .then(response => response.json())
                    .then(data => {
                        if (data && data.manga_slug) {
                            window.location.replace('/truyen-tranh/' + data.manga_slug + hash);
                        }
                    })
                    .catch(() => {});
            }
        }
    })();
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
                    chapter_slug: chapterId || null
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

    async function voteManga(mangaId, vote) {
        try {
            const response = await $.ajax({
                type: 'POST',
                url: '/truyen-tranh/{{ $mangaSlug }}/vote',
                headers: {
                    'Accept': 'application/json'
                },
                data: { 
                    vote: vote
                },
                dataType: 'json',
            });

            if (response && response.status === 'success') {
                if (response.data && response.data.avgRating !== undefined) {
                    const avgRating = parseFloat(response.data.avgRating);
                    const ratingPercent = (avgRating / 5) * 100;
                    $('.m-star .star-rating span').css('width', ratingPercent + '%');
                    $('.m-star span:last-child').text(avgRating.toFixed(1));
                    if (window.mangaDetail) {
                        window.mangaDetail.avgVote = avgRating;
                    }
                    if (typeof mangaDetail !== 'undefined') {
                        mangaDetail.avgVote = avgRating;
                    }
                }
                return response.data;
            }
            return null;
        } catch (error) {
            if (error.responseJSON && error.responseJSON.message) {
                alert(error.responseJSON.message);
            } else if (error.status === 401) {
                alert('Vui lòng đăng nhập để đánh giá');
            } else {
                alert('Có lỗi xảy ra khi đánh giá. Vui lòng thử lại.');
            }
            return null;
        }
    }

    async function followManga(mangaId) {
        try {
            const response = await $.ajax({
                type: 'POST',
                url: '/truyen-tranh/{{ $mangaSlug }}/follow',
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
                alert('Vui lòng đăng nhập để theo dõi truyện');
            } else {
                alert('Có lỗi xảy ra khi theo dõi. Vui lòng thử lại.');
            }
            return null;
        }
    }
</script>

    <script src="{{ asset('js/custom/comment/comment.js') }}"></script>
    <script src="{{ asset('js/custom/manga-detail/report-manga.js') }}"></script>
    <script src="{{ asset('js/custom/manga-detail/index.js') }}"></script>
@endpush

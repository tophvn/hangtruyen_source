@extends('layouts.manga')

@php
    $mangaName = $manga['name'] ?? 'Đang cập nhật';
    $mangaDescription = $manga['description'] ?? '';
    $mangaCover = $manga['cover_url'] ?? asset('images/logo-dark.png');
    $mangaRating = $manga['rating'] ?? 0;
    $mangaStatus = $manga['status'] ?? 'ongoing';
    $mangaSlugFull = $mangaSlug ?? '';
    
    $lastChapterNumber = $mangaMetadata->last_chapter_number ?? null;
    if (!$lastChapterNumber && isset($manga['chapters']) && count($manga['chapters']) > 0) {
        $firstChapter = $manga['chapters'][0];
        $lastChapterNumber = $firstChapter['name'] ?? null;
    }
    
    $titleSuffix = '';
    if ($lastChapterNumber) {
        $chapterNum = preg_replace('/^Chapter\s+/i', '', $lastChapterNumber);
        $titleSuffix = ' (tới Chapter ' . trim($chapterNum) . ')';
    }
@endphp

@section('title', 'Truyện tranh ' . $mangaName . $titleSuffix . ' mới nhất miễn phí - HangTruyen')
@section('description', !empty($mangaDescription) ? strip_tags($mangaDescription) : 'Đọc ' . $mangaName . ' mới update full miễn phí tại HangTruyen. ' . $mangaName . ' là một trong những truyện tranh hot nhất hiện nay, cập nhật chương nhanh nhất.')
@section('keywords', $mangaName . ', truyện tranh ' . $mangaName . ', đọc ' . $mangaName . ' online, ' . $mangaName . ' full, ' . $mangaName . ' miễn phí, hangtruyen')
@section('canonical', url('/truyen-tranh/' . $mangaSlugFull))
@section('og:url', url('/truyen-tranh/' . $mangaSlugFull))
@section('og:type', 'article')
@section('og:title', 'Truyện tranh ' . $mangaName . $titleSuffix . ' mới nhất miễn phí - HangTruyen')
@section('og:description', !empty($mangaDescription) ? strip_tags(substr($mangaDescription, 0, 200)) : 'Đọc ' . $mangaName . ' mới update full miễn phí tại HangTruyen cập nhật chương nhanh nhất')
@section('og:image', $mangaCover)

@push('head')
@php
    $rawDescription = !empty($mangaDescription) ? strip_tags($mangaDescription) : ('Đọc ' . $mangaName . ' mới update full miễn phí tại HangTruyen');
    $bookDescription = html_entity_decode($rawDescription, ENT_QUOTES, 'UTF-8');
    $bookData = [
        '@context' => 'https://schema.org',
        '@type' => 'Book',
        'name' => $mangaName,
        'description' => $bookDescription,
        'image' => $mangaCover,
        'url' => url('/truyen-tranh/' . $mangaSlugFull),
        'aggregateRating' => [
            '@type' => 'AggregateRating',
            'ratingValue' => (string)$mangaRating,
            'bestRating' => '5',
            'worstRating' => '1'
        ]
    ];
@endphp
<script type="application/ld+json">
    {!! json_encode($bookData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
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
                "name": "{{ $mangaName }}",
                "item": "{{ url('/truyen-tranh/' . $mangaSlugFull) }}"
            }
        ]
    }
    </script>
@endpush

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
    
    (function() {
        const hash = window.location.hash;
        const commentMatch = hash.match(/cmt-(\d+)/);
        if (commentMatch && window.mangaDetail && window.mangaDetail.id) {
            const commentId = parseInt(commentMatch[1]);
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
                            if (data.manga_slug) {
                                window.location.replace('/truyen-tranh/' + data.manga_slug + hash);
                                return;
                            }
                        }
                    }
                    setTimeout(() => {
                        const commentElement = document.getElementById('cmt-' + commentId);
                        if (commentElement) {
                            commentElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        }
                    }, 100);
                })
                .catch(error => {
                    console.error('Error checking comment:', error);
                    setTimeout(() => {
                        const commentElement = document.getElementById('cmt-' + commentId);
                        if (!commentElement) {
                            console.warn('Comment #' + commentId + ' not found on current page');
                        } else {
                            commentElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        }
                    }, 500);
                });
        } else if (hash && hash.match(/cmt-(\d+)/)) {
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

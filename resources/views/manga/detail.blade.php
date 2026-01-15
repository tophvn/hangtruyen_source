@extends('layouts.manga')

@section('title', 'Truyện tranh ' . ($mangaTitle ?? 'GTO: Fury of Death Yamada') . ' mới nhất miễn phí - HangTruyen')
@section('description', 'Đọc ' . ($mangaTitle ?? 'GTO: Fury of Death Yamada') . ' mới update full miễn phí tại HangTruyen cập nhật chương nhanh nhất')
@section('keywords', ($mangaTitle ?? 'GTO: Fury of Death Yamada') . ' truyện tranh, ' . ($mangaTitle ?? 'GTO: Fury of Death Yamada') . ' full, đọc truyện tranh ' . ($mangaTitle ?? 'GTO: Fury of Death Yamada') . ' online')
@section('canonical', url('/truyen-tranh/' . ($mangaSlug ?? 'gto-fury-of-death-yamada')))
@section('og:url', url('/truyen-tranh/' . ($mangaSlug ?? 'gto-fury-of-death-yamada')))
@section('og:title', 'Truyện tranh ' . ($mangaTitle ?? 'GTO: Fury of Death Yamada') . ' mới nhất miễn phí - HangTruyen')
@section('og:description', 'Đọc ' . ($mangaTitle ?? 'GTO: Fury of Death Yamada') . ' mới update full miễn phí tại HangTruyen cập nhật chương nhanh nhất')

@section('content')
<div class="container">
        @include('manga.components.breadcrumb', [
            'mangaTitle' => $mangaTitle ?? 'GTO: Fury of Death Yamada',
            'mangaSlug' => $mangaSlug ?? 'gto-fury-of-death-yamada'
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

    <!-- Tracking pixel to count view -->
    <img src="https://api.hangtruyen.vip/tracking/manga?mangaId=1277938" alt="" width="1" height="1" style="display:none;" />

    @include('manga.components.modals')
@endsection

@push('scripts')
<script>
    var userVote = null;
    const mangaDetail = {
        "id": 1277938,
        "title": "GTO: Fury of Death Yamada",
        "slug": "/truyen-tranh/gto-fury-of-death-yamada",
        "avgVote": 0,
        "posterPath": "https://prvhtr.mgbucket.xyz/posters/bd/f3/gto-fury-of-death-yamada.jpeg",
        "overview": "<p><span style=\"font-size: 16px\">Bộ phim kể về Phó Hiệu trưởng Hiroshi Uchiyamada, người vô tình lạc vào một cơn ác mộng xuyên không gian sau khi đến Kabukicho để tìm kiếm nữ sinh mất tích Nanami.</span></p>",
        "countView": 284,
        "categoryId": 1,
        "status": 0,
        "author": "Tohru Fujisawa",
        "sourceAllView": 0,
        "sourceFollow": 0,
        "recentlyUpdatedAt": "2026-01-01T17:29:25.000Z",
        "isWebtoon": false,
        "isIndexing": 1,
        "genres": [
            {"name": "School Life", "slug": "school-life", "id": 28},
            {"name": "Comedy", "slug": "comedy", "id": 98},
            {"name": "Romance", "slug": "romance", "id": 101},
            {"name": "Action", "slug": "action", "id": 97},
            {"name": "HangTruyen", "slug": "hangtruyen", "id": 238}
        ],
        "chapters": [
            {"id": 2169687, "name": "Chapter 13", "index": 13, "slug": "chapter-13", "countView": 12, "releasedAt": "2026-01-01T17:26:40.000Z", "translators": []},
            {"id": 2169688, "name": "Chapter 12", "index": 12, "slug": "chapter-12", "countView": 4, "releasedAt": "2026-01-01T17:27:02.000Z", "translators": []}
        ],
        "category": {"id": 1, "name": "Manga", "slug": "manga"},
        "rawSlug": "gto-fury-of-death-yamada",
        "sourceAllViewString": "284",
        "sourceFollowString": "0"
    };
    const chapterDetail = null;
</script>

<script>
    async function postComment(mangaId, chapterId, content, parentCommentId) {
        const response = await $.ajax({
            type: 'POST',
            xhrFields: { withCredentials: true },
            url: 'https://api.hangtruyen.vip/comments' + (!!parentCommentId ? `/${parentCommentId}/reply` : ''),
            contentType: 'application/json',
            data: JSON.stringify({ mangaId, chapterId, content }),
            dataType: 'json',
        }).catch(() => {
            return null;
        });

        if (response && response.status === 200) {
            return response.data;
        }
        return null;
    }

    async function getComment(mangaId, chapterId, page, pageSize, orderBy) {
        const url = new URL('https://api.hangtruyen.vip/comments');
        url.searchParams.append('mangaId', mangaId);
        if (chapterId) {
            url.searchParams.append('chapterId', chapterId);
        }
        if (page) {
            url.searchParams.append('page', page);
        }
        if (pageSize) {
            url.searchParams.append('pageSize', pageSize);
        }
        if (orderBy) {
            url.searchParams.append('orderBy[]', orderBy);
        }

        const response = await $.ajax({
            type: 'GET',
            xhrFields: { withCredentials: true },
            url: url.toString(),
            contentType: 'application/json',
        }).catch(() => {
            return null;
        });

        if (response && response.status === 200) {
            return response.data;
        }
        return null;
    }

    async function getLikedCommentIds(mangaId) {
        const response = await $.ajax({
            type: 'GET',
            xhrFields: { withCredentials: true },
            url: `https://api.hangtruyen.vip/comments/liked-comments?mangaId=${mangaId}`,
            contentType: 'application/json',
            dataType: 'json',
        }).catch(() => {
            return null;
        });

        if (response && response.status === 200) {
            return response.data;
        }
        return [];
    }

    async function likeComment(commentId) {
        const response = await $.ajax({
            type: 'PATCH',
            xhrFields: { withCredentials: true },
            url: `https://api.hangtruyen.vip/comments/${commentId}/like`,
            contentType: 'application/json',
            dataType: 'json',
        }).catch(() => {
            return null;
        });

        if (response && response.status === 200) {
            return response.data;
        }
        return null;
    }

    async function postReport(mangaId, reasons, chapterId) {
        const response = await $.ajax({
            type: 'POST',
            xhrFields: { withCredentials: true },
            url: chapterId
                ? `https://api.hangtruyen.vip/mangas/${mangaId}/${chapterId}/report`
                : `https://api.hangtruyen.vip/mangas/${mangaId}/report`,
            contentType: 'application/json',
            data: JSON.stringify({ reasons }),
            dataType: 'json',
        }).catch(() => {
            return null;
        });

        if (response && response.status === 200) {
            return response.data;
        }
        return null;
    }

    async function voteManga(mangaId, vote) {
        const response = await $.ajax({
            type: 'POST',
            xhrFields: { withCredentials: true },
            url: 'https://api.hangtruyen.vip/mangas/' + mangaId + '/vote',
            contentType: 'application/json',
            data: JSON.stringify({ vote }),
            dataType: 'json',
        }).catch(() => {
            return null;
        });

        if (response && response.status === 200) {
            return response.data;
        }
        return null;
    }

    async function followManga(mangaId) {
        const response = await $.ajax({
            type: 'POST',
            xhrFields: { withCredentials: true },
            url: 'https://api.hangtruyen.vip/mangas/' + mangaId + '/follow',
            contentType: 'application/json',
        }).catch(() => {
            return null;
        });

        if (response && response.status === 200) {
            return response.data;
        }
        return null;
    }
</script>

    <script src="{{ asset('js/custom/comment/comment.js') }}"></script>
    <script src="{{ asset('js/custom/manga-detail/report-manga.js') }}"></script>
    <script src="{{ asset('js/custom/manga-detail/index.js') }}"></script>
@endpush

@extends('layouts.app')

@php
    $keyword = $keyword ?? '';
    $totalResults = $totalResults ?? 0;
    $results = $results ?? [];
    $currentPage = $currentPage ?? 1;
    $totalPages = $totalPages ?? 1;
@endphp

@section('title', ($keyword ? $keyword . ' - ' : '') . 'Tìm kiếm Truyện Tranh Mới Nhất | HangTruyen')
@section('description', 'Tìm kiếm truyện tranh ' . ($keyword ? $keyword . ' ' : '') . 'mới nhất, truyện full chap đầy đủ. Thông tin về truyện hay cập nhật mới nhất tại HangTruyen. ' . ($totalResults > 0 ? 'Tìm thấy ' . number_format($totalResults) . ' kết quả.' : ''))
@section('keywords', $keyword . ', tìm kiếm truyện tranh, hangtruyen, đọc truyện, truyện tranh, truyện full, manga online, manhua online, manhwa online')
@section('canonical', url('/tim-kiem' . ($keyword ? '?keyword=' . urlencode($keyword) : '')))
@section('og:url', url('/tim-kiem' . ($keyword ? '?keyword=' . urlencode($keyword) : '')))
@section('og:type', 'website')
@section('og:title', ($keyword ? $keyword . ' - ' : '') . 'Tìm kiếm Truyện Tranh | HangTruyen')
@section('og:description', 'Tìm kiếm truyện tranh ' . ($keyword ? $keyword . ' ' : '') . 'mới nhất tại HangTruyen')
@section('og:image', asset('images/logo-dark.png'))

@push('head')
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "SearchResultsPage",
    "name": "{{ $keyword ? 'Tìm kiếm: ' . $keyword : 'Tìm kiếm truyện tranh' }}",
    "description": "Kết quả tìm kiếm truyện tranh tại HangTruyen",
    "url": "{{ url('/tim-kiem' . ($keyword ? '?keyword=' . urlencode($keyword) : '')) }}"
}
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
            "name": "Tìm kiếm{{ $keyword ? ': ' . $keyword : '' }}",
            "item": "{{ url('/tim-kiem' . ($keyword ? '?keyword=' . urlencode($keyword) : '')) }}"
        }
    ]
}
</script>
@endpush

@section('content')
<div class="search-wrapper">
    <div class="container">
        <div class="page-breadcrumb">
            <span class="item"><a href="{{ url('/') }}">Trang chủ</a></span>
            <span class="item breadcrumb_last" aria-current="page">Tìm kiếm</span>
        </div>
        <div class="row flex-lg-row-reverse">
            <div class="col-lg-4">
                @include('search.components.sidebar-filter', [
                    'keyword' => $keyword,
                    'allTags' => $allTags ?? [],
                    'tags' => $tags ?? [],
                    'sort' => $sort ?? 'updated_at_desc',
                ])
            </div>
            <div class="col-lg-8">
                <div class="group-title">
                    <div class="only-title">
                        @php
                            $hasFilters = !empty($keyword) || (isset($tags) && count($tags) > 0) || (isset($categories) && count($categories) > 0) || (isset($sort) && $sort != 'updated_at_desc');
                        @endphp
                        <h1 class="m-title title">{{ $hasFilters ? 'Kết quả tìm kiếm' . ($keyword ? ': ' . $keyword : '') : 'Truyện mới nhất' }}</h1>
                        <h2 class="sub">{{ $hasFilters ? 'Kết quả được lọc theo mong muốn của bạn' : 'Danh sách 10 truyện mới cập nhật' }}</h2>
                    </div>
                    @if($hasFilters)
                        <span>Có <strong class="color">{{ number_format($totalResults) }}</strong> kết quả liên quan</span>
                    @else
                        <span>Có <strong class="color">{{ number_format($totalResults) }}</strong> truyện trong hệ thống</span>
                    @endif
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
                                            @if(isset($manga['chapters']) && is_array($manga['chapters']) && count($manga['chapters']) > 0)
                                                @foreach(array_slice($manga['chapters'], 0, 2) as $chapter)
                                                    @php
                                                        $chapterTime = isset($chapter['updated_at']) && $chapter['updated_at'] 
                                                            ? formatVietnameseTime($chapter['updated_at'])
                                                            : ($chapter['releasedAt'] ?? '');
                                                        $chapterNumber = $chapter['number'] ?? '';
                                                        $chapterSlug = $chapter['slug'] ?? 'chapter-' . $chapterNumber;
                                                        $chapterUrl = '/truyen-tranh/' . $manga['slug'] . '/' . $chapterSlug;
                                                    @endphp
                                                    <li class="chapter">
                                                        <a data-id="{{ $chapter['id'] ?? '' }}" href="{{ $chapterUrl }}" title="{{ $chapter['name'] ?? '' }}">
                                                            {{ $chapter['name'] ?? '' }}<span>{{ $chapterTime }}</span>
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
                        @php
                            $queryParams = [];
                            if ($keyword) {
                                $queryParams['keyword'] = $keyword;
                            }
                            if (isset($sort) && $sort) {
                                $queryParams['sort'] = $sort;
                            }
                            if (isset($categories) && is_array($categories) && count($categories) > 0) {
                                $queryParams['categories'] = implode(',', $categories);
                            }
                            if (isset($tags) && is_array($tags) && count($tags) > 0) {
                                $queryParams['tags'] = implode(',', $tags);
                            }
                            $queryString = http_build_query($queryParams);
                        @endphp
                        <ul class="pagination" data-count-page="{{ $totalPages }}">
                            @if($currentPage > 1)
                                <li data-page="0">
                                    <a class="prev-page" href="{{ url('/tim-kiem') }}?page={{ $currentPage - 1 }}{{ $queryString ? '&' . $queryString : '' }}" title="Chuyển đến trang {{ $currentPage - 1 }}" data-page="{{ $currentPage - 1 }}" rel="prev">
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
                                    <a href="{{ url('/tim-kiem') }}?page={{ $i }}{{ $queryString ? '&' . $queryString : '' }}" data-page="{{ $i }}">{{ $i }}</a>
                                </li>
                            @endfor

                            @if($currentPage < $totalPages)
                                <li data-page="0">
                                    <a class="next-page" href="{{ url('/tim-kiem') }}?page={{ $currentPage + 1 }}{{ $queryString ? '&' . $queryString : '' }}" title="Chuyển đến trang {{ $currentPage + 1 }}" data-page="{{ $currentPage + 1 }}" rel="next">
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
<script src="{{ asset('js/custom/search-advanced/index.js') }}"></script>
<script>
    $(document).ready(function() {
        $('a.btn-filter').off('click');
        $('.form-search-normal > .form-search').off('submit');
        
        const userSelectedTags = new Set();
        $('#view-all-tags').on('click', function(e) {
            e.preventDefault();
            const $hiddenTags = $('.list-genres .tag-item.d-none');
            const $link = $(this);
            
            if ($hiddenTags.length > 0) {
                $hiddenTags.removeClass('d-none');
                $link.text('Ẩn bớt');
            } else {    
                $('.list-genres .tag-item').each(function(index) {
                    if (index >= 23) {
                        $(this).addClass('d-none');
                    }
                });
                $link.text('Xem tất cả');
            }
        });
        
        function bindTagClickHandlers() {
            $('.list-genres span.tag-item, .list-genres > span').off('click.tagClick');
            
            $('.list-genres span.tag-item, .list-genres > span').on('click.tagClick', function(e) {
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();
                
                const $tag = $(this);
                const element = this;
                const tagId = $tag.attr('data-value') || $tag.data('value');
                
                const wasActive = $tag.hasClass('active');
                $tag.toggleClass('active');
                element.classList.toggle('active');
                
                if ($tag.hasClass('active')) {
                    userSelectedTags.add(tagId);
                } else {
                    userSelectedTags.delete(tagId);
                }
                
                return false;
            });
        }
        
        bindTagClickHandlers();
        
        setTimeout(bindTagClickHandlers, 1000);
        
        function setActiveCategoriesFromURL() {
            const url = new URL(window.location.href);
            const categoryIds = url.searchParams.get('categoryIds') ? url.searchParams.get('categoryIds').split(',').map(id => id.trim()).filter(id => id) : [];
            
            if (categoryIds.length > 0) {
                $('.list-cats .checkbox-all').prop('checked', false);
                
                categoryIds.forEach(catId => {
                    $(`.list-cats input[value="${catId}"]`).prop('checked', true);
                });
            } else {
                $('.list-cats .checkbox-all').prop('checked', true);
            }
        }
        
        setActiveCategoriesFromURL();
        
        $('.list-cats input[type="checkbox"]').on('change', function() {
            if ($(this).hasClass('checkbox-all')) {
                if ($(this).is(':checked')) {
                    $('.list-cats input[type="checkbox"]').not('.checkbox-all').prop('checked', false);
                }
            } else {
                $('.list-cats .checkbox-all').prop('checked', false);
            }
        });
        
        $('#dd-sort .dropdown-item').on('click', function(e) {
            e.preventDefault();
            const sortValue = $(this).data('value');
            $('#dd-sort .dropdown-toggle span').text($(this).text());
            $('#dd-sort').attr('data-value', sortValue);
        });
        
        @if(isset($sort) && $sort)
            const currentSort = '{{ $sort }}';
            let sortFound = false;
            $('#dd-sort .dropdown-item').each(function() {
                if ($(this).data('value') === currentSort) {
                    $('#dd-sort .dropdown-toggle span').text($(this).text());
                    $('#dd-sort').attr('data-value', currentSort);
                    sortFound = true;
                }
            });
            if (!sortFound) {
                $('#dd-sort').attr('data-value', 'updated_at_desc');
            }
        @else
            $('#dd-sort').attr('data-value', 'updated_at_desc');
        @endif
        
        function setActiveTagsFromURL() {
            const url = new URL(window.location.href);
            const chosenGenreIds = url.searchParams.get('genreIds') ? url.searchParams.get('genreIds').split(',').map(id => id.trim()).filter(id => id) : [];
            const chosenTags = url.searchParams.get('tags') ? url.searchParams.get('tags').split(',').map(id => id.trim()).filter(id => id) : [];
            const allChosenTags = [...chosenGenreIds, ...chosenTags];
            
            @if(isset($tags) && is_array($tags) && count($tags) > 0)
                const serverTags = @json($tags);
                serverTags.forEach(tagId => {
                    const tagStr = String(tagId).trim();
                    if (tagStr && !allChosenTags.includes(tagStr)) {
                        allChosenTags.push(tagStr);
                    }
                });
            @endif
            
            if (allChosenTags.length === 0) {
                return;
            }
            
            const genreElements = $('.list-genres span, .list-genres > span');
            
            for (let i = 0; i < genreElements.length; i++) {
                const $element = $(genreElements[i]);
                const genreId = $element.attr('data-value') || $element.data('value');
                
                if (genreId) {
                    const isActive = allChosenTags.some(tagId => {
                        const genreIdStr = String(genreId).trim();
                        const tagIdStr = String(tagId).trim();
                        return genreIdStr === tagIdStr || genreId == tagId || genreIdStr === tagIdStr;
                    });
                    
                    if (isActive) {
                        $element.addClass('active');
                        userSelectedTags.add(String(genreId));
                    }
                }
            }
        }
        
        setActiveTagsFromURL();
        
        function handleFilterSubmit(e) {
            if (e) {
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();
            }
            
            const currentUrl = new URL(window.location.href);
            const keyword = $('.form-search input[name="keyword"], .form-search-normal input[name="keyword"]').val() || '';
            const sort = $('#dd-sort').attr('data-value') || currentUrl.searchParams.get('orderBy') || currentUrl.searchParams.get('sort') || 'updated_at_desc';
            const selectedCategories = [];
            const selectedTags = [];
            
            $('.list-cats input[type="checkbox"]:checked').not('.checkbox-all').each(function() {
                const val = $(this).val();
                if (val) {
                    selectedCategories.push(val);
                }
            });
            
            $('.list-genres span.active, .list-genres > span.active').each(function() {
                const tagValue = $(this).attr('data-value') || $(this).data('value');
                if (tagValue) {
                    selectedTags.push(String(tagValue));
                }
            });
            
            if (selectedTags.length === 0) {
                const urlGenreIds = currentUrl.searchParams.get('genreIds');
                if (urlGenreIds) {
                    selectedTags.push(...urlGenreIds.split(',').map(id => id.trim()).filter(id => id));
                }
            }
            
            const url = new URL(window.location.origin + '/tim-kiem');
            
            if (keyword) {
                url.searchParams.set('keyword', keyword);
            } else {
                url.searchParams.delete('keyword');
            }
            
            if (sort) {
                url.searchParams.set('orderBy', sort);
                url.searchParams.set('sort', sort);
            } else {
                url.searchParams.delete('orderBy');
                url.searchParams.delete('sort');
            }
            
            if (selectedCategories.length > 0) {
                url.searchParams.set('categoryIds', selectedCategories.filter(c => !isNaN(c)).join(','));
            } else {
                url.searchParams.delete('categoryIds');
            }
            
            if (selectedTags.length > 0) {
                url.searchParams.set('genreIds', selectedTags.join(','));
            } else {
                url.searchParams.delete('genreIds');
            }
            
            url.searchParams.set('page', '1');
            
            window.location.href = url.href;
        }
        
        $('a.btn-filter, #btn-filter-submit').off('click');
        $('.form-search, .form-search-normal > .form-search').off('submit');
        
        $('a.btn-filter, #btn-filter-submit').on('click.filterSubmit', function(e) {
            handleFilterSubmit(e);
            return false;
        });
        
        $('.form-search, .form-search-normal > .form-search').on('submit.filterSubmit', function(e) {
            handleFilterSubmit(e);
            return false;
        });
    });
</script>
@endpush
@endsection

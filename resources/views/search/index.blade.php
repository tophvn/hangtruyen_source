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
                                    <a class="prev-page" href="{{ url('/tim-kiem') }}?page={{ $currentPage - 1 }}{{ $queryString ? '&' . $queryString : '' }}" title="Chuyển đến trang {{ $currentPage - 1 }}" data-page="{{ $currentPage - 1 }}">
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
                                    <a class="next-page" href="{{ url('/tim-kiem') }}?page={{ $currentPage + 1 }}{{ $queryString ? '&' . $queryString : '' }}" title="Chuyển đến trang {{ $currentPage + 1 }}" data-page="{{ $currentPage + 1 }}">
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
        // Remove conflicting handlers from search-advanced/index.js
        $('a.btn-filter').off('click');
        $('.form-search-normal > .form-search').off('submit');
        
        // Track user selected tags to prevent URL from overriding user choices
        const userSelectedTags = new Set();
        // Toggle hiển thị tags khi bấm "Xem tất cả"
        $('#view-all-tags').on('click', function(e) {
            e.preventDefault();
            const $hiddenTags = $('.list-genres .tag-item.d-none');
            const $link = $(this);
            
            if ($hiddenTags.length > 0) {
                // Hiển thị các tags ẩn
                $hiddenTags.removeClass('d-none');
                $link.text('Ẩn bớt');
            } else {
                // Ẩn lại các tags (trừ 23 tags đầu tiên)
                $('.list-genres .tag-item').each(function(index) {
                    if (index >= 23) {
                        $(this).addClass('d-none');
                    }
                });
                $link.text('Xem tất cả');
            }
        });
        
        // Handle tag click - ensure it works even if custom.js runs
        // Use both jQuery and native DOM to ensure compatibility
        function bindTagClickHandlers() {
            // Remove any existing handlers first
            $('.list-genres span.tag-item, .list-genres > span').off('click.tagClick');
            
            // Bind with namespace - handle both .tag-item and direct span children
            $('.list-genres span.tag-item, .list-genres > span').on('click.tagClick', function(e) {
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();
                
                const $tag = $(this);
                const element = this;
                const tagId = $tag.attr('data-value') || $tag.data('value');
                
                // Toggle using both methods
                const wasActive = $tag.hasClass('active');
                $tag.toggleClass('active');
                element.classList.toggle('active');
                
                // Track user selection
                if ($tag.hasClass('active')) {
                    userSelectedTags.add(tagId);
                } else {
                    userSelectedTags.delete(tagId);
                }
                
                return false;
            });
        }
        
        // Bind immediately
        bindTagClickHandlers();
        
        // Re-bind after a delay to ensure it works even if custom.js runs later
        setTimeout(bindTagClickHandlers, 1000);
        
        // Set selected categories from URL on page load
        function setActiveCategoriesFromURL() {
            const url = new URL(window.location.href);
            const categoryIds = url.searchParams.get('categoryIds') ? url.searchParams.get('categoryIds').split(',').map(id => id.trim()).filter(id => id) : [];
            
            if (categoryIds.length > 0) {
                // Uncheck "Tất cả"
                $('.list-cats .checkbox-all').prop('checked', false);
                
                // Check selected categories
                categoryIds.forEach(catId => {
                    $(`.list-cats input[value="${catId}"]`).prop('checked', true);
                });
            } else {
                // If no categories selected, check "Tất cả"
                $('.list-cats .checkbox-all').prop('checked', true);
            }
        }
        
        // Call on page load
        setActiveCategoriesFromURL();
        
        // Handle category checkbox
        $('.list-cats input[type="checkbox"]').on('change', function() {
            if ($(this).hasClass('checkbox-all')) {
                if ($(this).is(':checked')) {
                    $('.list-cats input[type="checkbox"]').not('.checkbox-all').prop('checked', false);
                }
            } else {
                $('.list-cats .checkbox-all').prop('checked', false);
            }
        });
        
        // Handle sort dropdown
        $('#dd-sort .dropdown-item').on('click', function(e) {
            e.preventDefault();
            const sortValue = $(this).data('value');
            $('#dd-sort .dropdown-toggle span').text($(this).text());
            $('#dd-sort').attr('data-value', sortValue);
        });
        
        // Set current sort value
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
        
        // Set selected tags from URL (like original search-advanced/index.js)
        // Only set from URL, don't remove user-selected tags
        function setActiveTagsFromURL() {
            const url = new URL(window.location.href);
            const chosenGenreIds = url.searchParams.get('genreIds') ? url.searchParams.get('genreIds').split(',').map(id => id.trim()).filter(id => id) : [];
            const chosenTags = url.searchParams.get('tags') ? url.searchParams.get('tags').split(',').map(id => id.trim()).filter(id => id) : [];
            const allChosenTags = [...chosenGenreIds, ...chosenTags];
            
            // Also support tags from server-side (backward compatibility)
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
                // No tags in URL, don't modify anything (let user click to select)
                return;
            }
            
            // Set active tags - check both .tag-item and direct span children
            const genreElements = $('.list-genres span, .list-genres > span');
            
            for (let i = 0; i < genreElements.length; i++) {
                const $element = $(genreElements[i]);
                const genreId = $element.attr('data-value') || $element.data('value');
                
                // Check if this tag should be active
                if (genreId) {
                    // Try both string and number comparison (loose equality)
                    const isActive = allChosenTags.some(tagId => {
                        // Convert both to strings for comparison
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
        
        // Call only once when page loads (to set from URL)
        setActiveTagsFromURL();
        
        // Function to handle filter submit (reusable)
        function handleFilterSubmit(e) {
            if (e) {
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();
            }
            
            // Get current filters from URL
            const currentUrl = new URL(window.location.href);
            const keyword = $('.form-search input[name="keyword"], .form-search-normal input[name="keyword"]').val() || '';
            const sort = $('#dd-sort').attr('data-value') || currentUrl.searchParams.get('orderBy') || currentUrl.searchParams.get('sort') || 'updated_at_desc';
            const selectedCategories = [];
            const selectedTags = [];
            
            // Get selected categories from checkboxes
            $('.list-cats input[type="checkbox"]:checked').not('.checkbox-all').each(function() {
                const val = $(this).val();
                if (val) {
                    selectedCategories.push(val);
                }
            });
            
            // Get selected tags - check both active class selectors
            $('.list-genres span.active, .list-genres > span.active').each(function() {
                const tagValue = $(this).attr('data-value') || $(this).data('value');
                if (tagValue) {
                    selectedTags.push(String(tagValue));
                }
            });
            
            // If no tags selected in DOM but have in URL, keep URL tags
            if (selectedTags.length === 0) {
                const urlGenreIds = currentUrl.searchParams.get('genreIds');
                if (urlGenreIds) {
                    selectedTags.push(...urlGenreIds.split(',').map(id => id.trim()).filter(id => id));
                }
            }
            
            // Build URL using URLSearchParams
            const url = new URL(window.location.origin + '/tim-kiem');
            
            if (keyword) {
                url.searchParams.set('keyword', keyword);
            } else {
                url.searchParams.delete('keyword');
            }
            
            if (sort) {
                url.searchParams.set('orderBy', sort);
                url.searchParams.set('sort', sort); // Also set sort for backward compatibility
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
            
            // Reset to page 1 when filtering
            url.searchParams.set('page', '1');
            
            window.location.href = url.href;
        }
        
        // Handle filter submit - remove old handlers first to avoid duplicates
        // Remove all handlers first, including from search-advanced/index.js
        $('a.btn-filter, #btn-filter-submit').off('click');
        $('.form-search, .form-search-normal > .form-search').off('submit');
        
        // Bind our handlers with high priority
        $('a.btn-filter, #btn-filter-submit').on('click.filterSubmit', function(e) {
            handleFilterSubmit(e);
            return false;
        });
        
        // Handle form search submit - preserve all filters
        $('.form-search, .form-search-normal > .form-search').on('submit.filterSubmit', function(e) {
            handleFilterSubmit(e);
            return false;
        });
    });
</script>
@endpush
@endsection

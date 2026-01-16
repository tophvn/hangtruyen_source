<div class="sidebar mb-3">
    <span class="title">Bộ lọc</span>
    <div class="form-search-normal">
        <form class="form-search" action="{{ url('/tim-kiem') }}" method="GET">
            <input class="form-control" type="text" name="keyword" placeholder="Tìm kiếm tên truyện" aria-label="Tìm kiếm" value="{{ $keyword ?? '' }}" />
            <i class="icon-search-normal"></i>
        </form>
        <a href="" class="fsn-icon d-lg-none">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                <path d="M10.9404 22.65C10.4604 22.65 9.99039 22.53 9.55039 22.29C8.67039 21.8 8.14039 20.91 8.14039 19.91V14.61C8.14039 14.11 7.81039 13.36 7.50039 12.98L3.76039 9.01998C3.13039 8.38998 2.65039 7.30998 2.65039 6.49998V4.19998C2.65039 2.59998 3.86039 1.34998 5.40039 1.34998H18.6004C20.1204 1.34998 21.3504 2.57998 21.3504 4.09998V6.29998C21.3504 7.34998 20.7204 8.53998 20.1304 9.12998L15.8004 12.96C15.3804 13.31 15.0504 14.08 15.0504 14.7V19C15.0504 19.89 14.4904 20.92 13.7904 21.34L12.4104 22.23C11.9604 22.51 11.4504 22.65 10.9404 22.65ZM5.40039 2.84998C4.70039 2.84998 4.15039 3.43998 4.15039 4.19998V6.49998C4.15039 6.86998 4.45039 7.58998 4.83039 7.96998L8.64039 11.98C9.15039 12.61 9.65039 13.66 9.65039 14.6V19.9C9.65039 20.55 10.1004 20.87 10.2904 20.97C10.7104 21.2 11.2204 21.2 11.6104 20.96L13.0004 20.07C13.2804 19.9 13.5604 19.36 13.5604 19V14.7C13.5604 13.63 14.0804 12.45 14.8304 11.82L19.1104 8.02998C19.4504 7.68998 19.8604 6.87998 19.8604 6.28998V4.09998C19.8604 3.40998 19.3004 2.84998 18.6104 2.84998H5.40039Z" fill="#473D98" />
                <path d="M5.99968 10.75C5.85968 10.75 5.72968 10.71 5.59968 10.64C5.24968 10.42 5.13968 9.95002 5.35968 9.60002L10.2897 1.70002C10.5097 1.35002 10.9697 1.24002 11.3197 1.46002C11.6697 1.68002 11.7797 2.14002 11.5597 2.49002L6.62968 10.39C6.48968 10.62 6.24968 10.75 5.99968 10.75Z" fill="#473D98" />
            </svg>
        </a>
    </div>
    <div class="list-settings">
        <div id="dd-sort" class="dropdown">
            <a href="" class="dropdown-toggle" id="dropdownZoom" data-bs-toggle="dropdown" aria-expanded="false">
                <span>Sắp xếp</span><i class="icon-arrow-down-1"></i>
            </a>
            <div class="dropdown-menu" aria-labelledby="dropdownZoom">
                <div class="list-mode">
                    <span><a data-value="view_desc" class="dropdown-item" href="#">Lượt xem giảm dần</a></span>
                    <span><a data-value="view_asc" class="dropdown-item" href="#">Lượt xem tăng dần</a></span>
                    <span><a data-value="udpated_at_date_desc" class="dropdown-item" href="#">Ngày cập nhật giảm dần</a></span>
                    <span><a data-value="udpated_at_date_asc" class="dropdown-item" href="#">Ngày cập nhật tăng dần</a></span>
                    <span><a data-value="created_at_date_desc" class="dropdown-item" href="#">Ngày đăng giảm dần</a></span>
                    <span><a data-value="created_at_date_asc" class="dropdown-item" href="#">Ngày đăng tăng dần</a></span>
                </div>
            </div>
        </div>
        <div>
            <span>Thể loại:</span>
            <div class="list-cats list-checkbox">
                <div class="form-check">
                    <input class="form-check-input checkbox-all" type="checkbox" value="" id="cat--all">
                    <label class="form-check-label" for="cat--all">Tất cả</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="1" id="cat--1">
                    <label class="form-check-label" for="cat--1">Manga</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="2" id="cat--2">
                    <label class="form-check-label" for="cat--2">Manhua</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="3" id="cat--3">
                    <label class="form-check-label" for="cat--3">Manhwa</label>
                </div>
            </div>
        </div>
        <div>
            <span>Tags:</span>
            <div class="list-genres">
                @if(isset($allTags) && is_array($allTags) && count($allTags) > 0)
                    @foreach($allTags as $index => $tag)
                        @php
                            $isHidden = $index >= 23;
                            $tagId = $tag['id'] ?? '';
                            $tagValue = $tag['slug'] ?? $tag['name'] ?? (is_string($tag) ? $tag : '');
                            $tagName = $tag['name'] ?? (is_string($tag) ? $tag : '');
                            // Check if selected by ID, slug, or name
                            $isSelected = isset($tags) && is_array($tags) && (
                                in_array($tagId, $tags) || 
                                in_array($tagValue, $tags) || 
                                in_array($tagName, $tags)
                            );
                        @endphp
                        <span data-value="{{ $tagId }}" class="tag-item {{ $isHidden ? 'd-none' : '' }} {{ $isSelected ? 'active' : '' }}" style="cursor: pointer;">#{{ $tagName }}</span>
                    @endforeach
                @else
                    <span class="text-muted">Chưa có tags</span>
                @endif
            </div>
            @if(isset($allTags) && count($allTags) > 23)
                <a href="javascript:void(0)" id="view-all-tags" class="view-all mt-2 d-inline-block">Xem tất cả</a>
            @endif
        </div>
        <a class="btn btn-filter" href="javascript:void(0)" id="btn-filter-submit">
            <span>Tìm kiếm</span>
        </a>
    </div>
</div>

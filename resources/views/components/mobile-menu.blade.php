<div class="offcanvas offcanvas-start" tabindex="-1" id="menumobile" aria-labelledby="offcanvasLabel">
    <div class="offcanvas-body">
        <div class="off-header">
            <div class="dark-mode">
                <button type="button" class="btn-switch" aria-label="Switch mode">
                    <img src="{{ asset('images/btn-lightmode.png') }}" class="light" width="38" height="35" alt="Light mode">
                    <img src="{{ asset('images/btn-darkmode.png') }}" class="dark" width="38" height="35" alt="Dark mode">
                </button>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close">
                <i class="icon-close-circle"></i>
            </button>
        </div>
        <ul class="nav navbar-nav flex-wrap main-menu">
            <li class="active">
                <a href="{{ url('/') }}">Trang chủ</a>
            </li>
            <li class="">
                <a href="/hot-nhat?type=all">Hot nhất</a>
            </li>
            <li>
                <a href="/tin-tuc">Tin tức</a>
            </li>
            <li class="has-sub">
                <a href="/the-loai" class="sub-toggle" aria-expanded="false">Thể loại</a>
                <div class="dropdown-menu">
                    <span><a class="dropdown-item" href="/the-loai/manga">Manga (Nhật)</a></span>
                    <span><a class="dropdown-item" href="/the-loai/manhua">Manhua (Trung)</a></span>
                    <span><a class="dropdown-item" href="/the-loai/manhwa">Manhwa (Hàn)</a></span>
                    <span><a class="dropdown-item" href="/genre/viet-nam">Việt Nam</a></span>
                </div>
            </li>
            <li class="has-sub">
                <a href="/the-loai" class="sub-toggle" aria-expanded="false">Tags</a>
                <div class="dropdown-menu">
                    @php
                        $displayCategories = $categories->take(9);
                    @endphp
                    @if($displayCategories->count() > 0)
                        @foreach($displayCategories as $category)
                            <span><a class="dropdown-item" href="/genre/{{ $category->slug }}">{{ $category->name }}@if($category->manga_count > 0) ({{ number_format($category->manga_count) }})@endif</a></span>
                        @endforeach
                    @else
                        <span><a class="dropdown-item" href="/genre/action">Action</a></span>
                        <span><a class="dropdown-item" href="/genre/romance">Romance</a></span>
                        <span><a class="dropdown-item" href="/genre/comedy">Comedy</a></span>
                    @endif
                    <span><button class="view-all" onclick="viewAllTags()">Xem tất cả</button></span>
                </div>
            </li>
        </ul>
    </div>
</div>

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
                    <span><a class="dropdown-item" href="/the-loai/marvel-comics">Marvel Comics (Mỹ)</a></span>
                    <span><a class="dropdown-item" href="/the-loai/dc-comics">DC Comics (Mỹ)</a></span>
                </div>
            </li>
            <li class="has-sub">
                <a href="/the-loai" class="sub-toggle" aria-expanded="false">Tags</a>
                <div class="dropdown-menu">
                    <span><a class="dropdown-item" href="/genre/hangtruyen">HangTruyen (471)</a></span>
                    <span><a class="dropdown-item" href="/genre/action">Action (6028)</a></span>
                    <span><a class="dropdown-item" href="/genre/romance">Romance (5931)</a></span>
                    <span><a class="dropdown-item" href="/genre/comedy">Comedy (5548)</a></span>
                    <span><a class="dropdown-item" href="/genre/fantasy">Fantasy (3983)</a></span>
                    <span><a class="dropdown-item" href="/genre/drama">Drama (3644)</a></span>
                    <span><a class="dropdown-item" href="/genre/adventure">Adventure (3114)</a></span>
                    <span><a class="dropdown-item" href="/genre/ngon-tinh">Ngôn Tình (2790)</a></span>
                    <span><a class="dropdown-item" href="/genre/school-life">School Life (2449)</a></span>
                    <span><button class="view-all" onclick="viewAllTags()">Xem tất cả</button></span>
                </div>
            </li>
        </ul>
    </div>
</div>

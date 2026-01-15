<header class="header" id="header">
    <div class="container">
        <div class="main-header d-flex align-items-center">
            <a id="mobile_menu" class="d-flex d-xl-none" data-bs-toggle="offcanvas" href="#menumobile" aria-label="Menu">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                    <path d="M21 7.75H3C2.59 7.75 2.25 7.41 2.25 7C2.25 6.59 2.59 6.25 3 6.25H21C21.41 6.25 21.75 6.59 21.75 7C21.75 7.41 21.41 7.75 21 7.75Z" fill="#1E201E"/>
                    <path d="M21 12.75H3C2.59 12.75 2.25 12.41 2.25 12C2.25 11.59 2.59 11.25 3 11.25H21C21.41 11.25 21.75 11.59 21.75 12C21.75 12.41 21.41 12.75 21 12.75Z" fill="#1E201E"/>
                    <path d="M21 17.75H3C2.59 17.75 2.25 17.41 2.25 17C2.25 16.59 2.59 16.25 3 16.25H21C21.41 16.25 21.75 16.59 21.75 17C21.75 17.41 21.41 17.75 21 17.75Z" fill="#1E201E"/>
                </svg>
            </a>
            <a class="d-flex d-xl-none menu-random" href="/random">
                <img alt="" src="{{ asset('images/random.png') }}" alt="Random" width="24" height="24" />
            </a>
            <a class="logo" title="Truyện tranh online" href="{{ url('/') }}">
                <img class="logo-light" alt="Đọc truyện tranh miễn phí tại HangTruyen" src="{{ asset('images/logo.png') }}" width="150" height="54"/>
                <img class="logo-dark" alt="Đọc truyện tranh miễn phí tại HangTruyen" src="{{ asset('images/logo-dark.png') }}" width="150" height="54"/>
            </a>

            <ul class="nav navbar-nav flex-row flex-wrap main-menu d-none d-xl-flex">
                <li class="">
                    <a href="/random">Random</a>
                </li>
                <li class="">
                    <a href="/hot-nhat?type=all">Hot nhất</a>
                </li>
                <li class="has-sub">
                    <a href="/the-loai" class="sub-toggle" aria-expanded="false">Thể loại
                        <i class="icon-arrow-down-1"></i>
                    </a>
                    <div class="dropdown-menu">
                        <span><a class="dropdown-item" href="/the-loai/manga">Manga (Nhật)</a></span>
                        <span><a class="dropdown-item" href="/the-loai/manhua">Manhua (Trung)</a></span>
                        <span><a class="dropdown-item" href="/the-loai/manhwa">Manhwa (Hàn)</a></span>
                        <span><a class="dropdown-item" href="/the-loai/marvel-comics">Marvel Comics (Mỹ)</a></span>
                        <span><a class="dropdown-item" href="/the-loai/dc-comics">DC Comics (Mỹ)</a></span>
                    </div>
                </li>
                <li class="has-sub menu-tag">
                    <a href="/genre" class="sub-toggle" aria-expanded="false">Tags
                        <i class="icon-arrow-down-1"></i>
                    </a>
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
                        <div class="more-tags" style="display: none;">
                            <!-- More tags sẽ được thêm sau -->
                        </div>
                    </div>
                </li>
                <li>
                    <a href="/tin-tuc">Tin tức</a>
                </li>
            </ul>

            <div class="header-right d-flex align-items-center justify-content-end ms-auto">
                <a href="#" class="toggle-formsearch ms-auto d-flex d-xl-none" aria-label="Search">
                    <i class="icon-search-normal"></i>
                </a>
                <!-- Search Modal -->
                <form class="d-xl-flex form-search" id="form-search" action="/tim-kiem">
                    <input class="form-control" type="text" placeholder="Tìm kiếm" aria-label="Tìm kiếm" />
                    <a href="/tim-kiem" class="i-filter"> Nâng cao </a>
                    <i class="icon-search-normal"></i>
                    <button type="button" class="s-clear">
                        <i class="icon-close-circle"></i>
                    </button>
                    <div class="nav search-result-wrapper" id="search-suggest">
                        <p>Gợi ý cho bạn</p>
                        <div class="tab-content">
                            <ul class="result list-unstyled"></ul>
                            <a href="/tim-kiem" class="view-all"> Xem toàn bộ kết quả </a>
                        </div>
                    </div>
                    <div class="overlay"></div>
                </form>

                @include('components.header-scripts')

                <div class="dark-mode d-none d-xl-block">
                    <button type="button" class="btn-switch" aria-label="Switch mode">
                        <img src="{{ asset('images/btn-lightmode.png') }}" class="light" width="38" height="35" alt="Light mode" />
                        <img src="{{ asset('images/btn-darkmode.png') }}" class="dark" width="38" height="35" alt="Dark mode" />
                    </button>
                </div>
                <div hidden class="noti" id="box-noti">
                    <a href="javascript:void(0)" class="btn-noti">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                            <path d="M12 10.5195C11.59 10.5195 11.25 10.1795 11.25 9.76945V6.43945C11.25 6.02945 11.59 5.68945 12 5.68945C12.41 5.68945 12.75 6.02945 12.75 6.43945V9.76945C12.75 10.1895 12.41 10.5195 12 10.5195Z" fill="#2B4992"/>
                            <path d="M12.0208 20.3502C9.44084 20.3502 6.87084 19.9402 4.42084 19.1202C3.51084 18.8202 2.82084 18.1702 2.52084 17.3502C2.22084 16.5302 2.32084 15.5902 2.81084 14.7702L4.08084 12.6502C4.36084 12.1802 4.61084 11.3002 4.61084 10.7502V8.65023C4.61084 4.56023 7.93084 1.24023 12.0208 1.24023C16.1108 1.24023 19.4308 4.56023 19.4308 8.65023V10.7502C19.4308 11.2902 19.6808 12.1802 19.9608 12.6502L21.2308 14.7702C21.7008 15.5502 21.7808 16.4802 21.4708 17.3302C21.1608 18.1802 20.4808 18.8302 19.6208 19.1202C17.1708 19.9502 14.6008 20.3502 12.0208 20.3502ZM12.0208 2.75023C8.76084 2.75023 6.11084 5.40023 6.11084 8.66023V10.7602C6.11084 11.5702 5.79084 12.7402 5.37084 13.4302L4.10084 15.5602C3.84084 15.9902 3.78084 16.4502 3.93084 16.8502C4.08084 17.2502 4.42084 17.5502 4.90084 17.7102C9.50084 19.2402 14.5608 19.2402 19.1608 17.7102C19.5908 17.5702 19.9208 17.2502 20.0708 16.8302C20.2308 16.4102 20.1808 15.9502 19.9508 15.5602L18.6808 13.4402C18.2608 12.7502 17.9408 11.5802 17.9408 10.7702V8.67023C17.9308 5.40023 15.2808 2.75023 12.0208 2.75023Z" fill="#2B4992"/>
                            <path d="M11.9999 22.9003C10.9299 22.9003 9.87992 22.4603 9.11992 21.7003C8.35992 20.9403 7.91992 19.8903 7.91992 18.8203H9.41992C9.41992 19.5003 9.69992 20.1603 10.1799 20.6403C10.6599 21.1203 11.3199 21.4003 11.9999 21.4003C13.4199 21.4003 14.5799 20.2403 14.5799 18.8203H16.0799C16.0799 21.0703 14.2499 22.9003 11.9999 22.9003Z" fill="#2B4992"/>
                        </svg>
                        <span class="badge-noti">1</span>
                    </a>
                    <div class="noti-content list-unstyled no-scrollbar"></div>
                    <div class="overlay-noti"></div>
                </div>
                <div class="nav-account list-inline" id="not-loggin">
                    <span class="login-link d-none d-xl-flex">
                        <button class="btn btn-login" rel="nofollow" data-bs-toggle="modal" data-bs-target="#loginModal">
                            <span>Đăng nhập</span>
                        </button>
                    </span>
                    <a id="avatar" class="d-flex d-xl-none user-avatar-img" data-bs-toggle="modal" data-bs-target="#loginModal">
                        <img class="" alt="Avatar" src="{{ asset('images/no-avatar.png') }}" style="object-fit:contain;" />
                    </a>
                </div>
                <!-- Đã login  -->
                <div hidden class="dropdown nav-account" id="has-login">
                    <button class="dropdown-toggle" type="button" id="menuAccount" data-bs-toggle="dropdown" aria-expanded="false">
                        <div id="avatar-temp-header" class="avatar-temp user-avatar-img" data-name=""></div>
                        <span id="username" class="d-none d-xl-block">Marriage Gray</span>
                    </button>
                    <div class="dropdown-menu" aria-labelledby="menuAccount">
                        <span><a class="dropdown-item" href="/tai-khoan">Tài khoản</a></span>
                        <span><a class="dropdown-item" href="/tai-khoan#reading">Truyện đang đọc</a></span>
                        <span><a class="dropdown-item" href="/tai-khoan#following">Truyện đã lưu</a></span>
                        <span><a id="logout" class="dropdown-item user-logout" href="#">Đăng xuất</a></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>

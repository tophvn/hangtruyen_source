<footer id="footer">
    <div class="container">
        <div class="row">
            <div class="col-12 col-lg-4">
                <div class="f-left">
                    <a href="{{ url('/') }}" class="logo">
                        <img class="" alt="Truyện tranh online mới nhất" src="{{ asset('images/logo-dark.png') }}" width="146" height="52" />
                    </a>
                    <p class="mt-3">Hangtruyen là website đọc <b>truyện tranh</b> online uy tín hàng đầu Việt Nam. Tất cả các truyện trên website đăng tải được Hangtruyen biên dịch và tổng hợp từ nhiều nguồn trên Internet.</p>
                </div>
            </div>
            <div class="col-12 col-lg-6">
                <ul class="f-menu">
                    <li><a href="/trang/ve-chung-toi" rel="nofollow">Về chúng tôi</a></li>
                    <li><a href="/trang/chinh-sach-bao-mat" rel="nofollow">Chính sách bảo mật</a></li>
                    <li><a href="/trang/dieu-khoan-dich-vu" rel="nofollow">Điều khoản dịch vụ</a></li>
                    <li><a href="/trang/tuyen-bo-mien-tru-trach-nhiem" rel="nofollow">Tuyên bố miễn trừ trách nhiệm</a></li>
                </ul>
            </div>
            <div class="col-12 col-lg-2">
                <ul class="social-link">
                    @php
                        $facebookUrl = \App\Models\Setting::get('facebook_url', '');
                        $twitterUrl = \App\Models\Setting::get('twitter_url', '');
                        $youtubeUrl = \App\Models\Setting::get('youtube_url', '');
                        $githubUrl = \App\Models\Setting::get('github_url', '');
                    @endphp
                    @if($facebookUrl)
                        <li>
                            <a href="{{ $facebookUrl }}" target="_blank" aria-label="Facebook" rel="nofollow">
                                <svg xmlns="http://www.w3.org/2000/svg" width="34" height="34" viewBox="0 0 34 34" fill="none">
                                    <g clip-path="url(#clip0_392_113156)">
                                        <path d="M34 17C34 7.61115 26.3888 0 17 0C7.61115 0 0 7.61115 0 17C0 25.4851 6.21662 32.5181 14.3438 33.7935V21.9141H10.0273V17H14.3438V13.2547C14.3438 8.99406 16.8818 6.64062 20.7649 6.64062C22.6243 6.64062 24.5703 6.97266 24.5703 6.97266V11.1562H22.4267C20.315 11.1562 19.6562 12.4668 19.6562 13.8125V17H24.3711L23.6174 21.9141H19.6562V33.7935C27.7834 32.5181 34 25.4851 34 17Z" fill="white"/>
                                    </g>
                                    <defs>
                                        <clipPath id="clip0_392_113156">
                                            <rect width="34" height="34" fill="white"/>
                                        </clipPath>
                                    </defs>
                                </svg>
                            </a>
                        </li>
                    @endif
                    @if($twitterUrl)
                        <li>
                            <a href="{{ $twitterUrl }}" target="_blank" aria-label="Twitter" rel="nofollow">
                                <svg xmlns="http://www.w3.org/2000/svg" width="34" height="34" viewBox="0 0 34 34" fill="none">
                                    <g clip-path="url(#clip0_392_113157)">
                                        <path d="M10.6961 30.8125C23.5231 30.8125 30.5409 20.1829 30.5409 10.9677C30.5409 10.6689 30.5343 10.3634 30.521 10.0646C31.8862 9.0773 33.0643 7.85442 34 6.4534C32.7286 7.01908 31.3787 7.38852 29.9964 7.5491C31.4518 6.67668 32.5416 5.30615 33.0637 3.69156C31.6944 4.50302 30.1971 5.07543 28.6357 5.38426C27.5837 4.26644 26.1927 3.52631 24.6779 3.2783C23.163 3.03029 21.6087 3.28822 20.2551 4.0122C18.9016 4.73618 17.8242 5.88589 17.1896 7.28359C16.555 8.68128 16.3985 10.2491 16.7443 11.7446C13.9719 11.6055 11.2596 10.8853 8.78331 9.6307C6.30704 8.37609 4.12207 6.6151 2.37004 4.46188C1.47957 5.99716 1.20708 7.81389 1.60796 9.54286C2.00884 11.2718 3.05301 12.7833 4.52824 13.77C3.42072 13.7349 2.33747 13.4367 1.36797 12.9001V12.9864C1.36698 14.5976 1.92397 16.1594 2.94427 17.4063C3.96457 18.6532 5.38521 19.5084 6.96469 19.8263C5.93874 20.107 4.86197 20.1479 3.8177 19.9458C4.26339 21.3314 5.13056 22.5433 6.29817 23.4124C7.46579 24.2814 8.87559 24.7642 10.3308 24.7935C7.86027 26.7341 4.80841 27.7867 1.6668 27.7818C1.10966 27.7809 0.553065 27.7468 0 27.6795C3.19155 29.727 6.90417 30.8145 10.6961 30.8125Z" fill="white"/>
                                    </g>
                                    <defs>
                                        <clipPath id="clip0_392_113157">
                                            <rect width="34" height="34" fill="white"/>
                                        </clipPath>
                                    </defs>
                                </svg>
                            </a>
                        </li>
                    @endif
                    @if($youtubeUrl)
                        <li>
                            <a href="{{ $youtubeUrl }}" target="_blank" aria-label="YouTube" rel="nofollow">
                                <svg xmlns="http://www.w3.org/2000/svg" width="34" height="34" viewBox="0 0 34 34" fill="none">
                                    <g clip-path="url(#clip0_youtube)">
                                        <path d="M33.2549 8.75664C32.8999 7.37866 31.7664 6.24514 30.3884 5.89014C28.1334 5.3125 17 5.3125 17 5.3125C17 5.3125 5.86662 5.3125 3.61162 5.89014C2.23364 6.24514 1.10012 7.37866 0.745117 8.75664C0.16748 11.0116 0.16748 15.7083 0.16748 15.7083C0.16748 15.7083 0.16748 20.405 0.745117 22.6599C1.10012 24.0379 2.23364 25.1714 3.61162 25.5264C5.86662 26.1041 17 26.1041 17 26.1041C17 26.1041 28.1334 26.1041 30.3884 25.5264C31.7664 25.1714 32.8999 24.0379 33.2549 22.6599C33.8326 20.405 33.8326 15.7083 33.8326 15.7083C33.8326 15.7083 33.8326 11.0116 33.2549 8.75664ZM13.6899 20.4998V10.9169L22.1073 15.7083L13.6899 20.4998Z" fill="white"/>
                                    </g>
                                    <defs>
                                        <clipPath id="clip0_youtube">
                                            <rect width="34" height="34" fill="white"/>
                                        </clipPath>
                                    </defs>
                                </svg>
                            </a>
                        </li>
                    @endif
                    @if($githubUrl)
                        <li>
                            <a href="{{ $githubUrl }}" target="_blank" aria-label="GitHub" rel="nofollow">
                                <svg xmlns="http://www.w3.org/2000/svg" width="34" height="34" viewBox="0 0 34 34" fill="none">
                                    <g clip-path="url(#clip0_github)">
                                        <path fill-rule="evenodd" clip-rule="evenodd" d="M17 0C7.61115 0 0 7.61115 0 17C0 24.5328 4.86547 31.0475 11.6042 33.4219C12.4479 33.5729 12.7917 33.0208 12.7917 32.5312C12.7917 32.0937 12.7708 30.75 12.7708 29.2812C8.5 30.0625 7.39583 28.1875 7.0625 27.2292C6.83333 26.6667 6.02083 24.9167 5.3125 24.3958C4.72917 23.9792 3.875 22.8333 5.29167 22.8125C6.625 22.7917 7.52083 24.0208 7.8125 24.6042C9.3125 27.2917 11.9167 26.4792 12.8333 26.0208C13 25.125 13.3958 24.5208 13.8333 24.1458C10.0625 23.75 6.14583 22.3958 6.14583 15.9375C6.14583 14.1042 6.72917 12.5417 7.85417 11.2708C7.64583 10.8125 7.0625 9.125 7.97917 6.85417C7.97917 6.85417 9.375 6.41667 12.8333 8.5625C14.1667 8.1875 15.625 8 17.0417 8C18.4583 8 19.9167 8.1875 21.25 8.5625C24.7083 6.39583 26.1042 6.85417 26.1042 6.85417C27.0208 9.125 26.4375 10.8125 26.2292 11.2708C27.3542 12.5417 27.9375 14.0833 27.9375 15.9375C27.9375 22.4167 24 23.75 20.2292 24.125C20.7708 24.6042 21.25 25.5 21.25 26.8958C21.25 28.7917 21.2292 30.3542 21.2292 32.5312C21.2292 33.0208 21.5625 33.5938 22.4167 33.4219C29.1345 31.0475 34 24.5328 34 17C34 7.61115 26.3888 0 17 0Z" fill="white"/>
                                    </g>
                                    <defs>
                                        <clipPath id="clip0_github">
                                            <rect width="34" height="34" fill="white"/>
                                        </clipPath>
                                    </defs>
                                </svg>
                            </a>
                        </li>
                    @endif
                </ul>
            </div>
        </div>
        <p class="copyright text-center">Copyright © 2026, All rights reserved.</p>
    </div>
    <span id="back-totop" style="display: inline;"></span>
</footer>

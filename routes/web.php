<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Auth routes
Route::get('/auth/google', [App\Http\Controllers\AuthController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('/auth/google/callback', [App\Http\Controllers\AuthController::class, 'handleGoogleCallback'])->name('auth.google.callback');
Route::get('/auth/logout', [App\Http\Controllers\AuthController::class, 'logout'])->name('auth.logout');
Route::post('/auth/logout', [App\Http\Controllers\AuthController::class, 'logout'])->name('auth.logout.post');

Route::get('/', function () {
    return view('home.index');
});

Route::get('/truyen-tranh/{slug}', function ($slug) {
    return view('manga.detail', [
        'mangaSlug' => $slug,
        'mangaTitle' => 'GTO: Fury of Death Yamada',
        'mangaImage' => 'https://prvhtr.mgbucket.xyz/posters/bd/f3/gto-fury-of-death-yamada.jpeg',
        'rating' => 0,
        'status' => 'Đang tiến hành',
        'author' => 'Tohru Fujisawa',
        'updatedAt' => '17h29 01/01/2026',
        'views' => '284',
        'description' => 'Bộ phim kể về Phó Hiệu trưởng Hiroshi Uchiyamada, người vô tình lạc vào một cơn ác mộng xuyên không gian sau khi đến Kabukicho để tìm kiếm nữ sinh mất tích Nanami.',
        'fullDescription' => 'Bộ phim kể về Phó Hiệu trưởng Hiroshi Uchiyamada, người vô tình lạc vào một cơn ác mộng xuyên không gian sau khi đến Kabukicho để tìm kiếm nữ sinh mất tích Nanami.',
        'followCount' => 0,
    ]);
})->name('manga.detail');

Route::get('/truyen-tranh/{mangaSlug}/{chapterSlug}', function ($mangaSlug, $chapterSlug) {
    return view('manga.chapter', [
        'mangaSlug' => $mangaSlug,
        'chapterSlug' => $chapterSlug,
        'mangaTitle' => 'GTO: Fury of Death Yamada',
        'chapterName' => 'Chapter 13',
        'chapterId' => 2169687,
        'chapterImages' => [
            'https://prvhtr.mgbucket.xyz/chapters/1277938/2169687/1.jpg',
            'https://prvhtr.mgbucket.xyz/chapters/1277938/2169687/2.jpg',
            'https://prvhtr.mgbucket.xyz/chapters/1277938/2169687/3.jpg',
            'https://prvhtr.mgbucket.xyz/chapters/1277938/2169687/4.jpg',
            'https://prvhtr.mgbucket.xyz/chapters/1277938/2169687/5.jpg',
        ],
        'prevChapter' => [
            'slug' => 'chapter-12',
            'name' => 'Chapter 12',
            'url' => '/truyen-tranh/' . $mangaSlug . '/chapter-12',
        ],
        'nextChapter' => [
            'slug' => 'chapter-14',
            'name' => 'Chapter 14',
            'url' => '/truyen-tranh/' . $mangaSlug . '/chapter-14',
        ],
    ]);
})->name('manga.chapter');

Route::get('/tim-kiem', function () {
    $keyword = request()->get('keyword', '');
    $page = request()->get('page', 1);
    
    // Demo data - 6 truyện mẫu
    $results = [
        [
            'slug' => 'one-punch-man',
            'title' => 'One Punch Man',
            'posterPath' => 'https://prvhtr.mgbucket.xyz/posters/2024.11.13/eFBaGhXD2T5EBvM4el.jpg',
            'avgVote' => 5,
            'countView' => 208330,
            'chapters' => [
                ['id' => 2170271, 'slug' => 'chapter-294', 'name' => 'Chapter 294', 'releasedAt' => '18 giờ trước'],
                ['id' => 2168449, 'slug' => 'chapter-293', 'name' => 'Chapter 293', 'releasedAt' => '1 tháng trước'],
            ],
        ],
        [
            'slug' => 'blue-lock',
            'title' => 'Blue Lock',
            'posterPath' => 'https://prvhtr.mgbucket.xyz/posters/5f/cf/blue-lock.jpg',
            'avgVote' => 3.1,
            'countView' => 62970,
            'chapters' => [
                ['id' => 2170270, 'slug' => 'chapter-331', 'name' => 'Chapter 331', 'releasedAt' => '1 ngày trước'],
                ['id' => 2169975, 'slug' => 'chapter-330', 'name' => 'Chapter 330', 'releasedAt' => '8 ngày trước'],
            ],
        ],
        [
            'slug' => 'yeu-than-ky',
            'title' => 'Yêu Thần Ký',
            'posterPath' => 'https://prvhtr.mgbucket.xyz/posters/a5/ee/yeu-than-ky.png',
            'avgVote' => 4.9,
            'countView' => 69420,
            'chapters' => [
                ['id' => 2170269, 'slug' => 'chapter-665', 'name' => 'Chapter #665', 'releasedAt' => '2 ngày trước'],
                ['id' => 2170025, 'slug' => 'chapter-664', 'name' => 'Chapter #664', 'releasedAt' => '6 ngày trước'],
            ],
        ],
        [
            'slug' => 'tuyet-the-vo-than',
            'title' => 'Tuyệt Thế Võ Thần',
            'posterPath' => 'https://prvhtr.mgbucket.xyz/posters/fe/f3/tuyet-the-vo-than.png',
            'avgVote' => 4.2,
            'countView' => 10240000,
            'chapters' => [
                ['id' => 2170268, 'slug' => 'chapter-1106', 'name' => 'Chapter #1106', 'releasedAt' => '2 ngày trước'],
                ['id' => 2170267, 'slug' => 'chapter-1105', 'name' => 'Chapter #1105', 'releasedAt' => '2 ngày trước'],
            ],
        ],
        [
            'slug' => 'su-tro-lai-cua-phap-su-vi-dai-sau-4000-nam',
            'title' => 'Sự Trở Lại Của Pháp Sư Vĩ Đại Sau 4000 Năm',
            'posterPath' => 'https://prvhtr.mgbucket.xyz/posters/dc/2d/su-tro-lai-cua-phap-su-vi-dai-sau-4000-nam.png',
            'avgVote' => 4.8,
            'countView' => 2000000,
            'chapters' => [
                ['id' => 2170266, 'slug' => 'chapter-234', 'name' => 'Chapter #234', 'releasedAt' => '2 ngày trước'],
                ['id' => 2170252, 'slug' => 'chapter-233', 'name' => 'Chapter #233', 'releasedAt' => '2 ngày trước'],
            ],
        ],
        [
            'slug' => 'ta-co-mot-son-trai',
            'title' => 'Ta Có Một Sơn Trại',
            'posterPath' => 'https://prvhtr.mgbucket.xyz/posters/62/dc/ta-co-mot-son-trai.jpg',
            'avgVote' => 4.2,
            'countView' => 111390,
            'chapters' => [
                ['id' => 2170265, 'slug' => 'chapter-1273', 'name' => 'Chapter #1273', 'releasedAt' => '2 ngày trước'],
                ['id' => 2170264, 'slug' => 'chapter-1272', 'name' => 'Chapter #1272', 'releasedAt' => '2 ngày trước'],
            ],
        ],
    ];
    
    return view('search.index', [
        'keyword' => $keyword,
        'results' => $results,
        'totalResults' => 14387,
        'currentPage' => $page,
        'totalPages' => 1439,
    ]);
})->name('search');

Route::get('/genre', function () {
    // Demo data - các genres với truyện mẫu
    $genres = [
        'action' => [
            'name' => 'Action',
            'slug' => 'action',
            'mangas' => [
                [
                    'slug' => 'tham-tu-lung-danh-conan-gio-tra-cua-zero-nxb-kim-dong',
                    'title' => 'Thám tử lừng danh Conan - Giờ trà của Zero (NXB Kim Đồng)',
                    'posterPath' => 'https://prvhtr.mgbucket.xyz/posters/d1/f3/tham-tu-lung-danh-conan-gio-tra-cua-zero-nxb-kim-dong.jpg',
                    'avgVote' => 0,
                    'chapters' => [
                        ['id' => 2150372, 'slug' => 'time-60-thuong-that', 'name' => 'Time #60: Thường thật', 'releasedAt' => '3 tháng trước'],
                    ],
                ],
                [
                    'slug' => 'blue-lock-nxb-kim-dong',
                    'title' => 'Blue lock (NXB Kim Đồng)',
                    'posterPath' => 'https://prvhtr.mgbucket.xyz/posters/2f/01/bluelock-nxb-kim-dong.jpg',
                    'avgVote' => 0,
                    'chapters' => [
                        ['id' => 2153586, 'slug' => 'chapter-248-tran-dau-cuoi-cung', 'name' => 'Chapter #248: Trận đấu cuối cùng', 'releasedAt' => '3 tháng trước'],
                    ],
                ],
                [
                    'slug' => 'death-mage',
                    'title' => 'Death Mage',
                    'posterPath' => 'https://prvhtr.mgbucket.xyz/posters/94/f7/death-mage.png',
                    'avgVote' => 0,
                    'chapters' => [
                        ['id' => 2168042, 'slug' => 'chapter-69', 'name' => 'Chapter #69', 'releasedAt' => '1 tháng trước'],
                    ],
                ],
                [
                    'slug' => 'phap-su-manh-nhat-dung-sach-chien-luoc-tu-minh-tieu-diet-ma-vuong',
                    'title' => 'Pháp Sư Mạnh Nhất Dùng Sách Chiến Lược, Tự Mình Tiêu Diệt Ma Vương',
                    'posterPath' => 'https://prvhtr.mgbucket.xyz/posters/24/15/the-strongest-wizard-making-full-use-of-the-strategy-guide-no-taking-orders-i-ll-slay-the-demon-king-my-own-way.png',
                    'avgVote' => 0,
                    'chapters' => [
                        ['id' => 2156215, 'slug' => 'chapter-68', 'name' => 'Chapter #68', 'releasedAt' => '3 tháng trước'],
                    ],
                ],
                [
                    'slug' => 'thien-than-diet-the-seraph-of-the-end-nxb-kim-dong',
                    'title' => 'Thiên thần diệt thế - Seraph of the end (NXB Kim Đồng)',
                    'posterPath' => 'https://prvhtr.mgbucket.xyz/posters/54/9f/thien-than-diet-the-seraph-of-the-end-nxb-kim-dong.jpg',
                    'avgVote' => 0,
                    'chapters' => [
                        ['id' => 2165817, 'slug' => 'chuong-110-qua-khu-phoi-bay', 'name' => 'Chương #110: Quá khứ phơi bày', 'releasedAt' => '2 tháng trước'],
                    ],
                ],
                [
                    'slug' => 'chu-thuat-hoi-chien-nxb-kim-dong',
                    'title' => 'Chú thuật hồi chiến (NXB Kim Đồng)',
                    'posterPath' => 'https://prvhtr.mgbucket.xyz/posters/1c/66/chu-thuat-hoi-chien-nxb-kim-dong.jpg',
                    'avgVote' => 0,
                    'chapters' => [
                        ['id' => 2170188, 'slug' => 'chuong-124-bien-co-shibuya-42', 'name' => 'Chương #124: Biến cố Shibuya 42', 'releasedAt' => '2 ngày trước'],
                    ],
                ],
                [
                    'slug' => 'alice-in-borderland',
                    'title' => 'Alice in Borderland',
                    'posterPath' => 'https://prvhtr.mgbucket.xyz/posters/1a/98/alice-in-borderland.png',
                    'avgVote' => 0,
                    'chapters' => [
                        ['id' => 2157002, 'slug' => 'chapter-65', 'name' => 'Chapter #65', 'releasedAt' => '3 tháng trước'],
                    ],
                ],
                [
                    'slug' => 'khi-toi-chuyen-sinh-thanh-mot-thanh-kiem',
                    'title' => 'Khi Tôi Chuyển Sinh Thành Một Thanh Kiếm',
                    'posterPath' => 'https://prvhtr.mgbucket.xyz/posters/bb/cf/khi-toi-chuyen-sinh-thanh-mot-thanh-kiem.jpg',
                    'avgVote' => 0,
                    'chapters' => [
                        ['id' => 2167854, 'slug' => 'chapter-59', 'name' => 'Chapter #59', 'releasedAt' => '1 tháng trước'],
                    ],
                ],
                [
                    'slug' => 'bang-xep-hang-quan-vuong-nxb-kim-dong',
                    'title' => 'Bảng xếp hạng quân vương (NXB Kim Đồng)',
                    'posterPath' => 'https://prvhtr.mgbucket.xyz/posters/a9/39/bang-xep-hang-quan-vuong-nxb-kim-dong.png',
                    'avgVote' => 0,
                    'chapters' => [
                        ['id' => 2164485, 'slug' => 'hoi-193-5-chuyen-ve-thong-linh-bang-dao-tac-lon', 'name' => 'Hồi #193.5: CHUYỆN VỀ THỐNG LĨNH BĂNG ĐẠO TẶC LỚN', 'releasedAt' => '2 tháng trước'],
                    ],
                ],
                [
                    'slug' => 'toi-necromancer-co-doc',
                    'title' => 'Tôi - Necromancer Cô Độc',
                    'posterPath' => 'https://prvhtr.mgbucket.xyz/posters/d5/0f/toi-necromancer-co-doc.png',
                    'avgVote' => 0,
                    'chapters' => [
                        ['id' => 2168495, 'slug' => 'chapter-76', 'name' => 'Chapter #76', 'releasedAt' => '23 ngày trước'],
                    ],
                ],
            ],
        ],
        'romance' => [
            'name' => 'Romance',
            'slug' => 'romance',
            'mangas' => [
                [
                    'slug' => 'kimi-to-koete-koi-ni-naru',
                    'title' => 'Kimi to Koete Koi ni Naru',
                    'posterPath' => 'https://prvhtr.mgbucket.xyz/posters/00/a5/kimi-to-koete-koi-ni-naru.jpg',
                    'avgVote' => 0,
                    'chapters' => [
                        ['id' => 2168116, 'slug' => 'chapter-17', 'name' => 'Chapter #17', 'releasedAt' => '1 tháng trước'],
                    ],
                ],
                [
                    'slug' => 'nhan-nha-tuoi-30-sau-khi-bi-duoi-khoi-quan-doan-ma-vuong',
                    'title' => 'Nhàn Nhã Tuổi 30 Sau Khi Bị Đuổi Khỏi Quân Đoàn Ma Vương',
                    'posterPath' => 'https://prvhtr.mgbucket.xyz/posters/26/1b/nhan-nha-tuoi-30-sau-khi-bi-duoi-khoi-quan-doan-ma-vuong.png',
                    'avgVote' => 0,
                    'chapters' => [
                        ['id' => 2165164, 'slug' => 'chapter-74', 'name' => 'Chapter #74', 'releasedAt' => '2 tháng trước'],
                    ],
                ],
                [
                    'slug' => 'chien-tich-me-cung-cua-tank-manh-nhat-bi-truc-xuat-du-so-huu-khang-luc-9999',
                    'title' => 'Chiến Tích Mê Cung Của Tank Mạnh Nhất - Bị Trục Xuất Dù Sở Hữu Kháng Lực 9999',
                    'posterPath' => 'https://prvhtr.mgbucket.xyz/posters/09/41/chien-tich-me-cung-cua-tank-manh-nhat-bi-truc-xuat-du-so-huu-khang-luc-9999.png',
                    'avgVote' => 0,
                    'chapters' => [
                        ['id' => 2160395, 'slug' => 'chapter-60', 'name' => 'Chapter #60', 'releasedAt' => '2 tháng trước'],
                    ],
                ],
                [
                    'slug' => 'co-gai-than-yeu-cua-toi',
                    'title' => 'Cô Gái Thân Yêu Của Tôi',
                    'posterPath' => 'https://prvhtr.mgbucket.xyz/posters/2025.04.19/MIijo8TyAkhthRFQBR.jpg',
                    'avgVote' => 0,
                    'chapters' => [
                        ['id' => 2168115, 'slug' => 'chapter-32', 'name' => 'Chapter 32', 'releasedAt' => '7 tháng trước'],
                    ],
                ],
                [
                    'slug' => 'that-kho-so-khi-nguoi-ban-thoi-tho-au-la-dai-phap-su',
                    'title' => 'Thật Khổ Sở Khi Người Bạn Thời Thơ Ấu Là Đại Pháp Sư',
                    'posterPath' => 'https://prvhtr.mgbucket.xyz/posters/example.jpg',
                    'avgVote' => 0,
                    'chapters' => [
                        ['id' => 2168114, 'slug' => 'chapter-38', 'name' => 'Chapter 38', 'releasedAt' => '5 tháng trước'],
                    ],
                ],
            ],
        ],
        'comedy' => [
            'name' => 'Comedy',
            'slug' => 'comedy',
            'mangas' => [
                [
                    'slug' => 'oan-gia-chung-nha-thien-than-x-ac-quy-sao-ma-than-duoc',
                    'title' => 'Oan Gia Chung Nhà: Thiên Thần X Ác Quỷ, Sao Mà Thân Được!?',
                    'posterPath' => 'https://prvhtr.mgbucket.xyz/posters/example.jpg',
                    'avgVote' => 0,
                    'chapters' => [
                        ['id' => 2168113, 'slug' => 'chapter-128-5', 'name' => 'Chapter 128.5', 'releasedAt' => '9 tháng trước'],
                    ],
                ],
                [
                    'slug' => 'nhan-nha-tuoi-30-sau-khi-bi-duoi-khoi-quan-doan-ma-vuong',
                    'title' => 'Nhàn Nhã Tuổi 30 Sau Khi Bị Đuổi Khỏi Quân Đoàn Ma Vương',
                    'posterPath' => 'https://prvhtr.mgbucket.xyz/posters/26/1b/nhan-nha-tuoi-30-sau-khi-bi-duoi-khoi-quan-doan-ma-vuong.png',
                    'avgVote' => 0,
                    'chapters' => [
                        ['id' => 2165164, 'slug' => 'chapter-74', 'name' => 'Chapter #74', 'releasedAt' => '2 tháng trước'],
                    ],
                ],
                [
                    'slug' => 'kimi-to-koete-koi-ni-naru',
                    'title' => 'Kimi to Koete Koi ni Naru',
                    'posterPath' => 'https://prvhtr.mgbucket.xyz/posters/00/a5/kimi-to-koete-koi-ni-naru.jpg',
                    'avgVote' => 0,
                    'chapters' => [
                        ['id' => 2168116, 'slug' => 'chapter-17', 'name' => 'Chapter #17', 'releasedAt' => '1 tháng trước'],
                    ],
                ],
                [
                    'slug' => 'beast-tamer-nguoi-thuan-hoa-thu',
                    'title' => 'Beast Tamer - Người Thuần Hóa Thú',
                    'posterPath' => 'https://prvhtr.mgbucket.xyz/posters/example.jpg',
                    'avgVote' => 0,
                    'chapters' => [
                        ['id' => 2168112, 'slug' => 'chapter-93', 'name' => 'Chapter #93', 'releasedAt' => '1 tháng trước'],
                    ],
                ],
                [
                    'slug' => 'diet-slime-suot-300-nam-toi-levelmax-luc-nao-chang-hay-nxb-the-gioi',
                    'title' => 'Diệt Slime Suốt 300 Năm, Tôi Levelmax Lúc Nào Chẳng Hay (NXB Thế Giới)',
                    'posterPath' => 'https://prvhtr.mgbucket.xyz/posters/example.jpg',
                    'avgVote' => 0,
                    'chapters' => [
                        ['id' => 2168111, 'slug' => 'chuong-68-den-hon-dao-khong-nguoi', 'name' => 'Chapter #68: Đến hòn đảo không người', 'releasedAt' => '14 ngày trước'],
                    ],
                ],
            ],
        ],
        'fantasy' => [
            'name' => 'Fantasy',
            'slug' => 'fantasy',
            'mangas' => [
                [
                    'slug' => 'cuoc-song-nhan-nha-o-the-gioi-khac-cua-ung-vien-dung-gia-so-huu-suc-manh-gian-lan-tu-cap-2',
                    'title' => 'Cuộc Sống Nhàn Nhã Ở Thế Giới Khác Của Ứng Viên Dũng Giả Sở Hữu Sức Mạnh Gian Lận Từ Cấp 2',
                    'posterPath' => 'https://prvhtr.mgbucket.xyz/posters/f1/0a/chillin-in-another-world-with-level-2-super-cheat-powers.jpg',
                    'avgVote' => 0,
                    'chapters' => [
                        ['id' => 2168110, 'slug' => 'chapter-51', 'name' => 'Chapter #51', 'releasedAt' => '18 ngày trước'],
                    ],
                ],
                [
                    'slug' => 'one-piece-nxb-kim-dong',
                    'title' => 'One Piece (NXB Kim Đồng)',
                    'posterPath' => 'https://prvhtr.mgbucket.xyz/posters/c8/3a/one-piece-nxb-kim-dong.jpg',
                    'avgVote' => 0,
                    'chapters' => [
                        ['id' => 2165991, 'slug' => 'chuong-1015-xieng-xich', 'name' => 'Chương #1015: Xiềng xích', 'releasedAt' => '2 tháng trước'],
                    ],
                ],
                [
                    'slug' => 'cau-chuyen-sinh-ton-cua-kiem-vuong-o-the-gioi-khac',
                    'title' => 'Câu Chuyện Sinh Tồn Của Kiếm Vương Ở Thế Giới Khác',
                    'posterPath' => 'https://prvhtr.mgbucket.xyz/posters/2b/9d/cau-chuyen-sinh-ton-cua-kiem-vuong-o-the-gioi-khac.png',
                    'avgVote' => 0,
                    'chapters' => [
                        ['id' => 2167092, 'slug' => 'chapter-132', 'name' => 'Chapter #132', 'releasedAt' => '1 tháng trước'],
                    ],
                ],
                [
                    'slug' => 'ky-si-chuyen-sinh-bi-luu-day-tro-nen-bat-bai-nho-tro-choi',
                    'title' => 'Kỵ Sĩ Chuyển Sinh Bị Lưu Đày, Trở Nên Bất Bại Nhờ Trò Chơi',
                    'posterPath' => 'https://prvhtr.mgbucket.xyz/posters/example.jpg',
                    'avgVote' => 0,
                    'chapters' => [
                        ['id' => 2168109, 'slug' => 'chapter-15', 'name' => 'Chapter #15', 'releasedAt' => '2 ngày trước'],
                    ],
                ],
                [
                    'slug' => 'death-mage',
                    'title' => 'Death Mage',
                    'posterPath' => 'https://prvhtr.mgbucket.xyz/posters/94/f7/death-mage.png',
                    'avgVote' => 0,
                    'chapters' => [
                        ['id' => 2168042, 'slug' => 'chapter-69', 'name' => 'Chapter #69', 'releasedAt' => '1 tháng trước'],
                    ],
                ],
            ],
        ],
        'drama' => [
            'name' => 'Drama',
            'slug' => 'drama',
            'mangas' => [
                [
                    'slug' => 'cuoc-song-thuong-ngay-cua-ke-bien-thai',
                    'title' => 'Cuộc Sống Thường Ngày Của Kẻ Biến Thái',
                    'posterPath' => 'https://prvhtr.mgbucket.xyz/posters/example.jpg',
                    'avgVote' => 0,
                    'chapters' => [
                        ['id' => 2168108, 'slug' => 'chapter-144', 'name' => 'Chapter #144', 'releasedAt' => '3 tháng trước'],
                    ],
                ],
                [
                    'slug' => 'one-piece-nxb-kim-dong',
                    'title' => 'One Piece (NXB Kim Đồng)',
                    'posterPath' => 'https://prvhtr.mgbucket.xyz/posters/c8/3a/one-piece-nxb-kim-dong.jpg',
                    'avgVote' => 0,
                    'chapters' => [
                        ['id' => 2165991, 'slug' => 'chuong-1015-xieng-xich', 'name' => 'Chương #1015: Xiềng xích', 'releasedAt' => '2 tháng trước'],
                    ],
                ],
                [
                    'slug' => 'death-mage',
                    'title' => 'Death Mage',
                    'posterPath' => 'https://prvhtr.mgbucket.xyz/posters/94/f7/death-mage.png',
                    'avgVote' => 0,
                    'chapters' => [
                        ['id' => 2168042, 'slug' => 'chapter-69', 'name' => 'Chapter #69', 'releasedAt' => '1 tháng trước'],
                    ],
                ],
                [
                    'slug' => 'ky-si-chuyen-sinh-bi-luu-day-tro-nen-bat-bai-nho-tro-choi',
                    'title' => 'Kỵ Sĩ Chuyển Sinh Bị Lưu Đày, Trở Nên Bất Bại Nhờ Trò Chơi',
                    'posterPath' => 'https://prvhtr.mgbucket.xyz/posters/example.jpg',
                    'avgVote' => 0,
                    'chapters' => [
                        ['id' => 2168109, 'slug' => 'chapter-15', 'name' => 'Chapter #15', 'releasedAt' => '2 ngày trước'],
                    ],
                ],
                [
                    'slug' => 'menh-lenh-bi-mat-tu-chi-sep',
                    'title' => 'Mệnh Lệnh Bí Mật Từ Chị Sếp',
                    'posterPath' => 'https://prvhtr.mgbucket.xyz/posters/example.jpg',
                    'avgVote' => 0,
                    'chapters' => [
                        ['id' => 2168107, 'slug' => 'chapter-110-end', 'name' => 'Chapter #110 - END', 'releasedAt' => '5 tháng trước'],
                    ],
                ],
            ],
        ],
        'adventure' => [
            'name' => 'Adventure',
            'slug' => 'adventure',
            'mangas' => [
                [
                    'slug' => 'hoi-quy-voi-suc-manh-cua-nha-vua',
                    'title' => 'Hồi Quy Với Sức Mạnh Của Nhà Vua',
                    'posterPath' => 'https://prvhtr.mgbucket.xyz/posters/example.jpg',
                    'avgVote' => 0,
                    'chapters' => [
                        ['id' => 2168106, 'slug' => 'chapter-81', 'name' => 'Chapter #81', 'releasedAt' => '2 tháng trước'],
                    ],
                ],
                [
                    'slug' => 'phap-su-manh-nhat-dung-sach-chien-luoc-tu-minh-tieu-diet-ma-vuong',
                    'title' => 'Pháp Sư Mạnh Nhất Dùng Sách Chiến Lược, Tự Mình Tiêu Diệt Ma Vương',
                    'posterPath' => 'https://prvhtr.mgbucket.xyz/posters/24/15/the-strongest-wizard-making-full-use-of-the-strategy-guide-no-taking-orders-i-ll-slay-the-demon-king-my-own-way.png',
                    'avgVote' => 0,
                    'chapters' => [
                        ['id' => 2156215, 'slug' => 'chapter-68', 'name' => 'Chapter #68', 'releasedAt' => '3 tháng trước'],
                    ],
                ],
                [
                    'slug' => 'khi-toi-chuyen-sinh-thanh-mot-thanh-kiem',
                    'title' => 'Khi Tôi Chuyển Sinh Thành Một Thanh Kiếm',
                    'posterPath' => 'https://prvhtr.mgbucket.xyz/posters/bb/cf/khi-toi-chuyen-sinh-thanh-mot-thanh-kiem.jpg',
                    'avgVote' => 0,
                    'chapters' => [
                        ['id' => 2167854, 'slug' => 'chapter-59', 'name' => 'Chapter #59', 'releasedAt' => '1 tháng trước'],
                    ],
                ],
                [
                    'slug' => 'phap-su-hoi-phuc-bi-duoi-khoi-to-doi-hoa-ra-la-manh-nhat',
                    'title' => 'Pháp Sư Hồi Phục Bị Đuổi Khỏi Tổ Đội Hóa Ra Là Mạnh Nhất',
                    'posterPath' => 'https://prvhtr.mgbucket.xyz/posters/example.jpg',
                    'avgVote' => 0,
                    'chapters' => [
                        ['id' => 2168105, 'slug' => 'chapter-35', 'name' => 'Chapter #35', 'releasedAt' => '3 tháng trước'],
                    ],
                ],
                [
                    'slug' => 'cuoc-song-nhan-nha-o-the-gioi-khac-cua-ung-vien-dung-gia-so-huu-suc-manh-gian-lan-tu-cap-2',
                    'title' => 'Cuộc Sống Nhàn Nhã Ở Thế Giới Khác Của Ứng Viên Dũng Giả Sở Hữu Sức Mạnh Gian Lận Từ Cấp 2',
                    'posterPath' => 'https://prvhtr.mgbucket.xyz/posters/f1/0a/chillin-in-another-world-with-level-2-super-cheat-powers.jpg',
                    'avgVote' => 0,
                    'chapters' => [
                        ['id' => 2168110, 'slug' => 'chapter-51', 'name' => 'Chapter #51', 'releasedAt' => '18 ngày trước'],
                    ],
                ],
            ],
        ],
        'ngon-tinh' => [
            'name' => 'Ngôn Tình',
            'slug' => 'ngon-tinh',
            'mangas' => [
                [
                    'slug' => 'cuoc-tuyen-chon-vuong-phi-trieu-joseon',
                    'title' => 'Cuộc Tuyển Chọn Vương Phi Triều Joseon',
                    'posterPath' => 'https://prvhtr.mgbucket.xyz/posters/example.jpg',
                    'avgVote' => 0,
                    'chapters' => [
                        ['id' => 2168104, 'slug' => 'chapter-376', 'name' => 'Chapter 376', 'releasedAt' => '3 tháng trước'],
                    ],
                ],
                [
                    'slug' => 'kieu-quy-phi-thu-doan-ac-doc-va-hoang-thuong-khong-de-choc',
                    'title' => 'Kiều Quý Phi Thủ Đoạn Ác Độc Và Hoàng Thượng Không Dễ Chọc',
                    'posterPath' => 'https://prvhtr.mgbucket.xyz/posters/example.jpg',
                    'avgVote' => 0,
                    'chapters' => [
                        ['id' => 2168103, 'slug' => 'chapter-309', 'name' => 'Chapter 309', 'releasedAt' => '9 tháng trước'],
                    ],
                ],
                [
                    'slug' => 'cuoc-song-thuong-ngay-cua-ke-bien-thai',
                    'title' => 'Cuộc Sống Thường Ngày Của Kẻ Biến Thái',
                    'posterPath' => 'https://prvhtr.mgbucket.xyz/posters/example.jpg',
                    'avgVote' => 0,
                    'chapters' => [
                        ['id' => 2168108, 'slug' => 'chapter-144', 'name' => 'Chapter #144', 'releasedAt' => '3 tháng trước'],
                    ],
                ],
                [
                    'slug' => 'sau-khi-xuyen-thanh-tieu-bao-bao-ca-nha-phan-dien-deu-muon-giet-toi',
                    'title' => 'Sau Khi Xuyên Thành Tiểu Bảo Bảo , Cả Nhà Phản Diện Đều Muốn Giết Tôi!',
                    'posterPath' => 'https://prvhtr.mgbucket.xyz/posters/example.jpg',
                    'avgVote' => 0,
                    'chapters' => [
                        ['id' => 2168102, 'slug' => 'chapter-235', 'name' => 'Chapter 235', 'releasedAt' => '3 tháng trước'],
                    ],
                ],
                [
                    'slug' => 'nhat-dinh-vuong-phi',
                    'title' => 'Nhất Đỉnh Vương Phi',
                    'posterPath' => 'https://prvhtr.mgbucket.xyz/posters/example.jpg',
                    'avgVote' => 0,
                    'chapters' => [
                        ['id' => 2168101, 'slug' => 'chapter-163', 'name' => 'Chapter 163', 'releasedAt' => '8 tháng trước'],
                    ],
                ],
            ],
        ],
        'school-life' => [
            'name' => 'School Life',
            'slug' => 'school-life',
            'mangas' => [
                [
                    'slug' => 'tro-lai-voi-chanbi',
                    'title' => 'Trở lại với Chanbi',
                    'posterPath' => 'https://prvhtr.mgbucket.xyz/posters/example.jpg',
                    'avgVote' => 0,
                    'chapters' => [
                        ['id' => 2168100, 'slug' => 'chapter-082', 'name' => 'Chapter #082', 'releasedAt' => '2 ngày trước'],
                    ],
                ],
                [
                    'slug' => 'cuoc-phieu-luu-ky-la-cua-jojo-phan-4-full-mau',
                    'title' => 'Cuộc phiêu lưu kỳ lạ của JoJo - Phần 4 (Full màu)',
                    'posterPath' => 'https://prvhtr.mgbucket.xyz/posters/example.jpg',
                    'avgVote' => 0,
                    'chapters' => [
                        ['id' => 2168099, 'slug' => 'chapter-174-end', 'name' => 'Chapter #174 (END)', 'releasedAt' => '8 tháng trước'],
                    ],
                ],
                [
                    'slug' => 'yeonwoos-innocence',
                    'title' => 'Yeonwoo\'s Innocence',
                    'posterPath' => 'https://prvhtr.mgbucket.xyz/posters/example.jpg',
                    'avgVote' => 0,
                    'chapters' => [
                        ['id' => 2168098, 'slug' => 'chapter-206', 'name' => 'Chapter #206', 'releasedAt' => '4 tháng trước'],
                    ],
                ],
                [
                    'slug' => 'co-ban-nai-nokotan-cua-toi',
                    'title' => 'Cô Bạn Nai Nokotan Của Tôi!',
                    'posterPath' => 'https://prvhtr.mgbucket.xyz/posters/example.jpg',
                    'avgVote' => 0,
                    'chapters' => [
                        ['id' => 2168097, 'slug' => 'chapter-48', 'name' => 'Chapter #48', 'releasedAt' => '8 tháng trước'],
                    ],
                ],
                [
                    'slug' => 'tham-tu-lung-danh-conan-loat-truyen-tranh-nxb-kim-dong',
                    'title' => 'Thám tử lừng danh Conan Loạt truyện tranh (NXB Kim Đồng)',
                    'posterPath' => 'https://prvhtr.mgbucket.xyz/posters/example.jpg',
                    'avgVote' => 0,
                    'chapters' => [
                        ['id' => 2168096, 'slug' => 'chapter-1176', 'name' => 'Chapter #1176', 'releasedAt' => '4 tháng trước'],
                    ],
                ],
            ],
        ],
        'slice-of-life' => [
            'name' => 'Slice of Life',
            'slug' => 'slice-of-life',
            'mangas' => [
                [
                    'slug' => 'diet-slime-suot-300-nam-toi-levelmax-luc-nao-chang-hay-nxb-the-gioi',
                    'title' => 'Diệt Slime Suốt 300 Năm, Tôi Levelmax Lúc Nào Chẳng Hay (NXB Thế Giới)',
                    'posterPath' => 'https://prvhtr.mgbucket.xyz/posters/example.jpg',
                    'avgVote' => 0,
                    'chapters' => [
                        ['id' => 2168111, 'slug' => 'chuong-68-den-hon-dao-khong-nguoi', 'name' => 'Chapter #68: Đến hòn đảo không người', 'releasedAt' => '14 ngày trước'],
                    ],
                ],
                [
                    'slug' => 'menh-lenh-bi-mat-tu-chi-sep',
                    'title' => 'Mệnh Lệnh Bí Mật Từ Chị Sếp',
                    'posterPath' => 'https://prvhtr.mgbucket.xyz/posters/example.jpg',
                    'avgVote' => 0,
                    'chapters' => [
                        ['id' => 2168107, 'slug' => 'chapter-110-end', 'name' => 'Chapter #110 - END', 'releasedAt' => '5 tháng trước'],
                    ],
                ],
                [
                    'slug' => 'bocchi-the-rock-ngoai-truyen-nhat-ky-say-ruou-cua-hiroi-kikuri',
                    'title' => 'Bocchi The Rock! Ngoại Truyện: Nhật Ký Say Rượu Của Hiroi Kikuri',
                    'posterPath' => 'https://prvhtr.mgbucket.xyz/posters/example.jpg',
                    'avgVote' => 0,
                    'chapters' => [
                        ['id' => 2168095, 'slug' => 'chapter-42', 'name' => 'Chapter 42', 'releasedAt' => '6 tháng trước'],
                    ],
                ],
                [
                    'slug' => 'su-tro-lai-cua-chien-than',
                    'title' => 'Sự Trở Lại Của Chiến Thần',
                    'posterPath' => 'https://prvhtr.mgbucket.xyz/posters/example.jpg',
                    'avgVote' => 0,
                    'chapters' => [
                        ['id' => 2168094, 'slug' => 'chapter-116', 'name' => 'Chapter #116', 'releasedAt' => '3 tháng trước'],
                    ],
                ],
                [
                    'slug' => 'beast-tamer-nguoi-thuan-hoa-thu',
                    'title' => 'Beast Tamer - Người Thuần Hóa Thú',
                    'posterPath' => 'https://prvhtr.mgbucket.xyz/posters/example.jpg',
                    'avgVote' => 0,
                    'chapters' => [
                        ['id' => 2168112, 'slug' => 'chapter-93', 'name' => 'Chapter #93', 'releasedAt' => '1 tháng trước'],
                    ],
                ],
            ],
        ],
        'shoujo' => [
            'name' => 'Shoujo',
            'slug' => 'shoujo',
            'mangas' => [
                [
                    'slug' => 'hom-nay-cau-ay-cung-that-de-thuong',
                    'title' => 'Hôm nay cậu ấy cũng thật dễ thương',
                    'posterPath' => 'https://prvhtr.mgbucket.xyz/posters/example.jpg',
                    'avgVote' => 0,
                    'chapters' => [
                        ['id' => 2168093, 'slug' => 'ngoai-truyen-7', 'name' => 'Ngoại truyện 7', 'releasedAt' => '1 năm trước'],
                    ],
                ],
                [
                    'slug' => 'teru-teru-x-shounen',
                    'title' => 'Teru Teru X Shounen',
                    'posterPath' => 'https://prvhtr.mgbucket.xyz/posters/example.jpg',
                    'avgVote' => 3.5,
                    'chapters' => [
                        ['id' => 2168092, 'slug' => 'chapter-85', 'name' => 'Chapter 85', 'releasedAt' => '5 năm trước'],
                    ],
                ],
                [
                    'slug' => 'your-majesty-please-stop-now',
                    'title' => 'Your Majesty, Please Stop Now',
                    'posterPath' => 'https://prvhtr.mgbucket.xyz/posters/example.jpg',
                    'avgVote' => 0,
                    'chapters' => [
                        ['id' => 2168091, 'slug' => 'chapter-32', 'name' => 'Chapter 32', 'releasedAt' => '7 tháng trước'],
                    ],
                ],
                [
                    'slug' => 'kyuuketsuki-chan-to-kouhai-chan',
                    'title' => 'Kyuuketsuki-chan to Kouhai-chan',
                    'posterPath' => 'https://prvhtr.mgbucket.xyz/posters/example.jpg',
                    'avgVote' => 3.7,
                    'chapters' => [
                        ['id' => 2168090, 'slug' => 'chapter-23', 'name' => 'Chapter 23', 'releasedAt' => '5 năm trước'],
                    ],
                ],
                [
                    'slug' => 'trong-sinh-vao-the-gioi-tu-chan-cyber-ta-do-sat-tat-ca-de-buoc-len-dinh-phong',
                    'title' => 'Trọng Sinh Vào Thế Giới Tu Chân Cyber, Ta Đồ Sát Tất Cả Để Bước Lên Đỉnh Phong',
                    'posterPath' => 'https://prvhtr.mgbucket.xyz/posters/example.jpg',
                    'avgVote' => 0,
                    'chapters' => [
                        ['id' => 2168089, 'slug' => 'chapter-325', 'name' => 'Chapter 325', 'releasedAt' => '3 tháng trước'],
                    ],
                ],
            ],
        ],
    ];
    
    return view('genre.all', [
        'genres' => $genres,
    ]);
})->name('genre.all');

Route::get('/genre/{slug}', function ($slug) {
    $page = request()->get('page', 1);
    
    // Map genre slug to name and description
    $genreMap = [
        'action' => [
            'name' => 'Action',
            'title' => 'Truyện tranh Action',
            'description' => 'Truyện tranh Action là thể loại thường có nội dung về đánh nhau, bạo lực, hỗn loạn, với diễn biến nhanh',
        ],
        'romance' => [
            'name' => 'Romance',
            'title' => 'Truyện tranh Romance',
            'description' => 'Truyện tranh Romance là thể loại tập trung vào tình yêu và các mối quan hệ lãng mạn',
        ],
        'comedy' => [
            'name' => 'Comedy',
            'title' => 'Truyện tranh Comedy',
            'description' => 'Truyện tranh Comedy là thể loại hài hước, vui nhộn với các tình huống gây cười',
        ],
    ];
    
    $genre = $genreMap[$slug] ?? [
        'name' => ucfirst($slug),
        'title' => 'Truyện tranh ' . ucfirst($slug),
        'description' => 'Danh sách truyện tranh ' . ucfirst($slug),
    ];
    
    // Demo data - 10 truyện mẫu cho Action
    $results = [
        [
            'slug' => 'alice-in-borderland',
            'title' => 'Alice in Borderland',
            'posterPath' => 'https://prvhtr.mgbucket.xyz/posters/1a/98/alice-in-borderland.png',
            'avgVote' => 0,
            'chapters' => [
                ['id' => 2157002, 'slug' => 'chapter-65', 'name' => 'Chapter #65', 'releasedAt' => '3 tháng trước'],
            ],
        ],
        [
            'slug' => 'tham-tu-lung-danh-conan-gio-tra-cua-zero-nxb-kim-dong',
            'title' => 'Thám tử lừng danh Conan - Giờ trà của Zero (NXB Kim Đồng)',
            'posterPath' => 'https://prvhtr.mgbucket.xyz/posters/d1/f3/tham-tu-lung-danh-conan-gio-tra-cua-zero-nxb-kim-dong.jpg',
            'avgVote' => 0,
            'chapters' => [
                ['id' => 2150372, 'slug' => 'time-60-thuong-that', 'name' => 'Time #60: Thường thật', 'releasedAt' => '3 tháng trước'],
            ],
        ],
        [
            'slug' => 'astro-boy-atom-cau-be-tay-sat',
            'title' => 'Astro Boy (Atom - Cậu bé tay sắt)',
            'posterPath' => 'https://prvhtr.mgbucket.xyz/posters/22/1e/astro-boy-atom-cau-be-tay-sat.png',
            'avgVote' => 0,
            'chapters' => [
                ['id' => 2166903, 'slug' => 'chuong-69-meeva', 'name' => 'Chương #69: Meeva', 'releasedAt' => '2 tháng trước'],
            ],
        ],
        [
            'slug' => 'cau-chuyen-sinh-ton-cua-kiem-vuong-o-the-gioi-khac',
            'title' => 'Câu Chuyện Sinh Tồn Của Kiếm Vương Ở Thế Giới Khác',
            'posterPath' => 'https://prvhtr.mgbucket.xyz/posters/2b/9d/cau-chuyen-sinh-ton-cua-kiem-vuong-o-the-gioi-khac.png',
            'avgVote' => 0,
            'chapters' => [
                ['id' => 2167092, 'slug' => 'chapter-132', 'name' => 'Chapter #132', 'releasedAt' => '1 tháng trước'],
            ],
        ],
        [
            'slug' => 'toi-necromancer-co-doc',
            'title' => 'Tôi - Necromancer Cô Độc',
            'posterPath' => 'https://prvhtr.mgbucket.xyz/posters/d5/0f/toi-necromancer-co-doc.png',
            'avgVote' => 0,
            'chapters' => [
                ['id' => 2168495, 'slug' => 'chapter-76', 'name' => 'Chapter #76', 'releasedAt' => '23 ngày trước'],
            ],
        ],
        [
            'slug' => 'dragon-ball-super-nxb-kim-dong',
            'title' => 'Dragon Ball Super (NXB Kim Đồng)',
            'posterPath' => 'https://prvhtr.mgbucket.xyz/posters/09/90/dragon-ball-super-nxb-kim-dong.jpg',
            'avgVote' => 0,
            'chapters' => [
                ['id' => 2151377, 'slug' => 'chuong-100-ma-quang-sat-phao-bung-no', 'name' => 'Chương #100: Ma quang sát pháo bùng nổ!', 'releasedAt' => '3 tháng trước'],
            ],
        ],
        [
            'slug' => 'khi-toi-chuyen-sinh-thanh-mot-thanh-kiem',
            'title' => 'Khi Tôi Chuyển Sinh Thành Một Thanh Kiếm',
            'posterPath' => 'https://prvhtr.mgbucket.xyz/posters/bb/cf/khi-toi-chuyen-sinh-thanh-mot-thanh-kiem.jpg',
            'avgVote' => 0,
            'chapters' => [
                ['id' => 2167854, 'slug' => 'chapter-59', 'name' => 'Chapter #59', 'releasedAt' => '1 tháng trước'],
            ],
        ],
        [
            'slug' => 'jujutsu-kaisen-modulo',
            'title' => 'Jujutsu Kaisen Modulo',
            'posterPath' => 'https://prvhtr.mgbucket.xyz/posters/49/d7/jujutsu-kaisen-modulo.png',
            'avgVote' => 0,
            'chapters' => [
                ['id' => 2170261, 'slug' => 'chapter-17', 'name' => 'Chapter #17', 'releasedAt' => '2 ngày trước'],
            ],
        ],
        [
            'slug' => 'chu-thuat-hoi-chien-nxb-kim-dong',
            'title' => 'Chú thuật hồi chiến (NXB Kim Đồng)',
            'posterPath' => 'https://prvhtr.mgbucket.xyz/posters/example.jpg',
            'avgVote' => 0,
            'chapters' => [
                ['id' => 2169342, 'slug' => 'chuong-124-bien-co-shibuya-42', 'name' => 'Chương #124: Biến cố Shibuya 42', 'releasedAt' => '2 ngày trước'],
            ],
        ],
        [
            'slug' => 'phap-su-manh-nhat-dung-sach-chien-luoc-tu-minh-tieu-diet-ma-vuong',
            'title' => 'Pháp Sư Mạnh Nhất Dùng Sách Chiến Lược, Tự Mình Tiêu Diệt Ma Vương',
            'posterPath' => 'https://prvhtr.mgbucket.xyz/posters/example.jpg',
            'avgVote' => 0,
            'chapters' => [
                ['id' => 2160395, 'slug' => 'chapter-68', 'name' => 'Chapter #68', 'releasedAt' => '3 tháng trước'],
            ],
        ],
    ];
    
    return view('genre.index', [
        'slug' => $slug,
        'genre' => $genre,
        'results' => $results,
        'currentPage' => $page,
        'totalPages' => 227,
    ]);
})->name('genre');

Route::get('/the-loai/{slug}', function ($slug) {
    $page = request()->get('page', 1);
    
    // Map category slug to name and description
    $categoryMap = [
        'manga' => [
            'name' => 'Manga',
            'title' => 'Truyện tranh Manga',
            'description' => 'Truyện tranh Nhật Bản',
        ],
        'manhua' => [
            'name' => 'Manhua',
            'title' => 'Truyện tranh Manhua',
            'description' => 'Truyện tranh Trung Quốc',
        ],
        'manhwa' => [
            'name' => 'Manhwa',
            'title' => 'Truyện tranh Manhwa',
            'description' => 'Truyện tranh Hàn Quốc',
        ],
        'marvel-comics' => [
            'name' => 'Marvel Comics',
            'title' => 'Truyện tranh Marvel Comics',
            'description' => 'Truyện tranh Marvel Comics (Mỹ)',
        ],
        'dc-comics' => [
            'name' => 'DC Comics',
            'title' => 'Truyện tranh DC Comics',
            'description' => 'Truyện tranh DC Comics (Mỹ)',
        ],
    ];
    
    $category = $categoryMap[$slug] ?? [
        'name' => ucfirst($slug),
        'title' => 'Truyện tranh ' . ucfirst($slug),
        'description' => 'Danh sách truyện tranh ' . ucfirst($slug),
    ];
    
    // Demo data - 10 truyện mẫu cho Manga
    $results = [
        [
            'slug' => 'one-punch-man',
            'title' => 'One Punch Man',
            'posterPath' => 'https://prvhtr.mgbucket.xyz/posters/2024.11.13/eFBaGhXD2T5EBvM4el.jpg',
            'avgVote' => 5,
            'chapters' => [
                ['id' => 2170271, 'slug' => 'chapter-294', 'name' => 'Chapter 294', 'releasedAt' => '19 giờ trước'],
            ],
        ],
        [
            'slug' => 'blue-lock',
            'title' => 'Blue Lock',
            'posterPath' => 'https://prvhtr.mgbucket.xyz/posters/5f/cf/blue-lock.jpg',
            'avgVote' => 3.1,
            'chapters' => [
                ['id' => 2170270, 'slug' => 'chapter-331', 'name' => 'Chapter 331', 'releasedAt' => '1 ngày trước'],
            ],
        ],
        [
            'slug' => 'the-fragrant-flower-blooms-with-dignity-kaoru-hana-wa-rin-to-saku',
            'title' => 'The Fragrant Flower Blooms With Dignity - Kaoru Hana Wa Rin To Saku',
            'posterPath' => 'https://prvhtr.mgbucket.xyz/posters/15/17/the-fragrant-flower-blooms-with-dignity-kaoru-hana-wa-rin-to-saku.png',
            'avgVote' => 3.8,
            'chapters' => [
                ['id' => 2170262, 'slug' => 'chapter-174', 'name' => 'Chapter #174', 'releasedAt' => '2 ngày trước'],
            ],
        ],
        [
            'slug' => 'jujutsu-kaisen-modulo',
            'title' => 'Jujutsu Kaisen Modulo',
            'posterPath' => 'https://prvhtr.mgbucket.xyz/posters/49/d7/jujutsu-kaisen-modulo.png',
            'avgVote' => 0,
            'chapters' => [
                ['id' => 2170261, 'slug' => 'chapter-17', 'name' => 'Chapter #17', 'releasedAt' => '2 ngày trước'],
            ],
        ],
        [
            'slug' => 'mua-he-hikaru-ra-di',
            'title' => 'Mùa Hè Hikaru Ra Đi',
            'posterPath' => 'https://prvhtr.mgbucket.xyz/posters/90/2f/mua-he-hikaru-ra-di.png',
            'avgVote' => 0,
            'chapters' => [
                ['id' => 2170260, 'slug' => 'chapter-42', 'name' => 'Chapter #42', 'releasedAt' => '2 ngày trước'],
            ],
        ],
        [
            'slug' => 'shangri-la-frontier',
            'title' => 'Shangri-La Frontier',
            'posterPath' => 'https://prvhtr.mgbucket.xyz/posters/b4/61/crappy-game-hunter-challenges-god-tier-game.jpg',
            'avgVote' => 3.5,
            'chapters' => [
                ['id' => 2170258, 'slug' => 'chapter-249', 'name' => 'Chapter #249', 'releasedAt' => '2 ngày trước'],
            ],
        ],
        [
            'slug' => 'bi-phan-boi-boi-dong-doi-va-so-huu-gacha-khong-gioi-han-lv-9999',
            'title' => 'Bị Phản Bội Bởi Đồng Đội Và Sở Hữu [Gacha Không Giới Hạn] Lv.9999',
            'posterPath' => 'https://prvhtr.mgbucket.xyz/posters/5e/d1/bi-phan-boi-boi-dong-doi-va-so-huu-gacha-khong-gioi-han-lv-9999.png',
            'avgVote' => 3.6,
            'chapters' => [
                ['id' => 2170256, 'slug' => 'chapter-187', 'name' => 'Chapter #187', 'releasedAt' => '2 ngày trước'],
            ],
        ],
        [
            'slug' => 'ky-si-chuyen-sinh-bi-luu-day-tro-nen-bat-bai-nho-tro-choi',
            'title' => 'Kỵ Sĩ Chuyển Sinh Bị Lưu Đày, Trở Nên Bất Bại Nhờ Trò Chơi',
            'posterPath' => 'https://prvhtr.mgbucket.xyz/posters/example.jpg',
            'avgVote' => 0,
            'chapters' => [
                ['id' => 2170255, 'slug' => 'chapter-152', 'name' => 'Chapter #152', 'releasedAt' => '2 ngày trước'],
            ],
        ],
        [
            'slug' => 'ba-chi-em-nha-mikadono-de-doi-pho-that-day',
            'title' => 'Ba Chị Em Nhà Mikadono Dễ Đối Phó Thật Đấy',
            'posterPath' => 'https://prvhtr.mgbucket.xyz/posters/example.jpg',
            'avgVote' => 3.8,
            'chapters' => [
                ['id' => 2170254, 'slug' => 'chapter-188', 'name' => 'Chapter #188', 'releasedAt' => '2 ngày trước'],
            ],
        ],
        [
            'slug' => 'chu-thuat-hoi-chien-nxb-kim-dong',
            'title' => 'Chú thuật hồi chiến (NXB Kim Đồng)',
            'posterPath' => 'https://prvhtr.mgbucket.xyz/posters/example.jpg',
            'avgVote' => 0,
            'chapters' => [
                ['id' => 2169342, 'slug' => 'chuong-124-bien-co-shibuya-42', 'name' => 'Chương #124: Biến cố Shibuya 42', 'releasedAt' => '2 ngày trước'],
            ],
        ],
    ];
    
    return view('category.index', [
        'slug' => $slug,
        'category' => $category,
        'results' => $results,
        'currentPage' => $page,
        'totalPages' => 248,
    ]);
})->name('category');

Route::get('/hot-nhat', function () {
    $type = request()->get('type', 'all');
    
    // Demo data - 12 truyện hot nhất
    $results = [
        [
            'slug' => 'vo-luyen-dinh-phong',
            'title' => 'Võ Luyện Đỉnh Phong',
            'posterPath' => 'https://prvhtr.mgbucket.xyz/posters/c8/1e/vo-luyen-dinh-phong.png',
            'avgVote' => 3.3,
            'countView' => 66400000,
            'chapters' => [
                ['id' => 2168858, 'slug' => 'chapter-3860', 'name' => 'Chapter #3860', 'releasedAt' => '23 ngày trước'],
                ['id' => 2168451, 'slug' => 'chapter-3859', 'name' => 'Chapter #3859', 'releasedAt' => '1 tháng trước'],
            ],
        ],
        [
            'slug' => 'ta-co-mot-son-trai',
            'title' => 'Ta Có Một Sơn Trại',
            'posterPath' => 'https://prvhtr.mgbucket.xyz/posters/62/dc/ta-co-mot-son-trai.jpg',
            'avgVote' => 4.2,
            'countView' => 80730,
            'chapters' => [
                ['id' => 2170265, 'slug' => 'chapter-1273', 'name' => 'Chapter #1273', 'releasedAt' => '2 ngày trước'],
                ['id' => 2170264, 'slug' => 'chapter-1272', 'name' => 'Chapter #1272', 'releasedAt' => '2 ngày trước'],
            ],
        ],
        [
            'slug' => 'dai-quan-gia-la-ma-hoang',
            'title' => 'Đại Quản Gia Là Ma Hoàng',
            'posterPath' => 'https://prvhtr.mgbucket.xyz/posters/4b/24/dai-quan-gia-la-ma-hoang.png',
            'avgVote' => 5,
            'countView' => 120750,
            'chapters' => [
                ['id' => 2170224, 'slug' => 'chapter-804', 'name' => 'Chapter 804', 'releasedAt' => '2 ngày trước'],
                ['id' => 2170223, 'slug' => 'chapter-803', 'name' => 'Chapter 803', 'releasedAt' => '2 ngày trước'],
            ],
        ],
        [
            'slug' => 'nguoi-trong-giang-ho',
            'title' => 'Người Trong Giang Hồ',
            'posterPath' => 'https://prvhtr.mgbucket.xyz/posters/18/e8/nguoi-trong-giang-ho.jpg',
            'avgVote' => 5,
            'countView' => 360620,
            'chapters' => [
                ['id' => 595991, 'slug' => 'chapter-2335', 'name' => 'Chapter 2335', 'releasedAt' => '5 năm trước'],
                ['id' => 595993, 'slug' => 'chapter-2334', 'name' => 'Chapter 2334', 'releasedAt' => '5 năm trước'],
            ],
        ],
        [
            'slug' => 'chang-re-manh-nhat-lich-su',
            'title' => 'Chàng Rể Mạnh Nhất Lịch Sử',
            'posterPath' => 'https://prvhtr.mgbucket.xyz/posters/0d/92/chang-re-manh-nhat-lich-su.jpg',
            'avgVote' => 5,
            'countView' => 2280000,
            'chapters' => [
                ['id' => 2170242, 'slug' => 'chapter-368', 'name' => 'Chapter #368', 'releasedAt' => '2 ngày trước'],
                ['id' => 2170243, 'slug' => 'chapter-367', 'name' => 'Chapter #367', 'releasedAt' => '2 ngày trước'],
            ],
        ],
        [
            'slug' => 'shangri-la-frontier',
            'title' => 'Shangri-La Frontier',
            'posterPath' => 'https://prvhtr.mgbucket.xyz/posters/b4/61/crappy-game-hunter-challenges-god-tier-game.jpg',
            'avgVote' => 3.5,
            'countView' => 40940,
            'chapters' => [
                ['id' => 2170258, 'slug' => 'chapter-249', 'name' => 'Chapter #249', 'releasedAt' => '2 ngày trước'],
                ['id' => 2169625, 'slug' => 'chapter-248', 'name' => 'Chapter #248', 'releasedAt' => '14 ngày trước'],
            ],
        ],
        [
            'slug' => 'a-wonderful-new-world',
            'title' => 'A Wonderful New World',
            'posterPath' => 'https://prvhtr.mgbucket.xyz/posters/8d/30/a-wonderful-new-world.png',
            'avgVote' => 0,
            'countView' => 0,
            'chapters' => [
                ['id' => 2028109, 'slug' => 'chapter-262-end', 'name' => 'Chapter #262 END', 'releasedAt' => '4 tháng trước'],
                ['id' => 2028108, 'slug' => 'chapter-261', 'name' => 'Chapter #261', 'releasedAt' => '5 tháng trước'],
            ],
        ],
        [
            'slug' => 'nguyen-ton',
            'title' => 'Nguyên Tôn',
            'posterPath' => 'https://prvhtr.mgbucket.xyz/posters/93/53/nguyen-ton.png',
            'avgVote' => 4.6,
            'countView' => 13280000,
            'chapters' => [
                ['id' => 1873512, 'slug' => 'chapter-951', 'name' => 'Chapter 951', 'releasedAt' => '9 tháng trước'],
            ],
        ],
        [
            'slug' => 'shuumatsu-no-valkyrie',
            'title' => 'Shuumatsu no Valkyrie',
            'posterPath' => 'https://prvhtr.mgbucket.xyz/posters/4e/08/shuumatsu-no-valkyrie.jpg',
            'avgVote' => 3.1,
            'countView' => 80230,
            'chapters' => [
                ['id' => 2064446, 'slug' => 'chapter-107', 'name' => 'Chapter 107', 'releasedAt' => '4 tháng trước'],
                ['id' => 2064380, 'slug' => 'chapter-106', 'name' => 'Chapter 106', 'releasedAt' => '4 tháng trước'],
            ],
        ],
        [
            'slug' => 'tuyet-the-vo-than',
            'title' => 'Tuyệt Thế Võ Thần',
            'posterPath' => 'https://prvhtr.mgbucket.xyz/posters/fe/f3/tuyet-the-vo-than.png',
            'avgVote' => 4.2,
            'countView' => 10240000,
            'chapters' => [
                ['id' => 2170268, 'slug' => 'chapter-1106', 'name' => 'Chapter #1106', 'releasedAt' => '2 ngày trước'],
                ['id' => 2170267, 'slug' => 'chapter-1105', 'name' => 'Chapter #1105', 'releasedAt' => '2 ngày trước'],
            ],
        ],
        [
            'slug' => 'one-punch-man',
            'title' => 'One Punch Man',
            'posterPath' => 'https://prvhtr.mgbucket.xyz/posters/2024.11.13/eFBaGhXD2T5EBvM4el.jpg',
            'avgVote' => 5,
            'countView' => 208330,
            'chapters' => [
                ['id' => 2170271, 'slug' => 'chapter-294', 'name' => 'Chapter 294', 'releasedAt' => '19 giờ trước'],
            ],
        ],
        [
            'slug' => 'blue-lock',
            'title' => 'Blue Lock',
            'posterPath' => 'https://prvhtr.mgbucket.xyz/posters/5f/cf/blue-lock.jpg',
            'avgVote' => 3.1,
            'countView' => 62970,
            'chapters' => [
                ['id' => 2170270, 'slug' => 'chapter-331', 'name' => 'Chapter 331', 'releasedAt' => '1 ngày trước'],
            ],
        ],
    ];
    
    return view('hot.index', [
        'type' => $type,
        'results' => $results,
    ]);
})->name('hot');

// Trang tin tức
Route::get('/tin-tuc', function () {
    $currentPage = request()->get('page', 1);
    $totalPages = 3;
    
    // Tin nổi bật
    $featuredNews = [
        'slug' => 'top-truyen-tranh-manhwa-hay-nhat',
        'title' => 'Top 10 truyện tranh Manhwa hay nhất',
        'image' => 'https://img.htrcdn.com/fast/0x200/oss.cdnfastest.com/90htr/blogs/MTc0Nzk3MDQ1MzM1Mg==/top-truyen-tranh-manhwa-hay-nhat.jpg',
        'description' => 'Manhwa hay còn gọi là truyện tranh Hàn Quốc là món ăn tinh thần không thể thiếu của giới trẻ ngày na',
        'author' => 'Mạc Văn Đĩnh',
        'date' => '23/05/2025',
    ];
    
    // Tin khác
    $otherNews = [
        [
            'slug' => 'sunekichi-honekawa-nguoi-anh-ho-tai-gioi-cua-suneo',
            'title' => 'Sunekichi Honekawa - Người anh họ tài giỏi của Suneo',
            'image' => 'https://img.htrcdn.com/fast/0x200/oss.cdnfastest.com/90htr/blogs/MTc0OTgxNTMzODE5Ng==/sunekichi-honekawa-nguoi-anh-ho-tai-gioi-cua-suneo.png',
            'description' => 'Nhắc đến thế giới nhân vật phụ trong Doraemon thì có một cái tên cũng nhận được nhiều sự chú ý, đó c',
            'author' => 'Mạc Văn Đĩnh',
            'date' => '13/06/2025',
        ],
        [
            'slug' => 'ba-honekawa-me-cua-suneo-trong-cau-chuyen-doraemon',
            'title' => 'Bà Honekawa - Mẹ của Suneo trong câu chuyện Doraemon',
            'image' => 'https://img.htrcdn.com/fast/0x200/oss.cdnfastest.com/90htr/blogs/MTc0OTgxNDk5NDQ3MA==/ba-honekawa-me-cua-suneo-trong-cau-chuyen-doraemon.png',
            'description' => 'Mỗi nhân vật phụ trong Doraemon đều được xây dựng tỉ mỉ, tạo nên một thế giới đa sắc màu và phản ánh',
            'author' => 'Mạc Văn Đĩnh',
            'date' => '13/06/2025',
        ],
        [
            'slug' => 'me-cua-jaian-ba-gouda-nghiem-khac-nhung-giau-tinh-thuong',
            'title' => 'Mẹ của Jaian bà Gouda nghiêm khắc nhưng giàu tình thương',
            'image' => 'https://img.htrcdn.com/fast/0x200/oss.cdnfastest.com/90htr/blogs/MTc0OTgxNDcyNTg2Mw==/me-cua-jaian-ba-gouda-nghiem-khac-nhung-giau-tinh-thuong.png',
            'description' => 'Ngoài Nobita và những người bạn thì các bà mẹ trong thế giới Doraemon cũng rất được yêu thích. Một t',
            'author' => 'Mạc Văn Đĩnh',
            'date' => '13/06/2025',
        ],
        [
            'slug' => 'kaminari-ong-hang-xom-nong-tinh-nhung-day-tinh-cam-trong-doraemon',
            'title' => 'Kaminari - Ông hàng xóm nóng tính nhưng đầy tình cảm trong Doraemon',
            'image' => 'https://img.htrcdn.com/fast/0x200/oss.cdnfastest.com/90htr/blogs/MTc0OTY5ODE4MTA4Mg==/kaminari-ong-hang-xom-nong-tinh-nhung-day-tinh-cam-trong-doraemon.png',
            'description' => 'Sở dĩ Doraemon có thể thu hút hàng triệu người hâm mộ qua bao thời gian không chỉ vì nhóm nhân vật c',
            'author' => 'Mạc Văn Đĩnh',
            'date' => '12/06/2025',
        ],
        [
            'slug' => 'hoshino-sumire-ngoi-sao-tuoi-teen-tai-nang-trong-doraemon',
            'title' => 'Hoshino Sumire - Ngôi sao tuổi teen tài năng trong Doraemon',
            'image' => 'https://img.htrcdn.com/fast/0x200/oss.cdnfastest.com/90htr/blogs/MTc0OTY5ODMxNzAyOA==/hoshino-sumire-ngoi-sao-tuoi-teen-tai-nang-trong-doraemon.png',
            'description' => 'Hoshino Sumire là nhân vật để lại ấn tượng sâu sắc nhờ sự duyên dáng và chiều sâu trong tính cách dù',
            'author' => 'Mạc Văn Đĩnh',
            'date' => '12/06/2025',
        ],
        [
            'slug' => 'yasuo-tanaka-cau-nhoc-nang-dong-hoat-bat-trong-the-gioi-doraemon',
            'title' => 'Yasuo Tanaka - Cậu nhóc năng động, hoạt bát trong thế giới Doraemon',
            'image' => 'https://img.htrcdn.com/fast/0x200/oss.cdnfastest.com/90htr/blogs/MTc0OTY5ODA2NzI3Nw==/yasuo-tanaka-cau-nhoc-nang-dong-hoat-bat-trong-the-gioi-doraemon.png',
            'description' => 'Trong Doraemon có nhiều nhân vật phụ góp phần làm phong phú thêm thế giới học đường và đời sống thườ',
            'author' => 'Mạc Văn Đĩnh',
            'date' => '12/06/2025',
        ],
        [
            'slug' => 'tong-quan-ve-suc-manh-va-tieu-su-duong-khai-trong-vo-luyen-dinh-phong',
            'title' => 'Tổng quan về sức mạnh và tiểu sử Dương Khai trong Võ Luyện Đỉnh Phong',
            'image' => 'https://img.htrcdn.com/fast/0x200/oss.cdnfastest.com/90htr/blogs/MTc0OTE4Mjk1NDY3OQ==/tong-quan-ve-suc-manh-va-tieu-su-duong-khai-trong-vo-luyen-dinh-phong.jpg',
            'description' => 'Võ Luyện Đỉnh Phong là truyện kể về thế giới tu tiên, trong đó Dương Khai là nhân vật chính nhưng bẩ',
            'author' => 'Mạc Văn Đĩnh',
            'date' => '09/06/2025',
        ],
        [
            'slug' => 'danh-tinh-nhan-vat-rum-trong-truyen-conan',
            'title' => 'Danh tính nhân vật Rum trong truyện Conan',
            'image' => 'https://img.htrcdn.com/fast/0x200/oss.cdnfastest.com/90htr/blogs/MTc0ODkzMzUyNzc4OQ==/danh-tinh-nhan-vat-rum-trong-truyen-conan.jpg',
            'description' => 'Rum là nhân vật nguy hiểm và là chỉ huy số hai trong Tổ chức Áo đen chỉ đứng sau ông trùm. Tên này r',
            'author' => 'Mạc Văn Đĩnh',
            'date' => '03/06/2025',
        ],
    ];
    
    return view('news.index', [
        'currentPage' => $currentPage,
        'totalPages' => $totalPages,
        'featuredNews' => $featuredNews,
        'otherNews' => $otherNews,
    ]);
})->name('news');

// Trang truyện mới nhất
Route::get('/new', function () {
    $currentPage = request()->get('page', 1);
    $totalPages = 516;
    
    $results = [
        [
            'slug' => 'kiep-nay-toi-se-tro-thanh-gia-chu',
            'title' => 'Kiếp Này, Tôi Sẽ Trở Thành Gia Chủ',
            'posterPath' => 'https://prvhtr.mgbucket.xyz/posters/e3/f7/kiep-nay-toi-se-tro-thanh-gia-chu.png',
            'avgVote' => 3,
            'chapters' => [
                ['id' => 2170215, 'slug' => 'chapter-203', 'name' => 'Chapter 203', 'releasedAt' => '2 ngày trước'],
            ],
        ],
        [
            'slug' => 'gacha-vo-han',
            'title' => 'Gacha Vô Hạn',
            'posterPath' => 'https://prvhtr.mgbucket.xyz/posters/d8/bb/gacha-vo-han.png',
            'avgVote' => 5,
            'chapters' => [
                ['id' => 2170213, 'slug' => 'chapter-183', 'name' => 'Chapter #183', 'releasedAt' => '2 ngày trước'],
            ],
        ],
        [
            'slug' => 'toan-tri-doc-gia',
            'title' => 'Toàn Trí Độc Giả',
            'posterPath' => 'https://prvhtr.mgbucket.xyz/posters/77/55/toan-tri-doc-gia.png',
            'avgVote' => 3.9,
            'chapters' => [
                ['id' => 2170205, 'slug' => 'chapter-296', 'name' => 'Chapter #296', 'releasedAt' => '2 ngày trước'],
            ],
        ],
        [
            'slug' => 'ban-hoc-cua-toi-la-linh-danh-thue',
            'title' => 'Bạn Học Của Tôi Là Lính Đánh Thuê',
            'posterPath' => 'https://prvhtr.mgbucket.xyz/posters/76/6b/ban-hoc-cua-toi-la-linh-danh-thue.png',
            'avgVote' => 4.6,
            'chapters' => [
                ['id' => 2170203, 'slug' => 'chapter-270', 'name' => 'Chapter #270', 'releasedAt' => '2 ngày trước'],
            ],
        ],
        [
            'slug' => 'dai-tuong-vo-hinh',
            'title' => 'Đại Tượng Vô Hình',
            'posterPath' => 'https://prvhtr.mgbucket.xyz/posters/0f/91/dai-tuong-vo-hinh.png',
            'avgVote' => 4.7,
            'chapters' => [
                ['id' => 2170201, 'slug' => 'chapter-542', 'name' => 'Chapter #542', 'releasedAt' => '2 ngày trước'],
            ],
        ],
        [
            'slug' => 'cau-ut-nha-cong-tuoc-la-sat-thu-hoi-quy',
            'title' => 'Cậu Út Nhà Công Tước Là Sát Thủ Hồi Quy',
            'posterPath' => 'https://prvhtr.mgbucket.xyz/posters/1a/7d/cau-ut-nha-cong-tuoc-la-sat-thu-hoi-quy.png',
            'avgVote' => 4.9,
            'chapters' => [
                ['id' => 2170191, 'slug' => 'chapter-103', 'name' => 'Chapter #103', 'releasedAt' => '2 ngày trước'],
            ],
        ],
        [
            'slug' => 'tro-lai-voi-chanbi',
            'title' => 'Trở lại với Chanbi',
            'posterPath' => 'https://prvhtr.mgbucket.xyz/posters/08/b9/tro-lai-voi-chanbi.png',
            'avgVote' => 0,
            'chapters' => [
                ['id' => 2170189, 'slug' => 'chapter-082', 'name' => 'Chapter #082', 'releasedAt' => '2 ngày trước'],
            ],
        ],
        [
            'slug' => 'bang-xep-hang-quan-vuong-nxb-kim-dong',
            'title' => 'Bảng xếp hạng quân vương (NXB Kim Đồng)',
            'posterPath' => 'https://prvhtr.mgbucket.xyz/posters/5a/3b/bang-xep-hang-quan-vuong-nxb-kim-dong.jpg',
            'avgVote' => 0,
            'chapters' => [
                ['id' => 2164485, 'slug' => 'hoi-193-5-chuyen-ve-thong-linh-bang-dao-tac-lon', 'name' => 'Hồi #193.5: CHUYỆN VỀ THỐNG LĨNH BĂNG ĐẠO TẶC LỚN', 'releasedAt' => '2 tháng trước'],
            ],
        ],
        [
            'slug' => 'diet-slime-suot-300-nam-toi-levelmax-luc-nao-chang-hay-nxb-the-gioi',
            'title' => 'Diệt Slime Suốt 300 Năm, Tôi Levelmax Lúc Nào Chẳng Hay (NXB Thế Giới)',
            'posterPath' => 'https://prvhtr.mgbucket.xyz/posters/27/15/diet-slime-suot-300-nam-toi-levelmax-luc-nao-chang-hay-nxb-the-gioi.jpg',
            'avgVote' => 0,
            'chapters' => [
                ['id' => 2169729, 'slug' => 'chapter-68-den-hon-dao-khong-nguoi', 'name' => 'Chapter #68: Đến hòn đảo không người', 'releasedAt' => '14 ngày trước'],
            ],
        ],
        [
            'slug' => 'gto-fury-of-death-yamada',
            'title' => 'GTO: Fury of Death Yamada',
            'posterPath' => 'https://prvhtr.mgbucket.xyz/posters/bd/f3/gto-fury-of-death-yamada.jpeg',
            'avgVote' => 0,
            'chapters' => [
                ['id' => 2169687, 'slug' => 'chapter-13', 'name' => 'Chapter 13', 'releasedAt' => '14 ngày trước'],
            ],
        ],
        [
            'slug' => 'the-boys',
            'title' => 'The Boys',
            'posterPath' => 'https://prvhtr.mgbucket.xyz/posters/a6/11/the-boys.jpeg',
            'avgVote' => 0,
            'chapters' => [
                ['id' => 2169654, 'slug' => 'chapter-29', 'name' => 'Chapter 29', 'releasedAt' => '14 ngày trước'],
            ],
        ],
        [
            'slug' => 'one-piece',
            'title' => 'One Piece',
            'posterPath' => 'https://prvhtr.mgbucket.xyz/posters/op/one-piece.jpg',
            'avgVote' => 4.8,
            'chapters' => [
                ['id' => 2169650, 'slug' => 'chapter-1100', 'name' => 'Chapter 1100', 'releasedAt' => '1 ngày trước'],
            ],
        ],
    ];
    
    return view('new.index', [
        'currentPage' => $currentPage,
        'totalPages' => $totalPages,
        'results' => $results,
    ]);
})->name('new');

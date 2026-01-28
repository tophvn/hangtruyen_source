<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MangaController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\ImageProxyController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\PageController;

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
Route::get('/auth/google', [AuthController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback'])->name('auth.google.callback');
Route::get('/auth/logout', [AuthController::class, 'logout'])->name('auth.logout');
Route::post('/auth/logout', [AuthController::class, 'logout'])->name('auth.logout.post');

Route::get('/uploads/comics/{path}', [ImageProxyController::class, 'comics'])->where('path', '.*');
Route::get('/uploads/{path}', [ImageProxyController::class, 'uploads'])->where('path', '.*');

Route::get('/proxy-img', [ImageProxyController::class, 'proxy']);

// Home
Route::get('/', [HomeController::class, 'index']);

// Manga routes
Route::get('/truyen-tranh/{slug}', [MangaController::class, 'detail'])->name('manga.detail');
Route::get('/truyen-tranh/{mangaSlug}/{chapterSlug}', [MangaController::class, 'chapter'])->name('manga.chapter');
Route::post('/truyen-tranh/{slug}/vote', [MangaController::class, 'vote'])->middleware('web')->name('manga.vote');
Route::post('/truyen-tranh/{slug}/follow', [MangaController::class, 'follow'])->middleware('web')->name('manga.follow');
Route::post('/truyen-tranh/{slug}/report', [MangaController::class, 'report'])->middleware('web')->name('manga.report');
Route::get('/random', [MangaController::class, 'random'])->name('random');

// Comment routes
Route::get('/truyen-tranh/{slug}/comments', [CommentController::class, 'index'])->name('manga.comments.index');
Route::post('/truyen-tranh/{slug}/comments', [CommentController::class, 'store'])->middleware('web')->name('manga.comments.store');
Route::post('/truyen-tranh/{slug}/comments/{commentId}/like', [CommentController::class, 'like'])->middleware('web')->name('manga.comments.like');
Route::get('/truyen-tranh/{slug}/comments/liked-ids', [CommentController::class, 'getLikedIds'])->name('manga.comments.liked-ids');
Route::get('/api/comment/{commentId}/manga-id', [CommentController::class, 'getMangaId']);

// Search routes
Route::get('/tim-kiem', [SearchController::class, 'index'])->name('search');
Route::get('/api/search', [SearchController::class, 'api']);

// Account routes
Route::middleware('auth')->group(function () {
    Route::get('/tai-khoan', [AccountController::class, 'index'])->name('account.index');
    Route::put('/tai-khoan', [AccountController::class, 'update'])->name('account.update');
    Route::post('/tai-khoan/upload-avatar', [AccountController::class, 'uploadAvatar'])->name('account.upload-avatar');
    Route::post('/tai-khoan/clear-reading', [AccountController::class, 'clearReading'])->name('account.clear-reading');
});

Route::get('/genre', [MangaController::class, 'genreAll'])->name('genre.all');
Route::get('/genre/{slug}', [MangaController::class, 'genre'])->name('genre');

// Category routes
Route::get('/the-loai', [MangaController::class, 'categoryAll'])->name('the-loai.all');
Route::get('/the-loai/{slug}', [MangaController::class, 'category'])->name('category');

// Completed mangas
Route::get('/da-hoan-thanh', [MangaController::class, 'completed'])->name('completed');

// Hot mangas
Route::get('/hot-nhat', [MangaController::class, 'hot'])->name('hot');

// News routes
Route::get('/tin-tuc', [PostController::class, 'index'])->name('news');
Route::get('/tin-tuc/{slug}', [PostController::class, 'detail'])->name('news.detail');

// New mangas
Route::get('/new', [MangaController::class, 'newMangas'])->name('new');

// Static pages
Route::get('/trang/chinh-sach-bao-mat', [PageController::class, 'privacy'])->name('page.privacy');
Route::get('/trang/dieu-khoan-dich-vu', [PageController::class, 'terms'])->name('page.terms');
Route::get('/trang/tuyen-bo-mien-tru-trach-nhiem', [PageController::class, 'disclaimer'])->name('page.disclaimer');
Route::get('/trang/ve-chung-toi', [PageController::class, 'about'])->name('page.about');

// Admin routes
Route::prefix('admin')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('admin.index');

    // Users
    Route::get('/users', [AdminController::class, 'users'])->name('admin.users');
    Route::post('/users/{id}/change-role', [AdminController::class, 'changeRole'])->name('admin.users.change-role');
    Route::post('/users/{id}/ban', [AdminController::class, 'banUser'])->name('admin.users.ban');
    Route::post('/users/{id}/unban', [AdminController::class, 'unbanUser'])->name('admin.users.unban');

    // Comments
    Route::get('/comments', [AdminController::class, 'comments'])->name('admin.comments');
    Route::post('/comments/{id}/delete', [AdminController::class, 'deleteComment'])->name('admin.comments.delete');

    // Mangas
    Route::get('/mangas', [AdminController::class, 'mangas'])->name('admin.mangas');
    Route::get('/mangas/search', [AdminController::class, 'searchMangas'])->name('admin.mangas.search');
    Route::post('/mangas/trending', [AdminController::class, 'saveTrending'])->name('admin.mangas.trending');

    // Crawl
    Route::post('/mangas/crawl', [AdminController::class, 'crawlStart'])->name('admin.mangas.crawl');
    Route::get('/mangas/crawl/progress/{jobId}', [AdminController::class, 'crawlProgress'])->name('admin.mangas.crawl.progress');

    // Settings
    Route::get('/settings', [AdminController::class, 'settings'])->name('admin.settings');
    Route::post('/settings/site', [AdminController::class, 'saveSiteSettings'])->name('admin.settings.site');
    Route::post('/settings/site/images/reset', [AdminController::class, 'resetSiteImages'])->name('admin.settings.site.images.reset');
    Route::post('/settings/site/text/reset', [AdminController::class, 'resetSiteText'])->name('admin.settings.site.text.reset');
    Route::post('/settings/effect', [AdminController::class, 'saveEffect'])->name('admin.settings.effect');
    Route::post('/settings/social', [AdminController::class, 'saveSocial'])->name('admin.settings.social');
    Route::post('/settings/social/clear', [AdminController::class, 'clearSocial'])->name('admin.settings.social.clear');
    Route::post('/settings/gtag', [AdminController::class, 'saveGtag'])->name('admin.settings.gtag');
    Route::post('/settings/gtag/clear', [AdminController::class, 'clearGtag'])->name('admin.settings.gtag.clear');

    // Posts
    Route::get('/posts', [AdminController::class, 'posts'])->name('admin.posts');
    Route::get('/posts/create', [AdminController::class, 'createPost'])->name('admin.posts.create');
    Route::post('/posts', [AdminController::class, 'storePost'])->name('admin.posts.store');
    Route::get('/posts/{id}/edit', [AdminController::class, 'editPost'])->name('admin.posts.edit');
    Route::put('/posts/{id}', [AdminController::class, 'updatePost'])->name('admin.posts.update');
    Route::delete('/posts/{id}', [AdminController::class, 'destroyPost'])->name('admin.posts.destroy');
    Route::post('/posts/{id}/toggle-status', [AdminController::class, 'togglePostStatus'])->name('admin.posts.toggle-status');
    Route::post('/posts/upload-image', [AdminController::class, 'uploadPostImage'])->name('admin.posts.upload-image');

    // Reports
    Route::get('/reports', [AdminController::class, 'reports'])->name('admin.reports');
    Route::post('/reports/{id}/status', [AdminController::class, 'updateReportStatus'])->name('admin.reports.status');
    Route::delete('/reports/{id}', [AdminController::class, 'deleteReport'])->name('admin.reports.delete');

    // Categories
    Route::get('/categories', [AdminController::class, 'categories'])->name('admin.categories');
    Route::post('/categories/{id}/update', [AdminController::class, 'updateCategory'])->name('admin.categories.update');

    // Analytics
    Route::get('/analytics', [AdminController::class, 'analytics'])->name('admin.analytics');

    // Ads & SEO
    Route::get('/ads-seo', [AdminController::class, 'adsSeo'])->name('admin.ads-seo');
    Route::post('/ads-seo/save', [AdminController::class, 'saveAdsSeo'])->name('admin.ads-seo.save');
});

Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
Route::get('/robots.txt', [SitemapController::class, 'robots'])->name('robots');


<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\MangaMetadata;
use App\Models\MangaChapter;
use App\Models\MangaComment;
use App\Models\Setting;
use App\Models\Post;
use App\Services\OTruyenService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;

class AdminController extends Controller
{
    // Dashboard
    public function index()
    {
        $stats = [
            'total_users' => User::count(),
            'total_mangas' => MangaMetadata::where('is_active', true)->count(),
            'total_chapters' => MangaChapter::count(),
        ];

        return view('admin.index', compact('stats'));
    }

    // Users
    public function users(Request $request)
    {
        $search = $request->input('search', '');

        $query = User::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('email', 'like', '%' . $search . '%')
                    ->orWhere('name', 'like', '%' . $search . '%');
            });
        }

        // Thống kê
        $stats = [
            'online_now' => User::where('last_seen_at', '>=', now()->subMinutes(5))->count(),
            'online_today' => User::where('last_seen_at', '>=', now()->startOfDay())->count(),
            'new_today' => User::where('created_at', '>=', now()->startOfDay())->count(),
            'new_month' => User::where('created_at', '>=', now()->startOfMonth())->count(),
            'banned' => User::whereNotNull('banned_until')->where('banned_until', '>', now())->count(),
        ];

        $query->orderByRaw('CASE WHEN last_seen_at IS NOT NULL AND last_seen_at >= ? THEN 0 ELSE 1 END', [now()->subMinutes(5)])
            ->orderBy('id', 'desc');

        $users = $query->paginate(10)->appends(request()->query());

        return view('admin.users', compact('users', 'search', 'stats'));
    }

    public function changeRole(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $role = $request->input('role');

        if (!in_array($role, ['user', 'admin'])) {
            return response()->json(['status' => 'error', 'message' => 'Role không hợp lệ'], 400);
        }

        $user->role = $role;
        $user->save();

        return response()->json(['status' => 'success', 'message' => 'Đã thay đổi vai trò thành công']);
    }


    public function togglePostStatus($id)
    {
        $post = Post::findOrFail($id);
        $post->is_active = !$post->is_active;
        $post->save();

        return response()->json([
            'status' => 'success',
            'message' => $post->is_active ? 'Hiện bài viết thành công' : 'Ẩn bài viết thành công',
            'is_active' => $post->is_active
        ]);
    }

    public function banUser(Request $request, $id)
    {
        $user = User::findOrFail($id);

        if ($user->id === auth()->id()) {
            return response()->json(['status' => 'error', 'message' => 'Bạn không thể ban chính mình'], 400);
        }

        $days = (int) $request->input('days');

        if ($days <= 0) {
            return response()->json(['status' => 'error', 'message' => 'Số ngày không hợp lệ'], 400);
        }

        if ($days >= 999999) {
            $user->banned_until = now()->addYears(100);
        } else {
            $user->banned_until = now()->addDays($days);
        }

        $user->save();

        return response()->json(['status' => 'success', 'message' => 'Đã ban user thành công']);
    }

    public function unbanUser($id)
    {
        $user = User::findOrFail($id);
        $user->banned_until = null;
        $user->save();

        return response()->json(['status' => 'success', 'message' => 'Đã unban user thành công']);
    }

    // Comments
    public function comments(Request $request)
    {
        $search = $request->input('search', '');
        $userSearch = $request->input('user_search', '');

        $query = MangaComment::with(['user', 'manga', 'chapter', 'parent'])
            ->orderBy('created_at', 'desc');

        if ($search) {
            $query->where('content', 'like', '%' . $search . '%');
        }

        if ($userSearch) {
            $query->whereHas('user', function ($q) use ($userSearch) {
                $q->where('name', 'like', '%' . $userSearch . '%')
                    ->orWhere('email', 'like', '%' . $userSearch . '%');
            });
        }

        $comments = $query->paginate(20)->appends(request()->query());

        return view('admin.comments', compact('comments', 'search', 'userSearch'));
    }

    public function deleteComment($id)
    {
        $comment = MangaComment::findOrFail($id);
        $comment->replies()->delete();
        $comment->likes()->delete();
        $comment->delete();

        return response()->json(['status' => 'success', 'message' => 'Xóa bình luận thành công']);
    }

    // Mangas
    public function mangas()
    {
        $recentMangas = MangaMetadata::orderBy('updated_at', 'desc')->limit(10)->get();

        $trendingSlugs = json_decode(Setting::get('trending_mangas', '[]'), true) ?? [];
        $trendingMangas = MangaMetadata::whereIn('slug', $trendingSlugs)
            ->get()
            ->sortBy(function ($manga) use ($trendingSlugs) {
                return array_search($manga->slug, $trendingSlugs);
            })
            ->values();

        return view('admin.mangas', compact('recentMangas', 'trendingMangas'));
    }

    public function searchMangas(Request $request)
    {
        $search = $request->input('q', '');
        if (empty($search)) {
            return response()->json([]);
        }

        if (strpos($search, ',') !== false) {
            $slugs = array_map('trim', explode(',', $search));
            $mangas = MangaMetadata::whereIn('slug', $slugs)->get(['id', 'title', 'slug', 'cover_url']);
        } else {
            if (strlen($search) < 2) {
                return response()->json([]);
            }
            $mangas = MangaMetadata::where('title', 'like', '%' . $search . '%')
                ->orWhere('slug', 'like', '%' . $search . '%')
                ->limit(20)
                ->get(['id', 'title', 'slug', 'cover_url']);
        }

        return response()->json($mangas);
    }

    public function saveTrending(Request $request)
    {
        $slugs = $request->input('slugs', []);
        if (count($slugs) > 8) {
            return response()->json([
                'status' => 'error',
                'message' => 'Chọn tối đa 8 truyện'
            ], 400);
        }

        Setting::set('trending_mangas', json_encode($slugs));

        return response()->json([
            'status' => 'success',
            'message' => 'Đã lưu danh sách Top truyện thành công'
        ]);
    }

    // Settings
    public function settings()
    {
        $settings = [
            'site_name' => Setting::get('site_name', 'HangTruyen'),
            'site_description' => Setting::get('site_description', ''),
            'site_keywords' => Setting::get('site_keywords', ''),
            'favicon_path' => Setting::get('favicon_path', '/images/favicon.png'),
            'logo_path' => Setting::get('logo_path', '/images/logo.png'),
            'logo_dark_path' => Setting::get('logo_dark_path', '/images/logo-dark.png'),
            'mini_logo_path' => Setting::get('mini_logo_path', '/images/mini-logo.png'),
            'facebook_url' => Setting::get('facebook_url', ''),
            'twitter_url' => Setting::get('twitter_url', ''),
            'youtube_url' => Setting::get('youtube_url', ''),
            'github_url' => Setting::get('github_url', ''),
            'gmail_url' => Setting::get('gmail_url', ''),
            'gtag_code' => Setting::get('gtag_code', ''),
        ];

        $effects = [
            'none' => 'Không dùng hiệu ứng',
            'bubbles' => 'Bubbles',
            'firework' => 'Pháo hoa',
            'fireworks_sound' => 'Pháo hoa (có âm thanh)',
            'halloween' => 'Halloween',
            'hearts' => 'Trái tim',
            'hoadao' => 'Hoa đào',
            'hoamai' => 'Hoa mai',
            'leaves' => 'Lá rơi',
            'lixi' => 'Lì xì',
            'matrix' => 'Matrix',
            'quockhanh' => 'Quốc khánh',
            'snow' => 'Tuyết rơi',
            'stars' => 'Ngôi sao',
            'trungthu' => 'Trung thu',
        ];
        $currentEffect = Setting::get('site_effect', 'none');

        return view('admin.settings', [
            'settings' => $settings,
            'effects' => $effects,
            'currentEffect' => $currentEffect,
        ]);
    }

    public function saveSiteSettings(Request $request)
    {
        $validated = $request->validate([
            'site_name' => 'nullable|string|max:255',
            'site_description' => 'nullable|string|max:2000',
            'site_keywords' => 'nullable|string|max:2000',
            'favicon' => 'nullable|file|mimes:png|max:2048',
            'logo' => 'nullable|file|mimes:png|max:5120',
            'logo_dark' => 'nullable|file|mimes:png|max:5120',
            'mini_logo' => 'nullable|file|mimes:png|max:5120',
        ]);

        $setOrDelete = function (string $key, $value) {
            if (is_string($value)) {
                $value = trim($value);
            }
            if ($value === null || $value === '') {
                Setting::where('key', $key)->delete();
                return;
            }
            Setting::set($key, $value);
        };

        $setOrDelete('site_name', $validated['site_name'] ?? null);
        $setOrDelete('site_description', $validated['site_description'] ?? null);
        $setOrDelete('site_keywords', $validated['site_keywords'] ?? null);

        $imagesDir = public_path('images');
        if (!is_dir($imagesDir)) {
            @mkdir($imagesDir, 0755, true);
        }

        $defaultsDir = public_path('images/_defaults');
        if (!is_dir($defaultsDir)) {
            @mkdir($defaultsDir, 0755, true);
        }
        foreach (['favicon.png', 'logo.png', 'logo-dark.png', 'mini-logo.png'] as $fname) {
            $src = $imagesDir . DIRECTORY_SEPARATOR . $fname;
            $dst = $defaultsDir . DIRECTORY_SEPARATOR . $fname;
            if (file_exists($src) && !file_exists($dst)) {
                @copy($src, $dst);
            }
        }

        if ($request->hasFile('favicon')) {
            $request->file('favicon')->move($imagesDir, 'favicon.png');
            Setting::set('favicon_path', '/images/favicon.png');
        }
        if ($request->hasFile('logo')) {
            $request->file('logo')->move($imagesDir, 'logo.png');
            Setting::set('logo_path', '/images/logo.png');
        }
        if ($request->hasFile('logo_dark')) {
            $request->file('logo_dark')->move($imagesDir, 'logo-dark.png');
            Setting::set('logo_dark_path', '/images/logo-dark.png');
        }
        if ($request->hasFile('mini_logo')) {
            $request->file('mini_logo')->move($imagesDir, 'mini-logo.png');
            Setting::set('mini_logo_path', '/images/mini-logo.png');
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Đã lưu cấu hình Website thành công',
        ]);
    }

    public function resetSiteImages(Request $request)
    {
        $validated = $request->validate([
            'target' => 'required|string|in:favicon,logo,logo_dark,mini_logo,all',
        ]);

        $imagesDir = public_path('images');
        $defaultsDir = public_path('images/_defaults');

        $map = [
            'favicon' => 'favicon.png',
            'logo' => 'logo.png',
            'logo_dark' => 'logo-dark.png',
            'mini_logo' => 'mini-logo.png',
        ];

        $targets = $validated['target'] === 'all' ? array_keys($map) : [$validated['target']];

        foreach ($targets as $key) {
            $fname = $map[$key] ?? null;
            if (!$fname)
                continue;

            $src = $defaultsDir . DIRECTORY_SEPARATOR . $fname;
            $dst = $imagesDir . DIRECTORY_SEPARATOR . $fname;

            if (!file_exists($src)) {
                if (!is_dir($defaultsDir))
                    @mkdir($defaultsDir, 0755, true);
                if (file_exists($dst))
                    @copy($dst, $src);
            }

            if (!file_exists($src)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Không tìm thấy ảnh mặc định để khôi phục.',
                ], 422);
            }

            @copy($src, $dst);
        }

        Setting::set('favicon_path', '/images/favicon.png');
        Setting::set('logo_path', '/images/logo.png');
        Setting::set('logo_dark_path', '/images/logo-dark.png');
        Setting::set('mini_logo_path', '/images/mini-logo.png');

        return response()->json([
            'status' => 'success',
            'message' => 'Đã khôi phục ảnh mặc định thành công',
        ]);
    }

    public function resetSiteText(Request $request)
    {
        $validated = $request->validate([
            'target' => 'required|string|in:description,keywords,all',
        ]);

        $targets = $validated['target'] === 'all'
            ? ['site_description', 'site_keywords']
            : ($validated['target'] === 'description' ? ['site_description'] : ['site_keywords']);

        Setting::whereIn('key', $targets)->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Đã khôi phục mặc định thành công',
        ]);
    }

    public function saveEffect(Request $request)
    {
        $effects = ['none', 'bubbles', 'firework', 'fireworks_sound', 'halloween', 'hearts', 'hoadao', 'hoamai', 'leaves', 'lixi', 'matrix', 'quockhanh', 'snow', 'stars', 'trungthu'];

        $validated = $request->validate([
            'site_effect' => 'required|string|in:' . implode(',', $effects),
        ]);

        $value = $validated['site_effect'] ?? 'none';

        if ($value === 'none') {
            Setting::where('key', 'site_effect')->delete();
        } else {
            Setting::set('site_effect', $value);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Đã lưu cấu hình hiệu ứng thành công',
        ]);
    }

    public function saveSocial(Request $request)
    {
        Setting::set('facebook_url', $request->input('facebook_url', ''));
        Setting::set('twitter_url', $request->input('twitter_url', ''));
        Setting::set('youtube_url', $request->input('youtube_url', ''));
        Setting::set('github_url', $request->input('github_url', ''));
        Setting::set('gmail_url', $request->input('gmail_url', ''));

        return response()->json([
            'status' => 'success',
            'message' => 'Đã lưu cấu hình thành công'
        ]);
    }

    public function clearSocial()
    {
        Setting::whereIn('key', ['facebook_url', 'twitter_url', 'youtube_url', 'github_url', 'gmail_url'])->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Đã xóa cấu hình thành công'
        ]);
    }

    public function saveGtag(Request $request)
    {
        Setting::set('gtag_code', $request->input('gtag_code', ''));

        return response()->json([
            'status' => 'success',
            'message' => 'Đã lưu Google Tag thành công'
        ]);
    }

    public function clearGtag()
    {
        Setting::where('key', 'gtag_code')->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Đã xóa Google Tag thành công'
        ]);
    }

    // Posts
    public function posts()
    {
        $posts = Post::orderBy('created_at', 'desc')->paginate(20);

        $stats = [
            'total' => Post::count(),
            'published' => Post::where('is_active', true)->count(),
            'draft' => Post::where('is_active', false)->count(),
            'featured' => Post::where('is_featured', true)->count(),
            'new_today' => Post::whereDate('created_at', now()->toDateString())->count(),
        ];

        return view('admin.posts', compact('posts', 'stats'));
    }

    public function createPost()
    {
        return view('admin.posts-create');
    }

    public function storePost(Request $request)
    {
        $request->validate([
            'title' => 'required|max:255',
            'slug' => 'required|unique:posts|max:255',
            'content' => 'required',
            'description' => 'nullable',
            'image' => 'nullable|string',
            'author' => 'nullable|max:255',
        ]);

        Post::create([
            'title' => $request->input('title'),
            'slug' => $request->input('slug'),
            'content' => $request->input('content'),
            'description' => $request->input('description'),
            'image' => $request->input('image'),
            'author' => $request->input('author', 'Admin'),
            'is_featured' => $request->has('is_featured'),
            'is_active' => $request->has('is_published'),
            'published_at' => $request->has('is_published') ? now() : null,
        ]);

        return redirect()->route('admin.posts')->with('success', 'Tạo bài viết thành công');
    }

    public function editPost($id)
    {
        $post = Post::findOrFail($id);
        return view('admin.posts-edit', compact('post'));
    }

    public function updatePost(Request $request, $id)
    {
        $post = Post::findOrFail($id);

        $request->validate([
            'title' => 'required|max:255',
            'slug' => 'required|max:255|unique:posts,slug,' . $id,
            'content' => 'required',
            'description' => 'nullable',
            'image' => 'nullable|string',
            'author' => 'nullable|max:255',
        ]);

        $post->update([
            'title' => $request->input('title'),
            'slug' => $request->input('slug'),
            'content' => $request->input('content'),
            'description' => $request->input('description'),
            'image' => $request->input('image'),
            'author' => $request->input('author', 'Admin'),
            'is_featured' => $request->has('is_featured'),
            'is_active' => $request->has('is_published'),
            'published_at' => $request->has('is_published') ? now() : null,
        ]);

        return redirect()->route('admin.posts')->with('success', 'Cập nhật bài viết thành công');
    }

    public function destroyPost($id)
    {
        $post = Post::findOrFail($id);
        $post->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Xóa bài viết thành công'
        ]);
    }

    public function uploadPostImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $image = $request->file('image');
        $filename = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
        $path = $image->storeAs('posts', $filename, 'public');

        return response()->json([
            'url' => asset('storage/' . $path)
        ]);
    }

    // Reports
    public function reports(Request $request)
    {
        $tab = $request->input('tab', 'pending');

        $query = \App\Models\MangaReport::with(['manga', 'user']);

        if ($tab === 'resolved') {
            $query->whereIn('status', ['resolved', 'ignored']);
        } else {
            $query->where('status', 'pending');
        }

        $reports = $query->orderBy('created_at', 'desc')->paginate(20);
        $reports->appends(['tab' => $tab]);

        return view('admin.reports', compact('reports', 'tab'));
    }

    public function deleteReport($id)
    {
        $report = \App\Models\MangaReport::findOrFail($id);
        $report->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Xóa báo lỗi thành công'
        ]);
    }

    public function updateReportStatus(Request $request, $id)
    {
        $report = \App\Models\MangaReport::findOrFail($id);
        $status = $request->input('status');

        if (!in_array($status, ['pending', 'resolved', 'ignored'])) {
            return response()->json(['status' => 'error', 'message' => 'Trạng thái không hợp lệ'], 400);
        }

        $report->status = $status;
        $report->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Cập nhật trạng thái thành công'
        ]);
    }

    public function crawlStart(Request $request)
    {
        $listType = $request->input('list_type', 'truyen-moi');
        $pagesInput = $request->input('pages', '');
        $allPages = $request->has('all_pages');

        if ($allPages) {
            $pages = ['all'];
        } else {
            if (strpos($pagesInput, '-') !== false) {
                list($start, $end) = explode('-', $pagesInput);
                $pages = range((int) $start, (int) $end);
            } elseif (strpos($pagesInput, ',') !== false) {
                $pages = array_map('intval', explode(',', $pagesInput));
            } else {
                $pages = [(int) $pagesInput];
            }
            $pages = array_filter($pages, function ($p) {
                return $p > 0;
            });
            sort($pages);
        }

        if (empty($pages)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Vui lòng chọn trang hoặc chọn Crawl tất cả'
            ], 400);
        }

        $jobId = uniqid('crawl_');
        $jobKey = "crawl_job_{$jobId}";

        $jobData = [
            'id' => $jobId,
            'status' => 'running',
            'list_type' => $listType,
            'pages' => $pages,
            'all_pages' => $allPages,
            'current_page' => 0,
            'total_pages' => count($pages),
            'processed_items' => [],
            'current_item' => 0,
            'total_items' => 0,
            'logs' => [],
            'logs_sent_count' => 0,
            'created_at' => now(),
        ];

        session()->put($jobKey, $jobData);

        return response()->json([
            'status' => 'success',
            'job_id' => $jobId
        ]);
    }

    public function crawlProgress(Request $request, $jobId)
    {
        $jobKey = "crawl_job_{$jobId}";
        $jobData = session()->get($jobKey);

        if (!$jobData) {
            return response()->json([
                'status' => 'error',
                'message' => 'Crawl job không tồn tại hoặc đã hết hạn'
            ], 404);
        }

        if ($jobData['status'] === 'running' || $jobData['status'] === 'pending') {
            $this->processCrawlJob($jobKey, $jobData);
        }

        $logs = $jobData['logs'] ?? [];
        $logsSentCount = $jobData['logs_sent_count'] ?? 0;
        $newLogs = array_slice($logs, $logsSentCount);

        if (count($newLogs) > 0) {
            $jobData['logs_sent_count'] = count($logs);
            session()->put($jobKey, $jobData);
        }

        return response()->json([
            'status' => $jobData['status'],
            'current_page' => $jobData['current_page'] ?? 0,
            'total_pages' => $jobData['total_pages'] ?? 0,
            'current_item' => $jobData['current_item'] ?? 0,
            'total_items' => $jobData['total_items'] ?? 0,
            'logs' => $newLogs,
            'logs_count' => count($logs),
            'error' => $jobData['error'] ?? null
        ]);
    }

    private function processCrawlJob($jobKey, &$jobData)
    {
        $otruyenService = new OTruyenService();
        $listType = $jobData['list_type'];
        $pages = $jobData['pages'];
        $allPages = $jobData['all_pages'] ?? false;
        $currentPage = $jobData['current_page'] ?? 0;
        $currentMangaIndexInPage = $jobData['current_manga_index_in_page'] ?? 0;
        $processedItems = $jobData['processed_items'] ?? [];
        $logsSentCount = $jobData['logs_sent_count'] ?? 0;
        $processedThisPoll = 0;
        $maxPerPoll = 24;

        try {
            $totalItems = $jobData['total_items'] ?? 0;

            if ($allPages && $pages[0] === 'all') {
                $pageNum = $jobData['current_page'] > 0 ? $jobData['current_page'] : 1;
                $consecutiveEmptyPages = $jobData['consecutive_empty_pages'] ?? 0;
                $maxConsecutiveEmpty = 3;

                while ($consecutiveEmptyPages < $maxConsecutiveEmpty) {
                    if ($currentMangaIndexInPage > 0 && $currentPage == $pageNum) {
                        break;
                    }

                    $currentPage = $pageNum;
                    $currentMangaIndexInPage = 0;

                    if ($processedThisPoll >= $maxPerPoll) {
                        $jobData['current_page'] = $pageNum;
                        $jobData['consecutive_empty_pages'] = $consecutiveEmptyPages;
                        $jobData['total_pages'] = $pageNum;
                        $jobData['current_manga_index_in_page'] = 0;
                        $jobData['logs_sent_count'] = $logsSentCount;
                        $jobData['processed_items'] = $processedItems;
                        $jobData['current_item'] = count($processedItems);
                        $jobData['total_items'] = $totalItems;
                        session()->put($jobKey, $jobData);
                        return;
                    }

                    Cache::forget("otruyen:list:{$listType}:page:{$pageNum}:limit:24:shuffle:0");
                    $pageData = $otruyenService->getMangaByList($listType, $pageNum, 24, false);

                    if (!$pageData || empty($pageData['mangas'])) {
                        $consecutiveEmptyPages++;
                        $newLog = [
                            'message' => $pageData ? "Không có truyện nào trong trang {$pageNum}" : "Không thể lấy dữ liệu trang {$pageNum}",
                            'type' => $pageData ? 'warning' : 'error'
                        ];
                        $jobData['logs'][] = $newLog;
                        $pageNum++;
                        $jobData['current_page'] = $pageNum;
                        $jobData['consecutive_empty_pages'] = $consecutiveEmptyPages;
                        $jobData['total_pages'] = $pageNum;
                        $jobData['current_manga_index_in_page'] = 0;
                        session()->put($jobKey, $jobData);
                        continue;
                    }

                    $consecutiveEmptyPages = 0;
                    $jobData['consecutive_empty_pages'] = 0;

                    $mangas = $pageData['mangas'];
                    $totalItems += count($mangas);

                    if ($currentPage != $pageNum || $currentMangaIndexInPage == 0) {
                        $jobData['logs'][] = [
                            'message' => "Đang crawl trang {$pageNum}... (" . count($mangas) . " truyện)",
                            'type' => 'info'
                        ];
                    }

                    $skippedCount = 0;
                    $updatedCount = 0;
                    $createdCount = 0;

                    $mangaIndex = $currentMangaIndexInPage;
                    $totalMangasInPage = count($mangas);

                    foreach ($mangas as $index => $manga) {
                        if ($index < $currentMangaIndexInPage)
                            continue;

                        $slug = $manga['slug'] ?? null;
                        if (!$slug) {
                            $skippedCount++;
                            $mangaIndex++;
                            continue;
                        }

                        if (in_array($slug, $processedItems)) {
                            $skippedCount++;
                            $mangaIndex++;
                            continue;
                        }

                        if ($processedThisPoll >= $maxPerPoll) {
                            $jobData['current_page'] = $pageNum;
                            $jobData['current_manga_index_in_page'] = $mangaIndex;
                            session()->put($jobKey, $jobData);
                            return;
                        }

                        try {
                            $mangaMetadataBefore = MangaMetadata::where('slug', $slug)->first();
                            $wasExisting = $mangaMetadataBefore !== null;

                            $mangaDetail = $otruyenService->getMangaDetail($slug, true);

                            if ($mangaDetail) {
                                usleep(100000);
                                $mangaMetadata = MangaMetadata::where('slug', $slug)->first();

                                if ($mangaMetadata) {
                                    if (!$wasExisting) {
                                        $createdCount++;
                                        $jobData['logs'][] = [
                                            'message' => "✓ Đã tạo mới: {$mangaDetail['name']}",
                                            'type' => 'success'
                                        ];
                                    } else {
                                        $updatedCount++;
                                        $jobData['logs'][] = [
                                            'message' => "↻ Đã cập nhật: {$mangaDetail['name']}",
                                            'type' => 'info'
                                        ];
                                    }
                                } else {
                                    $otruyenService->saveOrUpdateManga($mangaDetail, ['slug' => $slug]);
                                    $mangaMetadata = MangaMetadata::where('slug', $slug)->first();
                                    if ($mangaMetadata) {
                                        $createdCount++;
                                        $jobData['logs'][] = [
                                            'message' => "✓ Đã tạo mới (force save): {$mangaDetail['name']}",
                                            'type' => 'success'
                                        ];
                                    } else {
                                        $skippedCount++;
                                        $jobData['logs'][] = [
                                            'message' => "⚠ Đã lấy dữ liệu nhưng không lưu được: " . ($mangaDetail['name'] ?? $slug),
                                            'type' => 'warning'
                                        ];
                                    }
                                }
                            } else {
                                $skippedCount++;
                                $jobData['logs'][] = [
                                    'message' => "✗ Bỏ qua (không tìm thấy): " . ($manga['name'] ?? $slug),
                                    'type' => 'warning'
                                ];
                            }

                            $processedItems[] = $slug;
                            $processedThisPoll++;
                        } catch (\Exception $e) {
                            $skippedCount++;
                            $processedThisPoll++;
                            $jobData['logs'][] = [
                                'message' => "✗ Lỗi crawl: " . ($manga['name'] ?? $slug) . " - " . $e->getMessage(),
                                'type' => 'error'
                            ];
                        }

                        $mangaIndex++;
                    }

                    if ($mangaIndex >= $totalMangasInPage) {
                        $jobData['logs'][] = [
                            'message' => "Hoàn thành trang {$pageNum}: Tạo mới {$createdCount}, Cập nhật {$updatedCount}, Bỏ qua {$skippedCount}",
                            'type' => 'info'
                        ];
                        $pageNum++;
                        $currentMangaIndexInPage = 0;
                        $jobData['current_page'] = $pageNum;
                    } else {
                        $jobData['current_page'] = $pageNum;
                        $jobData['current_manga_index_in_page'] = $mangaIndex;
                    }

                    $jobData['processed_items'] = $processedItems;
                    $jobData['current_item'] = count($processedItems);
                    $jobData['total_items'] = $totalItems;
                    session()->put($jobKey, $jobData);

                    if ($processedThisPoll >= $maxPerPoll) {
                        return;
                    }
                }

                if ($consecutiveEmptyPages >= $maxConsecutiveEmpty) {
                    $jobData['status'] = 'completed';
                    $jobData['logs'][] = [
                        'message' => "✓ Hoàn tất crawl! Đã gặp {$maxConsecutiveEmpty} trang rỗng liên tiếp. Tổng cộng đã xử lý " . count($processedItems) . " truyện",
                        'type' => 'success'
                    ];
                }
            } else {
                foreach ($pages as $pageNum) {
                    if ($currentPage > $pageNum)
                        continue;
                    if ($currentPage == $pageNum && $currentMangaIndexInPage >= 24)
                        continue;

                    Cache::forget("otruyen:list:{$listType}:page:{$pageNum}:limit:24:shuffle:0");
                    $pageData = $otruyenService->getMangaByList($listType, $pageNum, 24, false);

                    if (!$pageData || empty($pageData['mangas'])) {
                        $jobData['logs'][] = [
                            'message' => "Không thể lấy dữ liệu trang {$pageNum}",
                            'type' => 'error'
                        ];
                        $currentPage = $pageNum + 1;
                        $currentMangaIndexInPage = 0;
                        continue;
                    }

                    $mangas = $pageData['mangas'];
                    $totalItems += count($mangas);

                    if ($currentPage != $pageNum || $currentMangaIndexInPage == 0) {
                        $jobData['logs'][] = [
                            'message' => "Đang crawl trang {$pageNum}... (" . count($mangas) . " truyện)",
                            'type' => 'info'
                        ];
                    }

                    $skippedCount = 0;
                    $updatedCount = 0;
                    $createdCount = 0;
                    $mangaIndex = $currentMangaIndexInPage;

                    foreach ($mangas as $index => $manga) {
                        if ($index < $currentMangaIndexInPage)
                            continue;

                        $slug = $manga['slug'] ?? null;
                        if (!$slug || in_array($slug, $processedItems)) {
                            if ($slug)
                                $skippedCount++;
                            $mangaIndex++;
                            continue;
                        }

                        if ($processedThisPoll >= $maxPerPoll) {
                            $jobData['current_page'] = $pageNum;
                            $jobData['current_manga_index_in_page'] = $mangaIndex;
                            session()->put($jobKey, $jobData);
                            return;
                        }

                        try {
                            $mangaMetadataBefore = MangaMetadata::where('slug', $slug)->first();
                            $wasExisting = $mangaMetadataBefore !== null;
                            $mangaDetail = $otruyenService->getMangaDetail($slug, true);

                            if ($mangaDetail) {
                                usleep(100000);
                                $mangaMetadata = MangaMetadata::where('slug', $slug)->first();
                                if ($mangaMetadata) {
                                    if (!$wasExisting) {
                                        $createdCount++;
                                        $jobData['logs'][] = [
                                            'message' => "✓ Đã tạo mới: {$mangaDetail['name']}",
                                            'type' => 'success'
                                        ];
                                    } else {
                                        $updatedCount++;
                                        $jobData['logs'][] = [
                                            'message' => "↻ Đã cập nhật: {$mangaDetail['name']}",
                                            'type' => 'info'
                                        ];
                                    }
                                } else {
                                    $otruyenService->saveOrUpdateManga($mangaDetail, ['slug' => $slug]);
                                    $createdCount++;
                                }
                            }
                            $processedItems[] = $slug;
                            $processedThisPoll++;
                        } catch (\Exception $e) {
                            $skippedCount++;
                            $processedThisPoll++;
                        }
                        $mangaIndex++;
                    }

                    if ($mangaIndex >= count($mangas)) {
                        $currentPage = $pageNum + 1;
                        $currentMangaIndexInPage = 0;
                    } else {
                        $currentPage = $pageNum;
                        $currentMangaIndexInPage = $mangaIndex;
                    }

                    $jobData['current_page'] = $currentPage;
                    $jobData['current_manga_index_in_page'] = $currentMangaIndexInPage;
                    $jobData['processed_items'] = $processedItems;
                    session()->put($jobKey, $jobData);

                    if ($processedThisPoll >= $maxPerPoll)
                        return;
                }

                $maxPage = count($pages) > 0 ? max($pages) : 0;
                if ($currentPage > $maxPage) {
                    $jobData['status'] = 'completed';
                    $jobData['logs'][] = [
                        'message' => "✓ Hoàn tất crawl! Tổng cộng đã xử lý " . count($processedItems) . " truyện",
                        'type' => 'success'
                    ];
                }
            }

            session()->put($jobKey, $jobData);
        } catch (\Exception $e) {
            $jobData['status'] = 'failed';
            $jobData['error'] = $e->getMessage();
            $jobData['logs'][] = [
                'message' => "✗ Lỗi: " . $e->getMessage(),
                'type' => 'error'
            ];
            session()->put($jobKey, $jobData);
        }
    }
    // Categories
    public function categories(Request $request)
    {
        $categories = \App\Models\Category::orderBy('sort_order', 'asc')
            ->orderBy('name', 'asc')
            ->get();

        return view('admin.categories', compact('categories'));
    }

    public function updateCategory(Request $request, $id)
    {
        $category = \App\Models\Category::findOrFail($id);

        $category->update([
            'is_active' => $request->has('is_active'),
            'sort_order' => $request->input('sort_order', 0),
            'name' => $request->input('name'),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Cập nhật thể loại thành công'
        ]);
    }

    // Analytics
    public function analytics(Request $request)
    {
        $days = 30;
        $dateLimit = now()->subDays($days)->toDateString();

        $dailyStats = \App\Models\MangaDailyView::where('view_date', '>=', $dateLimit)
            ->selectRaw('view_date, SUM(views_count) as total_views')
            ->groupBy('view_date')
            ->orderBy('view_date', 'asc')
            ->get();

        $topMangas = \App\Models\MangaDailyView::with('manga')
            ->where('view_date', '>=', $dateLimit)
            ->selectRaw('manga_id, SUM(views_count) as total_views')
            ->groupBy('manga_id')
            ->orderBy('total_views', 'desc')
            ->limit(10)
            ->get();

        return view('admin.analytics', compact('dailyStats', 'topMangas', 'days'));
    }

    // Ads & SEO
    public function adsSeo()
    {
        $settings = \App\Models\Setting::pluck('value', 'key')->toArray();
        return view('admin.ads_seo', compact('settings'));
    }

    public function saveAdsSeo(Request $request)
    {
        $data = $request->except('_token');

        foreach ($data as $key => $value) {
            \App\Models\Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Lưu cài đặt thành công'
        ]);
    }
}

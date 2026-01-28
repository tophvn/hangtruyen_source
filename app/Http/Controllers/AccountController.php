<?php

namespace App\Http\Controllers;

use App\Services\OTruyenService;
use App\Models\MangaMetadata;
use App\Models\MangaReadingHistory;
use App\Models\MangaFollow;
use App\Models\Category;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    protected $otruyenService;

    public function __construct(OTruyenService $otruyenService)
    {
        $this->middleware('auth');
        $this->otruyenService = $otruyenService;
    }

    public function index(Request $request)
    {
        $user = auth()->user();

        $allCategories = Category::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $allTags = Category::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $readingPage = max(1, (int) $request->get('reading_page', 1));
        $followingPage = max(1, (int) $request->get('following_page', 1));
        $perPage = 9;

        // Get reading history
        $readingHistory = $this->getReadingHistory($user, $readingPage, $perPage);
        $totalReadingItems = $readingHistory['total'];
        $totalReadingPages = (int) ceil($totalReadingItems / $perPage);

        // Get following mangas
        $followingMangas = $this->getFollowingMangas($user, $followingPage, $perPage);
        $totalFollowingItems = $followingMangas['total'];
        $totalFollowingPages = (int) ceil($totalFollowingItems / $perPage);

        return view('account.index', [
            'user' => $user,
            'allCategories' => $allCategories,
            'allTags' => $allTags,
            'readingHistory' => $readingHistory['data'],
            'readingPage' => $readingPage,
            'totalReadingPages' => $totalReadingPages,
            'followingMangas' => $followingMangas['data'],
            'followingPage' => $followingPage,
            'totalFollowingPages' => $totalFollowingPages,
        ]);
    }

    public function update(Request $request)
    {
        $user = auth()->user();

        $name = $request->input('name');

        if ($name) {
            $user->name = $name;
        }
        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Cập nhật thông tin thành công',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'avatar' => $user->avatar,
                ]
            ]
        ]);
    }

    public function uploadAvatar(Request $request)
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Chưa đăng nhập'
            ], 401);
        }

        if (!$request->hasFile('avatar')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Không có file được upload'
            ], 400);
        }

        $file = $request->file('avatar');

        if (!$file->isValid()) {
            return response()->json([
                'status' => 'error',
                'message' => 'File không hợp lệ'
            ], 400);
        }

        $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/jpg'];
        if (!in_array($file->getMimeType(), $allowedMimes)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Chỉ chấp nhận file ảnh (jpg, png, gif)'
            ], 400);
        }

        if ($file->getSize() > 5 * 1024 * 1024) {
            return response()->json([
                'status' => 'error',
                'message' => 'File ảnh không được vượt quá 5MB'
            ], 400);
        }

        // Delete old avatar
        $oldAvatar = $user->avatar;
        if ($oldAvatar && !filter_var($oldAvatar, FILTER_VALIDATE_URL)) {
            $oldPath = public_path('images/avatars/users/' . basename($oldAvatar));
            if (file_exists($oldPath) && is_file($oldPath)) {
                @unlink($oldPath);
            }
        }

        $uploadDir = public_path('images/avatars/users');
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $extension = $file->getClientOriginalExtension() ?: $file->guessExtension() ?: 'jpg';
        if (!in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif'])) {
            $extension = 'jpg';
        }

        $fileName = $user->id . '.' . strtolower($extension);
        $filePath = $uploadDir . '/' . $fileName;

        $file->move($uploadDir, $fileName);

        $relativePath = 'images/avatars/users/' . $fileName;
        $url = asset($relativePath);

        $user->avatar = $relativePath;
        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Upload ảnh thành công',
            'data' => [
                'url' => $url,
                'path' => $relativePath
            ]
        ]);
    }

    public function clearReading(Request $request)
    {
        $user = auth()->user();
        $mangaId = $request->input('manga_id');

        if (!$mangaId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Thiếu thông tin manga_id'
            ], 400);
        }

        MangaReadingHistory::where('user_id', $user->id)
            ->where('manga_id', $mangaId)
            ->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Xóa lịch sử đọc thành công'
        ]);
    }

    protected function getReadingHistory($user, $page, $perPage)
    {
        $readingHistoryQuery = MangaReadingHistory::where('user_id', $user->id)
            ->with(['manga', 'chapter'])
            ->orderBy('last_read_at', 'desc')
            ->get()
            ->unique('manga_id');

        $totalReadingItems = $readingHistoryQuery->count();
        $readingHistoryPaginated = $readingHistoryQuery->slice(($page - 1) * $perPage, $perPage);

        $readingHistory = $readingHistoryPaginated->map(function ($history) {
            $manga = $history->manga;
            $chapter = $history->chapter;

            if (!$manga) {
                return null;
            }

            $mangaDetail = $this->otruyenService->getMangaDetail($manga->slug);
            $totalChapters = count($mangaDetail['chapters'] ?? []);

            $currentChapterNumber = 0;
            if ($chapter && $chapter->chapter_slug) {
                $chapterSlug = $chapter->chapter_slug;
                $chapterNumberStr = preg_replace('/^chapter-/', '', $chapterSlug);

                if (preg_match('/^(\d+)/', $chapterNumberStr, $matches)) {
                    $currentChapterNumber = (int) $matches[1];
                } elseif (is_numeric($chapterNumberStr)) {
                    $currentChapterNumber = (int) $chapterNumberStr;
                }
            }

            $maxChapterNumber = 0;
            if (isset($mangaDetail['chapters']) && is_array($mangaDetail['chapters']) && count($mangaDetail['chapters']) > 0) {
                $firstChapter = $mangaDetail['chapters'][0];
                $firstChapterSlug = $firstChapter['slug'] ?? '';
                $firstChapterNumberStr = preg_replace('/^chapter-/', '', $firstChapterSlug);
                if (preg_match('/^(\d+)/', $firstChapterNumberStr, $matches)) {
                    $maxChapterNumber = (int) $matches[1];
                } elseif (is_numeric($firstChapterNumberStr)) {
                    $maxChapterNumber = (int) $firstChapterNumberStr;
                }
            }

            if ($maxChapterNumber === 0) {
                $maxChapterNumber = $totalChapters;
            }

            $progressPercent = $maxChapterNumber > 0 && $currentChapterNumber > 0
                ? ($currentChapterNumber / $maxChapterNumber) * 100
                : 0;

            return [
                'id' => $manga->id,
                'slug' => $manga->slug,
                'title' => $manga->title ?? 'Đang cập nhật',
                'cover_url' => $manga->cover_url ?? asset('images/pre-load1.png'),
                'rating' => $manga->rating ? (float) $manga->rating : 0,
                'views_count' => $manga->views_count ?? 0,
                'last_chapter' => $chapter ? [
                    'name' => formatChapterNameForDisplay($chapter->chapter_name),
                    'slug' => $chapter->chapter_slug,
                    'updated_at' => $chapter->updated_at ? formatVietnameseTime($chapter->updated_at) : null,
                ] : null,
                'last_read_at' => $history->last_read_at ? formatVietnameseTime($history->last_read_at) : null,
                'progress' => [
                    'current' => $currentChapterNumber,
                    'total' => $maxChapterNumber > 0 ? $maxChapterNumber : $totalChapters,
                    'percent' => $progressPercent,
                ],
            ];
        })
            ->filter(function ($manga) {
                return $manga !== null;
            });

        return [
            'data' => $readingHistory,
            'total' => $totalReadingItems
        ];
    }

    protected function getFollowingMangas($user, $page, $perPage)
    {
        $followingQuery = MangaFollow::where('user_id', $user->id)
            ->with('manga')
            ->orderBy('created_at', 'desc')
            ->get();

        $totalFollowingItems = $followingQuery->count();
        $followingPaginated = $followingQuery->slice(($page - 1) * $perPage, $perPage);

        $followingMangas = $followingPaginated->map(function ($follow) {
            $manga = $follow->manga;

            if (!$manga) {
                return null;
            }

            $lastChapter = $manga->chapters()
                ->orderBy('updated_at', 'desc')
                ->orderBy('chapter_name', 'desc')
                ->first();

            $lastChapterData = null;
            if ($lastChapter) {
                $lastChapterData = [
                    'name' => $lastChapter->chapter_name,
                    'slug' => $lastChapter->chapter_slug,
                    'updated_at' => $lastChapter->updated_at ? formatVietnameseTime($lastChapter->updated_at) : null,
                ];
            } else {
                $mangaDetail = $this->otruyenService->getMangaDetail($manga->slug);
                if ($mangaDetail && isset($mangaDetail['chapters']) && is_array($mangaDetail['chapters']) && count($mangaDetail['chapters']) > 0) {
                    $lastChapterFromApi = $mangaDetail['chapters'][0];
                    $lastChapterData = [
                        'name' => 'Chapter ' . $lastChapterFromApi['name'],
                        'slug' => $lastChapterFromApi['slug'],
                        'updated_at' => isset($lastChapterFromApi['updated_at']) ? formatVietnameseTime($lastChapterFromApi['updated_at']) : null,
                    ];
                }
            }

            return [
                'id' => $manga->id,
                'slug' => $manga->slug,
                'title' => $manga->title ?? 'Đang cập nhật',
                'cover_url' => $manga->cover_url ?? asset('images/pre-load1.png'),
                'rating' => $manga->rating ? (float) $manga->rating : 0,
                'last_chapter' => $lastChapterData,
            ];
        })
            ->filter(function ($manga) {
                return $manga !== null;
            });

        return [
            'data' => $followingMangas,
            'total' => $totalFollowingItems
        ];
    }
}

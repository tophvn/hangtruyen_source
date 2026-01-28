<?php

namespace App\Http\Controllers;

use App\Services\OTruyenService;
use App\Models\MangaMetadata;
use App\Models\MangaChapter;
use App\Models\MangaComment;
use App\Models\MangaCommentLike;
use App\Models\MangaDailyView;
use App\Models\MangaReadingHistory;
use App\Models\MangaRating;
use App\Models\MangaFollow;
use App\Models\MangaReport;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use DB;

class MangaController extends Controller
{
    protected $otruyenService;

    public function __construct(OTruyenService $otruyenService)
    {
        $this->otruyenService = $otruyenService;
    }

    public function detail($slug)
    {
        $mangaDetail = $this->otruyenService->getMangaDetail($slug);

        if (!$mangaDetail) {
            abort(404, 'Truyện không tồn tại');
        }

        $responseSlug = $mangaDetail['slug'] ?? '';
        if ($responseSlug !== $slug) {
            Cache::forget("otruyen:manga_detail:{$slug}");
            $mangaDetail = $this->otruyenService->getMangaDetail($slug);

            if (!$mangaDetail || ($mangaDetail['slug'] ?? '') !== $slug) {
                abort(404, 'Truyện không tồn tại hoặc dữ liệu không hợp lệ');
            }
        }

        $mangaMetadata = MangaMetadata::where('slug', $slug)
            ->orderBy('id', 'desc')
            ->first();

        if (!$mangaMetadata) {
            try {
                $mangaMetadata = MangaMetadata::create([
                    'slug' => $slug,
                    'source_type' => 'otruyen',
                    'source_identifier' => $mangaDetail['id'] ?? $slug,
                    'title' => $mangaDetail['name'] ?? 'Đang cập nhật',
                    'description' => strip_tags($mangaDetail['description'] ?? ''),
                    'cover_url' => $mangaDetail['cover_url'] ?? '',
                    'author' => is_array($mangaDetail['author'] ?? []) ? implode(', ', $mangaDetail['author']) : ($mangaDetail['author'] ?? ''),
                    'status' => $mangaDetail['status'] ?? 'ongoing',
                    'tags' => array_map(function ($tag) {
                        return $tag['name'] ?? '';
                    }, $mangaDetail['tags'] ?? []),
                    'origin_name' => $mangaDetail['origin_name'] ?? [],
                    'is_active' => true,
                    'last_synced_at' => now(),
                ]);
            } catch (\Exception $e) {
                $mangaMetadata = MangaMetadata::where('slug', $slug)
                    ->orderBy('id', 'desc')
                    ->first();
                if (!$mangaMetadata) {
                    abort(404, 'Truyện không tồn tại trong hệ thống');
                }
            }
        }

        if (!$mangaMetadata) {
            abort(404, 'Truyện không tồn tại trong hệ thống');
        }

        $mangaMetadata->refresh();

        // Remove duplicates
        $duplicates = MangaMetadata::where('slug', $slug)
            ->where('id', '!=', $mangaMetadata->id)
            ->get();
        if ($duplicates->count() > 0) {
            foreach ($duplicates as $duplicate) {
                $duplicate->delete();
            }
        }

        // Ensure slug is correct
        if ($mangaMetadata->slug !== $slug) {
            $mangaMetadata->slug = $slug;
            $mangaMetadata->save();
            $mangaMetadata->refresh();
        }

        // Update metadata
        $mangaMetadata->title = $mangaDetail['name'] ?? $mangaMetadata->title;
        if (empty($mangaMetadata->cover_url) && !empty($mangaDetail['cover_url'])) {
            $mangaMetadata->cover_url = $mangaDetail['cover_url'];
        }
        if (empty($mangaMetadata->description) && !empty($mangaDetail['description'])) {
            $mangaMetadata->description = $mangaDetail['description'];
        }
        $mangaMetadata->save();

        $totalViews = (int) ($mangaMetadata->views_count ?? 0);
        $chapterViews = MangaChapter::where('manga_id', $mangaMetadata->id)
            ->pluck('views_count', 'chapter_slug')
            ->toArray();

        $avgRating = $mangaMetadata->rating ? (float) $mangaMetadata->rating : 0;
        $userRating = null;
        $isFollowing = false;
        $followsCount = $mangaMetadata->getFollowsCount();

        if (auth()->check()) {
            $userRating = $mangaMetadata->getUserRating(auth()->id());
            $isFollowing = $mangaMetadata->isFollowedBy(auth()->id());
        }

        $relatedMangas = $this->getRelatedMangas($mangaMetadata);

        $comments = MangaComment::where('manga_id', $mangaMetadata->id)
            ->whereNull('parent_id')
            ->orderBy('created_at', 'desc')
            ->with(['user', 'chapter', 'replies.user', 'replies.chapter'])
            ->take(20)
            ->get();

        $commentIds = $comments->pluck('id')->toArray();
        $replyIds = [];
        foreach ($comments as $comment) {
            $replyIds = array_merge($replyIds, $comment->replies->pluck('id')->toArray());
        }
        $allCommentIds = array_merge($commentIds, $replyIds);

        $likedCommentIds = [];
        if (auth()->check() && !empty($allCommentIds)) {
            $likedCommentIds = MangaCommentLike::whereIn('comment_id', $allCommentIds)
                ->where('user_id', auth()->id())
                ->pluck('comment_id')
                ->toArray();
        }

        $commentsCount = MangaComment::where('manga_id', $mangaMetadata->id)
            ->whereNull('parent_id')
            ->count();

        // Format tags
        $tagsFromDb = $mangaMetadata->tags;
        if (is_string($tagsFromDb)) {
            $tagsFromDb = json_decode($tagsFromDb, true) ?? [];
        }
        if (!is_array($tagsFromDb)) {
            $tagsFromDb = [];
        }

        $typeKeywords = ['Manga', 'Manhua', 'Manhwa', 'manga', 'manhua', 'manhwa'];
        $tagsFormatted = [];
        if (is_array($tagsFromDb) && count($tagsFromDb) > 0) {
            foreach ($tagsFromDb as $tag) {
                $tagName = '';
                if (is_string($tag)) {
                    $tagName = $tag;
                } elseif (is_array($tag) && isset($tag['name'])) {
                    $tagName = $tag['name'];
                }

                if (!empty($tagName) && !in_array($tagName, $typeKeywords)) {
                    if (is_string($tag)) {
                        $tagSlug = Str::slug($tag);
                        $tagsFormatted[] = [
                            'name' => $tag,
                            'slug' => $tagSlug
                        ];
                    } elseif (is_array($tag)) {
                        $tagSlug = $tag['slug'] ?? Str::slug($tag['name']);
                        $tagsFormatted[] = [
                            'name' => $tag['name'],
                            'slug' => $tagSlug
                        ];
                    }
                }
            }
        }
        if (empty($tagsFormatted) && isset($mangaDetail['tags']) && is_array($mangaDetail['tags'])) {
            foreach ($mangaDetail['tags'] as $tag) {
                $tagName = is_array($tag) ? ($tag['name'] ?? '') : '';
                if (!empty($tagName) && !in_array($tagName, $typeKeywords)) {
                    $tagsFormatted[] = $tag;
                }
            }
        }

        // Format author
        $authorFromDb = $mangaMetadata->author;
        $authorFormatted = [];
        if (is_string($authorFromDb) && !empty($authorFromDb)) {
            $authorFormatted = explode(',', $authorFromDb);
            $authorFormatted = array_map('trim', $authorFormatted);
        } elseif (is_array($authorFromDb)) {
            $authorFormatted = $authorFromDb;
        }
        if (empty($authorFormatted) && isset($mangaDetail['author'])) {
            if (is_array($mangaDetail['author'])) {
                $authorFormatted = $mangaDetail['author'];
            } elseif (is_string($mangaDetail['author'])) {
                $authorFormatted = [$mangaDetail['author']];
            }
        }

        // Get manga type
        $typeFromTags = null;
        if (is_array($tagsFromDb)) {
            foreach ($tagsFromDb as $tag) {
                $tagName = is_string($tag) ? $tag : ($tag['name'] ?? '');
                $tagNameLower = strtolower($tagName);
                if (in_array($tagNameLower, ['manga', 'manhua', 'manhwa'])) {
                    $typeFromTags = [
                        'name' => ucfirst($tagNameLower),
                        'slug' => $tagNameLower
                    ];
                    break;
                }
            }
        }

        $typeFromApi = $mangaDetail['type'] ?? null;
        $mangaType = $typeFromTags ?? $typeFromApi;

        $chaptersForView = $mangaDetail['chapters'] ?? [];

        $topFollowDay = $this->getTopFollow('day');
        $topFollowWeek = $this->getTopFollow('week');
        $topFollowMonth = $this->getTopFollow('month');

        $mangaForView = [
            'id' => $mangaMetadata->id,
            'name' => $mangaMetadata->title,
            'slug' => $mangaMetadata->slug,
            'cover_url' => $mangaMetadata->cover_url ?? $mangaDetail['cover_url'] ?? asset('images/pre-load1.png'),
            'description' => $mangaMetadata->description ?? $mangaDetail['description'] ?? '',
            'author' => $authorFormatted,
            'status' => $mangaMetadata->status ?? $mangaDetail['status'] ?? 'ongoing',
            'tags' => $tagsFormatted,
            'chapters' => $chaptersForView,
            'updated_at' => $mangaMetadata->updated_at ? $mangaMetadata->updated_at->toDateTimeString() : ($mangaDetail['updated_at'] ?? null),
            'type' => $mangaType,
        ];

        return view('manga.detail', [
            'manga' => $mangaForView,
            'mangaSlug' => $slug,
            'totalViews' => $totalViews,
            'chapterViews' => $chapterViews,
            'avgRating' => $avgRating,
            'userRating' => $userRating,
            'mangaMetadata' => $mangaMetadata,
            'relatedMangas' => $relatedMangas,
            'isFollowing' => $isFollowing,
            'followsCount' => $followsCount,
            'comments' => $comments,
            'likedCommentIds' => $likedCommentIds,
            'commentsCount' => $commentsCount,
            'topFollowDay' => $topFollowDay,
            'topFollowWeek' => $topFollowWeek,
            'topFollowMonth' => $topFollowMonth,
        ]);
    }

    public function chapter($mangaSlug, $chapterSlug)
    {
        $mangaDetail = $this->otruyenService->getMangaDetail($mangaSlug);

        if (!$mangaDetail) {
            abort(404, 'Truyện không tồn tại');
        }

        $mangaMetadata = MangaMetadata::where('slug', $mangaSlug)->first();
        if (!$mangaMetadata) {
            abort(404, 'Truyện không tồn tại trong hệ thống');
        }

        $chapters = $mangaDetail['chapters'] ?? [];
        $currentChapter = null;
        $currentIndex = -1;

        foreach ($chapters as $index => $chapter) {
            if ($chapter['slug'] === $chapterSlug) {
                $currentChapter = $chapter;
                $currentIndex = $index;
                break;
            }
        }

        if (!$currentChapter) {
            $chapterNumber = preg_replace('/^chapter-/', '', $chapterSlug);
            $chapterNumber = preg_replace('/[^0-9.]/', '', $chapterNumber);

            foreach ($chapters as $index => $chapter) {
                $chapterSlugFromData = $chapter['slug'] ?? '';
                $chapterNumberFromData = preg_replace('/^chapter-/', '', $chapterSlugFromData);
                $chapterNumberFromData = preg_replace('/[^0-9.]/', '', $chapterNumberFromData);

                if ($chapterNumberFromData === $chapterNumber) {
                    $currentChapter = $chapter;
                    $currentIndex = $index;
                    break;
                }

                $chapterName = $chapter['name'] ?? '';
                $chapterNameClean = preg_replace('/^Chapter\s+/i', '', $chapterName);
                $chapterNameClean = preg_replace('/[^0-9.]/', '', $chapterNameClean);

                if ($chapterNameClean === $chapterNumber || strpos($chapterName, $chapterNumber) !== false) {
                    $currentChapter = $chapter;
                    $currentIndex = $index;
                    break;
                }
            }
        }

        if (!$currentChapter) {
            abort(404, 'Chapter không tồn tại');
        }

        $chapterRecord = MangaChapter::firstOrNew([
            'manga_id' => $mangaMetadata->id,
            'chapter_slug' => $chapterSlug,
        ]);

        if (!$chapterRecord->exists) {
            $chapterRecord->chapter_name = $currentChapter['name'];
            $chapterRecord->chapter_api_id = $currentChapter['api_data'] ?? null;
            $chapterRecord->views_count = 1;
            $chapterRecord->first_viewed_at = now();
            $chapterRecord->last_viewed_at = now();
            $chapterRecord->save();
        } else {
            $chapterRecord->increment('views_count');
            $chapterRecord->last_viewed_at = now();
            $chapterRecord->save();
        }

        $chapterRecord->refresh();

        $mangaMetadata->updateViewsCount();

        MangaDailyView::incrementTodayViews($mangaMetadata->id);

        if (auth()->check()) {
            MangaReadingHistory::updateOrCreate(
                [
                    'user_id' => auth()->id(),
                    'manga_id' => $mangaMetadata->id,
                ],
                [
                    'chapter_id' => $chapterRecord->id,
                    'chapter_slug' => $chapterSlug,
                    'last_read_at' => now(),
                ]
            );
        }

        $chapterImages = null;
        if (!empty($currentChapter['api_data'])) {
            $chapterImages = $this->otruyenService->getChapterImages($currentChapter['api_data']);
        }

        $prevChapter = null;
        $nextChapter = null;

        if ($currentIndex < count($chapters) - 1) {
            $prevChapterData = $chapters[$currentIndex + 1];
            $prevChapter = [
                'name' => 'Chapter ' . $prevChapterData['name'],
                'url' => route('manga.chapter', ['mangaSlug' => $mangaSlug, 'chapterSlug' => $prevChapterData['slug']]),
            ];
        }

        if ($currentIndex > 0) {
            $nextChapterData = $chapters[$currentIndex - 1];
            $nextChapter = [
                'name' => 'Chapter ' . $nextChapterData['name'],
                'url' => route('manga.chapter', ['mangaSlug' => $mangaSlug, 'chapterSlug' => $nextChapterData['slug']]),
            ];
        }

        $comments = MangaComment::where('manga_id', $mangaMetadata->id)
            ->where('chapter_id', $chapterRecord->id)
            ->whereNull('parent_id')
            ->orderBy('created_at', 'desc')
            ->with(['user', 'chapter', 'replies.user', 'replies.chapter'])
            ->take(20)
            ->get();

        $commentIds = $comments->pluck('id')->toArray();
        $replyIds = [];
        foreach ($comments as $comment) {
            $replyIds = array_merge($replyIds, $comment->replies->pluck('id')->toArray());
        }
        $allCommentIds = array_merge($commentIds, $replyIds);

        $likedCommentIds = [];
        if (auth()->check() && !empty($allCommentIds)) {
            $likedCommentIds = MangaCommentLike::whereIn('comment_id', $allCommentIds)
                ->where('user_id', auth()->id())
                ->pluck('comment_id')
                ->toArray();
        }

        $commentsCount = MangaComment::where('manga_id', $mangaMetadata->id)
            ->where('chapter_id', $chapterRecord->id)
            ->whereNull('parent_id')
            ->count();

        return view('manga.chapter', [
            'mangaSlug' => $mangaSlug,
            'chapterSlug' => $chapterSlug,
            'mangaTitle' => $mangaDetail['name'] ?? 'Đang cập nhật',
            'chapterName' => 'Chapter ' . ($currentChapter['name'] ?? ''),
            'chapterId' => $chapterRecord->id,
            'chapterImages' => $chapterImages,
            'prevChapter' => $prevChapter,
            'nextChapter' => $nextChapter,
            'manga' => $mangaDetail,
            'mangaMetadata' => $mangaMetadata,
            'comments' => $comments,
            'likedCommentIds' => $likedCommentIds,
            'commentsCount' => $commentsCount,
            'mangaCover' => $mangaMetadata->cover_url ?? $mangaDetail['cover_url'] ?? asset('images/logo-dark.png'),
        ]);
    }

    public function vote(Request $request, $slug)
    {
        try {
            if (!auth()->check()) {
                return response()->json(['status' => 'error', 'message' => 'Vui lòng đăng nhập'], 401);
            }

            $mangaMetadata = MangaMetadata::where('slug', $slug)->first();
            if (!$mangaMetadata) {
                $mangaDetail = $this->otruyenService->getMangaDetail($slug);

                if (!$mangaDetail) {
                    return response()->json(['status' => 'error', 'message' => 'Truyện không tồn tại'], 404);
                }

                $mangaMetadata = MangaMetadata::create([
                    'slug' => $slug,
                    'source_type' => 'otruyen',
                    'source_identifier' => $slug,
                    'title' => $mangaDetail['name'] ?? 'Đang cập nhật',
                    'description' => $mangaDetail['description'] ?? null,
                    'cover_url' => $mangaDetail['cover_url'] ?? null,
                    'author' => is_array($mangaDetail['author'] ?? [])
                        ? implode(', ', $mangaDetail['author'])
                        : ($mangaDetail['author'] ?? null),
                    'status' => null,
                    'tags' => $mangaDetail['tags'] ?? [],
                    'is_active' => true,
                ]);
            }

            $vote = (int) $request->input('vote');
            if ($vote < 1 || $vote > 5) {
                return response()->json(['status' => 'error', 'message' => 'Đánh giá không hợp lệ'], 400);
            }

            MangaRating::updateOrCreate(
                [
                    'manga_id' => $mangaMetadata->id,
                    'user_id' => auth()->id(),
                ],
                [
                    'rating' => $vote,
                ]
            );

            $mangaMetadata->updateRating();

            return response()->json([
                'status' => 'success',
                'message' => 'Cảm ơn bạn đã nhận xét truyện',
                'data' => [
                    'avgRating' => $mangaMetadata->fresh()->rating ?? 0,
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Vote error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Có lỗi xảy ra khi đánh giá'
            ], 500);
        }
    }

    public function follow($slug)
    {
        try {
            if (!auth()->check()) {
                return response()->json(['status' => 'error', 'message' => 'Vui lòng đăng nhập'], 401);
            }

            $mangaMetadata = MangaMetadata::where('slug', $slug)->first();

            if (!$mangaMetadata) {
                return response()->json(['status' => 'error', 'message' => 'Truyện không tồn tại'], 404);
            }

            $userId = auth()->id();
            $isFollowing = $mangaMetadata->isFollowedBy($userId);

            if ($isFollowing) {
                MangaFollow::where('manga_id', $mangaMetadata->id)
                    ->where('user_id', $userId)
                    ->delete();

                return response()->json([
                    'status' => 'success',
                    'message' => 'Đã bỏ theo dõi',
                    'data' => [
                        'isFollowing' => false,
                        'followsCount' => $mangaMetadata->getFollowsCount(),
                    ]
                ]);
            } else {
                MangaFollow::create([
                    'manga_id' => $mangaMetadata->id,
                    'user_id' => $userId,
                ]);

                return response()->json([
                    'status' => 'success',
                    'message' => 'Đã theo dõi',
                    'data' => [
                        'isFollowing' => true,
                        'followsCount' => $mangaMetadata->getFollowsCount(),
                    ]
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Có lỗi xảy ra khi theo dõi'
            ], 500);
        }
    }

    public function report(Request $request, $slug)
    {
        try {
            $mangaMetadata = MangaMetadata::where('slug', $slug)->first();

            if (!$mangaMetadata) {
                return response()->json(['status' => 'error', 'message' => 'Truyện không tồn tại'], 404);
            }

            $content = $request->input('content');
            $chapterSlug = $request->input('chapter_slug');

            if (empty($content) || strlen(trim($content)) < 10) {
                return response()->json(['status' => 'error', 'message' => 'Nội dung báo cáo phải có ít nhất 10 ký tự'], 400);
            }

            if (strlen($content) > 3000) {
                return response()->json(['status' => 'error', 'message' => 'Nội dung báo cáo không được vượt quá 3000 ký tự'], 400);
            }

            MangaReport::create([
                'manga_id' => $mangaMetadata->id,
                'user_id' => auth()->id(),
                'chapter_slug' => $chapterSlug,
                'content' => trim($content),
                'status' => 'pending',
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Cảm ơn bạn đã báo cáo. Chúng tôi sẽ xem xét và xử lý sớm nhất có thể.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Có lỗi xảy ra khi gửi báo cáo'
            ], 500);
        }
    }

    public function random()
    {
        $manga = MangaMetadata::where('is_active', true)
            ->whereHas('chapters', function ($query) {
                $query->whereNotNull('chapter_slug');
            })
            ->inRandomOrder()
            ->first();

        if (!$manga) {
            return redirect('/')->with('error', 'Không tìm thấy truyện nào');
        }

        return redirect(route('manga.detail', ['slug' => $manga->slug]));
    }

    protected function getRelatedMangas($mangaMetadata)
    {
        $relatedMangas = collect();
        $attempts = 0;
        $maxAttempts = 20;

        while ($relatedMangas->count() < 5 && $attempts < $maxAttempts) {
            $mangas = MangaMetadata::where('is_active', true)
                ->where('id', '!=', $mangaMetadata->id)
                ->whereNotIn('id', $relatedMangas->pluck('id')->toArray())
                ->inRandomOrder()
                ->limit(10)
                ->get();

            foreach ($mangas as $manga) {
                if ($relatedMangas->count() >= 5) {
                    break;
                }

                $lastChapter = $manga->chapters()
                    ->orderBy('updated_at', 'desc')
                    ->orderBy('chapter_name', 'desc')
                    ->first();

                $lastChapterData = null;
                if ($lastChapter) {
                    $chapterName = $lastChapter->chapter_name;
                    if (!preg_match('/^Chapter\s+/i', $chapterName)) {
                        $chapterName = 'Chapter ' . $chapterName;
                    }
                    $lastChapterData = [
                        'name' => $chapterName,
                        'slug' => $lastChapter->chapter_slug,
                        'updated_at' => $lastChapter->updated_at ? $lastChapter->updated_at->diffForHumans() : null,
                    ];
                } elseif ($manga->last_chapter_number) {
                    $relatedMangaDetail = $this->otruyenService->getMangaDetail($manga->slug);
                    if ($relatedMangaDetail && isset($relatedMangaDetail['chapters']) && is_array($relatedMangaDetail['chapters']) && count($relatedMangaDetail['chapters']) > 0) {
                        $lastChapterFromApi = end($relatedMangaDetail['chapters']);
                        $lastChapterData = [
                            'name' => 'Chapter ' . $lastChapterFromApi['name'],
                            'slug' => $lastChapterFromApi['slug'],
                            'updated_at' => isset($lastChapterFromApi['updated_at']) ? \Carbon\Carbon::parse($lastChapterFromApi['updated_at'])->diffForHumans() : null,
                        ];
                    } else {
                        $lastChapterData = [
                            'name' => 'Chapter ' . $manga->last_chapter_number,
                            'slug' => 'chapter-' . $manga->last_chapter_number,
                            'updated_at' => $manga->last_synced_at ? $manga->last_synced_at->diffForHumans() : null,
                        ];
                    }
                }

                if ($lastChapterData) {
                    $viewsCount = $manga->views_count ?? 0;
                    $viewsFormatted = $viewsCount >= 1000000
                        ? number_format($viewsCount / 1000000, 1) . 'M'
                        : ($viewsCount >= 1000
                            ? number_format($viewsCount / 1000, 1) . 'K'
                            : $viewsCount);

                    $relatedMangas->push([
                        'id' => $manga->id,
                        'slug' => $manga->slug,
                        'title' => $manga->title,
                        'cover_url' => $manga->cover_url ?? asset('images/pre-load1.png'),
                        'rating' => $manga->rating ? (float) $manga->rating : 0,
                        'views_count' => $viewsFormatted . ' lượt xem',
                        'last_chapter' => $lastChapterData,
                    ]);
                }
            }

            $attempts++;
        }

        return $relatedMangas->take(5)->values();
    }

    protected function getTopFollow($period)
    {
        $today = now()->toDateString();
        $startDate = null;
        $endDate = $today;

        if ($period === 'day') {
            $startDate = $today;
        } elseif ($period === 'week') {
            $startDate = now()->startOfWeek()->toDateString();
        } elseif ($period === 'month') {
            $startDate = now()->startOfMonth()->toDateString();
        }

        $topMangas = MangaDailyView::whereBetween('view_date', [$startDate, $endDate])
            ->select('manga_id', DB::raw('SUM(views_count) as total_views'))
            ->groupBy('manga_id')
            ->orderBy('total_views', 'desc')
            ->limit(6)
            ->get();

        if ($topMangas->count() < 6 && $period === 'day') {
            $existingMangaIds = $topMangas->pluck('manga_id')->toArray();
            $needed = 6 - $topMangas->count();

            if ($needed > 0) {
                $additionalMangas = MangaDailyView::where('view_date', '<', $today)
                    ->when(count($existingMangaIds) > 0, function ($query) use ($existingMangaIds) {
                        return $query->whereNotIn('manga_id', $existingMangaIds);
                    })
                    ->select('manga_id', DB::raw('SUM(views_count) as total_views'))
                    ->groupBy('manga_id')
                    ->orderBy('total_views', 'desc')
                    ->limit($needed)
                    ->get();

                if ($additionalMangas->count() > 0) {
                    $topMangas = $topMangas->concat($additionalMangas)
                        ->sortByDesc(function ($item) {
                            return $item->total_views;
                        })
                        ->take(6)
                        ->values();
                }
            }
        }

        $mangaIds = $topMangas->pluck('manga_id')->toArray();
        $mangaMetadata = MangaMetadata::whereIn('id', $mangaIds)
            ->get()
            ->keyBy('id');

        $result = [];
        $rank = 1;
        foreach ($topMangas as $topManga) {
            $manga = $mangaMetadata->get($topManga->manga_id);
            if (!$manga)
                continue;

            $lastChapter = $manga->chapters()
                ->orderBy('updated_at', 'desc')
                ->orderBy('chapter_name', 'desc')
                ->first();

            $lastChapterData = null;
            if ($lastChapter) {
                $lastChapterData = [
                    'name' => formatChapterNameForDisplay($lastChapter->chapter_name),
                    'slug' => $lastChapter->chapter_slug,
                    'updated_at' => $lastChapter->updated_at ? formatVietnameseTime($lastChapter->updated_at) : null,
                ];
            }

            $viewsCount = (int) $topManga->total_views;
            $formattedViews = '';
            if ($viewsCount >= 1000000) {
                $formattedViews = number_format($viewsCount / 1000000, 1) . 'M';
            } elseif ($viewsCount >= 1000) {
                $formattedViews = number_format($viewsCount / 1000, 1) . 'K';
            } else {
                $formattedViews = number_format($viewsCount);
            }

            $result[] = [
                'id' => $manga->id,
                'slug' => $manga->slug,
                'title' => $manga->title ?? 'Đang cập nhật',
                'cover_url' => $manga->cover_url ?? asset('images/pre-load1.png'),
                'rating' => $manga->rating ? (float) $manga->rating : 0,
                'views_count' => $viewsCount,
                'views_formatted' => $formattedViews,
                'last_chapter' => $lastChapterData,
                'rank' => $rank,
            ];

            $rank++;
        }

        return $result;
    }

    public function genreAll()
    {
        $allowedSlugs = ['action', 'romance', 'comedy', 'fantasy', 'drama', 'ngon-tinh'];

        $categories = Category::where('is_active', true)
            ->whereIn('slug', $allowedSlugs)
            ->orderByRaw('FIELD(slug, "' . implode('","', $allowedSlugs) . '")')
            ->get();

        $genres = [];

        foreach ($categories as $category) {
            $mangas = MangaMetadata::where('is_active', true)
                ->where('chapters_count', '>', 0)
                ->where(function ($query) use ($category) {
                    $query->whereJsonContains('tags', $category->name)
                        ->orWhereJsonContains('tags', $category->slug);
                })
                ->inRandomOrder()
                ->limit(24)
                ->get()
                ->map(function ($manga) {
                    return [
                        'slug' => $manga->slug,
                        'title' => $manga->title,
                        'posterPath' => $manga->cover_url,
                        'avgVote' => $manga->rating ? (float) $manga->rating : 0,
                        'chapters' => $manga->last_chapter_number ? [
                            [
                                'id' => null,
                                'slug' => 'chapter-' . $manga->last_chapter_number,
                                'name' => 'Chapter ' . $manga->last_chapter_number,
                                'releasedAt' => $manga->last_synced_at ? formatVietnameseTime($manga->last_synced_at) : null,
                            ],
                        ] : [],
                    ];
                })
                ->toArray();

            $genres[] = [
                'slug' => $category->slug,
                'name' => $category->name,
                'mangas' => $mangas,
            ];
        }

        return view('genre.all', [
            'genres' => $genres,
        ]);
    }

    public function genre(Request $request, $slug)
    {
        $page = (int) $request->get('page', 1);

        $data = $this->otruyenService->getMangaByGenre($slug, $page);

        if (!$data) {
            abort(404, 'Thể loại không tồn tại');
        }

        $category = Category::where('slug', $slug)->first();
        $genreName = $category ? $category->name : ($data['titlePage'] ?? ucfirst($slug));

        $genre = [
            'name' => $genreName,
            'title' => 'Truyện tranh ' . $genreName,
            'description' => 'Danh sách truyện tranh ' . $genreName,
        ];

        $pagination = $data['pagination'] ?? [];
        $totalPages = 1;
        if (isset($pagination['totalItems']) && isset($pagination['totalItemsPerPage']) && $pagination['totalItemsPerPage'] > 0) {
            $totalPages = (int) ceil($pagination['totalItems'] / $pagination['totalItemsPerPage']);
        }

        return view('genre.index', [
            'slug' => $slug,
            'genre' => $genre,
            'results' => $data['mangas'] ?? [],
            'currentPage' => $page,
            'totalPages' => $totalPages,
        ]);
    }

    public function categoryAll()
    {
        $types = [
            ['slug' => 'manga', 'name' => 'Manga'],
            ['slug' => 'manhua', 'name' => 'Manhua'],
            ['slug' => 'manhwa', 'name' => 'Manhwa'],
            ['slug' => 'viet-nam', 'name' => 'Việt Nam'],
        ];

        $genres = [];

        foreach ($types as $type) {
            $data = $this->otruyenService->getMangaByType($type['slug'], 1, 24);

            if ($data && isset($data['mangas']) && count($data['mangas']) > 0) {
                $genres[] = [
                    'slug' => $type['slug'],
                    'name' => $type['name'],
                    'mangas' => $data['mangas'],
                ];
            } else {
                $genres[] = [
                    'slug' => $type['slug'],
                    'name' => $type['name'],
                    'mangas' => [],
                ];
            }
        }

        return view('genre.all', [
            'genres' => $genres,
        ]);
    }

    public function category(Request $request, $slug)
    {
        $page = (int) $request->get('page', 1);

        $data = $this->otruyenService->getMangaByGenre($slug, $page);

        if (!$data) {
            abort(404, 'Thể loại không tồn tại');
        }

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
        ];

        $categoryName = $data['titlePage'] ?? ($categoryMap[$slug]['name'] ?? ucfirst($slug));

        $category = $categoryMap[$slug] ?? [
            'name' => $categoryName,
            'title' => 'Truyện tranh ' . $categoryName,
            'description' => 'Danh sách truyện tranh ' . $categoryName,
        ];

        $pagination = $data['pagination'] ?? [];
        $totalPages = 1;
        if (isset($pagination['totalItems']) && isset($pagination['totalItemsPerPage']) && $pagination['totalItemsPerPage'] > 0) {
            $totalPages = (int) ceil($pagination['totalItems'] / $pagination['totalItemsPerPage']);
        }

        return view('category.index', [
            'slug' => $slug,
            'category' => $category,
            'results' => $data['mangas'] ?? [],
            'currentPage' => $page,
            'totalPages' => $totalPages,
        ]);
    }

    public function completed(Request $request)
    {
        $page = (int) $request->get('page', 1);

        $data = $this->otruyenService->getMangaByList('hoan-thanh', $page, 24, false);

        if (!$data) {
            abort(404, 'Không tìm thấy dữ liệu');
        }

        $pagination = $data['pagination'] ?? [];
        $totalPages = 1;
        if (isset($pagination['totalItems']) && isset($pagination['totalItemsPerPage']) && $pagination['totalItemsPerPage'] > 0) {
            $totalPages = (int) ceil($pagination['totalItems'] / $pagination['totalItemsPerPage']);
        }

        $category = [
            'name' => 'Truyện đã hoàn thành',
            'title' => 'Truyện tranh Truyện đã hoàn thành',
            'description' => 'Danh sách truyện Truyện đã hoàn thành hot nhất được gợi ý',
        ];

        return view('completed.index', [
            'category' => $category,
            'results' => $data['mangas'] ?? [],
            'currentPage' => $page,
            'totalPages' => $totalPages,
        ]);
    }

    public function hot(Request $request)
    {
        $type = $request->get('type', 'all');

        $getHotMangas = function ($period) {
            $today = now()->toDateString();
            $startDate = null;
            $endDate = $today;

            if ($period === 'all') {
                $topMangas = MangaMetadata::where('is_active', true)
                    ->where('views_count', '>', 0)
                    ->orderBy('views_count', 'desc')
                    ->limit(12)
                    ->get();

                $result = [];
                foreach ($topMangas as $manga) {
                    $lastChapter = $manga->chapters()
                        ->orderBy('updated_at', 'desc')
                        ->orderBy('chapter_name', 'desc')
                        ->first();

                    $chapters = [];
                    if ($lastChapter) {
                        $chapters[] = [
                            'id' => $lastChapter->id,
                            'slug' => $lastChapter->chapter_slug,
                            'name' => formatChapterNameForDisplay($lastChapter->chapter_name),
                            'releasedAt' => $lastChapter->updated_at ? formatVietnameseTime($lastChapter->updated_at) : null,
                        ];
                    }

                    $result[] = [
                        'slug' => $manga->slug,
                        'title' => $manga->title ?? 'Đang cập nhật',
                        'posterPath' => $manga->cover_url ?? asset('images/pre-load1.png'),
                        'avgVote' => $manga->rating ? (float) $manga->rating : 0,
                        'countView' => (int) ($manga->views_count ?? 0),
                        'chapters' => $chapters,
                    ];
                }

                return $result;
            } else {
                if ($period === 'day') {
                    $startDate = $today;
                } elseif ($period === 'week') {
                    $startDate = now()->startOfWeek()->toDateString();
                } elseif ($period === 'month') {
                    $startDate = now()->startOfMonth()->toDateString();
                }

                $topMangas = MangaDailyView::whereBetween('view_date', [$startDate, $endDate])
                    ->select('manga_id', DB::raw('SUM(views_count) as total_views'))
                    ->groupBy('manga_id')
                    ->orderBy('total_views', 'desc')
                    ->limit(12)
                    ->get();

                $mangaIds = $topMangas->pluck('manga_id')->toArray();
                $mangaMetadata = MangaMetadata::whereIn('id', $mangaIds)
                    ->where('is_active', true)
                    ->get()
                    ->keyBy('id');

                $result = [];
                foreach ($topMangas as $topManga) {
                    $manga = $mangaMetadata->get($topManga->manga_id);
                    if (!$manga)
                        continue;

                    $lastChapter = $manga->chapters()
                        ->orderBy('updated_at', 'desc')
                        ->orderBy('chapter_name', 'desc')
                        ->first();

                    $chapters = [];
                    if ($lastChapter) {
                        $chapters[] = [
                            'id' => $lastChapter->id,
                            'slug' => $lastChapter->chapter_slug,
                            'name' => formatChapterNameForDisplay($lastChapter->chapter_name),
                            'releasedAt' => $lastChapter->updated_at ? formatVietnameseTime($lastChapter->updated_at) : null,
                        ];
                    }

                    $result[] = [
                        'slug' => $manga->slug,
                        'title' => $manga->title ?? 'Đang cập nhật',
                        'posterPath' => $manga->cover_url ?? asset('images/pre-load1.png'),
                        'avgVote' => $manga->rating ? (float) $manga->rating : 0,
                        'countView' => (int) $topManga->total_views,
                        'chapters' => $chapters,
                    ];
                }

                return $result;
            }
        };

        $results = $getHotMangas($type);

        return view('hot.index', [
            'type' => $type,
            'results' => $results,
        ]);
    }

    public function newMangas(Request $request)
    {
        $page = max(1, (int) $request->get('page', 1));

        $data = $this->otruyenService->getOngoingMangas($page);

        if (!$data) {
            abort(404, 'Không thể tải dữ liệu');
        }

        $pagination = $data['pagination'] ?? [];
        $totalPages = 1;
        if (isset($pagination['totalItems']) && isset($pagination['totalItemsPerPage']) && $pagination['totalItemsPerPage'] > 0) {
            $totalPages = (int) ceil($pagination['totalItems'] / $pagination['totalItemsPerPage']);
        }

        $titlePage = $data['titlePage'] ?? 'Truyện đang phát hành';

        return view('new.index', [
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'results' => $data['mangas'] ?? [],
            'titlePage' => $titlePage,
        ]);
    }
}

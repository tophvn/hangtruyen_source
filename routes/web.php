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

Route::post('/truyen-tranh/{slug}/vote', function ($slug) {
    try {
        if (!auth()->check()) {
            return response()->json(['status' => 'error', 'message' => 'Vui lòng đăng nhập'], 401);
        }
        
        // Lấy hoặc tạo mangaMetadata
        $mangaMetadata = \App\Models\MangaMetadata::where('slug', $slug)->first();
        if (!$mangaMetadata) {
            // Nếu chưa có, lấy thông tin từ API và tạo mới
            $otruyenService = new \App\Services\OTruyenService();
            $mangaDetail = $otruyenService->getMangaDetail($slug);
            
            if (!$mangaDetail) {
                return response()->json(['status' => 'error', 'message' => 'Truyện không tồn tại'], 404);
            }
            
            $mangaMetadata = \App\Models\MangaMetadata::create([
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
        
        $vote = (int)request()->input('vote');
        if ($vote < 1 || $vote > 5) {
            return response()->json(['status' => 'error', 'message' => 'Đánh giá không hợp lệ'], 400);
        }
        
        \App\Models\MangaRating::updateOrCreate(
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
})->middleware('web')->name('manga.vote');

Route::post('/truyen-tranh/{slug}/follow', function ($slug) {
    try {
        if (!auth()->check()) {
            return response()->json(['status' => 'error', 'message' => 'Vui lòng đăng nhập'], 401);
        }

        $mangaMetadata = \App\Models\MangaMetadata::where('slug', $slug)->first();
        
        if (!$mangaMetadata) {
            return response()->json(['status' => 'error', 'message' => 'Truyện không tồn tại'], 404);
        }

        $userId = auth()->id();
        $isFollowing = $mangaMetadata->isFollowedBy($userId);

        if ($isFollowing) {
            \App\Models\MangaFollow::where('manga_id', $mangaMetadata->id)
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
            \App\Models\MangaFollow::create([
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
})->middleware('web')->name('manga.follow');

Route::post('/truyen-tranh/{slug}/comments', function ($slug) {
    try {
        if (!auth()->check()) {
            return response()->json(['status' => 'error', 'message' => 'Vui lòng đăng nhập'], 401);
        }

        $mangaMetadata = \App\Models\MangaMetadata::where('slug', $slug)->first();
        
        if (!$mangaMetadata) {
            return response()->json(['status' => 'error', 'message' => 'Truyện không tồn tại'], 404);
        }

        $request = request();
        $content = $request->input('content');
        $chapterId = $request->input('chapter_id');
        $parentId = $request->input('parent_id');

        if (empty($content) || strlen(trim($content)) === 0) {
            return response()->json(['status' => 'error', 'message' => 'Nội dung bình luận không được để trống'], 400);
        }

        if (strlen($content) > 3000) {
            return response()->json(['status' => 'error', 'message' => 'Nội dung bình luận không được vượt quá 3000 ký tự'], 400);
        }

        $chapter = null;
        if ($chapterId) {
            $chapter = \App\Models\MangaChapter::where('id', $chapterId)
                ->where('manga_id', $mangaMetadata->id)
                ->first();
            if (!$chapter) {
                return response()->json(['status' => 'error', 'message' => 'Chapter không tồn tại'], 404);
            }
        }

        if ($parentId) {
            $parentComment = \App\Models\MangaComment::where('id', $parentId)
                ->where('manga_id', $mangaMetadata->id)
                ->first();
            if (!$parentComment) {
                return response()->json(['status' => 'error', 'message' => 'Bình luận cha không tồn tại'], 404);
            }
        }

        $comment = \App\Models\MangaComment::create([
            'manga_id' => $mangaMetadata->id,
            'chapter_id' => $chapter ? $chapter->id : null,
            'user_id' => auth()->id(),
            'parent_id' => $parentId,
            'content' => trim($content),
        ]);

        $comment->load(['user', 'chapter']);

        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => $comment->id,
                'content' => $comment->content,
                'user' => [
                    'id' => $comment->user->id,
                    'name' => $comment->user->name,
                    'avatar' => $comment->user->avatar ?? null,
                ],
                'chapter' => $comment->chapter ? [
                    'id' => $comment->chapter->id,
                    'slug' => $comment->chapter->chapter_slug,
                    'name' => $comment->chapter->chapter_name,
                ] : null,
                'created_at' => $comment->created_at->diffForHumans(),
                'likes_count' => 0,
            ]
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Có lỗi xảy ra khi đăng bình luận'
        ], 500);
    }
})->middleware('web')->name('manga.comments.store');

Route::get('/truyen-tranh/{slug}/comments', function ($slug) {
    try {
        $mangaMetadata = \App\Models\MangaMetadata::where('slug', $slug)->first();
        
        if (!$mangaMetadata) {
            return response()->json(['status' => 'error', 'message' => 'Truyện không tồn tại'], 404);
        }

        $request = request();
        $chapterId = $request->input('chapter_id');
        $page = max(1, (int)$request->input('page', 1));
        $perPage = 20;
        $orderBy = $request->input('order', 'latest'); // latest, oldest, most_liked

        $query = \App\Models\MangaComment::where('manga_id', $mangaMetadata->id)
            ->whereNull('parent_id'); // Chỉ lấy top-level comments

        if ($chapterId) {
            $query->where('chapter_id', $chapterId);
        } else {

        }

        // Sorting
        if ($orderBy === 'oldest') {
            $query->orderBy('created_at', 'asc');
        } elseif ($orderBy === 'most_liked') {
            $query->orderBy('likes_count', 'desc')->orderBy('created_at', 'desc');
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $comments = $query->with(['user', 'chapter', 'replies.user', 'replies.chapter'])
            ->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get();

        $total = $query->count();
        $totalPages = ceil($total / $perPage);

        $userId = auth()->id();
        $likedCommentIds = [];
        if ($userId) {
            $commentIds = $comments->pluck('id')->toArray();
            // Get all reply IDs
            $replyIds = [];
            foreach ($comments as $comment) {
                $replyIds = array_merge($replyIds, $comment->replies->pluck('id')->toArray());
            }
            $allCommentIds = array_merge($commentIds, $replyIds);
            
            if (!empty($allCommentIds)) {
                $likedCommentIds = \App\Models\MangaCommentLike::whereIn('comment_id', $allCommentIds)
                    ->where('user_id', $userId)
                    ->pluck('comment_id')
                    ->toArray();
            }
        }

        $html = '';
        foreach ($comments as $comment) {
            $html .= view('manga.components.comment-item', [
                'comment' => $comment,
                'isLiked' => in_array($comment->id, $likedCommentIds),
                'mangaSlug' => $slug,
                'likedCommentIds' => $likedCommentIds,
            ])->render();
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'html' => $html,
                'page' => $page,
                'total_pages' => $totalPages,
                'total' => $total,
            ]
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Có lỗi xảy ra khi tải bình luận'
        ], 500);
    }
})->name('manga.comments.index');

Route::post('/truyen-tranh/{slug}/comments/{commentId}/like', function ($slug, $commentId) {
    try {
        if (!auth()->check()) {
            return response()->json(['status' => 'error', 'message' => 'Vui lòng đăng nhập'], 401);
        }

        $mangaMetadata = \App\Models\MangaMetadata::where('slug', $slug)->first();
        
        if (!$mangaMetadata) {
            return response()->json(['status' => 'error', 'message' => 'Truyện không tồn tại'], 404);
        }

        $comment = \App\Models\MangaComment::where('id', $commentId)
            ->where('manga_id', $mangaMetadata->id)
            ->first();

        if (!$comment) {
            return response()->json(['status' => 'error', 'message' => 'Bình luận không tồn tại'], 404);
        }

        $userId = auth()->id();
        $like = \App\Models\MangaCommentLike::where('comment_id', $commentId)
            ->where('user_id', $userId)
            ->first();

        if ($like) {
            $like->delete();
            $comment->decrementLikes();
            $isLiked = false;
        } else {
            \App\Models\MangaCommentLike::create([
                'comment_id' => $commentId,
                'user_id' => $userId,
            ]);
            $comment->incrementLikes();
            $isLiked = true;
        }

        $comment->refresh();

        return response()->json([
            'status' => 'success',
            'data' => [
                'isLiked' => $isLiked,
                'totalLike' => $comment->likes_count,
            ]
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Có lỗi xảy ra khi thích bình luận'
        ], 500);
    }
})->middleware('web')->name('manga.comments.like');

Route::get('/truyen-tranh/{slug}/comments/liked-ids', function ($slug) {
    try {
        if (!auth()->check()) {
            return response()->json(['status' => 'success', 'data' => []]);
        }

        $mangaMetadata = \App\Models\MangaMetadata::where('slug', $slug)->first();
        
        if (!$mangaMetadata) {
            return response()->json(['status' => 'error', 'message' => 'Truyện không tồn tại'], 404);
        }

        $commentIds = \App\Models\MangaComment::where('manga_id', $mangaMetadata->id)
            ->pluck('id')
            ->toArray();

        $likedCommentIds = \App\Models\MangaCommentLike::whereIn('comment_id', $commentIds)
            ->where('user_id', auth()->id())
            ->pluck('comment_id')
            ->toArray();

        return response()->json([
            'status' => 'success',
            'data' => $likedCommentIds
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Có lỗi xảy ra'
        ], 500);
    }
})->name('manga.comments.liked-ids');

Route::post('/truyen-tranh/{slug}/report', function ($slug) {
    try {
        $mangaMetadata = \App\Models\MangaMetadata::where('slug', $slug)->first();
        
        if (!$mangaMetadata) {
            return response()->json(['status' => 'error', 'message' => 'Truyện không tồn tại'], 404);
        }
        
        $content = request()->input('content');
        $chapterSlug = request()->input('chapter_slug');
        
        if (empty($content) || strlen(trim($content)) < 10) {
            return response()->json(['status' => 'error', 'message' => 'Nội dung báo cáo phải có ít nhất 10 ký tự'], 400);
        }
        
        if (strlen($content) > 3000) {
            return response()->json(['status' => 'error', 'message' => 'Nội dung báo cáo không được vượt quá 3000 ký tự'], 400);
        }
        
        \App\Models\MangaReport::create([
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
})->middleware('web')->name('manga.report');

Route::get('/', function () {
    $otruyenService = new \App\Services\OTruyenService();
    $recentlyUpdated = $otruyenService->getRecentlyUpdated(1, 30);
    
    if (isset($recentlyUpdated['mangas']) && is_array($recentlyUpdated['mangas'])) {
        $slugs = array_column($recentlyUpdated['mangas'], 'slug');
        $mangaMetadataMap = \App\Models\MangaMetadata::whereIn('slug', $slugs)
            ->get()
            ->keyBy('slug');
        
        foreach ($recentlyUpdated['mangas'] as &$manga) {
            $mangaMetadata = $mangaMetadataMap->get($manga['slug'] ?? '');
            if ($mangaMetadata) {
                $manga['views'] = (int)($mangaMetadata->views_count ?? 0);
            } else {
                $manga['views'] = 0;
            }
        }
        unset($manga);
    }
    
    $suggestedMangas = \App\Models\MangaMetadata::where('is_active', true)
        ->where('chapters_count', '>', 0)
        ->inRandomOrder()
        ->limit(24)
        ->get()
        ->map(function($manga) {
            return [
                'slug' => $manga->slug,
                'title' => $manga->title,
                'posterPath' => $manga->cover_url,
                'avgVote' => $manga->rating ? (float)$manga->rating : 0,
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
    
    $trendingSlugs = json_decode(\App\Models\Setting::get('trending_mangas', '[]'), true) ?? [];
    $trendingMangas = [];
    if (!empty($trendingSlugs)) {
        $trendingMangasData = \App\Models\MangaMetadata::whereIn('slug', $trendingSlugs)
            ->get()
            ->sortBy(function($manga) use ($trendingSlugs) {
                return array_search($manga->slug, $trendingSlugs);
            })
            ->values();
        
        foreach ($trendingMangasData as $manga) {
            $latestChapter = $manga->chapters()
                ->whereNotNull('chapter_slug')
                ->orderBy('updated_at', 'desc')
                ->first();
            
            $chapters = [];
            
            if ($latestChapter) {
                $chapterNumber = '';
                if (preg_match('/\d+/', $latestChapter->chapter_name, $matches)) {
                    $chapterNumber = $matches[0];
                } elseif (preg_match('/\d+/', $latestChapter->chapter_slug, $matches)) {
                    $chapterNumber = $matches[0];
                }
                
                $chapters[] = [
                    'id' => $latestChapter->id,
                    'slug' => $latestChapter->chapter_slug,
                    'name' => formatChapterNameForDisplay($latestChapter->chapter_name),
                    'releasedAt' => $latestChapter->updated_at ? formatVietnameseTime($latestChapter->updated_at) : null,
                ];
                
                if ($chapterNumber && is_numeric($chapterNumber) && (int)$chapterNumber > 1) {
                    $prevChapterNumber = (int)$chapterNumber - 1;
                    $prevChapterSlug = 'chapter-' . $prevChapterNumber;
                    $prevChapterName = 'Chapter ' . $prevChapterNumber;
                    
                    array_unshift($chapters, [
                        'id' => null,
                        'slug' => $prevChapterSlug,
                        'name' => $prevChapterName,
                        'releasedAt' => $latestChapter->updated_at ? formatVietnameseTime($latestChapter->updated_at) : null,
                    ]);
                }
            } elseif ($manga->last_chapter_number) {
                $chapterNumber = $manga->last_chapter_number;
                $chapters[] = [
                    'id' => null,
                    'slug' => 'chapter-' . $chapterNumber,
                    'name' => 'Chapter ' . $chapterNumber,
                    'releasedAt' => $manga->last_synced_at ? formatVietnameseTime($manga->last_synced_at) : null,
                ];
                
                if (is_numeric($chapterNumber) && (int)$chapterNumber > 1) {
                    $prevChapterNumber = (int)$chapterNumber - 1;
                    $prevChapterSlug = 'chapter-' . $prevChapterNumber;
                    $prevChapterName = 'Chapter ' . $prevChapterNumber;
                    
                    array_unshift($chapters, [
                        'id' => null,
                        'slug' => $prevChapterSlug,
                        'name' => $prevChapterName,
                        'releasedAt' => $manga->last_synced_at ? formatVietnameseTime($manga->last_synced_at) : null,
                    ]);
                }
            }
            
            $viewsFormatted = '';
            $viewsCount = (int)($manga->views_count ?? 0);
            if ($viewsCount >= 1000000) {
                $viewsFormatted = number_format($viewsCount / 1000000, 1) . 'M lượt xem';
            } elseif ($viewsCount >= 1000) {
                $viewsFormatted = number_format($viewsCount / 1000, 1) . 'K lượt xem';
            } else {
                $viewsFormatted = $viewsCount . ' lượt xem';
            }
            
            $trendingMangas[] = [
                'slug' => $manga->slug,
                'title' => $manga->title,
                'cover_url' => $manga->cover_url ?: asset('images/pre-load1.png'),
                'rating' => $manga->rating ? (float)$manga->rating : 0,
                'views_count' => $viewsCount,
                'views_formatted' => $viewsFormatted,
                'chapters' => array_slice($chapters, 0, 2),
            ];
        }
    }
    
    $sapRaMatData = $otruyenService->getMangaByList('sap-ra-mat', 1, 24, true);
    $hoanThanhData = $otruyenService->getMangaByList('hoan-thanh', 1, 24, true);
    
    $topComments = \App\Models\MangaComment::whereNull('parent_id')
        ->join('users', 'manga_comments.user_id', '=', 'users.id')
        ->join('manga_metadata', 'manga_comments.manga_id', '=', 'manga_metadata.id')
        ->select(
            'manga_comments.id',
            'manga_comments.content',
            'manga_comments.created_at',
            'manga_comments.manga_id',
            'users.id as user_id',
            'users.name as user_name',
            'users.avatar as user_avatar',
            'manga_metadata.id as manga_metadata_id',
            'manga_metadata.slug as manga_slug',
            'manga_metadata.title as manga_title',
            'manga_metadata.cover_url as manga_cover_url'
        )
        ->orderBy('manga_comments.created_at', 'desc')
        ->limit(10)
        ->get()
        ->map(function($row) {
            // Get user avatar or default
            if ($row->user_avatar) {
                if (filter_var($row->user_avatar, FILTER_VALIDATE_URL)) {
                    $avatar = $row->user_avatar;
                } else {
                    $avatar = asset('storage/' . $row->user_avatar);
                }
            } else {
                $avatar = asset('images/avatars/type3/' . (($row->user_id % 10) + 1) . '.png');
            }
            
            return [
                'id' => $row->id,
                'content' => $row->content,
                'created_at' => $row->created_at,
                'user' => [
                    'id' => $row->user_id,
                    'name' => $row->user_name ?? 'Người dùng',
                    'avatar' => $avatar,
                ],
                'manga' => [
                    'id' => $row->manga_metadata_id,
                    'slug' => $row->manga_slug ?? '',
                    'title' => $row->manga_title ?? 'Đang cập nhật',
                    'cover_url' => $row->manga_cover_url ?? asset('images/pre-load1.png'),
                ],
            ];
        });
    
    $getTopFollow = function($period) {
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
        
        $topMangas = \App\Models\MangaDailyView::whereBetween('view_date', [$startDate, $endDate])
            ->select('manga_id', \DB::raw('SUM(views_count) as total_views'))
            ->groupBy('manga_id')
            ->orderBy('total_views', 'desc')
            ->limit(6)
            ->get();
        
        if ($topMangas->count() < 6 && $period === 'day') {
            $existingMangaIds = $topMangas->pluck('manga_id')->toArray();
            $needed = 6 - $topMangas->count();
            
            if ($needed > 0) {
                $additionalMangas = \App\Models\MangaDailyView::where('view_date', '<', $today)
                    ->when(count($existingMangaIds) > 0, function($query) use ($existingMangaIds) {
                        return $query->whereNotIn('manga_id', $existingMangaIds);
                    })
                    ->select('manga_id', \DB::raw('SUM(views_count) as total_views'))
                    ->groupBy('manga_id')
                    ->orderBy('total_views', 'desc')
                    ->limit($needed)
                    ->get();
                
                // Merge và sắp xếp lại theo total_views
                if ($additionalMangas->count() > 0) {
                    $topMangas = $topMangas->concat($additionalMangas)
                        ->sortByDesc(function($item) {
                            return $item->total_views;
                        })
                        ->take(6)
                        ->values();
                }
            }
        }
        
        // Lấy thông tin chi tiết của các manga
        $mangaIds = $topMangas->pluck('manga_id')->toArray();
        $mangaMetadata = \App\Models\MangaMetadata::whereIn('id', $mangaIds)
            ->get()
            ->keyBy('id');
        
        $result = [];
        $rank = 1;
        foreach ($topMangas as $topManga) {
            $manga = $mangaMetadata->get($topManga->manga_id);
            if (!$manga) continue;
            
            // Lấy chapter mới nhất
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
            
            // Format views
            $viewsCount = (int)$topManga->total_views;
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
                'rating' => $manga->rating ? (float)$manga->rating : 0,
                'views_count' => $viewsCount,
                'views_formatted' => $formattedViews,
                'last_chapter' => $lastChapterData,
                'rank' => $rank,
            ];
            
            $rank++;
        }
        
        return $result;
    };
    
    $topFollowDay = $getTopFollow('day');
    $topFollowWeek = $getTopFollow('week');
    $topFollowMonth = $getTopFollow('month');
    
    $blogPosts = \App\Models\Post::where('is_active', true)
        ->whereNotNull('published_at')
        ->where('published_at', '<=', now())
        ->orderBy('published_at', 'desc')
        ->limit(12)
        ->get();
    
    return view('home.index', [
        'recentlyUpdated' => $recentlyUpdated['mangas'] ?? [],
        'recentlyUpdatedMetadata' => $recentlyUpdated['metadata'] ?? null,
        'suggestedMangas' => $suggestedMangas,
        'trendingMangas' => $trendingMangas,
        'sapRaMatMangas' => $sapRaMatData['mangas'] ?? [],
        'hoanThanhMangas' => $hoanThanhData['mangas'] ?? [],
        'topComments' => $topComments,
        'topFollowDay' => $topFollowDay,
        'topFollowWeek' => $topFollowWeek,
        'topFollowMonth' => $topFollowMonth,
        'blogPosts' => $blogPosts,
    ]);
});

Route::get('/truyen-tranh/{slug}', function ($slug) {
    $otruyenService = new \App\Services\OTruyenService();
    
    $mangaDetail = $otruyenService->getMangaDetail($slug);
    
    if (!$mangaDetail) {
        abort(404, 'Truyện không tồn tại');
    }
    $mangaMetadata = \App\Models\MangaMetadata::where('slug', $slug)
        ->orderBy('id', 'desc')
        ->first();
    
    if (!$mangaMetadata) {
        try {
            $mangaMetadata = \App\Models\MangaMetadata::create([
                'slug' => $slug,
                'source_type' => 'otruyen',
                'source_identifier' => $mangaDetail['id'] ?? $slug,
                'title' => $mangaDetail['name'] ?? 'Đang cập nhật',
                'description' => strip_tags($mangaDetail['description'] ?? ''),
                'cover_url' => $mangaDetail['cover_url'] ?? '',
                'author' => is_array($mangaDetail['author'] ?? []) ? implode(', ', $mangaDetail['author']) : ($mangaDetail['author'] ?? ''),
                'status' => $mangaDetail['status'] ?? 'ongoing',
                'tags' => array_map(function($tag) { return $tag['name'] ?? ''; }, $mangaDetail['tags'] ?? []),
                'origin_name' => $mangaDetail['origin_name'] ?? [],
                'is_active' => true,
                'last_synced_at' => now(),
            ]);
        } catch (\Exception $e) {
            $mangaMetadata = \App\Models\MangaMetadata::where('slug', $slug)
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
    
    $duplicates = \App\Models\MangaMetadata::where('slug', $slug)
        ->where('id', '!=', $mangaMetadata->id)
        ->get();
    if ($duplicates->count() > 0) {
        foreach ($duplicates as $duplicate) {
            $duplicate->delete();
        }
    }
    
    if ($mangaMetadata->slug !== $slug) {
        $mangaMetadata->slug = $slug;
        $mangaMetadata->save();
        $mangaMetadata->refresh();
    }
    
    $mangaMetadata->title = $mangaDetail['name'] ?? $mangaMetadata->title;
    if (empty($mangaMetadata->cover_url) && !empty($mangaDetail['cover_url'])) {
        $mangaMetadata->cover_url = $mangaDetail['cover_url'];
    }
    if (empty($mangaMetadata->description) && !empty($mangaDetail['description'])) {
        $mangaMetadata->description = $mangaDetail['description'];
    }
    $mangaMetadata->save();
    
    $totalViews = (int)($mangaMetadata->views_count ?? 0);
    $chapterViews = \App\Models\MangaChapter::where('manga_id', $mangaMetadata->id)
        ->pluck('views_count', 'chapter_slug')
        ->toArray();
    
    $avgRating = $mangaMetadata->rating ? (float)$mangaMetadata->rating : 0;
    $userRating = null;
    $isFollowing = false;
    $followsCount = $mangaMetadata->getFollowsCount();
    
    if (auth()->check()) {
        $userRating = $mangaMetadata->getUserRating(auth()->id());
        $isFollowing = $mangaMetadata->isFollowedBy(auth()->id());
    }
    
    $relatedMangas = collect();
    $attempts = 0;
    $maxAttempts = 20;
    
    while ($relatedMangas->count() < 5 && $attempts < $maxAttempts) {
        $mangas = \App\Models\MangaMetadata::where('is_active', true)
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
                $lastChapterData = [
                    'name' => $lastChapter->chapter_name,
                    'slug' => $lastChapter->chapter_slug,
                    'updated_at' => $lastChapter->updated_at ? $lastChapter->updated_at->diffForHumans() : null,
                ];
            } elseif ($manga->last_chapter_number) {
                $mangaDetail = $otruyenService->getMangaDetail($manga->slug);
                if ($mangaDetail && isset($mangaDetail['chapters']) && is_array($mangaDetail['chapters']) && count($mangaDetail['chapters']) > 0) {
                    $lastChapterFromApi = end($mangaDetail['chapters']);
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
                    'rating' => $manga->rating ? (float)$manga->rating : 0,
                    'views_count' => $viewsFormatted . ' lượt xem',
                    'last_chapter' => $lastChapterData,
                ]);
            }
        }
        
        $attempts++;
    }
    
    $relatedMangas = $relatedMangas->take(5)->values();
    $comments = \App\Models\MangaComment::where('manga_id', $mangaMetadata->id)
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
        $likedCommentIds = \App\Models\MangaCommentLike::whereIn('comment_id', $allCommentIds)
            ->where('user_id', auth()->id())
            ->pluck('comment_id')
            ->toArray();
    }
    
    $commentsCount = \App\Models\MangaComment::where('manga_id', $mangaMetadata->id)
        ->whereNull('parent_id')
        ->count();
    
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
                    $tagSlug = \Illuminate\Support\Str::slug($tag);
                    $tagsFormatted[] = [
                        'name' => $tag,
                        'slug' => $tagSlug
                    ];
                } elseif (is_array($tag)) {
                    $tagSlug = $tag['slug'] ?? \Illuminate\Support\Str::slug($tag['name']);
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
    
    $mangaForView = [
        'id' => $mangaMetadata->id,
        'name' => $mangaMetadata->title,
        'slug' => $mangaMetadata->slug,
        'cover_url' => $mangaMetadata->cover_url ?? $mangaDetail['cover_url'] ?? asset('images/pre-load1.png'),
        'description' => $mangaMetadata->description ?? $mangaDetail['description'] ?? '',
        'author' => $authorFormatted,
        'status' => $mangaMetadata->status ?? $mangaDetail['status'] ?? 'ongoing',
        'tags' => $tagsFormatted,
        'chapters' => $mangaDetail['chapters'] ?? [],
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
    ]);
})->name('manga.detail');

Route::get('/api/search', function () {
    $keyword = trim(request()->get('keyword', ''));
    
    if (empty($keyword)) {
        return response()->json([
            'status' => 'success',
            'data' => []
        ]);
    }
    
    $query = \App\Models\MangaMetadata::where('is_active', true)
        ->whereHas('chapters', function($q) {
            $q->whereNotNull('chapter_slug');
        })
        ->where(function($q) use ($keyword) {
            $q->where('title', 'LIKE', '%' . $keyword . '%')
              ->orWhere('slug', 'LIKE', '%' . $keyword . '%');
        })
        ->orderBy('views_count', 'desc')
        ->limit(8)
        ->get();
    
    $results = [];
    foreach ($query as $manga) {
        $latestChapter = $manga->chapters()
            ->whereNotNull('chapter_slug')
            ->orderBy('updated_at', 'desc')
            ->first();
        
        $chapterData = null;
        if ($latestChapter) {
            $chapterData = [
                'id' => $latestChapter->id,
                'slug' => $latestChapter->chapter_slug,
                'name' => formatChapterNameForDisplay($latestChapter->chapter_name),
            ];
        }
        
        if ($chapterData) {
            $results[] = [
                'title' => $manga->title,
                'posterPath' => $manga->cover_url ?: asset('images/pre-load1.png'),
                'slug' => '/truyen-tranh/' . $manga->slug,
                'chapters' => [$chapterData]
            ];
        }
    }
    
    return response()->json([
        'status' => 'success',
        'data' => $results
    ]);
});

Route::get('/api/comment/{commentId}/manga-id', function ($commentId) {
    $comment = \App\Models\MangaComment::with('manga')->find($commentId);
    if (!$comment) {
        return response()->json(['error' => 'Comment not found'], 404);
    }
    
    return response()->json([
        'comment_id' => $comment->id,
        'manga_id' => $comment->manga_id,
        'manga_slug' => $comment->manga ? $comment->manga->slug : null,
    ]);
});

if (!function_exists('formatChapterNameForDisplay')) {
    function formatChapterNameForDisplay($chapterName) {
        if (empty($chapterName)) {
            return 'Chapter';
        }
        $cleaned = trim(preg_replace('/^Chapter\s+/i', '', $chapterName));
        return 'Chapter ' . $cleaned;
    }
}

// Account page
Route::get('/tai-khoan', function () {
    if (!auth()->check()) {
        return redirect('/')->with('error', 'Vui lòng đăng nhập để xem tài khoản');
    }
    
    $user = auth()->user();
    $otruyenService = new \App\Services\OTruyenService();
    
    $allCategories = \App\Models\Category::where('is_active', true)
        ->orderBy('sort_order')
        ->orderBy('name')
        ->get();
    
    $allTags = \App\Models\Category::where('is_active', true)
        ->orderBy('sort_order')
        ->orderBy('name')
        ->get();
    
    $readingPage = max(1, (int)request()->get('reading_page', 1));
    $followingPage = max(1, (int)request()->get('following_page', 1));
    $perPage = 9;
    
    $readingHistoryQuery = \App\Models\MangaReadingHistory::where('user_id', $user->id)
        ->with(['manga', 'chapter'])
        ->orderBy('last_read_at', 'desc')
        ->get()
        ->unique('manga_id');
    
    $totalReadingItems = $readingHistoryQuery->count();
    $totalReadingPages = (int)ceil($totalReadingItems / $perPage);
    $readingHistoryPaginated = $readingHistoryQuery->slice(($readingPage - 1) * $perPage, $perPage);
    
    $readingHistory = $readingHistoryPaginated->map(function($history) use ($otruyenService) {
            $manga = $history->manga;
            $chapter = $history->chapter;
            
            if (!$manga) {
                return null;
            }
            
            $mangaDetail = $otruyenService->getMangaDetail($manga->slug);
            $totalChapters = count($mangaDetail['chapters'] ?? []);
            
            $currentChapterNumber = 0;
            if ($chapter && $chapter->chapter_slug) {
                $chapterSlug = $chapter->chapter_slug;
                $chapterNumberStr = preg_replace('/^chapter-/', '', $chapterSlug);
                
                if (preg_match('/^(\d+)/', $chapterNumberStr, $matches)) {
                    $currentChapterNumber = (int)$matches[1];
                } elseif (is_numeric($chapterNumberStr)) {
                    $currentChapterNumber = (int)$chapterNumberStr;
                }
            }
            
            $maxChapterNumber = 0;
            if (isset($mangaDetail['chapters']) && is_array($mangaDetail['chapters']) && count($mangaDetail['chapters']) > 0) {
                $firstChapter = $mangaDetail['chapters'][0];
                $firstChapterSlug = $firstChapter['slug'] ?? '';
                $firstChapterNumberStr = preg_replace('/^chapter-/', '', $firstChapterSlug);
                if (preg_match('/^(\d+)/', $firstChapterNumberStr, $matches)) {
                    $maxChapterNumber = (int)$matches[1];
                } elseif (is_numeric($firstChapterNumberStr)) {
                    $maxChapterNumber = (int)$firstChapterNumberStr;
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
                'rating' => $manga->rating ? (float)$manga->rating : 0,
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
        ->filter(function($manga) {
            return $manga !== null;
        });
    
    
    $followingQuery = \App\Models\MangaFollow::where('user_id', $user->id)
        ->with('manga')
        ->orderBy('created_at', 'desc')
        ->get();
    
    $totalFollowingItems = $followingQuery->count();
    $totalFollowingPages = (int)ceil($totalFollowingItems / $perPage);
    $followingPaginated = $followingQuery->slice(($followingPage - 1) * $perPage, $perPage);
    
    $followingMangas = $followingPaginated->map(function($follow) use ($otruyenService) {
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
                $mangaDetail = $otruyenService->getMangaDetail($manga->slug);
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
                'rating' => $manga->rating ? (float)$manga->rating : 0,
                'last_chapter' => $lastChapterData,
            ];
        })
        ->filter(function($manga) {
            return $manga !== null;
        });
    
    return view('account.index', [
        'user' => $user,
        'allCategories' => $allCategories,
        'allTags' => $allTags,
        'readingHistory' => $readingHistory,
        'readingPage' => $readingPage,
        'totalReadingPages' => $totalReadingPages,
        'followingMangas' => $followingMangas,
        'followingPage' => $followingPage,
        'totalFollowingPages' => $totalFollowingPages,
    ]);
})->name('account.index')->middleware('auth');

Route::get('/truyen-tranh/{mangaSlug}/{chapterSlug}', function ($mangaSlug, $chapterSlug) {
    $otruyenService = new \App\Services\OTruyenService();
    $mangaDetail = $otruyenService->getMangaDetail($mangaSlug);
    
    if (!$mangaDetail) {
        abort(404, 'Truyện không tồn tại');
    }
    
    $mangaMetadata = \App\Models\MangaMetadata::where('slug', $mangaSlug)->first();
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
        
        foreach ($chapters as $index => $chapter) {
            $chapterSlugFromData = $chapter['slug'] ?? '';
            $chapterNumberFromData = preg_replace('/^chapter-/', '', $chapterSlugFromData);
            
            if ($chapterNumberFromData === $chapterNumber) {
                $currentChapter = $chapter;
                $currentIndex = $index;
                break;
            }
            
            $chapterName = $chapter['name'] ?? '';
            if ($chapterName && (strpos($chapterName, $chapterNumber) !== false || $chapterName === $chapterNumber)) {
                $currentChapter = $chapter;
                $currentIndex = $index;
                break;
            }
        }
    }
    
    if (!$currentChapter) {
        abort(404, 'Chapter không tồn tại');
    }
    
    $chapterRecord = \App\Models\MangaChapter::firstOrNew([
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
    
    \App\Models\MangaDailyView::incrementTodayViews($mangaMetadata->id);
    
    if (auth()->check()) {
        \App\Models\MangaReadingHistory::updateOrCreate(
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
        $chapterImages = $otruyenService->getChapterImages($currentChapter['api_data']);
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
    
    $comments = \App\Models\MangaComment::where('manga_id', $mangaMetadata->id)
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
        $likedCommentIds = \App\Models\MangaCommentLike::whereIn('comment_id', $allCommentIds)
            ->where('user_id', auth()->id())
            ->pluck('comment_id')
            ->toArray();
    }
    
    $commentsCount = \App\Models\MangaComment::where('manga_id', $mangaMetadata->id)
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
    ]);
})->name('manga.chapter');

Route::get('/tim-kiem', function () {
    $request = request();
    $keyword = trim($request->get('keyword', ''));
    $page = max(1, (int)$request->get('page', 1));
    $perPage = 10;
    
    $sort = $request->get('sort', 'updated_at_desc');
    $orderBy = $request->get('orderBy', $sort);
    $sort = $orderBy ?: $sort;
    $categories = $request->get('categories', []);
    $categoryIds = $request->get('categoryIds', []);
    $tags = $request->get('tags', []);
    $genreIds = $request->get('genreIds', []);
    
    if (!empty($genreIds)) {
        $tags = $genreIds;
    }
    if (!empty($categoryIds)) {
        $categories = $categoryIds;
    }
    
    if (!is_array($categories)) {
        $categories = $categories ? explode(',', $categories) : [];
    }
    if (!is_array($tags)) {
        $tags = $tags ? explode(',', $tags) : [];
    }
    $tags = array_filter($tags);
    $categories = array_filter($categories);
    
    $query = \App\Models\MangaMetadata::where('is_active', true);
    
    if (!empty($keyword)) {
        $query->where(function($q) use ($keyword) {
            $q->where('title', 'LIKE', '%' . $keyword . '%')
              ->orWhere('slug', 'LIKE', '%' . $keyword . '%');
        });
    }
    
    if (!empty($categories)) {
        $categoryMap = [
            '1' => ['Manga', 'manga', 'Manga (Nhật)'],
            '2' => ['Manhua', 'manhua', 'Manhua (Trung)'], 
            '3' => ['Manhwa', 'manhwa', 'Manhwa (Hàn)'],
        ];
        
        $categorySearchValues = [];
        foreach ($categories as $catId) {
            if (isset($categoryMap[$catId])) {
                $categorySearchValues = array_merge($categorySearchValues, $categoryMap[$catId]);
            }
        }
        
        if (!empty($categorySearchValues)) {
            $categorySearchValues = array_unique($categorySearchValues);
            $query->where(function($q) use ($categorySearchValues) {
                foreach ($categorySearchValues as $value) {
                    $q->orWhereJsonContains('tags', $value);
                }
            });
        }
    }
    
    if (!empty($tags)) {
        $tagCategoryIds = array_filter(array_map('intval', $tags));
        $tagCategories = \App\Models\Category::whereIn('id', $tagCategoryIds)->get();
        
        $tagSearchValues = [];
        foreach ($tagCategories as $category) {
            $tagSearchValues[] = $category->name;
            
            $tagSearchValues[] = $category->slug;
            
            if (!empty($category->slug)) {
                $tagSearchValues[] = ucfirst($category->slug);
            }
            
            if (!empty($category->name)) {
                $tagSearchValues[] = strtoupper($category->name);
            }
        }
        
        foreach ($tags as $tagValue) {
            if (!is_numeric($tagValue)) {
                $tagSearchValues[] = $tagValue;
                $tagSearchValues[] = ucfirst($tagValue);
            }
        }
        
        $tagSearchValues = array_unique(array_filter($tagSearchValues));
        
        if (!empty($tagSearchValues)) {
            $query->where(function($q) use ($tagSearchValues) {
                foreach ($tagSearchValues as $tagValue) {
                    $q->orWhereJsonContains('tags', $tagValue);
                }
            });
        }
    }
    
    switch ($sort) {
        case 'view_desc':
            $query->orderBy('views_count', 'desc');
            break;
        case 'view_asc':
            $query->orderBy('views_count', 'asc');
            break;
        case 'updated_at_date_desc':
        case 'udpated_at_date_desc':
            $query->orderBy('updated_at', 'desc');
            break;
        case 'updated_at_date_asc':
        case 'udpated_at_date_asc':
            $query->orderBy('updated_at', 'asc');
            break;
        case 'created_at_date_desc':
            $query->orderBy('created_at', 'desc');
            break;
        case 'created_at_date_asc':
            $query->orderBy('created_at', 'asc');
            break;
        case 'rating_desc':
            $query->orderBy('rating', 'desc')->orderBy('updated_at', 'desc');
            break;
        case 'rating_asc':
            $query->orderBy('rating', 'asc')->orderBy('updated_at', 'desc');
            break;
        default:
            $query->orderBy('updated_at', 'desc');
            break;
    }
    
    $total = $query->count();
    $totalPages = max(1, ceil($total / $perPage));
    
    $mangas = $query->skip(($page - 1) * $perPage)
        ->take($perPage)
        ->get();
    
    $results = [];
    foreach ($mangas as $manga) {
        $latestChapter = $manga->chapters()
            ->whereNotNull('chapter_slug')
            ->orderBy('updated_at', 'desc')
            ->first();
        
        $chapterData = [];
        
        if ($latestChapter) {
            $chapterNumber = '';
            if (preg_match('/\d+/', $latestChapter->chapter_name, $matches)) {
                $chapterNumber = $matches[0];
            } elseif (preg_match('/\d+/', $latestChapter->chapter_slug, $matches)) {
                $chapterNumber = $matches[0];
            }
            
            $chapterData[] = [
                'id' => $latestChapter->id,
                'slug' => $latestChapter->chapter_slug,
                'name' => formatChapterNameForDisplay($latestChapter->chapter_name),
                'number' => $chapterNumber,
                'updated_at' => $latestChapter->updated_at ? $latestChapter->updated_at->toDateTimeString() : null,
                'releasedAt' => $latestChapter->updated_at ? $latestChapter->updated_at->diffForHumans() : null,
            ];
            
            if ($chapterNumber && is_numeric($chapterNumber) && (int)$chapterNumber > 1) {
                $prevChapterNumber = (int)$chapterNumber - 1;
                $prevChapterSlug = 'chapter-' . $prevChapterNumber;
                $prevChapterName = 'Chapter ' . $prevChapterNumber;
                
                $chapterData[] = [
                    'id' => null,
                    'slug' => $prevChapterSlug,
                    'name' => $prevChapterName,
                    'number' => (string)$prevChapterNumber,
                    'updated_at' => $latestChapter->updated_at ? $latestChapter->updated_at->toDateTimeString() : null,
                    'releasedAt' => $latestChapter->updated_at ? $latestChapter->updated_at->diffForHumans() : null,
                ];
            }
        } else {
            if ($manga->last_chapter_number) {
                $chapterNumber = $manga->last_chapter_number;
                $chapterSlug = 'chapter-' . $chapterNumber;
                $chapterName = 'Chapter ' . $chapterNumber;
                
                $chapterData[] = [
                    'id' => null,
                    'slug' => $chapterSlug,
                    'name' => $chapterName,
                    'number' => $chapterNumber,
                    'updated_at' => $manga->last_synced_at ? $manga->last_synced_at->toDateTimeString() : null,
                    'releasedAt' => $manga->last_synced_at ? $manga->last_synced_at->diffForHumans() : null,
                ];
                
                if (is_numeric($chapterNumber) && (int)$chapterNumber > 1) {
                    $prevChapterNumber = (int)$chapterNumber - 1;
                    $prevChapterSlug = 'chapter-' . $prevChapterNumber;
                    $prevChapterName = 'Chapter ' . $prevChapterNumber;
                    
                    $chapterData[] = [
                        'id' => null,
                        'slug' => $prevChapterSlug,
                        'name' => $prevChapterName,
                        'number' => (string)$prevChapterNumber,
                        'updated_at' => $manga->last_synced_at ? $manga->last_synced_at->toDateTimeString() : null,
                        'releasedAt' => $manga->last_synced_at ? $manga->last_synced_at->diffForHumans() : null,
                    ];
                }
            }
        }
        
        $results[] = [
            'slug' => $manga->slug,
            'title' => $manga->title,
            'posterPath' => $manga->cover_url ?: asset('images/pre-load1.png'),
            'avgVote' => (float)($manga->rating ?? 0),
            'countView' => (int)($manga->views_count ?? 0),
            'chapters' => $chapterData, 
        ];
    }
    
    $defaultTagOrder = [
        '16+', 'Action', 'Adult', 'Adventure', 'Anime', 'Chuyển Sinh', 'Cổ Đại',
        'Comedy', 'Comic', 'Cooking', 'Doujinshi', 'Drama', 'Đam Mỹ',
        'Ecchi', 'Fantasy', 'Gender Bender', 'Harem', 'Historical', 'Horror',
        'Josei', 'Live action', 'Manga', 'Manhua'
    ];
    
    $allCategories = \App\Models\Category::where('is_active', true)->get();
    
    // Tags cần ẩn (vì đã có ở phần Thể loại)
    $hiddenTagNames = ['Manga', 'Manhua', 'Manhwa'];
    
    $defaultTags = [];
    $remainingTags = [];
    
    foreach ($defaultTagOrder as $tagName) {
        // Skip các tags cần ẩn
        if (in_array($tagName, $hiddenTagNames)) {
            continue;
        }
        
        $category = $allCategories->first(function($cat) use ($tagName) {
            return $cat->name === $tagName;
        });
        if ($category) {
            $defaultTags[] = [
                'id' => $category->id,
                'slug' => $category->slug,
                'name' => $category->name,
            ];
        }
    }
    
    foreach ($allCategories as $category) {
        // Skip các tags cần ẩn
        if (in_array($category->name, $hiddenTagNames)) {
            continue;
        }
        
        if (!in_array($category->name, $defaultTagOrder)) {
            $remainingTags[] = [
                'id' => $category->id,
                'slug' => $category->slug,
                'name' => $category->name,
            ];
        }
    }
    
    usort($remainingTags, function($a, $b) use ($allCategories) {
        $catA = $allCategories->firstWhere('id', $a['id']);
        $catB = $allCategories->firstWhere('id', $b['id']);
        if ($catA && $catB) {
            if ($catA->sort_order != $catB->sort_order) {
                return $catA->sort_order <=> $catB->sort_order;
            }
            return strcmp($catA->name, $catB->name);
        }
        return 0;
    });
    
    $allTags = array_merge($defaultTags, $remainingTags);
    
    return view('search.index', [
        'keyword' => $keyword,
        'results' => $results,
        'totalResults' => $total,
        'currentPage' => $page,
        'totalPages' => $totalPages,
        'sort' => $sort,
        'categories' => $categories,
        'tags' => $tags,
        'allTags' => $allTags,
    ]);
})->name('search');

Route::get('/genre', function () {
    $allowedSlugs = ['action', 'romance', 'comedy', 'fantasy', 'drama', 'ngon-tinh'];
    
    $categories = \App\Models\Category::where('is_active', true)
        ->whereIn('slug', $allowedSlugs)
        ->orderByRaw('FIELD(slug, "' . implode('","', $allowedSlugs) . '")')
        ->get();
    
    $genres = [];
    
    foreach ($categories as $category) {
        $mangas = \App\Models\MangaMetadata::where('is_active', true)
            ->where('chapters_count', '>', 0)
            ->where(function($query) use ($category) {
                $query->whereJsonContains('tags', $category->name)
                      ->orWhereJsonContains('tags', $category->slug);
            })
            ->inRandomOrder()
            ->limit(24)
            ->get()
            ->map(function($manga) {
                return [
                    'slug' => $manga->slug,
                    'title' => $manga->title,
                    'posterPath' => $manga->cover_url,
                    'avgVote' => $manga->rating ? (float)$manga->rating : 0,
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
})->name('genre.all');

Route::get('/genre/{slug}', function ($slug) {
    $page = (int)request()->get('page', 1);
    
    $otruyenService = new \App\Services\OTruyenService();
    $data = $otruyenService->getMangaByGenre($slug, $page);
    
    if (!$data) {
        abort(404, 'Thể loại không tồn tại');
    }
    
    $category = \App\Models\Category::where('slug', $slug)->first();
    $genreName = $category ? $category->name : ($data['titlePage'] ?? ucfirst($slug));
    
    $genre = [
        'name' => $genreName,
        'title' => 'Truyện tranh ' . $genreName,
        'description' => 'Danh sách truyện tranh ' . $genreName,
    ];
    
    $pagination = $data['pagination'] ?? [];
    $totalPages = 1;
    if (isset($pagination['totalItems']) && isset($pagination['totalItemsPerPage']) && $pagination['totalItemsPerPage'] > 0) {
        $totalPages = (int)ceil($pagination['totalItems'] / $pagination['totalItemsPerPage']);
    }
    
    return view('genre.index', [
        'slug' => $slug,
        'genre' => $genre,
        'results' => $data['mangas'] ?? [],
        'currentPage' => $page,
        'totalPages' => $totalPages,
    ]);
})->name('genre');

Route::get('/the-loai', function () {
    $otruyenService = new \App\Services\OTruyenService();
    $types = [
        ['slug' => 'manga', 'name' => 'Manga'],
        ['slug' => 'manhua', 'name' => 'Manhua'],
        ['slug' => 'manhwa', 'name' => 'Manhwa'],
        ['slug' => 'viet-nam', 'name' => 'Việt Nam'],
    ];
    
    $genres = [];
    
    foreach ($types as $type) {
        $data = $otruyenService->getMangaByType($type['slug'], 1, 24);
        
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
})->name('the-loai.all');

Route::get('/the-loai/{slug}', function ($slug) {
    $page = (int)request()->get('page', 1);
    
    $otruyenService = new \App\Services\OTruyenService();
    $data = $otruyenService->getMangaByGenre($slug, $page);
    
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
        $totalPages = (int)ceil($pagination['totalItems'] / $pagination['totalItemsPerPage']);
    }
    
    return view('category.index', [
        'slug' => $slug,
        'category' => $category,
        'results' => $data['mangas'] ?? [],
        'currentPage' => $page,
        'totalPages' => $totalPages,
    ]);
})->name('category');

Route::get('/da-hoan-thanh', function () {
    $page = (int)request()->get('page', 1);
    
    $otruyenService = new \App\Services\OTruyenService();
    $data = $otruyenService->getMangaByList('hoan-thanh', $page, 24, false);
    
    if (!$data) {
        abort(404, 'Không tìm thấy dữ liệu');
    }
    
    $pagination = $data['pagination'] ?? [];
    $totalPages = 1;
    if (isset($pagination['totalItems']) && isset($pagination['totalItemsPerPage']) && $pagination['totalItemsPerPage'] > 0) {
        $totalPages = (int)ceil($pagination['totalItems'] / $pagination['totalItemsPerPage']);
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
})->name('completed');
Route::get('/hot-nhat', function () {
    $type = request()->get('type', 'all');
    
    $getHotMangas = function($period) {
        $today = now()->toDateString();
        $startDate = null;
        $endDate = $today;
        
        if ($period === 'all') {
            // Lấy top 12 theo tổng views từ tất cả thời gian (từ manga_metadata.views_count)
            $topMangas = \App\Models\MangaMetadata::where('is_active', true)
                ->where('views_count', '>', 0)
                ->orderBy('views_count', 'desc')
                ->limit(12)
                ->get();
            
            $mangaIds = $topMangas->pluck('id')->toArray();
            $mangaMetadata = $topMangas->keyBy('id');
            
            $result = [];
            foreach ($topMangas as $manga) {
                // Lấy chapter mới nhất
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
                
                // Format views
                $viewsCount = (int)($manga->views_count ?? 0);
                
                $result[] = [
                    'slug' => $manga->slug,
                    'title' => $manga->title ?? 'Đang cập nhật',
                    'posterPath' => $manga->cover_url ?? asset('images/pre-load1.png'),
                    'avgVote' => $manga->rating ? (float)$manga->rating : 0,
                    'countView' => $viewsCount,
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
            
            $topMangas = \App\Models\MangaDailyView::whereBetween('view_date', [$startDate, $endDate])
                ->select('manga_id', \DB::raw('SUM(views_count) as total_views'))
                ->groupBy('manga_id')
                ->orderBy('total_views', 'desc')
                ->limit(12)
                ->get();
            
            $mangaIds = $topMangas->pluck('manga_id')->toArray();
            $mangaMetadata = \App\Models\MangaMetadata::whereIn('id', $mangaIds)
                ->where('is_active', true)
                ->get()
                ->keyBy('id');
            
            $result = [];
            foreach ($topMangas as $topManga) {
                $manga = $mangaMetadata->get($topManga->manga_id);
                if (!$manga) continue;
                
                // Lấy chapter mới nhất
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
                
                // Format views
                $viewsCount = (int)$topManga->total_views;
                
                $result[] = [
                    'slug' => $manga->slug,
                    'title' => $manga->title ?? 'Đang cập nhật',
                    'posterPath' => $manga->cover_url ?? asset('images/pre-load1.png'),
                    'avgVote' => $manga->rating ? (float)$manga->rating : 0,
                    'countView' => $viewsCount,
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
})->name('hot');

// Random truyện
Route::get('/random', function () {
    $manga = \App\Models\MangaMetadata::where('is_active', true)
        ->whereHas('chapters', function($query) {
            $query->whereNotNull('chapter_slug');
        })
        ->inRandomOrder()
        ->first();
    
    if (!$manga) {
        return redirect('/')->with('error', 'Không tìm thấy truyện nào');
    }
    
    return redirect(route('manga.detail', ['slug' => $manga->slug]));
})->name('random');

// Trang tin tức
Route::get('/tin-tuc', function () {
    $currentPage = max(1, (int)request()->get('page', 1));
    $perPage = 6;
    
    $featuredPost = \App\Models\Post::where('is_active', true)
        ->where('is_featured', true)
        ->whereNotNull('published_at')
        ->where('published_at', '<=', now())
        ->orderBy('published_at', 'desc')
        ->first();
    
    $query = \App\Models\Post::where('is_active', true)
        ->whereNotNull('published_at')
        ->where('published_at', '<=', now());
    
    if ($featuredPost) {
        $query->where('id', '!=', $featuredPost->id);
    }
    
    $totalPosts = $query->count();
    $totalPages = max(1, ceil($totalPosts / $perPage));
    
    $otherNews = $query->orderBy('published_at', 'desc')
        ->skip(($currentPage - 1) * $perPage)
        ->take($perPage)
        ->get()
        ->map(function($post) {
            return [
                'slug' => $post->slug,
                'title' => $post->title,
                'image' => $post->image ?? asset('images/pre-load1.png'),
                'description' => $post->description ?? '',
                'author' => $post->author ?? 'Admin',
                'date' => $post->published_at ? $post->published_at->format('d/m/Y') : '',
            ];
        })
        ->toArray();
    
    $featuredNews = null;
    if ($featuredPost) {
        $featuredNews = [
            'slug' => $featuredPost->slug,
            'title' => $featuredPost->title,
            'image' => $featuredPost->image ?? asset('images/pre-load1.png'),
            'description' => $featuredPost->description ?? '',
            'author' => $featuredPost->author ?? 'Admin',
            'date' => $featuredPost->published_at ? $featuredPost->published_at->format('d/m/Y') : '',
        ];
    }
    
    return view('news.index', [
        'currentPage' => $currentPage,
        'totalPages' => $totalPages,
        'featuredNews' => $featuredNews,
        'otherNews' => $otherNews,
    ]);
})->name('news');

Route::get('/tin-tuc/{slug}', function ($slug) {
    $post = \App\Models\Post::where('slug', $slug)
        ->where('is_active', true)
        ->whereNotNull('published_at')
        ->where('published_at', '<=', now())
        ->firstOrFail();
    
    $relatedPosts = \App\Models\Post::where('is_active', true)
        ->where('id', '!=', $post->id)
        ->whereNotNull('published_at')
        ->where('published_at', '<=', now())
        ->orderBy('published_at', 'desc')
        ->limit(5)
        ->get();
    
    return view('news.detail', [
        'post' => $post,
        'relatedPosts' => $relatedPosts,
    ]);
})->name('news.detail');

// Trang truyện mới nhất
Route::get('/new', function () {
    $page = max(1, (int)request()->get('page', 1));
    
    $otruyenService = new \App\Services\OTruyenService();
    $data = $otruyenService->getOngoingMangas($page);
    
    if (!$data) {
        abort(404, 'Không thể tải dữ liệu');
    }
    
    $pagination = $data['pagination'] ?? [];
    $totalPages = 1;
    if (isset($pagination['totalItems']) && isset($pagination['totalItemsPerPage']) && $pagination['totalItemsPerPage'] > 0) {
        $totalPages = (int)ceil($pagination['totalItems'] / $pagination['totalItemsPerPage']);
    }
    
    $titlePage = $data['titlePage'] ?? 'Truyện đang phát hành';
    
    return view('new.index', [
        'currentPage' => $page,
        'totalPages' => $totalPages,
        'results' => $data['mangas'] ?? [],
        'titlePage' => $titlePage,
    ]);
})->name('new');

// Trang thông tin
Route::get('/trang/chinh-sach-bao-mat', function () {
    return view('page.chinh-sach-bao-mat');
})->name('page.privacy');

Route::get('/trang/dieu-khoan-dich-vu', function () {
    return view('page.dieu-khoan-dich-vu');
})->name('page.terms');

Route::get('/trang/tuyen-bo-mien-tru-trach-nhiem', function () {
    return view('page.tuyen-bo-mien-tru-trach-nhiem');
})->name('page.disclaimer');

Route::get('/trang/ve-chung-toi', function () {
    return view('page.ve-chung-toi');
})->name('page.about');

Route::middleware('auth')->group(function () {
    Route::put('/tai-khoan', function () {
        $user = auth()->user();
        
        $request = request();
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
    })->name('account.update');
    
    Route::post('/tai-khoan/upload-avatar', function () {
        $request = request();
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
    })->name('account.upload-avatar');
    
    Route::post('/tai-khoan/clear-reading', function () {
        $user = auth()->user();
        $mangaId = request()->input('manga_id');
        
        if (!$mangaId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Thiếu thông tin manga_id'
            ], 400);
        }
        
        \App\Models\MangaReadingHistory::where('user_id', $user->id)
            ->where('manga_id', $mangaId)
            ->delete();
        
        return response()->json([
            'status' => 'success',
            'message' => 'Xóa lịch sử đọc thành công'
        ]);
    })->name('account.clear-reading');
});

if (!function_exists('processCrawlJob')) {
    function processCrawlJob($jobKey, $jobData) {
        $otruyenService = new \App\Services\OTruyenService();
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
            $pagesToProcess = [];
            if ($allPages && $pages[0] === 'all') {
                if (isset($jobData['total_pages_calculated']) && $jobData['total_pages_calculated']) {
                    $pagesToProcess = range(1, $jobData['total_pages']);
                } else {
                    $firstPageData = $otruyenService->getMangaByList($listType, 1, 24, false);
                    if ($firstPageData && isset($firstPageData['pagination'])) {
                        $totalPages = $firstPageData['pagination']['total_pages'] ?? $firstPageData['pagination']['pageRanges'] ?? 1;
                        $jobData['total_pages'] = $totalPages;
                        $jobData['total_pages_calculated'] = true;
                        $pagesToProcess = range(1, $totalPages);
                    } else {
                        $pagesToProcess = [1];
                        $jobData['total_pages'] = 1;
                    }
                }
            } else {
                $pagesToProcess = $pages;
                $jobData['total_pages'] = count($pagesToProcess);
            }
            
            $totalItems = 0;
            
            foreach ($pagesToProcess as $pageNum) {
                if ($currentPage > $pageNum) {
                    continue;
                }
                if ($currentPage == $pageNum && $currentMangaIndexInPage >= 24) {
                    continue;
                }
                
                \Illuminate\Support\Facades\Cache::forget("otruyen:list:{$listType}:page:{$pageNum}:limit:24:shuffle:0");
                $pageData = $otruyenService->getMangaByList($listType, $pageNum, 24, false);
                if (!$pageData) {
                    $newLogs = [];
                    $newLogs[] = [
                        'message' => "Không thể lấy dữ liệu trang {$pageNum}",
                        'type' => 'error'
                    ];
                    $jobData['logs'] = array_merge($jobData['logs'] ?? [], $newLogs);
                    $jobData['logs_sent_count'] = $logsSentCount + count($newLogs);
                    $currentPage = $pageNum + 1;
                    $currentMangaIndexInPage = 0;
                    $jobData['current_page'] = $currentPage;
                    $jobData['current_manga_index_in_page'] = 0;
                    session()->put($jobKey, $jobData);
                    continue;
                }
                
                if (empty($pageData['mangas'])) {
                    $newLogs = [];
                    $newLogs[] = [
                        'message' => "Không có truyện nào trong trang {$pageNum}",
                        'type' => 'warning'
                    ];
                    $jobData['logs'] = array_merge($jobData['logs'] ?? [], $newLogs);
                    $jobData['logs_sent_count'] = $logsSentCount + count($newLogs);
                    $currentPage = $pageNum + 1;
                    $currentMangaIndexInPage = 0;
                    $jobData['current_page'] = $currentPage;
                    $jobData['current_manga_index_in_page'] = 0;
                    session()->put($jobKey, $jobData);
                    continue;
                }
                
                $mangas = $pageData['mangas'];
                $totalItems += count($mangas);
                
                $newLogs = [];
                if ($currentPage != $pageNum || $currentMangaIndexInPage == 0) {
                    $newLogs[] = [
                        'message' => "Đang crawl trang {$pageNum}... (" . count($mangas) . " truyện)",
                        'type' => 'info'
                    ];
                }
                
                $processedCount = 0;
                $skippedCount = 0;
                $updatedCount = 0;
                $createdCount = 0;
                
                $mangaIndex = $currentMangaIndexInPage;
                $totalMangasInPage = count($mangas);
                foreach ($mangas as $manga) {
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
                    
                    if ($mangaIndex >= $totalMangasInPage) {
                        break;
                    }
                    
                    if ($processedThisPoll >= $maxPerPoll) {
                        $jobData['current_page'] = $pageNum;
                        $jobData['current_manga_index_in_page'] = $mangaIndex;
                        $jobData['logs'] = array_merge($jobData['logs'] ?? [], $newLogs);
                        $jobData['logs_sent_count'] = $logsSentCount + count($newLogs);
                        session()->put($jobKey, $jobData);
                        return;
                    }
                    
                    try {
                        $mangaDetail = $otruyenService->getMangaDetail($slug);
                        
                        if ($mangaDetail) {
                            $mangaMetadata = \App\Models\MangaMetadata::where('slug', $slug)->first();
                            if ($mangaMetadata) {
                                if ($mangaMetadata->created_at->diffInSeconds($mangaMetadata->updated_at) <= 1) {
                                    $createdCount++;
                                    $newLogs[] = [
                                        'message' => "✓ Đã tạo mới: {$mangaDetail['name']}",
                                        'type' => 'success'
                                    ];
                                } else {
                                    $updatedCount++;
                                    $newLogs[] = [
                                        'message' => "↻ Đã cập nhật: {$mangaDetail['name']}",
                                        'type' => 'info'
                                    ];
                                }
                            }
                            $processedCount++;
                        } else {
                            $skippedCount++;
                            $newLogs[] = [
                                'message' => "✗ Bỏ qua (không tìm thấy): " . ($manga['name'] ?? $slug),
                                'type' => 'warning'
                            ];
                        }
                        
                        $processedItems[] = $slug;
                        $processedThisPoll++;
                    } catch (\Exception $e) {
                        $skippedCount++;
                        $processedThisPoll++;
                        $newLogs[] = [
                            'message' => "✗ Lỗi crawl: " . ($manga['name'] ?? $slug) . " - " . $e->getMessage(),
                            'type' => 'error'
                        ];
                    }
                    
                    $mangaIndex++;
                }
                
                if ($mangaIndex >= count($mangas)) {
                    $jobData['logs'][] = [
                        'message' => "Hoàn thành trang {$pageNum}: Tạo mới {$createdCount}, Cập nhật {$updatedCount}, Bỏ qua {$skippedCount}",
                        'type' => 'info'
                    ];
                    $currentPage = $pageNum + 1;
                    $currentMangaIndexInPage = 0;
                } else {
                    $currentPage = $pageNum;
                    $currentMangaIndexInPage = $mangaIndex;
                }
                
                $jobData['current_page'] = $currentPage;
                $jobData['current_manga_index_in_page'] = $currentMangaIndexInPage;
                $jobData['current_item'] = count($processedItems);
                $jobData['total_items'] = $totalItems;
                $jobData['logs'] = array_merge($jobData['logs'] ?? [], $newLogs);
                $jobData['processed_items'] = $processedItems;
                $jobData['logs_sent_count'] = $logsSentCount + count($newLogs);
                session()->put($jobKey, $jobData);
                
                if ($currentMangaIndexInPage > 0) {
                    return;
                }
                if ($processedThisPoll >= $maxPerPoll && $mangaIndex < count($mangas)) {
                    return;
                }
            }
            
            $maxPage = max($pagesToProcess);
            if ($currentPage > $maxPage || ($currentPage == $maxPage && $currentMangaIndexInPage >= 24)) {
                $jobData['status'] = 'completed';
                $jobData['logs'][] = [
                    'message' => "✓ Hoàn tất crawl! Tổng cộng đã xử lý " . count($processedItems) . " truyện",
                    'type' => 'success'
                ];
            } else {
                $jobData['status'] = 'running';
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
}

// Admin routes
Route::prefix('admin')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/', function () {
        $stats = [
            'total_users' => \App\Models\User::count(),
            'total_mangas' => \App\Models\MangaMetadata::where('is_active', true)->count(),
            'total_chapters' => \App\Models\MangaChapter::count(),
        ];
        
        return view('admin.index', compact('stats'));
    })->name('admin.index');
    
    Route::get('/users', function () {
        $search = request()->input('search', '');
        
        $query = \App\Models\User::query();
        
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('email', 'like', '%' . $search . '%')
                  ->orWhere('name', 'like', '%' . $search . '%');
            });
        }
        
        $query->orderByRaw('CASE WHEN last_login_at IS NOT NULL AND last_login_at >= ? THEN 0 ELSE 1 END', [now()->subMinutes(15)])
              ->orderBy('id', 'desc');
        
        $users = $query->paginate(10)->withQueryString();
        
        return view('admin.users', compact('users', 'search'));
    })->name('admin.users');
    
    Route::post('/users/{id}/change-role', function ($id) {
        $user = \App\Models\User::findOrFail($id);
        $role = request()->input('role');
        
        if (!in_array($role, ['user', 'admin'])) {
            return response()->json(['status' => 'error', 'message' => 'Role không hợp lệ'], 400);
        }
        
        $user->role = $role;
        $user->save();
        
        return response()->json(['status' => 'success', 'message' => 'Đổi quyền thành công']);
    })->name('admin.users.change-role');
    
    Route::post('/users/{id}/ban', function ($id) {
        $user = \App\Models\User::findOrFail($id);
            if ($user->id === auth()->id()) {
            return response()->json(['status' => 'error', 'message' => 'Bạn không thể ban chính mình'], 400);
        }
        
        $days = (int)request()->input('days');
        
        if ($days <= 0) {
            return response()->json(['status' => 'error', 'message' => 'Số ngày không hợp lệ'], 400);
        }
        
        if ($days >= 999999) {
            $user->banned_until = now()->addYears(100); // Vĩnh viễn
        } else {
            $user->banned_until = now()->addDays($days);
        }
        
        $user->save();
        
        return response()->json(['status' => 'success', 'message' => 'Ban user thành công']);
    })->name('admin.users.ban');
    
    Route::post('/users/{id}/unban', function ($id) {
        $user = \App\Models\User::findOrFail($id);
        
        $user->banned_until = null;
        $user->save();
        
        return response()->json(['status' => 'success', 'message' => 'Bỏ ban user thành công']);
    })->name('admin.users.unban');
    
    Route::get('/mangas', function () {
        $recentMangas = \App\Models\MangaMetadata::orderBy('updated_at', 'desc')
            ->limit(10)
            ->get();
        
        $trendingSlugs = json_decode(\App\Models\Setting::get('trending_mangas', '[]'), true) ?? [];
        $trendingMangas = \App\Models\MangaMetadata::whereIn('slug', $trendingSlugs)
            ->get()
            ->sortBy(function($manga) use ($trendingSlugs) {
                return array_search($manga->slug, $trendingSlugs);
            })
            ->values();
        
        return view('admin.mangas', compact('recentMangas', 'trendingMangas'));
    })->name('admin.mangas');
    
    Route::get('/mangas/search', function () {
        $search = request()->input('q', '');
        if (empty($search)) {
            return response()->json([]);
        }
        
        if (strpos($search, ',') !== false) {
            $slugs = array_map('trim', explode(',', $search));
            $mangas = \App\Models\MangaMetadata::whereIn('slug', $slugs)
                ->get(['id', 'title', 'slug', 'cover_url']);
        } else {
            if (strlen($search) < 2) {
                return response()->json([]);
            }
            $mangas = \App\Models\MangaMetadata::where('title', 'like', '%' . $search . '%')
                ->orWhere('slug', 'like', '%' . $search . '%')
                ->limit(20)
                ->get(['id', 'title', 'slug', 'cover_url']);
        }
        
        return response()->json($mangas);
    })->name('admin.mangas.search');
    
    Route::post('/mangas/trending', function () {
        $slugs = request()->input('slugs', []);
        if (count($slugs) > 8) {
            return response()->json([
                'status' => 'error',
                'message' => 'Chỉ được chọn tối đa 8 truyện'
            ], 400);
        }
        
        \App\Models\Setting::set('trending_mangas', json_encode($slugs));
        
        return response()->json([
            'status' => 'success',
            'message' => 'Đã lưu danh sách Top thịnh hành thành công'
        ]);
    })->name('admin.mangas.trending');
    
    Route::post('/mangas/crawl', function () {
        $listType = request()->input('list_type');
        $customPages = request()->has('custom_pages');
        $allPages = request()->has('all_pages');
        $pagesInput = request()->input('pages', '1');
        
        $pages = [];
        if ($allPages) {
            $pages = ['all'];
        } else {
            if (strpos($pagesInput, '-') !== false) {
                list($start, $end) = explode('-', $pagesInput);
                $pages = range((int)$start, (int)$end);
            } elseif (strpos($pagesInput, ',') !== false) {
                $pages = array_map('intval', explode(',', $pagesInput));
            } else {
                $pages = [(int)$pagesInput];
            }
        }
        
        $jobId = uniqid('crawl_', true);
        
        session()->put("crawl_job_{$jobId}", [
            'list_type' => $listType,
            'pages' => $pages,
            'all_pages' => $allPages,
            'status' => 'running',
            'logs' => [],
            'logs_sent_count' => 0,
            'current_page' => 0,
            'current_manga_index_in_page' => 0,
            'total_pages' => 0,
            'current_item' => 0,
            'total_items' => 0,
            'processed_items' => [],
        ]);
        
        return response()->json([
            'status' => 'success',
            'job_id' => $jobId,
            'message' => 'Crawl started'
        ]);
    })->name('admin.mangas.crawl');
    
    Route::get('/mangas/crawl/progress/{jobId}', function ($jobId) {
        $jobKey = "crawl_job_{$jobId}";
        $jobData = session()->get($jobKey);
        
        if (!$jobData) {
            return response()->json([
                'status' => 'not_found',
                'message' => 'Job not found'
            ], 404);
        }
        
        if ($jobData['status'] === 'running') {
            processCrawlJob($jobKey, $jobData);
            $jobData = session()->get($jobKey);
        }
        
        $allLogs = $jobData['logs'] ?? [];
        $logsSentCount = $jobData['logs_sent_count'] ?? 0;
        $newLogs = array_slice($allLogs, $logsSentCount);
        
        if (count($newLogs) > 0) {
            $jobData['logs_sent_count'] = count($allLogs);
            session()->put($jobKey, $jobData);
        }
        
        return response()->json([
            'status' => $jobData['status'],
            'logs' => $newLogs,
            'progress' => [
                'current_page' => $jobData['current_page'] ?? 0,
                'total_pages' => $jobData['total_pages'] ?? 0,
                'current_item' => $jobData['current_item'] ?? 0,
                'total_items' => $jobData['total_items'] ?? 0,
            ],
            'error' => $jobData['error'] ?? null,
        ]);
    })->name('admin.mangas.crawl.progress');
    
    Route::get('/settings', function () {
        $settings = [
            'facebook_url' => \App\Models\Setting::get('facebook_url', ''),
            'twitter_url' => \App\Models\Setting::get('twitter_url', ''),
            'gmail_url' => \App\Models\Setting::get('gmail_url', ''),
        ];
        return view('admin.settings', compact('settings'));
    })->name('admin.settings');
    
    Route::post('/settings/social', function () {
        $facebook = request()->input('facebook_url', '');
        $twitter = request()->input('twitter_url', '');
        $gmail = request()->input('gmail_url', '');
        
        \App\Models\Setting::set('facebook_url', $facebook);
        \App\Models\Setting::set('twitter_url', $twitter);
        \App\Models\Setting::set('gmail_url', $gmail);
        
        return response()->json([
            'status' => 'success',
            'message' => 'Đã lưu cấu hình thành công'
        ]);
    })->name('admin.settings.social');
    
    Route::post('/settings/social/clear', function () {
        \App\Models\Setting::whereIn('key', ['facebook_url', 'twitter_url', 'gmail_url'])->delete();
        
        return response()->json([
            'status' => 'success',
            'message' => 'Đã xóa cấu hình thành công'
        ]);
    })->name('admin.settings.social.clear');
    
    Route::get('/posts', function () {
        $posts = \App\Models\Post::orderBy('created_at', 'desc')->paginate(20);
        return view('admin.posts', compact('posts'));
    })->name('admin.posts');
    
    Route::get('/posts/create', function () {
        return view('admin.post-form', ['post' => null]);
    })->name('admin.posts.create');
    
    Route::get('/posts/{id}/edit', function ($id) {
        $post = \App\Models\Post::findOrFail($id);
        return view('admin.post-form', ['post' => $post]);
    })->name('admin.posts.edit');
    
    Route::post('/posts', function () {
        try {
            $request = request();
            
            \Log::info('Creating post', [
                'title' => $request->input('title'),
                'all_data' => $request->all()
            ]);
            
            if (!$request->has('title') || empty(trim($request->input('title')))) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Tiêu đề không được để trống'
                ], 400);
            }
            
            $slug = \Illuminate\Support\Str::slug($request->input('title'));
            if (empty($slug)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Tiêu đề không hợp lệ'
                ], 400);
            }
            
            $originalSlug = $slug;
            $counter = 1;
            while (\App\Models\Post::where('slug', $slug)->exists()) {
                $slug = $originalSlug . '-' . $counter;
                $counter++;
            }
            
            $postData = [
                'slug' => $slug,
                'title' => trim($request->input('title')),
                'description' => $request->input('description', ''),
                'content' => $request->input('content', ''),
                'image' => $request->input('image', ''),
                'author' => $request->input('author', auth()->user()->name ?? 'Admin'),
                'is_featured' => (bool)$request->input('is_featured', 0),
                'is_active' => (bool)$request->input('is_active', 1),
                'published_at' => $request->input('published_at') ? now()->parse($request->input('published_at')) : now(),
            ];
            
            \Log::info('Post data to create', $postData);
            
            $post = \App\Models\Post::create($postData);
            
            \Log::info('Post created successfully', ['post_id' => $post->id]);
            
            return response()->json([
                'status' => 'success',
                'message' => 'Đã tạo bài viết thành công',
                'post_id' => $post->id
            ]);
        } catch (\Exception $e) {
            \Log::error('Error creating post', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Lỗi: ' . $e->getMessage()
            ], 500);
        }
    })->name('admin.posts.store');
    
    Route::put('/posts/{id}', function ($id) {
        $post = \App\Models\Post::findOrFail($id);
        $request = request();
        
        $slug = \Illuminate\Support\Str::slug($request->input('title'));
        if ($slug !== $post->slug) {
            $originalSlug = $slug;
            $counter = 1;
            while (\App\Models\Post::where('slug', $slug)->where('id', '!=', $id)->exists()) {
                $slug = $originalSlug . '-' . $counter;
                $counter++;
            }
        }
        
        $post->update([
            'slug' => $slug,
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'content' => $request->input('content'),
            'image' => $request->input('image'),
            'author' => $request->input('author'),
            'is_featured' => (bool)$request->input('is_featured', 0),
            'is_active' => (bool)$request->input('is_active', 1),
            'published_at' => $request->input('published_at') ? now()->parse($request->input('published_at')) : $post->published_at,
        ]);
        
        return response()->json([
            'status' => 'success',
            'message' => 'Đã cập nhật bài viết thành công'
        ]);
    })->name('admin.posts.update');
    
    Route::delete('/posts/{id}', function ($id) {
        $post = \App\Models\Post::findOrFail($id);
        $post->delete();
        
        return response()->json([
            'status' => 'success',
            'message' => 'Đã xóa bài viết thành công'
        ]);
    })->name('admin.posts.delete');
    
    Route::post('/posts/upload-image', function () {
        $file = request()->file('image');
        
        if (!$file || !$file->isValid()) {
            return response()->json([
                'status' => 'error',
                'message' => 'File không hợp lệ'
            ], 400);
        }
        
        $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/jpg', 'image/webp'];
        if (!in_array($file->getMimeType(), $allowedMimes)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Chỉ chấp nhận file ảnh (jpg, png, gif, webp)'
            ], 400);
        }
        
        if ($file->getSize() > 10 * 1024 * 1024) {
            return response()->json([
                'status' => 'error',
                'message' => 'File quá lớn (tối đa 10MB)'
            ], 400);
        }
        
        $uploadDir = public_path('images/posts');
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $extension = $file->getClientOriginalExtension() ?: 'jpg';
        $fileName = uniqid('post_') . '.' . strtolower($extension);
        $filePath = $uploadDir . '/' . $fileName;
        
        $file->move($uploadDir, $fileName);
        
        $url = asset('images/posts/' . $fileName);
        
        return response()->json([
            'status' => 'success',
            'url' => $url
        ]);
    })->name('admin.posts.upload-image');
});

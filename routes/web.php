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
    
    return view('home.index', [
        'recentlyUpdated' => $recentlyUpdated['mangas'] ?? [],
        'recentlyUpdatedMetadata' => $recentlyUpdated['metadata'] ?? null,
        'suggestedMangas' => $suggestedMangas,
        'sapRaMatMangas' => $sapRaMatData['mangas'] ?? [],
        'hoanThanhMangas' => $hoanThanhData['mangas'] ?? [],
        'topComments' => $topComments,
        'topFollowDay' => $topFollowDay,
        'topFollowWeek' => $topFollowWeek,
        'topFollowMonth' => $topFollowMonth,
    ]);
});

Route::get('/truyen-tranh/{slug}', function ($slug) {
    $otruyenService = new \App\Services\OTruyenService();
    $mangaDetail = $otruyenService->getMangaDetail($slug);
    
    if (!$mangaDetail) {
        abort(404, 'Truyện không tồn tại');
    }
    
    // Tìm lại mangaMetadata theo slug từ URL (sau khi getMangaDetail có thể đã tạo/cập nhật)
    // Đảm bảo luôn dùng slug từ URL, không phải từ API
    // Sử dụng orderBy để đảm bảo luôn lấy record mới nhất nếu có duplicate
    $mangaMetadata = \App\Models\MangaMetadata::where('slug', $slug)
        ->orderBy('id', 'desc')
        ->first();
    
    if (!$mangaMetadata) {
        abort(404, 'Truyện không tồn tại trong hệ thống');
    }
    
    // Nếu có nhiều record với cùng slug, xóa các record cũ (chỉ giữ lại record mới nhất)
    if ($mangaMetadata) {
        $duplicates = \App\Models\MangaMetadata::where('slug', $slug)
            ->where('id', '!=', $mangaMetadata->id)
            ->get();
        if ($duplicates->count() > 0) {
            foreach ($duplicates as $duplicate) {
                // Xóa các record duplicate (trừ record hiện tại)
                $duplicate->delete();
            }
        }
    }
    
    // Final verification: Đảm bảo slug khớp với URL
    if ($mangaMetadata->slug !== $slug) {
        $mangaMetadata->slug = $slug;
        $mangaMetadata->save();
        $mangaMetadata->refresh();
    }
    
    // Update existing metadata if needed (nhưng không thay đổi slug)
    $mangaMetadata->title = $mangaDetail['name'] ?? $mangaMetadata->title;
    if (empty($mangaMetadata->cover_url) && !empty($mangaDetail['cover_url'])) {
        $mangaMetadata->cover_url = $mangaDetail['cover_url'];
    }
    if (empty($mangaMetadata->description) && !empty($mangaDetail['description'])) {
        $mangaMetadata->description = $mangaDetail['description'];
    }
    $mangaMetadata->save();
    
    // Đảm bảo mangaMetadata có id
    if (!$mangaMetadata->id) {
        $mangaMetadata->refresh();
    }
    
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
    
    // Load initial comments
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
    
    // Build $manga array từ $mangaMetadata để đảm bảo luôn hiển thị đúng dữ liệu từ database
    // Chỉ lấy chapters từ API, còn lại lấy từ database
    $tagsFromDb = $mangaMetadata->tags;
    if (is_string($tagsFromDb)) {
        $tagsFromDb = json_decode($tagsFromDb, true) ?? [];
    }
    if (!is_array($tagsFromDb)) {
        $tagsFromDb = [];
    }
    
    // Convert tags to format expected by view
    $tagsFormatted = [];
    if (is_array($tagsFromDb) && count($tagsFromDb) > 0) {
        foreach ($tagsFromDb as $tag) {
            if (is_string($tag)) {
                $tagSlug = \Illuminate\Support\Str::slug($tag);
                $tagsFormatted[] = [
                    'name' => $tag,
                    'slug' => $tagSlug
                ];
            } elseif (is_array($tag)) {
                // Đảm bảo có cả name và slug
                if (isset($tag['name'])) {
                    $tagSlug = $tag['slug'] ?? \Illuminate\Support\Str::slug($tag['name']);
                    $tagsFormatted[] = [
                        'name' => $tag['name'],
                        'slug' => $tagSlug
                    ];
                }
            }
        }
    }
    // Fallback to API tags if database tags are empty
    if (empty($tagsFormatted) && isset($mangaDetail['tags']) && is_array($mangaDetail['tags'])) {
        $tagsFormatted = $mangaDetail['tags'];
    }
    
    // Handle author - could be string or array
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
    
    $mangaForView = [
        'id' => $mangaMetadata->id,
        'name' => $mangaMetadata->title,
        'slug' => $mangaMetadata->slug,
        'cover_url' => $mangaMetadata->cover_url ?? $mangaDetail['cover_url'] ?? asset('images/pre-load1.png'),
        'description' => $mangaMetadata->description ?? $mangaDetail['description'] ?? '',
        'author' => $authorFormatted,
        'status' => $mangaMetadata->status ?? $mangaDetail['status'] ?? 'ongoing',
        'tags' => $tagsFormatted,
        'chapters' => $mangaDetail['chapters'] ?? [], // Chỉ chapters lấy từ API
        'updated_at' => $mangaMetadata->updated_at ? $mangaMetadata->updated_at->toDateTimeString() : ($mangaDetail['updated_at'] ?? null),
        'type' => $mangaDetail['type'] ?? null, 
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

// API route to get comment's manga_id
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

// Helper function to format chapter name
if (!function_exists('formatChapterNameForDisplay')) {
    function formatChapterNameForDisplay($chapterName) {
        if (empty($chapterName)) {
            return 'Chapter';
        }
        // Remove existing "Chapter" prefix if exists (case insensitive)
        $cleaned = trim(preg_replace('/^Chapter\s+/i', '', $chapterName));
        // Add "Chapter" prefix
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
    
    // Get all categories for suggestion settings
    // Note: categories table doesn't have 'type' column, so we get all active categories
    $allCategories = \App\Models\Category::where('is_active', true)
        ->orderBy('sort_order')
        ->orderBy('name')
        ->get();
    
    // Get all tags for suggestion settings (same as categories for now)
    $allTags = \App\Models\Category::where('is_active', true)
        ->orderBy('sort_order')
        ->orderBy('name')
        ->get();
    
    // Get reading history with pagination (9 per page)
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
            
            // Get manga detail from API to get total chapters
            $mangaDetail = $otruyenService->getMangaDetail($manga->slug);
            $totalChapters = count($mangaDetail['chapters'] ?? []);
            
            // Find current chapter number and calculate progress
            $currentChapterNumber = 0;
            if ($chapter && $chapter->chapter_slug) {
                $chapterSlug = $chapter->chapter_slug;
                $chapterNumberStr = preg_replace('/^chapter-/', '', $chapterSlug);
                
                // Try to extract numeric chapter number
                if (preg_match('/^(\d+)/', $chapterNumberStr, $matches)) {
                    $currentChapterNumber = (int)$matches[1];
                } elseif (is_numeric($chapterNumberStr)) {
                    $currentChapterNumber = (int)$chapterNumberStr;
                }
            }
            
            // Find max chapter number from chapters list
            $maxChapterNumber = 0;
            if (isset($mangaDetail['chapters']) && is_array($mangaDetail['chapters']) && count($mangaDetail['chapters']) > 0) {
                // Get the first chapter (newest) to find max number
                $firstChapter = $mangaDetail['chapters'][0];
                $firstChapterSlug = $firstChapter['slug'] ?? '';
                $firstChapterNumberStr = preg_replace('/^chapter-/', '', $firstChapterSlug);
                if (preg_match('/^(\d+)/', $firstChapterNumberStr, $matches)) {
                    $maxChapterNumber = (int)$matches[1];
                } elseif (is_numeric($firstChapterNumberStr)) {
                    $maxChapterNumber = (int)$firstChapterNumberStr;
                }
            }
            
            // If we couldn't determine from slug, use total chapters as fallback
            if ($maxChapterNumber === 0) {
                $maxChapterNumber = $totalChapters;
            }
            
            // Calculate progress: current chapter / max chapter
            // Progress shows how many chapters have been read
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
    
    // Get following mangas with pagination (9 per page)
    
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
            
            // Get last chapter from database
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
                // Fallback: get from API
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
    
    // Try exact match first
    foreach ($chapters as $index => $chapter) {
        if ($chapter['slug'] === $chapterSlug) {
            $currentChapter = $chapter;
            $currentIndex = $index;
            break;
        }
    }
    
    // If not found, try to match by extracting chapter number
    if (!$currentChapter) {
        // Extract number from chapterSlug (e.g., "chapter-53" -> "53")
        $chapterNumber = preg_replace('/^chapter-/', '', $chapterSlug);
        
        foreach ($chapters as $index => $chapter) {
            $chapterSlugFromData = $chapter['slug'] ?? '';
            $chapterNumberFromData = preg_replace('/^chapter-/', '', $chapterSlugFromData);
            
            // Try to match by number
            if ($chapterNumberFromData === $chapterNumber) {
                $currentChapter = $chapter;
                $currentIndex = $index;
                break;
            }
            
            // Also try matching by chapter name
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
    
    // Lưu views theo ngày để tính top theo dõi
    \App\Models\MangaDailyView::incrementTodayViews($mangaMetadata->id);
    
    // Lưu lịch sử đọc cho user đã đăng nhập
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
    
    // Load comments for this chapter
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
    
    // Filter parameters
    $sort = $request->get('sort', 'updated_at_desc');
    $orderBy = $request->get('orderBy', $sort); // Support both sort and orderBy
    $sort = $orderBy ?: $sort;
    $categories = $request->get('categories', []);
    $categoryIds = $request->get('categoryIds', []); // Support both
    $tags = $request->get('tags', []);
    $genreIds = $request->get('genreIds', []); // Support both tags and genreIds
    
    // Use genreIds if available, otherwise use tags
    if (!empty($genreIds)) {
        $tags = $genreIds;
    }
    // Use categoryIds if available, otherwise use categories
    if (!empty($categoryIds)) {
        $categories = $categoryIds;
    }
    
    if (!is_array($categories)) {
        $categories = $categories ? explode(',', $categories) : [];
    }
    if (!is_array($tags)) {
        $tags = $tags ? explode(',', $tags) : [];
    }
    // Filter out empty values
    $tags = array_filter($tags);
    $categories = array_filter($categories);
    
    $query = \App\Models\MangaMetadata::where('is_active', true);
    
    // Search by keyword
    if (!empty($keyword)) {
        $query->where(function($q) use ($keyword) {
            $q->where('title', 'LIKE', '%' . $keyword . '%')
              ->orWhere('slug', 'LIKE', '%' . $keyword . '%');
        });
    }
    
    // Filter by tags (tags are category IDs, need to get category name/slug to search in manga_metadata.tags JSON)
    if (!empty($tags)) {
        // Get category names and slugs from IDs
        $categoryIds = array_filter(array_map('intval', $tags));
        $categories = \App\Models\Category::whereIn('id', $categoryIds)->get();
        
        $tagSearchValues = [];
        foreach ($categories as $category) {
            $tagSearchValues[] = $category->name;
            $tagSearchValues[] = $category->slug;
        }
        
        // Also check if tags contain string values (for backward compatibility)
        foreach ($tags as $tagValue) {
            if (!is_numeric($tagValue)) {
                $tagSearchValues[] = $tagValue;
            }
        }
        
        $tagSearchValues = array_unique($tagSearchValues);
        
        if (!empty($tagSearchValues)) {
            $query->where(function($q) use ($tagSearchValues) {
                foreach ($tagSearchValues as $tagValue) {
                    $q->orWhereJsonContains('tags', $tagValue);
                }
            });
        }
    }
    
    // Sorting
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
    
    $mangas = $query->get();
    
    // Load chapters for each manga
    $results = [];
    foreach ($mangas as $manga) {
        $chapters = $manga->chapters()
            ->orderBy('updated_at', 'desc')
            ->take(2)
            ->get();
        
        $chapterData = [];
        foreach ($chapters as $chapter) {
            $chapterData[] = [
                'id' => $chapter->id,
                'slug' => $chapter->chapter_slug,
                'name' => 'Chapter ' . $chapter->chapter_name,
                'releasedAt' => $chapter->updated_at ? $chapter->updated_at->diffForHumans() : null,
            ];
        }
        
        // If no chapters, try to get from last_chapter_number
        if (empty($chapterData) && $manga->last_chapter_number) {
            $chapterData[] = [
                'id' => null,
                'slug' => 'chapter-' . $manga->last_chapter_number,
                'name' => 'Chapter ' . $manga->last_chapter_number,
                'releasedAt' => $manga->last_synced_at ? $manga->last_synced_at->diffForHumans() : null,
            ];
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
    
    // Get all categories from database (these are used as tags)
    // Define the default display order for first 23 tags
    $defaultTagOrder = [
        '16+', 'Action', 'Adult', 'Adventure', 'Anime', 'Chuyển Sinh', 'Cổ Đại',
        'Comedy', 'Comic', 'Cooking', 'Doujinshi', 'Drama', 'Đam Mỹ',
        'Ecchi', 'Fantasy', 'Gender Bender', 'Harem', 'Historical', 'Horror',
        'Josei', 'Live action', 'Manga', 'Manhua'
    ];
    
    $allCategories = \App\Models\Category::where('is_active', true)->get();
    
    // Separate tags into default (first 23) and remaining
    $defaultTags = [];
    $remainingTags = [];
    
    foreach ($defaultTagOrder as $tagName) {
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
    
    // Add remaining tags (not in default list)
    foreach ($allCategories as $category) {
        if (!in_array($category->name, $defaultTagOrder)) {
            $remainingTags[] = [
                'id' => $category->id,
                'slug' => $category->slug,
                'name' => $category->name,
            ];
        }
    }
    
    // Sort remaining tags by sort_order and name
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
    
    // Combine: default tags first, then remaining tags
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

Route::get('/genre/demo', function () {
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

Route::get('/the-loai/{slug}/demo', function ($slug) {
    $page = request()->get('page', 1);
    
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
            // Lấy top 12 theo views trong khoảng thời gian
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
            
            // Lấy thông tin chi tiết của các manga
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
    // Lấy random 1 truyện có ít nhất 1 chapter
    $manga = \App\Models\MangaMetadata::where('is_active', true)
        ->whereHas('chapters', function($query) {
            $query->whereNotNull('chapter_slug');
        })
        ->inRandomOrder()
        ->first();
    
    if (!$manga) {
        return redirect('/')->with('error', 'Không tìm thấy truyện nào');
    }
    
    // Redirect đến trang detail truyện
    return redirect(route('manga.detail', ['slug' => $manga->slug]));
})->name('random');

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

// Account routes
Route::middleware('auth')->group(function () {
    Route::put('/tai-khoan', function () {
        $user = auth()->user();
        
        $request = request();
        $name = $request->input('name');
        $avatar = $request->input('avatar');
        
        if ($name) {
            $user->name = $name;
        }
        if ($avatar) {
            $user->avatar = $avatar;
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
        
        if (!$request->hasFile('avatar')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Không có file được upload'
            ], 400);
        }
        
        $file = $request->file('avatar');
        
        // Validate file
        if (!$file->isValid()) {
            return response()->json([
                'status' => 'error',
                'message' => 'File không hợp lệ'
            ], 400);
        }
        
        // Validate image type
        $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/jpg'];
        if (!in_array($file->getMimeType(), $allowedMimes)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Chỉ chấp nhận file ảnh (jpg, png, gif)'
            ], 400);
        }
        
        // Store file
        $path = $file->store('avatars', 'public');
        $url = asset('storage/' . $path);
        
        return response()->json([
            'status' => 'success',
            'message' => 'Upload ảnh thành công',
            'data' => [
                'url' => $url,
                'path' => $path
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

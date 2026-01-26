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
        
        $mangaMetadata = \App\Models\MangaMetadata::where('slug', $slug)->first();
        if (!$mangaMetadata) {
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
        $orderBy = $request->input('order', 'latest');

        $query = \App\Models\MangaComment::where('manga_id', $mangaMetadata->id)
            ->whereNull('parent_id');

        if ($chapterId) {
            $query->where('chapter_id', $chapterId);
        } else {

        }

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
                $manga['rating'] = (float)($mangaMetadata->rating ?? 0);
            } else {
                $manga['views'] = 0;
                $manga['rating'] = 0;
            }
        }
        unset($manga);
    }
    
    $suggestedMangas = \App\Models\MangaMetadata::whereNotNull('last_chapter_number')
        ->where('last_chapter_number', '!=', '')
        ->whereNotNull('slug')
        ->where('slug', '!=', '')
        ->whereNotNull('title')
        ->where('title', '!=', '')
        ->inRandomOrder()
        ->limit(24)
        ->get()
        ->map(function($manga) {
            $chapterData = [];
            
            if ($manga->last_chapter_number) {
                $chapterNumber = preg_replace('/^Chapter\s+/i', '', $manga->last_chapter_number);
                $chapterNumber = trim($chapterNumber);
                
                $chapterData = [
                    [
                        'id' => null,
                        'slug' => 'chapter-' . $chapterNumber,
                        'name' => 'Chapter ' . $chapterNumber,
                        'releasedAt' => $manga->last_synced_at ? formatVietnameseTime($manga->last_synced_at) : null,
                    ],
                ];
            }
            
            return [
                'slug' => $manga->slug ?? '',
                'title' => $manga->title ?? 'Đang cập nhật',
                'posterPath' => $manga->cover_url ?? asset('images/pre-load1.png'),
                'avgVote' => $manga->rating ? (float)$manga->rating : 0,
                'chapters' => $chapterData,
            ];
        })
        ->filter(function($manga) {
            return !empty($manga['chapters']);
        })
        ->values()
        ->toArray();
    
    if (empty($suggestedMangas) && isset($recentlyUpdated['mangas']) && !empty($recentlyUpdated['mangas'])) {
        $suggestedMangas = array_slice(array_map(function($manga) {
            return [
                'slug' => $manga['slug'] ?? '',
                'title' => $manga['title'] ?? 'Đang cập nhật',
                'posterPath' => $manga['posterPath'] ?? asset('images/pre-load1.png'),
                'avgVote' => isset($manga['avgVote']) ? (float)$manga['avgVote'] : 0,
                'chapters' => isset($manga['chapters']) && is_array($manga['chapters']) && !empty($manga['chapters']) 
                    ? array_slice($manga['chapters'], 0, 1) 
                    : [],
            ];
        }, $recentlyUpdated['mangas']), 0, 24);
    }
    
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
    
    $sapRaMatMangas = \App\Models\MangaMetadata::where('is_active', true)
        ->where(function($query) {
            $query->where('status', 'ongoing')
                  ->orWhereNull('status');
        })
        ->whereNotNull('slug')
        ->whereNotNull('title')
        ->whereNotNull('last_chapter_number')
        ->where('last_chapter_number', '!=', '')
        ->inRandomOrder()
        ->limit(24)
        ->get()
        ->map(function($manga) {
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
            } elseif ($manga->last_chapter_number) {
                $chapterNumber = preg_replace('/^Chapter\s+/i', '', $manga->last_chapter_number);
                $releasedAt = null;
                if ($manga->updated_at) {
                    $releasedAt = formatVietnameseTime($manga->updated_at);
                } elseif ($manga->last_synced_at) {
                    $releasedAt = formatVietnameseTime($manga->last_synced_at);
                }
                $chapters[] = [
                    'id' => null,
                    'slug' => 'chapter-' . $chapterNumber,
                    'name' => 'Chapter ' . $chapterNumber,
                    'releasedAt' => $releasedAt,
                ];
            }
            
            return [
                'slug' => $manga->slug,
                'title' => $manga->title ?? 'Đang cập nhật',
                'posterPath' => $manga->cover_url ?? asset('images/pre-load1.png'),
                'avgVote' => $manga->rating ? (float)$manga->rating : 0,
                'chapters' => $chapters,
            ];
        })
        ->toArray();
    
    $hoanThanhMangas = \App\Models\MangaMetadata::where('is_active', true)
        ->where('status', 'completed')
        ->whereNotNull('slug')
        ->whereNotNull('title')
        ->whereNotNull('last_chapter_number')
        ->where('last_chapter_number', '!=', '')
        ->inRandomOrder()
        ->limit(24)
        ->get()
        ->map(function($manga) {
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
            } elseif ($manga->last_chapter_number) {
                $chapterNumber = preg_replace('/^Chapter\s+/i', '', $manga->last_chapter_number);
                $releasedAt = null;
                if ($manga->updated_at) {
                    $releasedAt = formatVietnameseTime($manga->updated_at);
                } elseif ($manga->last_synced_at) {
                    $releasedAt = formatVietnameseTime($manga->last_synced_at);
                }
                $chapters[] = [
                    'id' => null,
                    'slug' => 'chapter-' . $chapterNumber,
                    'name' => 'Chapter ' . $chapterNumber,
                    'releasedAt' => $releasedAt,
                ];
            }
            
            return [
                'slug' => $manga->slug,
                'title' => $manga->title ?? 'Đang cập nhật',
                'posterPath' => $manga->cover_url ?? asset('images/pre-load1.png'),
                'avgVote' => $manga->rating ? (float)$manga->rating : 0,
                'chapters' => $chapters,
            ];
        })
        ->toArray();
    
    $topComments = \App\Models\MangaComment::whereNull('parent_id')
        ->with(['user', 'manga'])
        ->has('user')
        ->has('manga')
        ->orderBy('created_at', 'desc')
        ->limit(10)
        ->get()
        ->filter(function($comment) {
            return $comment->user && $comment->manga;
        })
        ->map(function($comment) {
            $user = $comment->user;
            $manga = $comment->manga;
            
            if ($user->avatar) {
                if (filter_var($user->avatar, FILTER_VALIDATE_URL)) {
                    $avatar = $user->avatar;
                } else {
                    $avatar = asset('storage/' . $user->avatar);
                }
            } else {
                $avatar = asset('images/avatars/type3/' . (($user->id % 10) + 1) . '.png');
            }
            
            return [
                'id' => $comment->id,
                'content' => $comment->content,
                'created_at' => $comment->created_at,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name ?? 'Người dùng',
                    'avatar' => $avatar,
                ],
                'manga' => [
                    'id' => $manga->id,
                    'slug' => $manga->slug ?? '',
                    'title' => $manga->title ?? 'Đang cập nhật',
                    'cover_url' => $manga->cover_url ?? asset('images/pre-load1.png'),
                ],
            ];
        })
        ->values();
    
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
        
        $mangaIds = $topMangas->pluck('manga_id')->toArray();
        $mangaMetadata = \App\Models\MangaMetadata::whereIn('id', $mangaIds)
            ->get()
            ->keyBy('id');
        
        $result = [];
        $rank = 1;
        foreach ($topMangas as $topManga) {
            $manga = $mangaMetadata->get($topManga->manga_id);
            if (!$manga) continue;
            
            $lastChapterData = null;
            
            if ($manga->last_chapter_number) {
                $chapterNumber = preg_replace('/^Chapter\s+/i', '', $manga->last_chapter_number);
                $chapterNumber = trim($chapterNumber);
                
                $lastChapterData = [
                    'name' => 'Chapter ' . $chapterNumber,
                    'slug' => 'chapter-' . $chapterNumber,
                    'updated_at' => $manga->last_synced_at ? formatVietnameseTime($manga->last_synced_at) : null,
                ];
            }
            
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
        ->orderByRaw('COALESCE(published_at, created_at) DESC')
        ->limit(12)
        ->get();
    
    return view('home.index', [
        'recentlyUpdated' => $recentlyUpdated['mangas'] ?? [],
        'recentlyUpdatedMetadata' => $recentlyUpdated['metadata'] ?? null,
        'suggestedMangas' => $suggestedMangas,
        'trendingMangas' => $trendingMangas,
        'sapRaMatMangas' => $sapRaMatMangas,
        'hoanThanhMangas' => $hoanThanhMangas,
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
    
    $responseSlug = $mangaDetail['slug'] ?? '';
    if ($responseSlug !== $slug) {
        \Illuminate\Support\Facades\Cache::forget("otruyen:manga_detail:{$slug}");
        $mangaDetail = $otruyenService->getMangaDetail($slug);
        
        if (!$mangaDetail || ($mangaDetail['slug'] ?? '') !== $slug) {
            abort(404, 'Truyện không tồn tại hoặc dữ liệu không hợp lệ');
        }
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
                $relatedMangaDetail = $otruyenService->getMangaDetail($manga->slug);
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
    
    $chaptersForView = $mangaDetail['chapters'] ?? [];
    
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
        
        $mangaIds = $topMangas->pluck('manga_id')->toArray();
        $mangaMetadata = \App\Models\MangaMetadata::whereIn('id', $mangaIds)
            ->get()
            ->keyBy('id');
        
        $result = [];
        $rank = 1;
        foreach ($topMangas as $topManga) {
            $manga = $mangaMetadata->get($topManga->manga_id);
            if (!$manga) continue;
            
            $lastChapterData = null;
            
            if ($manga->last_chapter_number) {
                $chapterNumber = preg_replace('/^Chapter\s+/i', '', $manga->last_chapter_number);
                $chapterNumber = trim($chapterNumber);
                
                $lastChapterData = [
                    'name' => 'Chapter ' . $chapterNumber,
                    'slug' => 'chapter-' . $chapterNumber,
                    'updated_at' => $manga->last_synced_at ? formatVietnameseTime($manga->last_synced_at) : null,
                ];
            }
            
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
        ->whereNotNull('last_chapter_number')
        ->where('last_chapter_number', '!=', '')
        ->where(function($q) use ($keyword) {
            $q->where('title', 'LIKE', '%' . $keyword . '%')
              ->orWhere('slug', 'LIKE', '%' . $keyword . '%');
        })
        ->orderBy('views_count', 'desc')
        ->limit(8)
        ->get();
    
    $results = [];
    foreach ($query as $manga) {
        // BẮT BUỘC: Luôn dùng last_chapter_number từ metadata (chapter mới nhất thực sự)
        // KHÔNG dùng chapter từ database (chapters đã đọc)
        $chapterData = null;
        
        if ($manga->last_chapter_number) {
            $chapterNumber = preg_replace('/^Chapter\s+/i', '', $manga->last_chapter_number);
            $chapterNumber = trim($chapterNumber);
            
            $chapterData = [
                'id' => null,
                'slug' => 'chapter-' . $chapterNumber,
                'name' => 'Chapter ' . $chapterNumber,
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
        'mangaCover' => $mangaMetadata->cover_url ?? $mangaDetail['cover_url'] ?? asset('images/logo-dark.png'),
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
    
    $hiddenTagNames = ['Manga', 'Manhua', 'Manhwa'];
    
    $defaultTags = [];
    $remainingTags = [];
    
    foreach ($defaultTagOrder as $tagName) {
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
            $topMangas = \App\Models\MangaMetadata::where('is_active', true)
                ->where('views_count', '>', 0)
                ->orderBy('views_count', 'desc')
                ->limit(12)
                ->get();
            
            $mangaIds = $topMangas->pluck('id')->toArray();
            $mangaMetadata = $topMangas->keyBy('id');
            
            $result = [];
            foreach ($topMangas as $manga) {
                $chapters = [];
                
                if ($manga->last_chapter_number) {
                    $chapterNumber = preg_replace('/^Chapter\s+/i', '', $manga->last_chapter_number);
                    $chapterNumber = trim($chapterNumber);
                    
                    $chapters[] = [
                        'id' => null,
                        'slug' => 'chapter-' . $chapterNumber,
                        'name' => 'Chapter ' . $chapterNumber,
                        'releasedAt' => $manga->last_synced_at ? formatVietnameseTime($manga->last_synced_at) : null,
                    ];
                }
                
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
                
                $chapters = [];
                
                if ($manga->last_chapter_number) {
                    $chapterNumber = preg_replace('/^Chapter\s+/i', '', $manga->last_chapter_number);
                    $chapterNumber = trim($chapterNumber);
                    
                    $chapters[] = [
                        'id' => null,
                        'slug' => 'chapter-' . $chapterNumber,
                        'name' => 'Chapter ' . $chapterNumber,
                        'releasedAt' => $manga->last_synced_at ? formatVietnameseTime($manga->last_synced_at) : null,
                    ];
                }
                
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
    
    // Get Top Follow data
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
        
        $mangaIds = $topMangas->pluck('manga_id')->toArray();
        $mangaMetadata = \App\Models\MangaMetadata::whereIn('id', $mangaIds)
            ->get()
            ->keyBy('id');
        
        $result = [];
        $rank = 1;
        foreach ($topMangas as $topManga) {
            $manga = $mangaMetadata->get($topManga->manga_id);
            if (!$manga) continue;
            
            $lastChapterData = null;
            
            if ($manga->last_chapter_number) {
                $chapterNumber = preg_replace('/^Chapter\s+/i', '', $manga->last_chapter_number);
                $chapterNumber = trim($chapterNumber);
                
                $lastChapterData = [
                    'name' => 'Chapter ' . $chapterNumber,
                    'slug' => 'chapter-' . $chapterNumber,
                    'updated_at' => $manga->last_synced_at ? formatVietnameseTime($manga->last_synced_at) : null,
                ];
            }
            
            $viewsFormatted = '';
            $viewsCount = (int)($topManga->total_views ?? 0);
            if ($viewsCount >= 1000000) {
                $viewsFormatted = number_format($viewsCount / 1000000, 1) . 'M';
            } elseif ($viewsCount >= 1000) {
                $viewsFormatted = number_format($viewsCount / 1000, 1) . 'K';
            } else {
                $viewsFormatted = $viewsCount;
            }
            
            $result[] = [
                'rank' => $rank++,
                'slug' => $manga->slug,
                'title' => $manga->title,
                'cover_url' => $manga->cover_url ?: asset('images/pre-load1.png'),
                'rating' => $manga->rating ? (float)$manga->rating : 0,
                'views_count' => $topManga->total_views,
                'views_formatted' => $viewsFormatted,
                'last_chapter' => $lastChapterData,
            ];
        }
        
        return $result;
    };
    
    $topFollowDay = $getTopFollow('day');
    $topFollowWeek = $getTopFollow('week');
    $topFollowMonth = $getTopFollow('month');
    
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
        'topFollowDay' => $topFollowDay,
        'topFollowWeek' => $topFollowWeek,
        'topFollowMonth' => $topFollowMonth,
    ]);
})->name('news');

Route::get('/tin-tuc/{slug}', function ($slug) {
    $post = \App\Models\Post::where('slug', $slug)
        ->where('is_active', true)
        ->whereNotNull('published_at')
        ->where('published_at', '<=', now())
        ->firstOrFail();
    
    $featuredPost = \App\Models\Post::where('is_active', true)
        ->where('is_featured', true)
        ->whereNotNull('published_at')
        ->where('published_at', '<=', now())
        ->where('id', '!=', $post->id)
        ->inRandomOrder()
        ->first();
    
    $relatedPosts = \App\Models\Post::where('is_active', true)
        ->where('id', '!=', $post->id)
        ->whereNotNull('published_at')
        ->where('published_at', '<=', now())
        ->inRandomOrder()
        ->limit(10)
        ->get();
    
    return view('news.detail', [
        'post' => $post,
        'featuredPost' => $featuredPost,
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
            $totalItems = 0;
            
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
                        $jobData['logs'] = array_merge($jobData['logs'] ?? [], $newLogs ?? []);
                        $jobData['logs_sent_count'] = $logsSentCount + count($newLogs ?? []);
                        $jobData['processed_items'] = $processedItems;
                        $jobData['current_item'] = count($processedItems);
                        $jobData['total_items'] = $totalItems;
                        session()->put($jobKey, $jobData);
                        return;
                    }
                \Illuminate\Support\Facades\Cache::forget("otruyen:list:{$listType}:page:{$pageNum}:limit:24:shuffle:0");
                $pageData = $otruyenService->getMangaByList($listType, $pageNum, 24, false);
                if (!$pageData || empty($pageData['mangas'])) {
                    $consecutiveEmptyPages++;
                    $newLogs = [];
                    $newLogs[] = [
                        'message' => $pageData ? "Không có truyện nào trong trang {$pageNum}" : "Không thể lấy dữ liệu trang {$pageNum}",
                        'type' => $pageData ? 'warning' : 'error'
                    ];
                    $jobData['logs'] = array_merge($jobData['logs'] ?? [], $newLogs);
                    $jobData['logs_sent_count'] = $logsSentCount + count($newLogs);
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
                        $mangaMetadataBefore = \App\Models\MangaMetadata::where('slug', $slug)->first();
                        $wasExisting = $mangaMetadataBefore !== null;
                        
                        $mangaDetail = $otruyenService->getMangaDetail($slug, true);
                        
                        if ($mangaDetail) {
                            usleep(100000);
                            
                            $mangaMetadata = \App\Models\MangaMetadata::where('slug', $slug)->first();
    
                            if ($mangaMetadata) {
                                if (!$wasExisting) {
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
                                $processedCount++;
                            } else {
                                try {
                                    $otruyenService->saveOrUpdateManga($mangaDetail, ['slug' => $slug]);
                                    $mangaMetadata = \App\Models\MangaMetadata::where('slug', $slug)->first();
                                    if ($mangaMetadata) {
                                        $createdCount++;
                                        $newLogs[] = [
                                            'message' => "✓ Đã tạo mới (force save): {$mangaDetail['name']}",
                                            'type' => 'success'
                                        ];
                                        $processedCount++;
                                    } else {
                                        $skippedCount++;
                                        $newLogs[] = [
                                            'message' => "⚠ Đã lấy dữ liệu nhưng không lưu được: " . ($mangaDetail['name'] ?? $slug),
                                            'type' => 'warning'
                                        ];
                                    }
                                } catch (\Exception $saveEx) {
                                    $skippedCount++;
                                    $newLogs[] = [
                                        'message' => "✗ Lỗi lưu: " . ($mangaDetail['name'] ?? $slug) . " - " . $saveEx->getMessage(),
                                        'type' => 'error'
                                    ];
                                }
                            }
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
                    $pageNum++;
                    $currentMangaIndexInPage = 0;
                    $jobData['current_page'] = $pageNum;
                    $jobData['consecutive_empty_pages'] = 0;
                } else {
                    $jobData['current_page'] = $pageNum;
                    $jobData['current_manga_index_in_page'] = $mangaIndex;
                    $jobData['consecutive_empty_pages'] = 0;
                }
                
                $jobData['current_item'] = count($processedItems);
                $jobData['total_items'] = $totalItems;
                $jobData['total_pages'] = $pageNum;
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
                
                if ($consecutiveEmptyPages >= $maxConsecutiveEmpty) {
                    $jobData['status'] = 'completed';
                    $jobData['logs'][] = [
                        'message' => "✓ Hoàn tất crawl! Đã gặp {$maxConsecutiveEmpty} trang rỗng liên tiếp. Tổng cộng đã xử lý " . count($processedItems) . " truyện",
                        'type' => 'success'
                    ];
                } else {
                    $jobData['status'] = 'running';
                }
            } else {
                $pagesToProcess = $pages;
                $jobData['total_pages'] = count($pagesToProcess);
                
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
                            $mangaMetadataBefore = \App\Models\MangaMetadata::where('slug', $slug)->first();
                            $wasExisting = $mangaMetadataBefore !== null;
                            
                            $mangaDetail = $otruyenService->getMangaDetail($slug, true);
                            
                            if ($mangaDetail) {
                                usleep(100000);
                                
                                $mangaMetadata = \App\Models\MangaMetadata::where('slug', $slug)->first();
        
                                if ($mangaMetadata) {
                                    if (!$wasExisting) {
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
                                    $processedCount++;
                                } else {
                                    try {
                                        $otruyenService->saveOrUpdateManga($mangaDetail, ['slug' => $slug]);
                                        $mangaMetadata = \App\Models\MangaMetadata::where('slug', $slug)->first();
                                        if ($mangaMetadata) {
                                            $createdCount++;
                                            $newLogs[] = [
                                                'message' => "✓ Đã tạo mới (force save): {$mangaDetail['name']}",
                                                'type' => 'success'
                                            ];
                                            $processedCount++;
                                        } else {
                                            $skippedCount++;
                                            $newLogs[] = [
                                                'message' => "⚠ Đã lấy dữ liệu nhưng không lưu được: " . ($mangaDetail['name'] ?? $slug),
                                                'type' => 'warning'
                                            ];
                                        }
                                    } catch (\Exception $saveEx) {
                                        $skippedCount++;
                                        $newLogs[] = [
                                            'message' => "✗ Lỗi lưu: " . ($mangaDetail['name'] ?? $slug) . " - " . $saveEx->getMessage(),
                                            'type' => 'error'
                                        ];
                                    }
                                }
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
    
    Route::get('/comments', function () {
        $search = request()->input('search', '');
        $userSearch = request()->input('user_search', '');
        
        $query = \App\Models\MangaComment::with(['user', 'manga', 'chapter', 'parent'])
            ->orderBy('created_at', 'desc');
        
        if ($search) {
            $query->where('content', 'like', '%' . $search . '%');
        }
        
        if ($userSearch) {
            $query->whereHas('user', function($q) use ($userSearch) {
                $q->where('name', 'like', '%' . $userSearch . '%')
                  ->orWhere('email', 'like', '%' . $userSearch . '%');
            });
        }
        
        $comments = $query->paginate(20)->withQueryString();
        
        return view('admin.comments', compact('comments', 'search', 'userSearch'));
    })->name('admin.comments');
    
    Route::post('/comments/{id}/delete', function ($id) {
        $comment = \App\Models\MangaComment::findOrFail($id);
        
        $comment->replies()->delete();
        
        $comment->likes()->delete();
        
        $comment->delete();
        
        return response()->json(['status' => 'success', 'message' => 'Xóa bình luận thành công']);
    })->name('admin.comments.delete');
    
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
            // Site/Head settings
            'site_name' => \App\Models\Setting::get('site_name', 'HangTruyen'),
            'site_description' => \App\Models\Setting::get('site_description', ''),
            'site_keywords' => \App\Models\Setting::get('site_keywords', ''),
            'favicon_path' => \App\Models\Setting::get('favicon_path', '/images/favicon.png'),
            'logo_path' => \App\Models\Setting::get('logo_path', '/images/logo.png'),
            'logo_dark_path' => \App\Models\Setting::get('logo_dark_path', '/images/logo-dark.png'),
            'mini_logo_path' => \App\Models\Setting::get('mini_logo_path', '/images/mini-logo.png'),

            // Social + Google Tag
            'facebook_url' => \App\Models\Setting::get('facebook_url', ''),
            'twitter_url' => \App\Models\Setting::get('twitter_url', ''),
            'youtube_url' => \App\Models\Setting::get('youtube_url', ''),
            'github_url' => \App\Models\Setting::get('github_url', ''),
            'gmail_url' => \App\Models\Setting::get('gmail_url', ''),
            'gtag_code' => \App\Models\Setting::get('gtag_code', ''),
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
        $currentEffect = \App\Models\Setting::get('site_effect', 'none');
        
        return view('admin.settings', [
            'settings' => $settings,
            'effects' => $effects,
            'currentEffect' => $currentEffect,
        ]);
    })->name('admin.settings');

    Route::post('/settings/site', function (\Illuminate\Http\Request $request) {
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
                \App\Models\Setting::where('key', $key)->delete();
                return;
            }
            \App\Models\Setting::set($key, $value);
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

        $imageErrors = [];
        
        if ($request->hasFile('favicon')) {
            try {
                $file = $request->file('favicon');
                $targetPath = $imagesDir . DIRECTORY_SEPARATOR . 'favicon.png';
                if (file_exists($targetPath)) {
                    @unlink($targetPath);
                }
                if ($file->move($imagesDir, 'favicon.png')) {
                    if (file_exists($targetPath)) {
                        try {
                            \App\Models\Setting::set('favicon_path', '/images/favicon.png');
                            $saved = \App\Models\Setting::get('favicon_path');
                            if ($saved !== '/images/favicon.png') {
                                $imageErrors[] = 'Database không được cập nhật cho favicon_path';
                            }
                        } catch (\Exception $dbError) {
                            $imageErrors[] = 'Lỗi database khi lưu favicon_path: ' . $dbError->getMessage();
                            \Log::error('Database error saving favicon_path: ' . $dbError->getMessage());
                        }
                    } else {
                        $imageErrors[] = 'Không thể lưu file favicon.png';
                    }
                } else {
                    $imageErrors[] = 'Không thể di chuyển file favicon. Vui lòng kiểm tra quyền ghi thư mục public/images';
                }
            } catch (\Exception $e) {
                $imageErrors[] = 'Lỗi khi lưu favicon: ' . $e->getMessage();
                \Log::error('Error saving favicon: ' . $e->getMessage());
            }
        }
        
        if ($request->hasFile('logo')) {
            try {
                $file = $request->file('logo');
                $targetPath = $imagesDir . DIRECTORY_SEPARATOR . 'logo.png';
                if (file_exists($targetPath)) {
                    @unlink($targetPath);
                }
                if ($file->move($imagesDir, 'logo.png')) {
                    if (file_exists($targetPath)) {
                        try {
                            \App\Models\Setting::set('logo_path', '/images/logo.png');
                            $saved = \App\Models\Setting::get('logo_path');
                            if ($saved !== '/images/logo.png') {
                                $imageErrors[] = 'Database không được cập nhật cho logo_path';
                            }
                        } catch (\Exception $dbError) {
                            $imageErrors[] = 'Lỗi database khi lưu logo_path: ' . $dbError->getMessage();
                            \Log::error('Database error saving logo_path: ' . $dbError->getMessage());
                        }
                    } else {
                        $imageErrors[] = 'Không thể lưu file logo.png';
                    }
                } else {
                    $imageErrors[] = 'Không thể di chuyển file logo. Vui lòng kiểm tra quyền ghi thư mục public/images';
                }
            } catch (\Exception $e) {
                $imageErrors[] = 'Lỗi khi lưu logo: ' . $e->getMessage();
                \Log::error('Error saving logo: ' . $e->getMessage());
            }
        }
        
        if ($request->hasFile('logo_dark')) {
            try {
                $file = $request->file('logo_dark');
                $targetPath = $imagesDir . DIRECTORY_SEPARATOR . 'logo-dark.png';
                if (file_exists($targetPath)) {
                    @unlink($targetPath);
                }
                if ($file->move($imagesDir, 'logo-dark.png')) {
                    if (file_exists($targetPath)) {
                        try {
                            \App\Models\Setting::set('logo_dark_path', '/images/logo-dark.png');
                            $saved = \App\Models\Setting::get('logo_dark_path');
                            if ($saved !== '/images/logo-dark.png') {
                                $imageErrors[] = 'Database không được cập nhật cho logo_dark_path';
                            }
                        } catch (\Exception $dbError) {
                            $imageErrors[] = 'Lỗi database khi lưu logo_dark_path: ' . $dbError->getMessage();
                            \Log::error('Database error saving logo_dark_path: ' . $dbError->getMessage());
                        }
                    } else {
                        $imageErrors[] = 'Không thể lưu file logo-dark.png';
                    }
                } else {
                    $imageErrors[] = 'Không thể di chuyển file logo-dark. Vui lòng kiểm tra quyền ghi thư mục public/images';
                }
            } catch (\Exception $e) {
                $imageErrors[] = 'Lỗi khi lưu logo-dark: ' . $e->getMessage();
                \Log::error('Error saving logo-dark: ' . $e->getMessage());
            }
        }
        
        if ($request->hasFile('mini_logo')) {
            try {
                $file = $request->file('mini_logo');
                $targetPath = $imagesDir . DIRECTORY_SEPARATOR . 'mini-logo.png';
                if (file_exists($targetPath)) {
                    @unlink($targetPath);
                }
                if ($file->move($imagesDir, 'mini-logo.png')) {
                    if (file_exists($targetPath)) {
                        try {
                            \App\Models\Setting::set('mini_logo_path', '/images/mini-logo.png');
                            $saved = \App\Models\Setting::get('mini_logo_path');
                            if ($saved !== '/images/mini-logo.png') {
                                $imageErrors[] = 'Database không được cập nhật cho mini_logo_path';
                            }
                        } catch (\Exception $dbError) {
                            $imageErrors[] = 'Lỗi database khi lưu mini_logo_path: ' . $dbError->getMessage();
                            \Log::error('Database error saving mini_logo_path: ' . $dbError->getMessage());
                        }
                    } else {
                        $imageErrors[] = 'Không thể lưu file mini-logo.png';
                    }
                } else {
                    $imageErrors[] = 'Không thể di chuyển file mini-logo. Vui lòng kiểm tra quyền ghi thư mục public/images';
                }
            } catch (\Exception $e) {
                $imageErrors[] = 'Lỗi khi lưu mini-logo: ' . $e->getMessage();
                \Log::error('Error saving mini-logo: ' . $e->getMessage());
            }
        }
        
        if (!empty($imageErrors)) {
            return response()->json([
                'status' => 'error',
                'message' => implode('; ', $imageErrors),
            ], 422);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Đã lưu cấu hình Website thành công',
        ]);
    })->name('admin.settings.site');

    Route::post('/settings/site/images/reset', function (\Illuminate\Http\Request $request) {
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

        $targets = $validated['target'] === 'all'
            ? array_keys($map)
            : [$validated['target']];

        foreach ($targets as $key) {
            $fname = $map[$key] ?? null;
            if (!$fname) {
                continue;
            }

            $src = $defaultsDir . DIRECTORY_SEPARATOR . $fname;
            $dst = $imagesDir . DIRECTORY_SEPARATOR . $fname;

            if (!file_exists($src)) {
                if (!is_dir($defaultsDir)) {
                    @mkdir($defaultsDir, 0755, true);
                }
                if (file_exists($dst)) {
                    @copy($dst, $src);
                }
            }

            if (!file_exists($src)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Không tìm thấy ảnh mặc định để khôi phục. Vui lòng kiểm tra thư mục public/images/_defaults.',
                ], 422);
            }

            @copy($src, $dst);
        }

        \App\Models\Setting::set('favicon_path', '/images/favicon.png');
        \App\Models\Setting::set('logo_path', '/images/logo.png');
        \App\Models\Setting::set('logo_dark_path', '/images/logo-dark.png');
        \App\Models\Setting::set('mini_logo_path', '/images/mini-logo.png');

        return response()->json([
            'status' => 'success',
            'message' => 'Đã khôi phục ảnh mặc định thành công',
        ]);
    })->name('admin.settings.site.images.reset');

    Route::post('/settings/site/text/reset', function (\Illuminate\Http\Request $request) {
        $validated = $request->validate([
            'target' => 'required|string|in:description,keywords,all',
        ]);

        $targets = $validated['target'] === 'all'
            ? ['site_description', 'site_keywords']
            : ($validated['target'] === 'description' ? ['site_description'] : ['site_keywords']);

        \App\Models\Setting::whereIn('key', $targets)->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Đã khôi phục mặc định thành công',
        ]);
    })->name('admin.settings.site.text.reset');

    Route::post('/settings/effect', function (\Illuminate\Http\Request $request) {
        $effects = [
            'none',
            'bubbles',
            'firework',
            'fireworks_sound',
            'halloween',
            'hearts',
            'hoadao',
            'hoamai',
            'leaves',
            'lixi',
            'matrix',
            'quockhanh',
            'snow',
            'stars',
            'trungthu',
        ];

        $validated = $request->validate([
            'site_effect' => 'required|string|in:' . implode(',', $effects),
        ]);

        $value = $validated['site_effect'] ?? 'none';

        if ($value === 'none') {
            \App\Models\Setting::where('key', 'site_effect')->delete();
        } else {
            \App\Models\Setting::set('site_effect', $value);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Đã lưu cấu hình hiệu ứng thành công',
        ]);
    })->name('admin.settings.effect');
    
    Route::post('/settings/social', function () {
        $facebook = request()->input('facebook_url', '');
        $twitter = request()->input('twitter_url', '');
        $youtube = request()->input('youtube_url', '');
        $github = request()->input('github_url', '');
        $gmail = request()->input('gmail_url', '');
        
        \App\Models\Setting::set('facebook_url', $facebook);
        \App\Models\Setting::set('twitter_url', $twitter);
        \App\Models\Setting::set('youtube_url', $youtube);
        \App\Models\Setting::set('github_url', $github);
        \App\Models\Setting::set('gmail_url', $gmail);
        
        return response()->json([
            'status' => 'success',
            'message' => 'Đã lưu cấu hình thành công'
        ]);
    })->name('admin.settings.social');
    
    Route::post('/settings/social/clear', function () {
        \App\Models\Setting::whereIn('key', ['facebook_url', 'twitter_url', 'youtube_url', 'github_url', 'gmail_url'])->delete();
        
        return response()->json([
            'status' => 'success',
            'message' => 'Đã xóa cấu hình thành công'
        ]);
    })->name('admin.settings.social.clear');
    
    Route::post('/settings/gtag', function () {
        $gtagCode = request()->input('gtag_code', '');
        
        \App\Models\Setting::set('gtag_code', $gtagCode);
        
        return response()->json([
            'status' => 'success',
            'message' => 'Đã lưu Google Tag thành công'
        ]);
    })->name('admin.settings.gtag');
    
    Route::post('/settings/gtag/clear', function () {
        \App\Models\Setting::where('key', 'gtag_code')->delete();
        
        return response()->json([
            'status' => 'success',
            'message' => 'Đã xóa Google Tag thành công'
        ]);
    })->name('admin.settings.gtag.clear');
    
    Route::get('/posts', function () {
        $posts = \App\Models\Post::orderBy('created_at', 'desc')->paginate(20);
        return view('admin.posts', compact('posts'));
    })->name('admin.posts');
    
    Route::get('/posts/create', function () {
        return view('admin.posts-create');
    })->name('admin.posts.create');
    
    Route::post('/posts', function (\Illuminate\Http\Request $request) {
        $request->validate([
            'title' => 'required|max:255',
            'slug' => 'required|unique:posts|max:255',
            'content' => 'required',
            'description' => 'nullable',
            'image' => 'nullable|string',
            'author' => 'nullable|max:255',
        ]);
        
        \App\Models\Post::create([
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
    })->name('admin.posts.store');
    
    Route::get('/posts/{id}/edit', function ($id) {
        $post = \App\Models\Post::findOrFail($id);
        return view('admin.posts-edit', compact('post'));
    })->name('admin.posts.edit');
    
    Route::put('/posts/{id}', function (\Illuminate\Http\Request $request, $id) {
        $post = \App\Models\Post::findOrFail($id);
        
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
    })->name('admin.posts.update');
    
    Route::delete('/posts/{id}', function ($id) {
        $post = \App\Models\Post::findOrFail($id);
        $post->delete();
        
        return response()->json([
            'status' => 'success',
            'message' => 'Xóa bài viết thành công'
        ]);
    })->name('admin.posts.destroy');
    
    Route::post('/posts/upload-image', function (\Illuminate\Http\Request $request) {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);
        
        $image = $request->file('image');
        $filename = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
        $path = $image->storeAs('posts', $filename, 'public');
        
        return response()->json([
            'url' => asset('storage/' . $path)
        ]);
    })->name('admin.posts.upload-image');
});

Route::get('/sitemap.xml', function () {
    $sitemap = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    $sitemap .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
    
    $baseUrl = url('/');
    
    $sitemap .= '  <url>' . "\n";
    $sitemap .= '    <loc>' . $baseUrl . '</loc>' . "\n";
    $sitemap .= '    <lastmod>' . date('Y-m-d') . '</lastmod>' . "\n";
    $sitemap .= '    <changefreq>daily</changefreq>' . "\n";
    $sitemap .= '    <priority>1.0</priority>' . "\n";
    $sitemap .= '  </url>' . "\n";
    
    $mangas = \App\Models\MangaMetadata::where('is_active', true)
        ->whereNotNull('slug')
        ->orderBy('updated_at', 'desc')
        ->limit(10000)
        ->get();
    
    foreach ($mangas as $manga) {
        $sitemap .= '  <url>' . "\n";
        $sitemap .= '    <loc>' . $baseUrl . '/truyen-tranh/' . $manga->slug . '</loc>' . "\n";
        $sitemap .= '    <lastmod>' . $manga->updated_at->format('Y-m-d') . '</lastmod>' . "\n";
        $sitemap .= '    <changefreq>weekly</changefreq>' . "\n";
        $sitemap .= '    <priority>0.8</priority>' . "\n";
        $sitemap .= '  </url>' . "\n";
        
        $chapters = $manga->chapters()
            ->whereNotNull('chapter_slug')
            ->orderBy('updated_at', 'desc')
            ->limit(100)
            ->get();
        
        foreach ($chapters as $chapter) {
            $sitemap .= '  <url>' . "\n";
            $sitemap .= '    <loc>' . $baseUrl . '/truyen-tranh/' . $manga->slug . '/' . $chapter->chapter_slug . '</loc>' . "\n";
            $sitemap .= '    <lastmod>' . $chapter->updated_at->format('Y-m-d') . '</lastmod>' . "\n";
            $sitemap .= '    <changefreq>monthly</changefreq>' . "\n";
            $sitemap .= '    <priority>0.6</priority>' . "\n";
            $sitemap .= '  </url>' . "\n";
        }
    }
    
    $sitemap .= '</urlset>';
    
    return response($sitemap, 200)
        ->header('Content-Type', 'application/xml');
})->name('sitemap');

Route::get('/robots.txt', function () {
    $content = "User-agent: *\n";
    $content .= "Allow: /\n";
    $content .= "Disallow: /admin/\n";
    $content .= "Disallow: /api/\n";
    $content .= "Disallow: /account/\n";
    $content .= "\n";
    $content .= "Sitemap: " . url('/sitemap.xml');
    
    return response($content, 200)
        ->header('Content-Type', 'text/plain');
});

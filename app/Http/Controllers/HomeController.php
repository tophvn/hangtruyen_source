<?php

namespace App\Http\Controllers;

use App\Services\OTruyenService;
use App\Models\MangaMetadata;
use App\Models\MangaComment;
use App\Models\MangaDailyView;
use App\Models\Setting;
use App\Models\Post;
use DB;

class HomeController extends Controller
{
    protected $otruyenService;

    public function __construct(OTruyenService $otruyenService)
    {
        $this->otruyenService = $otruyenService;
    }

    public function index()
    {
        $recentlyUpdated = $this->otruyenService->getRecentlyUpdated(1, 30);

        if (isset($recentlyUpdated['mangas']) && is_array($recentlyUpdated['mangas'])) {
            $slugs = array_column($recentlyUpdated['mangas'], 'slug');
            $mangaMetadataMap = MangaMetadata::whereIn('slug', $slugs)
                ->get()
                ->keyBy('slug');

            foreach ($recentlyUpdated['mangas'] as &$manga) {
                $mangaMetadata = $mangaMetadataMap->get($manga['slug'] ?? '');
                if ($mangaMetadata) {
                    $manga['views'] = (int) ($mangaMetadata->views_count ?? 0);
                    $manga['rating'] = (float) ($mangaMetadata->rating ?? 0);
                } else {
                    $manga['views'] = 0;
                    $manga['rating'] = 0;
                }
            }
            unset($manga);
        }

        $suggestedMangas = $this->getSuggestedMangas($recentlyUpdated);
        $trendingMangas = $this->getTrendingMangas();
        $sapRaMatMangas = $this->getSapRaMatMangas();
        $hoanThanhMangas = $this->getHoanThanhMangas();
        $topComments = $this->getTopComments();

        $topFollowDay = $this->getTopFollow('day');
        $topFollowWeek = $this->getTopFollow('week');
        $topFollowMonth = $this->getTopFollow('month');

        $blogPosts = Post::where('is_active', true)
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
    }

    protected function getSuggestedMangas($recentlyUpdated)
    {
        $suggestedMangas = MangaMetadata::whereNotNull('last_chapter_number')
            ->where('last_chapter_number', '!=', '')
            ->whereNotNull('slug')
            ->where('slug', '!=', '')
            ->whereNotNull('title')
            ->where('title', '!=', '')
            ->inRandomOrder()
            ->limit(24)
            ->get()
            ->map(function ($manga) {
                $lastChapter = $manga->chapters()
                    ->orderBy('id', 'desc')
                    ->first();

                $chapterData = [];
                if ($lastChapter) {
                    $chapterName = $lastChapter->chapter_name;
                    if (!preg_match('/^Chapter\s+/i', $chapterName)) {
                        $chapterName = 'Chapter ' . $chapterName;
                    }
                    $chapterData = [
                        [
                            'id' => $lastChapter->id,
                            'slug' => $lastChapter->chapter_slug,
                            'name' => formatChapterNameForDisplay($manga->last_chapter_number ?: $lastChapter->chapter_name),
                            'releasedAt' => $manga->last_synced_at ? formatVietnameseTime($manga->last_synced_at) : ($lastChapter->updated_at ? formatVietnameseTime($lastChapter->updated_at) : null),
                        ],
                    ];
                } elseif ($manga->last_chapter_number) {
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
                    'posterPath' => proxyImageUrl($manga->cover_url),
                    'avgVote' => $manga->rating ? (float) $manga->rating : 0,
                    'chapters' => $chapterData,
                ];
            })
            ->filter(function ($manga) {
                return !empty($manga['chapters']);
            })
            ->values()
            ->toArray();

        if (empty($suggestedMangas) && isset($recentlyUpdated['mangas']) && !empty($recentlyUpdated['mangas'])) {
            $suggestedMangas = array_slice(array_map(function ($manga) {
                return [
                    'slug' => $manga['slug'] ?? '',
                    'title' => $manga['title'] ?? 'Đang cập nhật',
                    'posterPath' => proxyImageUrl($manga['posterPath'] ?? null),
                    'avgVote' => isset($manga['avgVote']) ? (float) $manga['avgVote'] : 0,
                    'chapters' => isset($manga['chapters']) && is_array($manga['chapters']) && !empty($manga['chapters'])
                        ? array_slice($manga['chapters'], 0, 1)
                        : [],
                ];
            }, $recentlyUpdated['mangas']), 0, 24);
        }

        return $suggestedMangas;
    }

    protected function getTrendingMangas()
    {
        $trendingSlugs = json_decode(Setting::get('trending_mangas', '[]'), true) ?? [];
        $trendingMangas = [];

        if (!empty($trendingSlugs)) {
            $trendingMangasData = MangaMetadata::whereIn('slug', $trendingSlugs)
                ->get()
                ->sortBy(function ($manga) use ($trendingSlugs) {
                    return array_search($manga->slug, $trendingSlugs);
                })
                ->values();

            foreach ($trendingMangasData as $manga) {
                $latestChapter = $manga->chapters()
                    ->whereNotNull('chapter_slug')
                    ->orderBy('id', 'desc')
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
                        'name' => formatChapterNameForDisplay($manga->last_chapter_number ?: $latestChapter->chapter_name),
                        'releasedAt' => $manga->last_synced_at ? formatVietnameseTime($manga->last_synced_at) : ($latestChapter->updated_at ? formatVietnameseTime($latestChapter->updated_at) : null),
                    ];

                    if ($chapterNumber && is_numeric($chapterNumber) && (int) $chapterNumber > 1) {
                        $prevChapterNumber = (int) $chapterNumber - 1;
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

                    if (is_numeric($chapterNumber) && (int) $chapterNumber > 1) {
                        $prevChapterNumber = (int) $chapterNumber - 1;
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
                $viewsCount = (int) ($manga->views_count ?? 0);
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
                    'cover_url' => proxyImageUrl($manga->cover_url),
                    'rating' => $manga->rating ? (float) $manga->rating : 0,
                    'views_count' => $viewsCount,
                    'views_formatted' => $viewsFormatted,
                    'chapters' => array_slice($chapters, 0, 2),
                ];
            }
        }

        return $trendingMangas;
    }

    protected function getSapRaMatMangas()
    {
        return MangaMetadata::where('is_active', true)
            ->where(function ($query) {
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
            ->map(function ($manga) {
                $lastChapter = $manga->chapters()
                    ->orderBy('id', 'desc')
                    ->first();

                $chapters = [];
                if ($lastChapter) {
                    $chapters[] = [
                        'id' => $lastChapter->id,
                        'slug' => $lastChapter->chapter_slug,
                        'name' => formatChapterNameForDisplay($manga->last_chapter_number ?: $lastChapter->chapter_name),
                        'releasedAt' => $manga->last_synced_at ? formatVietnameseTime($manga->last_synced_at) : ($lastChapter->updated_at ? formatVietnameseTime($lastChapter->updated_at) : null),
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
                    'posterPath' => proxyImageUrl($manga->cover_url),
                    'avgVote' => $manga->rating ? (float) $manga->rating : 0,
                    'chapters' => $chapters,
                ];
            })
            ->toArray();
    }

    protected function getHoanThanhMangas()
    {
        return MangaMetadata::where('is_active', true)
            ->where('status', 'completed')
            ->whereNotNull('slug')
            ->whereNotNull('title')
            ->whereNotNull('last_chapter_number')
            ->where('last_chapter_number', '!=', '')
            ->inRandomOrder()
            ->limit(24)
            ->get()
            ->map(function ($manga) {
                $lastChapter = $manga->chapters()
                    ->orderBy('id', 'desc')
                    ->first();

                $chapters = [];
                if ($lastChapter) {
                    $chapters[] = [
                        'id' => $lastChapter->id,
                        'slug' => $lastChapter->chapter_slug,
                        'name' => formatChapterNameForDisplay($manga->last_chapter_number ?: $lastChapter->chapter_name),
                        'releasedAt' => $manga->last_synced_at ? formatVietnameseTime($manga->last_synced_at) : ($lastChapter->updated_at ? formatVietnameseTime($lastChapter->updated_at) : null),
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
                    'posterPath' => proxyImageUrl($manga->cover_url),
                    'avgVote' => $manga->rating ? (float) $manga->rating : 0,
                    'chapters' => $chapters,
                ];
            })
            ->toArray();
    }

    protected function getTopComments()
    {
        return MangaComment::whereNull('parent_id')
            ->with(['user', 'manga'])
            ->has('user')
            ->has('manga')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->filter(function ($comment) {
                return $comment->user && $comment->manga;
            })
            ->map(function ($comment) {
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
                        'cover_url' => proxyImageUrl($manga->cover_url),
                    ],
                ];
            })
            ->values();
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
                ->orderBy('id', 'desc')
                ->first();

            $lastChapterData = null;
            if ($lastChapter) {
                $lastChapterData = [
                    'name' => formatChapterNameForDisplay($manga->last_chapter_number ?: $lastChapter->chapter_name),
                    'slug' => $lastChapter->chapter_slug,
                    'updated_at' => $manga->last_synced_at ? formatVietnameseTime($manga->last_synced_at) : ($lastChapter->updated_at ? formatVietnameseTime($lastChapter->updated_at) : null),
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
                'cover_url' => proxyImageUrl($manga->cover_url),
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
}

<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\MangaMetadata;
use App\Models\MangaDailyView;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PostController extends Controller
{
    public function index(Request $request)
    {
        $currentPage = max(1, (int) $request->get('page', 1));
        $perPage = 6;

        $topFollowDay = $this->getTopFollow('day');
        $topFollowWeek = $this->getTopFollow('week');
        $topFollowMonth = $this->getTopFollow('month');

        $featuredPost = Post::where('is_active', true)
            ->where('is_featured', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->orderBy('published_at', 'desc')
            ->first();

        $query = Post::where('is_active', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());

        if ($featuredPost) {
            $query->where('id', '!=', $featuredPost->id);
        }

        $posts = $query->orderBy('published_at', 'desc')
            ->paginate($perPage)
            ->appends(request()->query());

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

        $otherNews = collect($posts->items())->map(function ($post) {
            return [
                'slug' => $post->slug,
                'title' => $post->title,
                'image' => $post->image ?? asset('images/pre-load1.png'),
                'description' => $post->description ?? '',
                'author' => $post->author ?? 'Admin',
                'date' => $post->published_at ? $post->published_at->format('d/m/Y') : '',
            ];
        })->toArray();

        return view('news.index', [
            'currentPage' => $posts->currentPage(),
            'totalPages' => $posts->lastPage(),
            'featuredNews' => $featuredNews,
            'otherNews' => $otherNews,
            'topFollowDay' => $topFollowDay,
            'topFollowWeek' => $topFollowWeek,
            'topFollowMonth' => $topFollowMonth,
            'posts' => $posts,
        ]);
    }

    public function detail($slug)
    {
        $post = Post::where('slug', $slug)
            ->where('is_active', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->firstOrFail();

        $featuredPost = Post::where('is_active', true)
            ->where('is_featured', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->where('id', '!=', $post->id)
            ->inRandomOrder()
            ->first();

        $relatedPosts = Post::where('is_active', true)
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
            } elseif ($manga->last_chapter_number) {
                $lastChapterData = [
                    'name' => 'Chapter ' . $manga->last_chapter_number,
                    'slug' => 'chapter-' . $manga->last_chapter_number,
                    'updated_at' => $manga->last_synced_at ? formatVietnameseTime($manga->last_synced_at) : null,
                ];
            }

            $viewsCount = (int) ($topManga->total_views ?? 0);
            $viewsFormatted = '';
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
                'cover_url' => proxyImageUrl($manga->cover_url),
                'rating' => $manga->rating ? (float) $manga->rating : 0,
                'views_count' => $topManga->total_views,
                'views_formatted' => $viewsFormatted,
                'last_chapter' => $lastChapterData,
            ];
        }

        return $result;
    }
}

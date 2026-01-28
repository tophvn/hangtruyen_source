<?php

namespace App\Http\Controllers;

use App\Models\MangaMetadata;
use App\Models\Category;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $keyword = trim($request->get('keyword', ''));
        $page = max(1, (int) $request->get('page', 1));
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

        $query = MangaMetadata::where('is_active', true);

        if (!empty($keyword)) {
            $query->where(function ($q) use ($keyword) {
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
                $query->where(function ($q) use ($categorySearchValues) {
                    foreach ($categorySearchValues as $value) {
                        $q->orWhereJsonContains('tags', $value);
                    }
                });
            }
        }

        if (!empty($tags)) {
            $tagCategoryIds = array_filter(array_map('intval', $tags));
            $tagCategories = Category::whereIn('id', $tagCategoryIds)->get();

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
                $query->where(function ($q) use ($tagSearchValues) {
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

                if ($chapterNumber && is_numeric($chapterNumber) && (int) $chapterNumber > 1) {
                    $prevChapterNumber = (int) $chapterNumber - 1;
                    $prevChapterSlug = 'chapter-' . $prevChapterNumber;
                    $prevChapterName = 'Chapter ' . $prevChapterNumber;

                    $chapterData[] = [
                        'id' => null,
                        'slug' => $prevChapterSlug,
                        'name' => $prevChapterName,
                        'number' => (string) $prevChapterNumber,
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

                    if (is_numeric($chapterNumber) && (int) $chapterNumber > 1) {
                        $prevChapterNumber = (int) $chapterNumber - 1;
                        $prevChapterSlug = 'chapter-' . $prevChapterNumber;
                        $prevChapterName = 'Chapter ' . $prevChapterNumber;

                        $chapterData[] = [
                            'id' => null,
                            'slug' => $prevChapterSlug,
                            'name' => $prevChapterName,
                            'number' => (string) $prevChapterNumber,
                            'updated_at' => $manga->last_synced_at ? $manga->last_synced_at->toDateTimeString() : null,
                            'releasedAt' => $manga->last_synced_at ? $manga->last_synced_at->diffForHumans() : null,
                        ];
                    }
                }
            }

            $results[] = [
                'slug' => $manga->slug,
                'title' => $manga->title,
                'posterPath' => proxyImageUrl($manga->cover_url),
                'avgVote' => (float) ($manga->rating ?? 0),
                'countView' => (int) ($manga->views_count ?? 0),
                'chapters' => $chapterData,
            ];
        }

        $defaultTagOrder = [
            '16+',
            'Action',
            'Adult',
            'Adventure',
            'Anime',
            'Chuyển Sinh',
            'Cổ Đại',
            'Comedy',
            'Comic',
            'Cooking',
            'Doujinshi',
            'Drama',
            'Đam Mỹ',
            'Ecchi',
            'Fantasy',
            'Gender Bender',
            'Harem',
            'Historical',
            'Horror',
            'Josei',
            'Live action',
            'Manga',
            'Manhua'
        ];

        $allCategories = Category::where('is_active', true)->get();

        $hiddenTagNames = ['Manga', 'Manhua', 'Manhwa'];

        $defaultTags = [];
        $remainingTags = [];

        foreach ($defaultTagOrder as $tagName) {
            if (in_array($tagName, $hiddenTagNames)) {
                continue;
            }

            $category = $allCategories->first(function ($cat) use ($tagName) {
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

        usort($remainingTags, function ($a, $b) use ($allCategories) {
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
    }

    public function api(Request $request)
    {
        $keyword = trim($request->get('keyword', ''));

        if (empty($keyword)) {
            return response()->json([
                'status' => 'success',
                'data' => []
            ]);
        }

        $query = MangaMetadata::where('is_active', true)
            ->whereNotNull('last_chapter_number')
            ->where('last_chapter_number', '!=', '')
            ->where(function ($q) use ($keyword) {
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
                ->orderBy('chapter_name', 'desc')
                ->first();

            $chapterData = null;
            if ($latestChapter) {
                $chapterData = [
                    'id' => $latestChapter->id,
                    'slug' => $latestChapter->chapter_slug,
                    'name' => formatChapterNameForDisplay($latestChapter->chapter_name),
                ];
            } elseif ($manga->last_chapter_number) {
                $chapterNumber = preg_replace('/^Chapter\s+/i', '', $manga->last_chapter_number);
                $chapterData = [
                    'id' => null,
                    'slug' => 'chapter-' . $chapterNumber,
                    'name' => 'Chapter ' . $chapterNumber,
                ];
            }

            if ($chapterData) {
                $results[] = [
                    'title' => $manga->title,
                    'posterPath' => proxyImageUrl($manga->cover_url),
                    'slug' => '/truyen-tranh/' . $manga->slug,
                    'chapters' => [$chapterData]
                ];
            }
        }

        return response()->json([
            'status' => 'success',
            'data' => $results
        ]);
    }
}

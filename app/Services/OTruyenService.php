<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use App\Models\MangaMetadata;
use App\Models\Category;

class OTruyenService
{
    private $baseUrl = 'https://otruyenapi.com/v1/api';
    private $timeout = 10;

    public function getRecentlyUpdated($page = 1, $perPage = 30, $minCount = 24)
    {
        $cacheKey = "otruyen:recently_updated:{$page}:{$minCount}";
        
        return Cache::remember($cacheKey, 600, function () use ($page, $minCount) {
            try {
                $allMangas = [];
                $currentPage = $page;
                $maxPages = 3;
                $lastData = null;
                
                while (count($allMangas) < $minCount && $currentPage <= $maxPages) {
                    $pageCacheKey = "otruyen:page:{$currentPage}";
                    $pageData = Cache::remember($pageCacheKey, 600, function () use ($currentPage) {
                        $url = "{$this->baseUrl}/danh-sach/truyen-moi";
                        $params = ['page' => $currentPage];
                        
                        $response = Http::timeout($this->timeout)
                            ->withoutVerifying()
                            ->get($url, $params);
                        
                        if ($response->successful()) {
                            return $response->json();
                        }
                        
                        return null;
                    });
                    
                    if ($pageData) {
                        $lastData = $pageData;
                        $items = $pageData['data']['items'] ?? $pageData['data'] ?? [];
                        
                        if (is_array($items) && count($items) > 0) {
                            $transformed = $this->transformMangas($items);
                            
                            foreach ($transformed as $manga) {
                                if (count($manga['chapters'] ?? []) > 0) {
                                    $allMangas[] = $manga;
                                    if (count($allMangas) >= $minCount) {
                                        break 2;
                                    }
                                }
                            }
                            
                            if (empty($transformed) || count($items) < 24) {
                                break;
                            }
                        } else {
                            break;
                        }
                    } else {
                        break;
                    }
                    
                    $currentPage++;
                }
                
                if (count($allMangas) > 0) {
                    return [
                        'mangas' => array_slice($allMangas, 0, $minCount),
                        'metadata' => $this->extractMetadata($lastData ?? []),
                    ];
                }
                
                return ['mangas' => [], 'metadata' => null];
            } catch (\Exception $e) {
                return ['mangas' => [], 'metadata' => null];
            }
        });
    }

    protected function transformMangas($mangas)
    {
        return array_map(function ($manga) {
            $slug = $manga['slug'] ?? $this->generateSlug($manga['name'] ?? '', $manga['_id'] ?? '');
            
            $chapters = $this->getLatestChapters($manga);
            $updatedAt = $manga['updated_at'] ?? $manga['updatedAt'] ?? null;
            
            return [
                'id' => $manga['_id'] ?? null,
                'slug' => $slug,
                'name' => $manga['name'] ?? '',
                'cover_url' => $this->getImageUrl($manga['thumb_url'] ?? ''),
                'cover_mobile_url' => $this->getImageUrl($manga['thumb_url'] ?? ''),
                'chapters' => $chapters,
                'views' => $manga['views'] ?? $manga['views_count'] ?? null,
                'rating' => $manga['rating'] ?? null,
                'updated_at' => $updatedAt,
            ];
        }, $mangas);
    }

    protected function getImageUrl($thumbUrl)
    {
        if (empty($thumbUrl)) {
            return '';
        }
        
        if (strpos($thumbUrl, 'http') === 0) {
            return $thumbUrl;
        }
        
        if (strpos($thumbUrl, '/') === 0) {
            return 'https://img.otruyenapi.com' . $thumbUrl;
        }
        
        return 'https://img.otruyenapi.com/uploads/comics/' . $thumbUrl;
    }

    protected function getLatestChapters($manga)
    {
        $chapters = [];
        $updatedAt = $manga['updated_at'] ?? $manga['updatedAt'] ?? null;
        
        if (isset($manga['chaptersLatest']) && is_array($manga['chaptersLatest']) && count($manga['chaptersLatest']) > 0) {
            foreach ($manga['chaptersLatest'] as $chapter) {
                $chapterName = $chapter['chapter_name'] ?? null;
                $chapters[] = [
                    'number' => $chapterName,
                    'name' => 'Chapter ' . ($chapterName ?? ''),
                    'id' => null,
                    'updated_at' => $updatedAt,
                ];
            }
            
            if (count($chapters) == 1) {
                $chapterName = $chapters[0]['number'];
                if (is_numeric($chapterName)) {
                    $currentNumber = (int)$chapterName;
                    if ($currentNumber > 1) {
                        $prevNumber = $currentNumber - 1;
                        array_unshift($chapters, [
                            'number' => (string)$prevNumber,
                            'name' => 'Chapter ' . $prevNumber,
                            'id' => null,
                            'updated_at' => $updatedAt,
                        ]);
                    }
                }
            }
        } elseif (isset($manga['chapter_latest'])) {
            $chapterName = $manga['chapter_latest']['chapter_name'] ?? null;
            $chapters[] = [
                'number' => $chapterName,
                'name' => 'Chapter ' . ($chapterName ?? ''),
                'id' => $manga['chapter_latest']['chapter_id'] ?? null,
                'updated_at' => $updatedAt,
            ];
            
            if (is_numeric($chapterName) && (int)$chapterName > 1) {
                $prevNumber = (int)$chapterName - 1;
                array_unshift($chapters, [
                    'number' => (string)$prevNumber,
                    'name' => 'Chapter ' . $prevNumber,
                    'id' => null,
                    'updated_at' => $updatedAt,
                ]);
            }
        } elseif (isset($manga['chapters']) && is_array($manga['chapters']) && count($manga['chapters']) > 0) {
            $firstServer = $manga['chapters'][0];
            if (isset($firstServer['server_data']) && is_array($firstServer['server_data']) && count($firstServer['server_data']) > 0) {
                foreach (array_slice($firstServer['server_data'], 0, 2) as $chapter) {
                    $chapters[] = [
                        'number' => $chapter['chapter_name'] ?? null,
                        'name' => 'Chapter ' . ($chapter['chapter_name'] ?? ''),
                        'id' => null,
                        'updated_at' => $updatedAt,
                    ];
                }
            }
        }
        
        return array_slice($chapters, 0, 2);
    }

    protected function extractMetadata($data)
    {
        $pagination = $data['data']['params']['pagination'] ?? $data['data']['pagination'] ?? $data['pagination'] ?? [];
        
        return [
            'total_count' => $pagination['totalItems'] ?? $pagination['total_items'] ?? $data['total'] ?? $data['totalCount'] ?? 0,
            'total_pages' => $pagination['pageRanges'] ?? $pagination['total_pages'] ?? $data['totalPage'] ?? $data['totalPages'] ?? 1,
            'current_page' => $pagination['currentPage'] ?? $pagination['current_page'] ?? $data['currentPage'] ?? $data['current_page'] ?? 1,
            'per_page' => $pagination['totalItemsPerPage'] ?? $pagination['itemsPerPage'] ?? $data['itemsPerPage'] ?? $data['per_page'] ?? 24,
        ];
    }

    protected function generateSlug($name, $id)
    {
        $slug = Str::slug($name);
        return $slug ?: "manga-{$id}";
    }

    public function getMangaDetail($slug)
    {
        $cacheKey = "otruyen:manga_detail:{$slug}";
        
        return Cache::remember($cacheKey, 600, function () use ($slug) {
            try {
                $response = Http::timeout($this->timeout)
                    ->withoutVerifying()
                    ->get("{$this->baseUrl}/truyen-tranh/{$slug}");
                
                if ($response->successful()) {
                    $data = $response->json();
                    
                    if (isset($data['data']['item'])) {
                        $transformed = $this->transformMangaDetail($data);
                        $this->saveOrUpdateManga($transformed, $data['data']['item']);
                        return $transformed;
                    }
                }
                
                return null;
            } catch (\Exception $e) {
                return null;
            }
        });
    }

    protected function saveOrUpdateManga($transformed, $rawItem)
    {
        try {
            // Tìm manga theo slug, đảm bảo luôn lấy record mới nhất nếu có duplicate
            $manga = MangaMetadata::where('slug', $transformed['slug'])
                ->orderBy('id', 'desc')
                ->first();
            
            $chaptersCount = count($transformed['chapters'] ?? []);
            $lastChapterNumber = $chaptersCount > 0 ? $transformed['chapters'][0]['name'] : null;
            $authorString = is_array($transformed['author'] ?? []) 
                ? implode(', ', $transformed['author']) 
                : ($transformed['author'] ?? '');
            $tagsArray = array_map(function($tag) {
                return $tag['name'] ?? '';
            }, $transformed['tags'] ?? []);
            
            // Thêm type (Manga, Manhua, Manhwa) vào tags để có thể filter
            // QUAN TRỌNG: Luôn ưu tiên giữ nguyên type từ database (nếu có)
            // Chỉ thêm type mới từ API nếu database chưa có type
            $existingType = null;
            if ($manga && is_array($manga->tags)) {
                foreach ($manga->tags as $tag) {
                    $tagName = is_string($tag) ? $tag : ($tag['name'] ?? '');
                    $tagNameLower = strtolower($tagName);
                    if (in_array($tagNameLower, ['manga', 'manhua', 'manhwa'])) {
                        $existingType = $tagName; // Giữ nguyên case từ database
                        break;
                    }
                }
            }
            
            // Nếu database đã có type, giữ nguyên (không update từ API)
            if ($existingType) {
                // Đảm bảo type cũ có trong tagsArray
                if (!in_array($existingType, $tagsArray)) {
                    $tagsArray[] = $existingType;
                }
            } else {
                // Nếu database chưa có type, thêm từ API (nếu có và không phải mặc định)
                $typeIsDefault = $transformed['type_is_default'] ?? false;
                if (!$typeIsDefault && isset($transformed['type']['name']) && !empty($transformed['type']['name'])) {
                    $typeName = $transformed['type']['name'];
                    if (!in_array($typeName, $tagsArray)) {
                        $tagsArray[] = $typeName;
                    }
                }
            }
            
            $statusEnum = $rawItem['status'] ?? 'ongoing';
            
            $originNameArray = is_array($transformed['origin_name'] ?? []) 
                ? $transformed['origin_name'] 
                : [];
            
            $dataToSave = [
                'source_type' => 'otruyen',
                'source_identifier' => $transformed['id'] ?? $transformed['slug'],
                'title' => $transformed['name'],
                'origin_name' => $originNameArray,
                'description' => strip_tags($transformed['description'] ?? ''),
                'cover_url' => $transformed['cover_url'],
                'author' => $authorString,
                'status' => $statusEnum,
                'tags' => $tagsArray,
                'chapters_count' => $chaptersCount,
                'last_chapter_number' => $lastChapterNumber,
                'last_synced_at' => now(),
                'is_active' => true,
            ];
            
            if ($manga) {
                $needsUpdate = false;
                
                if ($manga->title != $dataToSave['title']) $needsUpdate = true;
                if ($manga->description != $dataToSave['description']) $needsUpdate = true;
                if ($manga->cover_url != $dataToSave['cover_url']) $needsUpdate = true;
                if ($manga->author != $dataToSave['author']) $needsUpdate = true;
                if ($manga->status != $dataToSave['status']) $needsUpdate = true;
                if ($manga->chapters_count != $dataToSave['chapters_count']) $needsUpdate = true;
                if ($manga->last_chapter_number != $dataToSave['last_chapter_number']) $needsUpdate = true;
                
                $existingTags = is_array($manga->tags) ? $manga->tags : [];
                // So sánh tags không phụ thuộc vào thứ tự
                $existingTagsSorted = array_values(array_unique($existingTags));
                sort($existingTagsSorted);
                $tagsArraySorted = array_values(array_unique($tagsArray));
                sort($tagsArraySorted);
                if ($existingTagsSorted !== $tagsArraySorted) {
                    $needsUpdate = true;
                }
                
                $existingOriginNames = is_array($manga->origin_name) ? $manga->origin_name : [];
                if (json_encode($existingOriginNames) != json_encode($originNameArray)) $needsUpdate = true;
                
                if ($needsUpdate) {
                    $manga->update($dataToSave);
                } else {
                    $manga->touch();
                }
            } else {
                // Chỉ tạo mới nếu chưa có record với slug này
                // Kiểm tra lại để tránh race condition
                $existingManga = MangaMetadata::where('slug', $transformed['slug'])->first();
                if (!$existingManga) {
                    MangaMetadata::create(array_merge($dataToSave, [
                        'slug' => $transformed['slug'],
                    ]));
                } else {
                    // Nếu đã có, update thay vì tạo mới
                    $existingManga->update($dataToSave);
                }
            }
        } catch (\Exception $e) {
        }
    }

    protected function transformMangaDetail($data)
    {
        $item = $data['data']['item'] ?? [];
        $imageDomain = $data['APP_DOMAIN_CDN_IMAGE'] ?? 'https://img.otruyenapi.com';
        
        $chapters = $this->extractChapters($item['chapters'] ?? []);
        $categories = $this->extractCategories($item['category'] ?? []);
        $typeAndTags = $this->separateTypeAndTags($categories);
        
        // Đánh dấu xem type có phải là mặc định không (không tìm thấy trong categories)
        $typeIsDefault = empty($categories) || !$this->hasTypeInCategories($categories);
        
        return [
            'id' => $item['_id'] ?? null,
            'name' => $item['name'] ?? 'Đang cập nhật',
            'slug' => $item['slug'] ?? '',
            'origin_name' => $item['origin_name'] ?? [],
            'description' => $item['content'] ?? 'Đang cập nhật',
            'cover_url' => $this->getImageUrlWithDomain($item['thumb_url'] ?? '', $imageDomain),
            'author' => $item['author'] ?? ['Đang cập nhật'],
            'status' => $this->mapStatus($item['status'] ?? ''),
            'type' => $typeAndTags['type'],
            'type_is_default' => $typeIsDefault, // Đánh dấu type có phải mặc định không
            'tags' => $typeAndTags['tags'],
            'chapters' => $chapters,
            'updated_at' => $item['updatedAt'] ?? null,
            'seo' => $data['data']['seoOnPage'] ?? [],
        ];
    }
    
    protected function hasTypeInCategories($categories)
    {
        $typeKeywords = ['manga', 'manhua', 'manhwa', 'truyen-mau', 'truyen-tranh'];
        foreach ($categories as $cat) {
            $slug = strtolower($cat['slug'] ?? '');
            $name = strtolower($cat['name'] ?? '');
            if (in_array($slug, $typeKeywords) || in_array($name, $typeKeywords)) {
                return true;
            }
        }
        return false;
    }

    protected function extractChapters($chaptersData)
    {
        $allChapters = [];
        $seenChapters = [];
        
        foreach ($chaptersData as $server) {
            if (isset($server['server_data']) && is_array($server['server_data'])) {
                foreach ($server['server_data'] as $chapter) {
                    $chapterName = $chapter['chapter_name'] ?? '';
                    $key = $chapterName;
                    
                    if (!isset($seenChapters[$key])) {
                        $allChapters[] = [
                            'name' => $chapterName,
                            'title' => $chapter['chapter_title'] ?? '',
                            'slug' => 'chapter-' . $chapterName,
                            'api_data' => $chapter['chapter_api_data'] ?? null,
                        ];
                        $seenChapters[$key] = true;
                    }
                }
            }
        }
        
        usort($allChapters, function($a, $b) {
            $aNum = $this->parseChapterNumber($a['name']);
            $bNum = $this->parseChapterNumber($b['name']);
            if ($aNum == $bNum) {
                return strcmp($a['name'], $b['name']);
            }
            return $bNum <=> $aNum;
        });
        
        return $allChapters;
    }

    protected function parseChapterNumber($chapterName)
    {
        if (preg_match('/^(\d+)/', $chapterName, $matches)) {
            return (float)$matches[1];
        }
        return 0;
    }

    protected function extractCategories($categories)
    {
        return array_map(function($cat) {
            return [
                'id' => $cat['id'] ?? '',
                'name' => $cat['name'] ?? '',
                'slug' => $cat['slug'] ?? '',
            ];
        }, $categories);
    }

    protected function separateTypeAndTags($categories)
    {
        $typeKeywords = ['manga', 'manhua', 'manhwa', 'truyen-mau', 'truyen-tranh'];
        $type = null;
        $tags = [];
        
        foreach ($categories as $cat) {
            $slug = strtolower($cat['slug'] ?? '');
            $name = strtolower($cat['name'] ?? '');
            
            if (in_array($slug, $typeKeywords) || in_array($name, $typeKeywords)) {
                if (!$type) {
                    $type = [
                        'id' => $cat['id'] ?? '',
                        'name' => $cat['name'] ?? 'Manga',
                        'slug' => $cat['slug'] ?? 'manga',
                    ];
                }
            } else {
                $tags[] = $cat;
            }
        }
        
        if (!$type) {
            $type = [
                'id' => '',
                'name' => 'Manga',
                'slug' => 'manga',
            ];
        }
        
        return [
            'type' => $type,
            'tags' => $tags,
        ];
    }

    protected function mapStatus($status)
    {
        $statusMap = [
            'ongoing' => 'Đang tiến hành',
            'completed' => 'Hoàn thành',
            'hiatus' => 'Tạm ngưng',
        ];
        
        return $statusMap[$status] ?? 'Đang cập nhật';
    }

    protected function getImageUrlWithDomain($thumbUrl, $domain = 'https://img.otruyenapi.com')
    {
        if (empty($thumbUrl)) {
            return '';
        }
        
        if (strpos($thumbUrl, 'http') === 0) {
            return $thumbUrl;
        }
        
        if (strpos($thumbUrl, '/') === 0) {
            return $domain . $thumbUrl;
        }
        
        return $domain . '/uploads/comics/' . $thumbUrl;
    }

    public function getChapterImages($apiUrl)
    {
        if (empty($apiUrl)) {
            return null;
        }
        
        $cacheKey = "otruyen:chapter_images:" . md5($apiUrl);
        
        return Cache::remember($cacheKey, 3600, function () use ($apiUrl) {
            try {
                $response = Http::timeout($this->timeout)
                    ->withoutVerifying()
                    ->get($apiUrl);
                
                if ($response->successful()) {
                    $data = $response->json();
                    
                    if (isset($data['data']['item'])) {
                        $item = $data['data']['item'];
                        $domainCdn = $data['data']['domain_cdn'] ?? 'https://sv1.otruyencdn.com';
                        $chapterPath = $item['chapter_path'] ?? '';
                        $chapterImages = $item['chapter_image'] ?? [];
                        
                        if (is_array($chapterImages) && !empty($chapterPath)) {
                            $imageUrls = [];
                            foreach ($chapterImages as $image) {
                                $imageFile = $image['image_file'] ?? '';
                                if (!empty($imageFile)) {
                                    $imageUrls[] = rtrim($domainCdn, '/') . '/' . trim($chapterPath, '/') . '/' . $imageFile;
                                }
                            }
                            
                            if (!empty($imageUrls)) {
                                return $imageUrls;
                            }
                        }
                    }
                }
                
                return null;
            } catch (\Exception $e) {
                return null;
            }
        });
    }

    public function getCategories()
    {
        $cacheKey = "otruyen:categories";
        
        return Cache::remember($cacheKey, 3600, function () {
            try {
                $response = Http::timeout($this->timeout)
                    ->withoutVerifying()
                    ->get("{$this->baseUrl}/the-loai");
                
                if ($response->successful()) {
                    $data = $response->json();
                    
                    if (isset($data['data']['items']) && is_array($data['data']['items'])) {
                        return array_map(function($item) {
                            return [
                                'id' => $item['_id'] ?? '',
                                'slug' => $item['slug'] ?? '',
                                'name' => $item['name'] ?? '',
                            ];
                        }, $data['data']['items']);
                    }
                }
                
                return [];
            } catch (\Exception $e) {
                return [];
            }
        });
    }

    public function syncCategories()
    {
        $categories = $this->getCategories();
        
        foreach ($categories as $cat) {
            Category::updateOrCreate(
                ['source_id' => $cat['id']],
                [
                    'slug' => $cat['slug'],
                    'name' => $cat['name'],
                    'is_active' => true,
                ]
            );
        }
        
        return count($categories);
    }

    public function getMangaByGenre($slug, $page = 1)
    {
        $cacheKey = "otruyen:genre:{$slug}:page:{$page}";
        
        return Cache::remember($cacheKey, 600, function () use ($slug, $page) {
            try {
                $response = Http::timeout($this->timeout)
                    ->withoutVerifying()
                    ->get("{$this->baseUrl}/the-loai/{$slug}", [
                        'page' => $page,
                    ]);
                
                if ($response->successful()) {
                    $data = $response->json();
                    
                    if (isset($data['data']['items']) && is_array($data['data']['items'])) {
                        $mangas = $this->transformGenreMangas($data['data']['items']);
                        $pagination = $data['data']['params']['pagination'] ?? [];
                        $seo = $data['data']['seoOnPage'] ?? [];
                        $titlePage = $data['data']['titlePage'] ?? '';
                        
                        return [
                            'mangas' => $mangas,
                            'pagination' => $pagination,
                            'seo' => $seo,
                            'titlePage' => $titlePage,
                            'breadCrumb' => $data['data']['breadCrumb'] ?? [],
                        ];
                    }
                }
                
                return null;
            } catch (\Exception $e) {
                return null;
            }
        });
    }
    
    public function getOngoingMangas($page = 1)
    {
        $cacheKey = "otruyen:ongoing:page:{$page}";
        
        return Cache::remember($cacheKey, 600, function () use ($page) {
            try {
                $response = Http::timeout($this->timeout)
                    ->withoutVerifying()
                    ->get("{$this->baseUrl}/danh-sach/dang-phat-hanh", [
                        'page' => $page,
                    ]);
                
                if ($response->successful()) {
                    $data = $response->json();
                    
                    if (isset($data['data']['items']) && is_array($data['data']['items'])) {
                        $mangas = $this->transformGenreMangas($data['data']['items'], false); // false = only 1 chapter
                        $pagination = $data['data']['params']['pagination'] ?? [];
                        $seo = $data['data']['seoOnPage'] ?? [];
                        $titlePage = $data['data']['titlePage'] ?? 'Truyện đang phát hành';
                        
                        return [
                            'mangas' => $mangas,
                            'pagination' => $pagination,
                            'seo' => $seo,
                            'titlePage' => $titlePage,
                            'breadCrumb' => $data['data']['breadCrumb'] ?? [],
                        ];
                    }
                }
                
                return null;
            } catch (\Exception $e) {
                return null;
            }
        });
    }

    protected function transformGenreMangas($items, $addPreviousChapter = true)
    {
        return array_map(function ($item) use ($addPreviousChapter) {
            $chapters = [];
            $updatedAt = $item['updatedAt'] ?? null;
            
            if (isset($item['chaptersLatest']) && is_array($item['chaptersLatest']) && count($item['chaptersLatest']) > 0) {
                $chapter = $item['chaptersLatest'][0];
                $chapterName = $chapter['chapter_name'] ?? '';
                $filename = $chapter['filename'] ?? '';
                
                if (!empty($filename)) {
                    if (preg_match('/\[Chap\s+(\d+)\]/i', $filename, $matches)) {
                        $chapterName = $matches[1];
                    } elseif (preg_match('/Chapter\s+(\d+)/i', $filename, $matches)) {
                        $chapterName = $matches[1];
                    }
                }
                
                if (!empty($chapterName)) {
                    $chapters[] = [
                        'id' => null,
                        'slug' => 'chapter-' . $chapterName,
                        'name' => 'Chapter ' . $chapterName,
                        'releasedAt' => $updatedAt ? $this->formatVietnameseTime($updatedAt) : null,
                    ];
                }
                
                // Only add previous chapter if $addPreviousChapter is true (for search page)
                // For /new page, $addPreviousChapter is false, so only 1 chapter will be shown
                if ($addPreviousChapter && count($chapters) > 0 && !empty($chapters[0]['slug'])) {
                    $chapterSlug = $chapters[0]['slug'];
                    $chapterNumber = str_replace('chapter-', '', $chapterSlug);
                    if (is_numeric($chapterNumber)) {
                        $currentNumber = (int)$chapterNumber;
                        if ($currentNumber > 1) {
                            $prevNumber = $currentNumber - 1;
                            $chapters[] = [
                                'id' => null,
                                'slug' => 'chapter-' . $prevNumber,
                                'name' => 'Chapter ' . $prevNumber,
                                'releasedAt' => $updatedAt ? $this->formatVietnameseTime($updatedAt) : null,
                            ];
                        }
                    }
                }
            }
            
            return [
                'slug' => $item['slug'] ?? '',
                'title' => $item['name'] ?? 'Đang cập nhật',
                'posterPath' => $this->getImageUrl($item['thumb_url'] ?? ''),
                'avgVote' => 0,
                'countView' => 0,
                'chapters' => $chapters,
                'status' => $this->mapStatus($item['status'] ?? ''),
                'updatedAt' => $updatedAt,
            ];
        }, $items);
    }

    public function getMangaByType($slug, $page = 1, $limit = 24)
    {
        $cacheKey = "otruyen:type:{$slug}:page:{$page}:limit:{$limit}";
        
        return Cache::remember($cacheKey, 600, function () use ($slug, $page, $limit) {
            try {
                $response = Http::timeout($this->timeout)
                    ->withoutVerifying()
                    ->get("{$this->baseUrl}/the-loai/{$slug}", [
                        'page' => $page,
                    ]);
                
                if ($response->successful()) {
                    $data = $response->json();
                    
                    if (isset($data['data']['items']) && is_array($data['data']['items'])) {
                        $allItems = $data['data']['items'];
                        $shuffledItems = $allItems;
                        shuffle($shuffledItems);
                        $selectedItems = array_slice($shuffledItems, 0, $limit);
                        $mangas = $this->transformGenreMangas($selectedItems);
                        $titlePage = $data['data']['titlePage'] ?? '';
                        
                        return [
                            'mangas' => $mangas,
                            'titlePage' => $titlePage,
                        ];
                    }
                }
                
                return null;
            } catch (\Exception $e) {
                return null;
            }
        });
    }

    public function getMangaByList($slug, $page = 1, $limit = 24, $shuffle = true)
    {
        $cacheKey = "otruyen:list:{$slug}:page:{$page}:limit:{$limit}:shuffle:" . ($shuffle ? '1' : '0');
        
        return Cache::remember($cacheKey, 600, function () use ($slug, $page, $limit, $shuffle) {
            try {
                $response = Http::timeout($this->timeout)
                    ->withoutVerifying()
                    ->get("{$this->baseUrl}/danh-sach/{$slug}", [
                        'page' => $page,
                    ]);
                
                if ($response->successful()) {
                    $data = $response->json();
                    
                    if (isset($data['data']['items']) && is_array($data['data']['items'])) {
                        $allItems = $data['data']['items'];
                        
                        if ($shuffle) {
                            $shuffledItems = $allItems;
                            shuffle($shuffledItems);
                            $selectedItems = array_slice($shuffledItems, 0, $limit);
                        } else {
                            $selectedItems = $allItems;
                        }
                        
                        $mangas = $this->transformGenreMangas($selectedItems);
                        $titlePage = $data['data']['titlePage'] ?? '';
                        $pagination = $data['data']['params']['pagination'] ?? [];
                        
                        return [
                            'mangas' => $mangas,
                            'titlePage' => $titlePage,
                            'pagination' => $pagination,
                        ];
                    }
                }
                
                return null;
            } catch (\Exception $e) {
                return null;
            }
        });
    }

    protected function formatVietnameseTime($dateTime)
    {
        if (!function_exists('formatVietnameseTime')) {
            return null;
        }
        return formatVietnameseTime($dateTime);
    }
}

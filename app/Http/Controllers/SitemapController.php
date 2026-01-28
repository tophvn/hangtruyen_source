<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MangaMetadata;
use App\Models\MangaChapter;

class SitemapController extends Controller
{
    public function index()
    {
        $sitemap = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $sitemap .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        $baseUrl = url('/');

        $sitemap .= '  <url>' . "\n";
        $sitemap .= '    <loc>' . $baseUrl . '</loc>' . "\n";
        $sitemap .= '    <lastmod>' . date('Y-m-d') . '</lastmod>' . "\n";
        $sitemap .= '    <changefreq>daily</changefreq>' . "\n";
        $sitemap .= '    <priority>1.0</priority>' . "\n";
        $sitemap .= '  </url>' . "\n";

        $mangas = MangaMetadata::where('is_active', true)
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
    }

    public function robots()
    {
        $content = "User-agent: *\n";
        $content .= "Allow: /\n";
        $content .= "Disallow: /admin/\n";
        $content .= "Disallow: /api/\n";
        $content .= "Disallow: /account/\n";
        $content .= "\n";
        $content .= "Sitemap: " . url('/sitemap.xml');

        return response($content, 200)
            ->header('Content-Type', 'text/plain');
    }
}

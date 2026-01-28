<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class ImageProxyController extends Controller
{
    /**
     * Proxy images from img.otruyenapi.com/uploads/comics/
     */
    public function comics($path)
    {
        $url = 'https://img.otruyenapi.com/uploads/comics/' . ltrim($path, '/');
        return $this->proxyImage($url);
    }

    /**
     * Proxy images from sv1.otruyencdn.com/uploads/
     */
    public function uploads($path)
    {
        $url = 'https://sv1.otruyencdn.com/uploads/' . ltrim($path, '/');
        return $this->proxyImage($url);
    }

    /**
     * Generic proxy with URL parameter
     */
    public function proxy(Request $request)
    {
        $url = $request->query('url');

        if (!$url || !filter_var($url, FILTER_VALIDATE_URL)) {
            abort(400, 'Invalid url');
        }

        // Security: Only allow whitelisted domains
        $allowedDomains = [
            'img.otruyenapi.com',
            'sv1.otruyencdn.com',
            'otruyenapi.com',
            'otruyencdn.com',
        ];

        $host = parse_url($url, PHP_URL_HOST);
        $isAllowed = false;
        foreach ($allowedDomains as $domain) {
            if ($host === $domain || str_ends_with($host, '.' . $domain)) {
                $isAllowed = true;
                break;
            }
        }

        if (!$isAllowed) {
            abort(403, 'Domain not allowed');
        }

        return $this->proxyImage($url);
    }

    /**
     * Core proxy logic with optimization
     */
    protected function proxyImage($url)
    {
        try {
            // Use streaming to avoid memory issues with large images
            $response = Http::timeout(30) // Tăng timeout lên 30s
                ->withoutVerifying()
                ->withOptions([
                    'stream' => true, // Enable streaming
                    'verify' => false,
                    'http_errors' => false,
                ])
                ->get($url);

            if (!$response->successful()) {
                \Log::warning("Image proxy failed for URL: {$url}, Status: {$response->status()}");
                abort($response->status());
            }

            $contentType = $response->header('Content-Type', 'image/jpeg');
            $contentLength = $response->header('Content-Length');

            // Create response with proper headers for caching
            return response($response->body(), 200)
                ->header('Content-Type', $contentType)
                ->header('Content-Length', $contentLength)
                ->header('Cache-Control', 'public, max-age=604800, immutable') // 7 days
                ->header('Expires', gmdate('D, d M Y H:i:s', time() + 604800) . ' GMT')
                ->header('X-Proxy-Cache', 'HIT')
                ->header('Access-Control-Allow-Origin', '*');

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            \Log::error("Image proxy connection error for URL: {$url}, Error: {$e->getMessage()}");
            abort(504, 'Gateway timeout');
        } catch (\Exception $e) {
            \Log::error("Image proxy error for URL: {$url}, Error: {$e->getMessage()}");
            abort(502, 'Image proxy error');
        }
    }
}

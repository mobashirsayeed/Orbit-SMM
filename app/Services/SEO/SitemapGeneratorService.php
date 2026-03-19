<?php

namespace App\Services\SEO;

use App\Models\XmlSitemap;
use Illuminate\Support\Facades\Storage;

class SitemapGeneratorService
{
    public function generateSitemap(int $tenantId, string $baseUrl, array $urls, string $name = 'sitemap'): XmlSitemap
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        foreach ($urls as $urlData) {
            $xml .= '<url>';
            $xml .= '<loc>' . e($this->normalizeUrl($baseUrl, $urlData['loc'])) . '</loc>';
            $xml .= '<lastmod>' . ($urlData['lastmod'] ?? now()->toIso8601String()) . '</lastmod>';
            $xml .= '<changefreq>' . ($urlData['changefreq'] ?? 'monthly') . '</changefreq>';
            $xml .= '<priority>' . ($urlData['priority'] ?? '0.5') . '</priority>';
            $xml .= '</url>';
        }

        $xml .= '</urlset>';

        $filename = "{$name}.xml";
        $path = "sitemaps/{$tenant_id}/{$filename}";
        
        Storage::disk('public')->put($path, $xml);

        return XmlSitemap::updateOrCreate(
            ['tenant_id' => $tenantId, 'sitemap_name' => $name],
            [
                'file_path' => $path,
                'url_count' => count($urls),
                'last_generated_at' => now(),
            ]
        );
    }

    public function generateSitemapIndex(int $tenantId, array $sitemapNames): string
    {
        $baseUrl = config('app.url');
        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        foreach ($sitemapNames as $name) {
            $xml .= '<sitemap>';
            $xml .= '<loc>' . e("{$baseUrl}/storage/sitemaps/{$tenant_id}/{$name}.xml") . '</loc>';
            $xml .= '<lastmod>' . now()->toIso8601String() . '</lastmod>';
            $xml .= '</sitemap>';
        }

        $xml .= '</sitemapindex>';

        $path = "sitemaps/{$tenant_id}/sitemap_index.xml";
        Storage::disk('public')->put($path, $xml);

        return $path;
    }

    private function normalizeUrl(string $baseUrl, string $url): string
    {
        if (str_starts_with($url, 'http')) {
            return $url;
        }
        return rtrim($baseUrl, '/') . '/' . ltrim($url, '/');
    }

    public function getSitemapUrl(XmlSitemap $sitemap): string
    {
        return Storage::disk('public')->url($sitemap->file_path);
    }
}

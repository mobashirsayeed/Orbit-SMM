<?php

namespace App\Services\SEO;

use App\Models\SEOMonitor;
use App\Models\SEOAudit;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use DOMDocument;
use DOMXPath;

class SEOCrawlerService
{
    public function crawlSite(SEOMonitor $monitor): array
    {
        $urls = [$monitor->domain];
        $crawled = [];
        $issues = [];

        $maxPages = 50; // Limit for shared hosting
        $crawledCount = 0;

        while (count($urls) > 0 && $crawledCount < $maxPages) {
            $url = array_shift($urls);
            
            if (in_array($url, $crawled)) {
                continue;
            }

            $audit = $this->auditUrl($url, $monitor);
            $crawled[] = $url;
            $crawledCount++;

            // Extract internal links
            $newUrls = $this->extractInternalLinks($audit, $monitor->domain);
            $urls = array_merge($urls, $newUrls);

            // Collect issues
            if ($audit->issues) {
                $issues = array_merge($issues, $audit->issues);
            }
        }

        // Calculate overall scores
        $scores = $this->calculateOverallScores($crawled);

        $monitor->update([
            'seo_score' => $scores['seo'],
            'performance_score' => $scores['performance'],
            'accessibility_score' => $scores['accessibility'],
            'best_practices_score' => $scores['best_practices'],
            'issues' => $issues,
            'recommendations' => $this->generateRecommendations($issues),
            'last_check_at' => now(),
        ]);

        return [
            'urls_crawled' => $crawledCount,
            'scores' => $scores,
            'issues_count' => count($issues),
        ];
    }

    private function auditUrl(string $url, SEOMonitor $monitor): SEOAudit
    {
        try {
            $startTime = microtime(true);
            
            $response = Http::timeout(30)->get($url);
            
            $loadTime = round((microtime(true) - $startTime) * 1000);
            $content = $response->body();
            $statusCode = $response->status();

            // Parse HTML
            $dom = new DOMDocument();
            @$dom->loadHTML($content);
            $xpath = new DOMXPath($dom);

            // Extract meta tags
            $metaTags = $this->extractMetaTags($xpath);
            $headings = $this->extractHeadings($xpath);
            $links = $this->extractLinks($xpath);
            $images = $this->extractImages($xpath);

            // Identify issues
            $issues = $this->identifyIssues($metaTags, $headings, $links, $images, $statusCode, $loadTime);

            // Calculate score
            $score = $this->calculateScore($issues);

            return SEOAudit::create([
                'tenant_id' => $monitor->tenant_id,
                'seo_monitor_id' => $monitor->id,
                'url' => $url,
                'status_code' => $statusCode,
                'load_time' => $loadTime,
                'page_size' => round(strlen($content) / 1024, 2),
                'meta_tags' => $metaTags,
                'headings' => $headings,
                'links' => $links,
                'images' => $images,
                'issues' => $issues,
                'score' => $score,
            ]);
        } catch (\Exception $e) {
            Log::error('SEO crawl failed', [
                'url' => $url,
                'error' => $e->getMessage(),
            ]);

            return SEOAudit::create([
                'tenant_id' => $monitor->tenant_id,
                'seo_monitor_id' => $monitor->id,
                'url' => $url,
                'status_code' => 0,
                'issues' => [[
                    'type' => 'crawl_error',
                    'severity' => 'critical',
                    'message' => $e->getMessage(),
                ]],
                'score' => 0,
            ]);
        }
    }

    private function extractMetaTags(DOMXPath $xpath): array
    {
        $metaTags = [];
        $nodes = $xpath->query('//meta');

        foreach ($nodes as $node) {
            $name = $node->getAttribute('name');
            $property = $node->getAttribute('property');
            $content = $node->getAttribute('content');

            if ($name) {
                $metaTags[$name] = $content;
            } elseif ($property) {
                $metaTags[$property] = $content;
            }
        }

        // Get title
        $titleNodes = $xpath->query('//title');
        if ($titleNodes->length > 0) {
            $metaTags['title'] = $titleNodes->item(0)->textContent;
        }

        return $metaTags;
    }

    private function extractHeadings(DOMXPath $xpath): array
    {
        $headings = [];
        for ($i = 1; $i <= 6; $i++) {
            $nodes = $xpath->query("//h{$i}");
            $headings["h{$i}"] = [];
            foreach ($nodes as $node) {
                $headings["h{$i}"][] = trim($node->textContent);
            }
        }
        return $headings;
    }

    private function extractLinks(DOMXPath $xpath): array
    {
        $links = ['internal' => [], 'external' => [], 'broken' => []];
        $nodes = $xpath->query('//a[@href]');

        foreach ($nodes as $node) {
            $href = $node->getAttribute('href');
            $text = trim($node->textContent);

            if (str_starts_with($href, 'http')) {
                $links['external'][] = ['url' => $href, 'text' => $text];
            } else {
                $links['internal'][] = ['url' => $href, 'text' => $text];
            }
        }

        return $links;
    }

    private function extractImages(DOMXPath $xpath): array
    {
        $images = [];
        $nodes = $xpath->query('//img');

        foreach ($nodes as $node) {
            $images[] = [
                'src' => $node->getAttribute('src'),
                'alt' => $node->getAttribute('alt'),
                'has_alt' => !empty($node->getAttribute('alt')),
            ];
        }

        return $images;
    }

    private function identifyIssues(array $metaTags, array $headings, array $links, array $images, int $statusCode, int $loadTime): array
    {
        $issues = [];

        // Title tag issues
        if (empty($metaTags['title'])) {
            $issues[] = ['type' => 'missing_title', 'severity' => 'critical', 'message' => 'Missing title tag'];
        } elseif (strlen($metaTags['title']) > 60) {
            $issues[] = ['type' => 'long_title', 'severity' => 'warning', 'message' => 'Title tag too long (>60 characters)'];
        } elseif (strlen($metaTags['title']) < 30) {
            $issues[] = ['type' => 'short_title', 'severity' => 'warning', 'message' => 'Title tag too short (<30 characters)'];
        }

        // Meta description issues
        if (empty($metaTags['description'])) {
            $issues[] = ['type' => 'missing_description', 'severity' => 'critical', 'message' => 'Missing meta description'];
        } elseif (strlen($metaTags['description']) > 160) {
            $issues[] = ['type' => 'long_description', 'severity' => 'warning', 'message' => 'Meta description too long (>160 characters)'];
        }

        // H1 issues
        if (empty($headings['h1'])) {
            $issues[] = ['type' => 'missing_h1', 'severity' => 'critical', 'message' => 'Missing H1 heading'];
        } elseif (count($headings['h1']) > 1) {
            $issues[] = ['type' => 'multiple_h1', 'severity' => 'warning', 'message' => 'Multiple H1 headings found'];
        }

        // Image alt issues
        $imagesWithoutAlt = collect($images)->filter(fn($img) => !$img['has_alt'])->count();
        if ($imagesWithoutAlt > 0) {
            $issues[] = [
                'type' => 'missing_alt', 
                'severity' => 'warning', 
                'message' => "{$imagesWithoutAlt} images missing alt text"
            ];
        }

        // Performance issues
        if ($loadTime > 3000) {
            $issues[] = ['type' => 'slow_load', 'severity' => 'warning', 'message' => 'Page load time > 3 seconds'];
        }

        // Status code issues
        if ($statusCode >= 400) {
            $issues[] = ['type' => 'http_error', 'severity' => 'critical', 'message' => "HTTP status code: {$statusCode}"];
        }

        return $issues;
    }

    private function calculateScore(array $issues): int
    {
        $score = 100;
        
        foreach ($issues as $issue) {
            $deduction = match ($issue['severity']) {
                'critical' => 15,
                'warning' => 5,
                'info' => 2,
                default => 0,
            };
            $score -= $deduction;
        }

        return max(0, $score);
    }

    private function calculateOverallScores(array $audits): array
    {
        if (empty($audits)) {
            return ['seo' => 0, 'performance' => 0, 'accessibility' => 0, 'best_practices' => 0];
        }

        $avgScore = collect($audits)->avg('score') ?? 0;

        return [
            'seo' => round($avgScore),
            'performance' => round($avgScore * 0.9), // Simulated
            'accessibility' => round($avgScore * 0.85), // Simulated
            'best_practices' => round($avgScore * 0.95), // Simulated
        ];
    }

    private function generateRecommendations(array $issues): array
    {
        $recommendations = [];

        $issueTypes = collect($issues)->pluck('type')->unique();

        if ($issueTypes->contains('missing_title')) {
            $recommendations[] = 'Add unique, descriptive title tags to all pages (50-60 characters)';
        }

        if ($issueTypes->contains('missing_description')) {
            $recommendations[] = 'Add meta descriptions to all pages (150-160 characters)';
        }

        if ($issueTypes->contains('missing_h1')) {
            $recommendations[] = 'Add exactly one H1 heading per page';
        }

        if ($issueTypes->contains('missing_alt')) {
            $recommendations[] = 'Add descriptive alt text to all images';
        }

        if ($issueTypes->contains('slow_load')) {
            $recommendations[] = 'Optimize images and enable caching to improve page speed';
        }

        return $recommendations;
    }

    private function extractInternalLinks(SEOAudit $audit, string $domain): array
    {
        $internalLinks = $audit->links['internal'] ?? [];
        $urls = [];

        foreach ($internalLinks as $link) {
            $url = rtrim($domain, '/') . '/' . ltrim($link['url'], '/');
            $urls[] = $url;
        }

        return array_unique($urls);
    }
}

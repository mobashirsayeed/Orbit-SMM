<?php

namespace App\Services\SEO;

use App\Models\SEOMonitor;
use App\Models\KeywordRanking;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class KeywordRankTrackerService
{
    public function trackKeywords(SEOMonitor $monitor): array
    {
        $keywords = $monitor->keywords ?? [];
        $results = [];

        foreach ($keywords as $keyword) {
            $position = $this->getKeywordPosition($keyword, $monitor->domain);
            
            // Get previous position
            $previous = KeywordRanking::where('seo_monitor_id', $monitor->id)
                ->where('keyword', $keyword)
                ->latest('tracked_date')
                ->first();

            $ranking = KeywordRanking::create([
                'tenant_id' => $monitor->tenant_id,
                'seo_monitor_id' => $monitor->id,
                'keyword' => $keyword,
                'position' => $position,
                'previous_position' => $previous?->position,
                'tracked_date' => now()->format('Y-m-d'),
            ]);

            $results[$keyword] = [
                'position' => $position,
                'change' => $ranking->position_change,
            ];
        }

        return $results;
    }

    private function getKeywordPosition(string $keyword, string $domain): ?int
    {
        // Note: In production, you would use a SERP API like DataForSEO, SerpAPI, etc.
        // This is a stub for demonstration
        
        try {
            // Simulated API call
            // $response = Http::get('https://api.serpapi.com/search', [
            //     'q' => $keyword,
            //     'api_key' => config('services.serpapi.key'),
            // ]);
            
            // For demo, return random position
            return rand(1, 100);
        } catch (\Exception $e) {
            Log::error('Keyword rank tracking failed', [
                'keyword' => $keyword,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    public function getRankingHistory(SEOMonitor $monitor, string $keyword, int $days = 30): array
    {
        return KeywordRanking::where('seo_monitor_id', $monitor->id)
            ->where('keyword', $keyword)
            ->where('tracked_date', '>=', now()->subDays($days))
            ->orderBy('tracked_date')
            ->get()
            ->map(fn($r) => [
                'date' => $r->tracked_date->format('Y-m-d'),
                'position' => $r->position,
            ])
            ->toArray();
    }

    public function getAveragePosition(SEOMonitor $monitor): ?float
    {
        return KeywordRanking::where('seo_monitor_id', $monitor->id)
            ->where('tracked_date', now()->format('Y-m-d'))
            ->avg('position');
    }
}

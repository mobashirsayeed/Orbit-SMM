<?php

namespace App\Services\Analytics;

use App\Models\AnalyticsReport;
use App\Models\AnalyticsSnapshot;
use App\Models\SocialPost;
use Illuminate\Support\Facades\Mail;

class ReportGeneratorService
{
    public function generateReport(int $tenantId, array $options): AnalyticsReport
    {
        $startDate = $options['start_date'];
        $endDate = $options['end_date'];
        $platforms = $options['platforms'];

        $metrics = $this->collectMetrics($tenantId, $startDate, $endDate, $platforms);
        $insights = $this->generateInsights($metrics);

        return AnalyticsReport::create([
            'tenant_id' => $tenantId,
            'type' => $options['type'],
            'report_date' => now(),
            'metrics' => $metrics,
            'insights' => $insights,
        ]);
    }

    public function sendReportEmail(AnalyticsReport $report, string $email): void
    {
        Mail::raw($this->generateEmailContent($report), function ($message) use ($email, $report) {
            $message->to($email)
                ->subject('Your Orbit Analytics Report - ' . now()->format('M d, Y'))
                ->from(config('mail.from.address'), config('mail.from.name'));
        });
    }

    public function scheduleReport(AnalyticsReport $report, string $frequency): void
    {
        // Schedule recurring report generation
        // This would integrate with Laravel's scheduler
    }

    private function collectMetrics(int $tenantId, string $startDate, string $endDate, array $platforms): array
    {
        $metrics = [
            'period' => [
                'start' => $startDate,
                'end' => $endDate,
            ],
            'platforms' => [],
            'totals' => [
                'followers' => 0,
                'engagement' => 0,
                'impressions' => 0,
                'posts' => 0,
            ],
        ];

        foreach ($platforms as $platform) {
            $platformMetrics = $this->getPlatformMetrics($tenantId, $platform, $startDate, $endDate);
            $metrics['platforms'][$platform] = $platformMetrics;
            
            $metrics['totals']['followers'] += $platformMetrics['followers'] ?? 0;
            $metrics['totals']['engagement'] += $platformMetrics['engagement'] ?? 0;
            $metrics['totals']['impressions'] += $platformMetrics['impressions'] ?? 0;
            $metrics['totals']['posts'] += $platformMetrics['posts'] ?? 0;
        }

        return $metrics;
    }

    private function getPlatformMetrics(int $tenantId, string $platform, string $startDate, string $endDate): array
    {
        $snapshots = AnalyticsSnapshot::where('tenant_id', $tenantId)
            ->where('platform', $platform)
            ->whereBetween('snapshot_date', [$startDate, $endDate])
            ->get();

        $posts = SocialPost::where('tenant_id', $tenantId)
            ->whereJsonContains('platforms', $platform)
            ->whereBetween('published_at', [$startDate, $endDate])
            ->published()
            ->count();

        return [
            'followers' => $snapshots->last()->metrics['followers'] ?? 0,
            'engagement' => $snapshots->last()->metrics['engagement'] ?? 0,
            'impressions' => $snapshots->last()->metrics['impressions'] ?? 0,
            'reach' => $snapshots->last()->metrics['reach'] ?? 0,
            'posts' => $posts,
            'growth' => $this->calculateGrowth($snapshots),
        ];
    }

    private function calculateGrowth($snapshots): float
    {
        if ($snapshots->count() < 2) {
            return 0;
        }

        $first = $snapshots->first()->metrics['followers'] ?? 0;
        $last = $snapshots->last()->metrics['followers'] ?? 0;

        if ($first === 0) {
            return 0;
        }

        return round((($last - $first) / $first) * 100, 2);
    }

    private function generateInsights(array $metrics): array
    {
        $insights = [];

        // Follower growth insight
        if ($metrics['totals']['followers'] > 0) {
            $insights[] = [
                'type' => 'growth',
                'message' => 'Total followers across all platforms: ' . number_format($metrics['totals']['followers']),
                'severity' => 'positive',
            ];
        }

        // Engagement insight
        if ($metrics['totals']['engagement'] > 0) {
            $insights[] = [
                'type' => 'engagement',
                'message' => 'Total engagements: ' . number_format($metrics['totals']['engagement']),
                'severity' => 'positive',
            ];
        }

        // Post frequency insight
        if ($metrics['totals']['posts'] > 0) {
            $insights[] = [
                'type' => 'activity',
                'message' => 'Posts published: ' . $metrics['totals']['posts'],
                'severity' => 'neutral',
            ];
        }

        return $insights;
    }

    private function generateEmailContent(AnalyticsReport $report): string
    {
        $content = "Orbit Analytics Report\n\n";
        $content .= "Period: " . $report->metrics['period']['start'] . " to " . $report->metrics['period']['end'] . "\n\n";
        $content .= "Total Followers: " . number_format($report->metrics['totals']['followers']) . "\n";
        $content .= "Total Engagement: " . number_format($report->metrics['totals']['engagement']) . "\n";
        $content .= "Total Posts: " . $report->metrics['totals']['posts'] . "\n\n";
        $content .= "View full report: " . route('analytics.reports.show', $report->id);

        return $content;
    }
}

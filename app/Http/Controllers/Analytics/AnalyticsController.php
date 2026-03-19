<?php

namespace App\Http\Controllers\Analytics;

use App\Http\Controllers\Controller;
use App\Models\AnalyticsReport;
use App\Models\SocialPost;
use App\Services\Analytics\AnalyticsSyncService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AnalyticsController extends Controller
{
    public function __construct(
        private readonly AnalyticsSyncService $syncService
    ) {}

    public function dashboard(Request $request)
    {
        $tenantId = app('tenant')->id;
        $days = $request->get('days', 30);

        $metrics = $this->syncService->getDashboardMetrics($tenantId, $days);

        // Get post performance
        $topPosts = SocialPost::where('tenant_id', $tenantId)
            ->published()
            ->where('published_at', '>=', now()->subDays($days))
            ->orderByDesc('meta->engagement')
            ->limit(5)
            ->get();

        // Get platform breakdown
        $platformBreakdown = $this->getPlatformBreakdown($tenantId, $days);

        return Inertia::render('Analytics/Dashboard', [
            'metrics' => $metrics,
            'topPosts' => $topPosts,
            'platformBreakdown' => $platformBreakdown,
            'dateRange' => [
                'start' => now()->subDays($days)->format('Y-m-d'),
                'end' => now()->format('Y-m-d'),
                'days' => $days,
            ],
        ]);
    }

    public function platform(Request $request, string $platform)
    {
        $tenantId = app('tenant')->id;
        $days = $request->get('days', 30);

        $this->syncService->syncPlatform($tenantId, $platform);

        $metrics = $this->getPlatformMetrics($tenantId, $platform, $days);

        return Inertia::render('Analytics/Platform', [
            'platform' => $platform,
            'metrics' => $metrics,
        ]);
    }

    public function sync(Request $request)
    {
        $tenantId = app('tenant')->id;
        $platform = $request->get('platform');

        if ($platform) {
            $this->syncService->syncPlatform($tenantId, $platform);
        } else {
            $this->syncService->syncAll($tenantId);
        }

        return back()->with('success', 'Analytics synced successfully');
    }

    public function reports(Request $request)
    {
        $tenantId = app('tenant')->id;

        $reports = AnalyticsReport::where('tenant_id', $tenantId)
            ->orderByDesc('report_date')
            ->paginate(20);

        return Inertia::render('Analytics/Reports', [
            'reports' => $reports,
        ]);
    }

    public function export(Request $request)
    {
        $tenantId = app('tenant')->id;
        $format = $request->get('format', 'csv');
        $days = $request->get('days', 30);

        $metrics = $this->syncService->getDashboardMetrics($tenantId, $days);

        if ($format === 'csv') {
            return $this->exportCsv($metrics);
        }

        return $this->exportPdf($metrics);
    }

    private function getPlatformBreakdown(int $tenantId, int $days): array
    {
        $platforms = ['facebook', 'twitter', 'linkedin', 'instagram'];
        $breakdown = [];

        foreach ($platforms as $platform) {
            $breakdown[$platform] = $this->getPlatformMetrics($tenantId, $platform, $days);
        }

        return $breakdown;
    }

    private function getPlatformMetrics(int $tenantId, string $platform, int $days): array
    {
        // Implementation similar to AnalyticsSyncService
        return [
            'followers' => 0,
            'engagement' => 0,
            'impressions' => 0,
            'reach' => 0,
            'posts' => 0,
        ];
    }

    private function exportCsv(array $metrics)
    {
        $filename = 'analytics_' . now()->format('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($metrics) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Metric', 'Value']);
            
            foreach ($metrics as $key => $value) {
                if (is_array($value)) {
                    foreach ($value as $subKey => $subValue) {
                        fputcsv($file, ["{$key}_{$subKey}", $subValue]);
                    }
                } else {
                    fputcsv($file, [$key, $value]);
                }
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function exportPdf(array $metrics)
    {
        // PDF export implementation
        return back()->with('error', 'PDF export not yet implemented');
    }
}

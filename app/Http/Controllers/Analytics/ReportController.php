<?php

namespace App\Http\Controllers\Analytics;

use App\Http\Controllers\Controller;
use App\Models\AnalyticsReport;
use App\Services\Analytics\ReportGeneratorService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReportController extends Controller
{
    public function __construct(
        private readonly ReportGeneratorService $generatorService
    ) {}

    public function create(Request $request)
    {
        $tenantId = app('tenant')->id;

        return Inertia::render('Analytics/ReportCreate', [
            'platforms' => ['facebook', 'twitter', 'linkedin', 'instagram'],
            'reportTypes' => [
                'weekly' => 'Weekly Report',
                'monthly' => 'Monthly Report',
                'custom' => 'Custom Report',
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:weekly,monthly,custom',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'platforms' => 'required|array',
            'platforms.*' => 'in:facebook,twitter,linkedin,instagram',
            'email' => 'nullable|email',
            'schedule' => 'nullable|in:daily,weekly,monthly',
        ]);

        $report = $this->generatorService->generateReport(
            app('tenant')->id,
            $validated
        );

        if ($validated['email']) {
            $this->generatorService->sendReportEmail($report, $validated['email']);
        }

        if ($validated['schedule']) {
            $this->generatorService->scheduleReport($report, $validated['schedule']);
        }

        return redirect()->route('analytics.reports')
            ->with('success', 'Report generated successfully');
    }

    public function show(AnalyticsReport $report)
    {
        $this->authorize('view', $report);

        return Inertia::render('Analytics/ReportShow', [
            'report' => $report,
        ]);
    }

    public function destroy(AnalyticsReport $report)
    {
        $this->authorize('delete', $report);

        $report->delete();

        return back()->with('success', 'Report deleted');
    }
}

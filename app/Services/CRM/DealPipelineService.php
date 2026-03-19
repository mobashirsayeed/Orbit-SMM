<?php

namespace App\Services\CRM;

use App\Models\Deal;
use App\Models\Pipeline;
use App\Models\PipelineStage;
use App\Models\DealActivity;

class DealPipelineService
{
    public function getPipelineOverview(int $tenantId): array
    {
        $pipelines = Pipeline::where('tenant_id', $tenantId)->with('stages.deals')->get();

        return $pipelines->map(fn($pipeline) => [
            'id' => $pipeline->id,
            'name' => $pipeline->name,
            'total_deals' => $pipeline->deals()->open()->count(),
            'total_value' => $pipeline->deals()->open()->sum('value'),
            'weighted_value' => $this->calculateWeightedValue($pipeline),
            'win_rate' => $pipeline->win_rate,
            'stages' => $pipeline->stages->map(fn($stage) => [
                'id' => $stage->id,
                'name' => $stage->name,
                'color' => $stage->color,
                'deals_count' => $stage->deals_count,
                'deals_value' => $stage->deals_value,
                'probability' => $stage->probability,
            ]),
        ])->toArray();
    }

    public function calculateWeightedValue(Pipeline $pipeline): float
    {
        $total = 0;
        
        foreach ($pipeline->deals()->open()->get() as $deal) {
            $total += $deal->weighted_value;
        }

        return $total;
    }

    public function createDeal(array $data): Deal
    {
        return Deal::create([
            'tenant_id' => $data['tenant_id'],
            'pipeline_id' => $data['pipeline_id'],
            'stage_id' => $data['stage_id'],
            'contact_id' => $data['contact_id'],
            'user_id' => $data['user_id'] ?? null,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'value' => $data['value'] ?? null,
            'currency' => $data['currency'] ?? 'USD',
            'expected_close_date' => $data['expected_close_date'] ?? null,
        ]);
    }

    public function moveDeal(Deal $deal, PipelineStage $newStage): void
    {
        $oldStageId = $deal->stage_id;
        $deal->moveToStage($newStage);

        // Log activity
        DealActivity::create([
            'tenant_id' => $deal->tenant_id,
            'deal_id' => $deal->id,
            'type' => 'note',
            'subject' => 'Stage changed',
            'description' => "Moved from stage {$oldStageId} to {$newStage->id}",
        ]);

        // Trigger automation rules
        $this->triggerAutomation($deal, 'stage_changed');
    }

    public function getUpcomingActivities(int $tenantId, int $days = 7): array
    {
        return DealActivity::where('tenant_id', $tenantId)
            ->pending()
            ->whereBetween('scheduled_at', [now(), now()->addDays($days)])
            ->with(['deal', 'user'])
            ->orderBy('scheduled_at')
            ->get()
            ->toArray();
    }

    public function getOverdueDeals(int $tenantId): array
    {
        return Deal::where('tenant_id', $tenantId)
            ->overdue()
            ->with(['contact', 'stage', 'owner'])
            ->orderBy('expected_close_date')
            ->get()
            ->toArray();
    }

    private function triggerAutomation(Deal $deal, string $triggerType): void
    {
        // Load automation rules and execute matching actions
        // This would integrate with the AutomationRule model
    }
}

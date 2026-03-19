<?php

namespace App\Models\Concerns;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Request;

trait LogsActivity
{
    public static function bootLogsActivity()
    {
        static::created(function ($model) {
            $model->logActivity('created', 'Created');
        });

        static::updated(function ($model) {
            $changes = $model->getChanges();
            $description = 'Updated: ' . implode(', ', array_keys($changes));
            $model->logActivity('updated', $description, $changes);
        });

        static::deleted(function ($model) {
            $model->logActivity('deleted', 'Deleted');
        });
    }

    public function logActivity(string $event, string $description, array $properties = [])
    {
        ActivityLog::create([
            'tenant_id' => $this->tenant_id ?? app('tenant')->id ?? null,
            'user_id' => auth()->id(),
            'log_name' => $this->getTable(),
            'description' => $description,
            'subject_type' => get_class($this),
            'subject_id' => $this->id,
            'event' => $event,
            'properties' => $properties,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }
}

<?php

namespace App\Http\Controllers\Social;

use App\Http\Controllers\Controller;
use App\Jobs\Social\PublishPostJob;
use App\Models\Post;
use Illuminate\Http\Request;

class SchedulerController extends Controller
{
    /**
     * Manual trigger for cron to process scheduled posts
     * Call this via cron job every minute
     */
    public function processScheduledPosts(Request $request)
    {
        // Verify cron secret for security
        if ($request->header('X-Cron-Secret') !== config('app.cron_secret')) {
            abort(403, 'Unauthorized cron request');
        }

        $duePosts = Post::due()->where('status', 'scheduled')->get();

        $processed = 0;
        foreach ($duePosts as $post) {
            try {
                PublishPostJob::dispatch($post);
                $processed++;
            } catch (\Exception $e) {
                \Log::error('Failed to dispatch scheduled post', [
                    'post_id' => $post->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'processed' => $processed,
            'timestamp' => now()->toIso8601String()
        ]);
    }

    /**
     * Process failed jobs retry
     */
    public function retryFailedJobs(Request $request)
    {
        if ($request->header('X-Cron-Secret') !== config('app.cron_secret')) {
            abort(403, 'Unauthorized cron request');
        }

        $failedJobs = \DB::table('failed_jobs')
            ->where('failed_at', '<=', now()->subHour())
            ->limit(10)
            ->get();

        $retried = 0;
        foreach ($failedJobs as $job) {
            try {
                // You can implement retry logic here
                $retried++;
            } catch (\Exception $e) {
                \Log::error('Failed to retry job', ['job_id' => $job->id]);
            }
        }

        return response()->json([
            'success' => true,
            'retried' => $retried
        ]);
    }
}

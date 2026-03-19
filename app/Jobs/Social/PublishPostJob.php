<?php

namespace App\Jobs\Social;

use App\Models\Post;
use App\Models\SocialAccount;
use App\Services\Social\PublisherService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PublishPostJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;
    public int $timeout = 120;
    public array $backoff = [30, 120, 600];
    public string $queue = 'default';

    public function __construct(private readonly Post $post) {}

    public function handle(PublisherService $pub): void
    {
        try {
            $this->post->update(['status' => 'publishing']);
            
            $results = [];
            $ok = true;

            foreach ($this->post->platforms as $platform) {
                $account = SocialAccount::withoutGlobalScopes()
                    ->where('workspace_id', $this->post->workspace_id)
                    ->where('platform', $platform)
                    ->first();

                if (!$account) {
                    $results[$platform] = ['success' => false, 'error' => 'No account connected'];
                    $ok = false;
                    continue;
                }

                try {
                    $r = match ($platform) {
                        'facebook' => $pub->publishToFacebook($this->post, $account),
                        'twitter' => $pub->publishToTwitter($this->post, $account),
                        'linkedin' => $pub->publishToLinkedIn($this->post, $account),
                        'instagram' => $pub->publishToInstagram($this->post, $account),
                        default => ['success' => false, 'error' => 'Unsupported platform'],
                    };

                    $results[$platform] = $r;

                    if (!$r['success']) {
                        $ok = false;
                    }

                    // Small delay to avoid rate limiting on shared hosting
                    sleep(2);

                } catch (\Exception $e) {
                    $results[$platform] = [
                        'success' => false, 
                        'error' => $e->getMessage()
                    ];
                    $ok = false;
                }
            }

            $this->post->update([
                'status' => $ok ? 'published' : 'failed',
                'published_at' => $ok ? now() : null,
                'platform_results' => $results,
            ]);

        } catch (\Exception $e) {
            \Log::error('PublishPostJob failed: ' . $e->getMessage(), [
                'post_id' => $this->post->id,
                'error' => $e->getMessage()
            ]);
            
            $this->post->update([
                'status' => 'failed',
                'meta' => array_merge(
                    $this->post->meta ?? [], 
                    ['error' => $e->getMessage()]
                ),
            ]);
        }
    }

    public function failed(\Throwable $exception): void
    {
        \Log::error('PublishPostJob permanently failed', [
            'post_id' => $this->post->id,
            'error' => $exception->getMessage()
        ]);

        $this->post->update([
            'status' => 'failed',
            'meta' => array_merge(
                $this->post->meta ?? [],
                ['permanent_error' => $exception->getMessage()]
            ),
        ]);
    }
}

<?php

namespace App\Services\AI;

use App\Models\SocialPost;
use App\Models\HashtagSet;
use Illuminate\Support\Facades\Http;

class HashtagSuggestionService
{
    public function suggestForPost(SocialPost $post, int $limit = 15): array
    {
        $prompt = "Generate {$limit} relevant hashtags for this social media post:
        
Post Content: {$post->body}
Platforms: " . implode(', ', $post->platforms) . "

Return as JSON array of strings only, no explanations.";

        $response = \OpenAI\Laravel\Facades\OpenAI::chat()->create([
            'model' => 'gpt-4o',
            'messages' => [
                ['role' => 'system', 'content' => 'You are a hashtag expert. Generate trending, relevant hashtags.'],
                ['role' => 'user', 'content' => $prompt],
            ],
            'temperature' => 0.7,
            'max_tokens' => 500,
        ]);

        $content = $response->choices[0]->message->content;
        $hashtags = json_decode($content, true) ?? [];

        // Clean hashtags
        $hashtags = array_map(fn($tag) => str_replace('#', '', $tag), $hashtags);
        $hashtags = array_filter($hashtags, fn($tag) => strlen($tag) > 0);

        return array_slice(array_values($hashtags), 0, $limit);
    }

    public function suggestByTopic(string $topic, int $limit = 15): array
    {
        $prompt = "Generate {$limit} popular hashtags related to: {$topic}
        
Return as JSON array of strings only.";

        $response = \OpenAI\Laravel\Facades\OpenAI::chat()->create([
            'model' => 'gpt-4o',
            'messages' => [
                ['role' => 'user', 'content' => $prompt],
            ],
            'temperature' => 0.7,
            'max_tokens' => 500,
        ]);

        $content = $response->choices[0]->message->content;
        $hashtags = json_decode($content, true) ?? [];

        return array_slice(array_values($hashtags), 0, $limit);
    }

    public function getSavedSets(int $tenantId, ?string $category = null): array
    {
        $query = HashtagSet::where('tenant_id', $tenantId);

        if ($category) {
            $query->where('category', $category);
        }

        return $query->orderBy('uses_count', 'desc')->get()->toArray();
    }

    public function saveHashtagSet(int $tenantId, string $name, array $hashtags, ?string $category = null): HashtagSet
    {
        return HashtagSet::create([
            'tenant_id' => $tenantId,
            'name' => $name,
            'hashtags' => $hashtags,
            'category' => $category,
        ]);
    }
}

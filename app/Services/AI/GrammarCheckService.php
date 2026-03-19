<?php

namespace App\Services\AI;

class GrammarCheckService
{
    public function check(string $text): array
    {
        $prompt = "Check the following text for grammar, spelling, and punctuation errors. 
Return a JSON object with:
- corrected_text: The corrected version
- errors: Array of objects with {original, corrected, type, explanation}
- score: Overall quality score 1-10

Text: {$text}";

        $response = \OpenAI\Laravel\Facades\OpenAI::chat()->create([
            'model' => 'gpt-4o',
            'messages' => [
                ['role' => 'system', 'content' => 'You are a grammar expert. Provide detailed corrections.'],
                ['role' => 'user', 'content' => $prompt],
            ],
            'temperature' => 0.3,
            'max_tokens' => 2000,
            'response_format' => ['type' => 'json_object'],
        ]);

        $content = $response->choices[0]->message->content;
        $result = json_decode($content, true);

        return $result ?? [
            'corrected_text' => $text,
            'errors' => [],
            'score' => 10,
        ];
    }

    public function improve(string $text, string $tone = 'professional'): string
    {
        $prompt = "Improve the following text for clarity, impact, and {$tone} tone. 
Keep the original meaning but make it more engaging and effective.

Text: {$text}";

        $response = \OpenAI\Laravel\Facades\OpenAI::chat()->create([
            'model' => 'gpt-4o',
            'messages' => [
                ['role' => 'system', 'content' => 'You are a professional editor. Improve text quality.'],
                ['role' => 'user', 'content' => $prompt],
            ],
            'temperature' => 0.5,
            'max_tokens' => 2000,
        ]);

        return $response->choices[0]->message->content ?? $text;
    }
}

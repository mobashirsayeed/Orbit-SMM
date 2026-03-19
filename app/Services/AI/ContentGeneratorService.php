<?php

namespace App\Services\AI;

use App\Models\BrandVoice;

class ContentGeneratorService
{
    public function generate(string $prompt, ?BrandVoice $bv = null): string
    {
        $response = \OpenAI\Laravel\Facades\OpenAI::chat()->create([
            'model' => 'gpt-4o',
            'messages' => [
                ['role' => 'system', 'content' => $this->systemPrompt($bv)],
                ['role' => 'user', 'content' => $prompt],
            ],
            'temperature' => 0.7,
            'max_tokens' => 2000,
        ]);
        return $response->choices[0]->message->content;
    }

    private function systemPrompt(?BrandVoice $bv): string
    {
        $base = "You are an expert digital marketing content creator.";
        if ($bv) {
            $base .= "\n\nBrand Voice: Tone={$bv->tone}. {$bv->instructions}";
        }
        return $base;
    }
}

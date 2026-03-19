<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Http;

class TranslationService
{
    public function translate(string $text, string $targetLanguage, string $sourceLanguage = 'en'): string
    {
        $response = \OpenAI\Laravel\Facades\OpenAI::chat()->create([
            'model' => 'gpt-4o',
            'messages' => [
                [
                    'role' => 'system', 
                    'content' => "You are a professional translator. Translate the following text from {$sourceLanguage} to {$targetLanguage}. Maintain the tone and style. Do not add any explanations."
                ],
                ['role' => 'user', 'content' => $text],
            ],
            'temperature' => 0.3,
            'max_tokens' => 2000,
        ]);

        return $response->choices[0]->message->content ?? $text;
    }

    public function detectLanguage(string $text): string
    {
        $response = \OpenAI\Laravel\Facades\OpenAI::chat()->create([
            'model' => 'gpt-4o',
            'messages' => [
                [
                    'role' => 'system', 
                    'content' => 'Detect the language of the following text. Return only the ISO 639-1 language code (e.g., en, es, fr, de).'
                ],
                ['role' => 'user', 'content' => $text],
            ],
            'temperature' => 0.1,
            'max_tokens' => 10,
        ]);

        $code = trim($response->choices[0]->message->content ?? 'en');
        
        // Validate language code
        $validCodes = ['en', 'es', 'fr', 'de', 'it', 'pt', 'nl', 'ru', 'ja', 'zh', 'ko', 'ar'];
        
        return in_array($code, $validCodes) ? $code : 'en';
    }

    public function translateMultiple(array $texts, string $targetLanguage, string $sourceLanguage = 'en'): array
    {
        $results = [];

        foreach ($texts as $text) {
            $results[] = $this->translate($text, $targetLanguage, $sourceLanguage);
        }

        return $results;
    }
}

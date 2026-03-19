<?php

namespace App\Services\AI;

use App\Models\AIContentGeneration;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class ImageGenerationService
{
    public function generate(string $prompt, string $size = '1024x1024', int $userId = null, int $tenantId = null): array
    {
        $response = \OpenAI\Laravel\Facades\OpenAI::images()->create([
            'model' => 'dall-e-3',
            'prompt' => $prompt,
            'n' => 1,
            'size' => $size,
            'response_format' => 'url',
        ]);

        $imageUrl = $response->data[0]->url ?? null;

        if (!$imageUrl) {
            return ['success' => false, 'error' => 'Image generation failed'];
        }

        // Download and store image
        $imageContent = Http::get($imageUrl)->body();
        $filename = 'ai-generated/' . uniqid() . '_' . time() . '.png';
        Storage::disk('public')->put($filename, $imageContent);

        $url = Storage::disk('public')->url($filename);

        // Log generation
        if ($userId && $tenantId) {
            AIContentGeneration::create([
                'tenant_id' => $tenantId,
                'user_id' => $userId,
                'type' => 'image',
                'prompt' => $prompt,
                'response' => $url,
                'tokens_used' => 0, // DALL-E uses different pricing
                'meta' => ['size' => $size, 'model' => 'dall-e-3'],
            ]);
        }

        return [
            'success' => true,
            'url' => $url,
            'original_url' => $imageUrl,
        ];
    }

    public function generateVariation(string $imageUrl, int $userId = null, int $tenantId = null): array
    {
        // Download original image
        $imageContent = Http::get($imageUrl)->body();
        $tempPath = storage_path('app/temp/' . uniqid() . '.png');
        file_put_contents($tempPath, $imageContent);

        $response = \OpenAI\Laravel\Facades\OpenAI::images()->createVariation([
            'image' => fopen($tempPath, 'r'),
            'n' => 1,
            'size' => '1024x1024',
            'response_format' => 'url',
        ]);

        unlink($tempPath);

        $variationUrl = $response->data[0]->url ?? null;

        if (!$variationUrl) {
            return ['success' => false, 'error' => 'Variation generation failed'];
        }

        // Download and store variation
        $variationContent = Http::get($variationUrl)->body();
        $filename = 'ai-generated/' . uniqid() . '_' . time() . '_variation.png';
        Storage::disk('public')->put($filename, $variationContent);

        $url = Storage::disk('public')->url($filename);

        return [
            'success' => true,
            'url' => $url,
            'original_url' => $variationUrl,
        ];
    }

    public function edit(string $imageUrl, string $maskUrl, string $prompt, int $userId = null, int $tenantId = null): array
    {
        // Download image and mask
        $imageContent = Http::get($imageUrl)->body();
        $maskContent = Http::get($maskUrl)->body();
        
        $imagePath = storage_path('app/temp/' . uniqid() . '.png');
        $maskPath = storage_path('app/temp/' . uniqid() . '_mask.png');
        
        file_put_contents($imagePath, $imageContent);
        file_put_contents($maskPath, $maskContent);

        $response = \OpenAI\Laravel\Facades\OpenAI::images()->edit([
            'image' => fopen($imagePath, 'r'),
            'mask' => fopen($maskPath, 'r'),
            'prompt' => $prompt,
            'n' => 1,
            'size' => '1024x1024',
            'response_format' => 'url',
        ]);

        unlink($imagePath);
        unlink($maskPath);

        $editedUrl = $response->data[0]->url ?? null;

        if (!$editedUrl) {
            return ['success' => false, 'error' => 'Image edit failed'];
        }

        // Download and store edited image
        $editedContent = Http::get($editedUrl)->body();
        $filename = 'ai-generated/' . uniqid() . '_' . time() . '_edited.png';
        Storage::disk('public')->put($filename, $editedContent);

        $url = Storage::disk('public')->url($filename);

        return [
            'success' => true,
            'url' => $url,
            'original_url' => $editedUrl,
        ];
    }
}

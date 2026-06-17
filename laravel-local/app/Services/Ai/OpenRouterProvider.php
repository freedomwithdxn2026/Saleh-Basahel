<?php

namespace App\Services\Ai;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class OpenRouterProvider implements AiProvider
{
    public function complete(array $messages, array $options = []): string
    {
        $apiKey = config('services.ai.openrouter.api_key');

        if (! is_string($apiKey) || trim($apiKey) === '') {
            throw new RuntimeException('OpenRouter API key is not configured.');
        }

        $baseUrl = rtrim((string) config('services.ai.openrouter.base_url'), '/');
        $model = (string) ($options['model'] ?? config('services.ai.openrouter.model'));
        $timeout = (int) ($options['timeout'] ?? config('services.ai.openrouter.timeout', 30));
        $maxRetries = (int) ($options['max_retries'] ?? config('services.ai.openrouter.max_retries', 2));

        $response = Http::withToken($apiKey)
            ->acceptJson()
            ->asJson()
            ->timeout(max(5, $timeout))
            ->retry(max(0, $maxRetries), 500)
            ->post($baseUrl.'/chat/completions', [
                'model' => $model,
                'messages' => $messages,
                'temperature' => $options['temperature'] ?? 0.7,
                'max_tokens' => $options['max_tokens'] ?? 700,
            ]);

        if (! $response->successful()) {
            throw new RuntimeException('OpenRouter request failed with HTTP '.$response->status().'.');
        }

        $content = $response->json('choices.0.message.content');

        if (! is_string($content) || trim($content) === '') {
            throw new RuntimeException('OpenRouter returned an empty response.');
        }

        return trim($content);
    }
}


<?php

namespace App\Services\Ai;

use InvalidArgumentException;

class AiProviderManager
{
    public function provider(?string $name = null): AiProvider
    {
        $name = $name ?: (string) config('services.ai.provider', 'openrouter');

        return match ($name) {
            'openrouter' => app(OpenRouterProvider::class),
            default => throw new InvalidArgumentException("Unsupported AI provider [{$name}]."),
        };
    }

    /**
     * @param array<int, array{role: string, content: string}> $messages
     * @param array<string, mixed> $options
     */
    public function complete(array $messages, array $options = []): string
    {
        return $this->provider($options['provider'] ?? null)->complete($messages, $options);
    }
}


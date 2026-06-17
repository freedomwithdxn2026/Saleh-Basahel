<?php

namespace App\Services\Ai;

interface AiProvider
{
    /**
     * @param array<int, array{role: string, content: string}> $messages
     * @param array<string, mixed> $options
     */
    public function complete(array $messages, array $options = []): string;
}


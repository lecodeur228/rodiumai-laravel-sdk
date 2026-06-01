<?php

namespace RodiumAI\Data;

/**
 * Helper for building chat message objects ({role, content}).
 */
class ChatMessage
{
    public function __construct(
        public readonly string $role,
        public readonly string $content,
    ) {}

    public static function user(string $content): static
    {
        return new static('user', $content);
    }

    public static function assistant(string $content): static
    {
        return new static('assistant', $content);
    }

    public static function system(string $content): static
    {
        return new static('system', $content);
    }

    /** @return array{role: string, content: string} */
    public function toArray(): array
    {
        return ['role' => $this->role, 'content' => $this->content];
    }
}

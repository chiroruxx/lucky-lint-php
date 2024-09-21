<?php

declare(strict_types=1);

namespace Chiroruxxxx\LuckyLint\Tokens;

readonly class Token
{
    public function __construct(
        public string $type,
        public string $content,
        public int $seq,
    ) {
    }

    public function __toString(): string
    {
        return "{$this->type}: {$this->content}";
    }
}

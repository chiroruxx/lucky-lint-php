<?php

declare(strict_types=1);

namespace Chiroruxxxx\LuckyLint\Strokes;

use Chiroruxxxx\LuckyLint\Tokens\NameToken;

readonly class CheckResult
{
    public function __construct(
        private NameToken $token,
        private int $count,
        private LuckyLevel $level,
    )
    {
    }

    public function getSeq(): int
    {
        return $this->token->keywordSeq;
    }

    public function getType(): string
    {
        return $this->token->keywordType;
    }

    public function getName(): string
    {
        return $this->token->getName();
    }

    public function getCount(): int
    {
        return $this->count;
    }

    public function getLevel(): LuckyLevel
    {
        return $this->level;
    }

    public function getLevelString(): string
    {
        return $this->level->toString();
    }
}

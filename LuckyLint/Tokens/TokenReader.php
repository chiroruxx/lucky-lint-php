<?php

declare(strict_types=1);

namespace Chiroruxxxx\LuckyLint\Tokens;

use PHP_CodeSniffer\Files\File;
use RuntimeException;

class TokenReader
{
    private int $seq = 0;

    public function __construct(
        private readonly File $file,
    ) {
    }

    public function read(int $seq): Token
    {
        $phpcsTokens = $this->file->getTokens();
        if (!isset($phpcsTokens[$seq])) {
            throw new RuntimeException("token is not found at $seq");
        }
        $phpcsToken = $phpcsTokens[$seq];

        if (count($phpcsToken) < 2) {
            throw new RuntimeException('Invalid token size');
        }
        $expectedKeys = [
            'type',
            'content',
        ];
        foreach ($expectedKeys as $expectedKey) {
            if (!array_key_exists($expectedKey, $phpcsToken)) {
                throw new RuntimeException("$expectedKey is not found");
            }
        }

        $this->seq = $seq;

        return new Token(
            $phpcsToken['type'],
            $phpcsToken['content'],
            $seq,
        );
    }

    public function next(int $step = 1): Token
    {
        return $this->read($this->seq + $step);
    }

    public function nextType(string $type): Token
    {
        while (true) {
            $token = $this->next();
            if ($token->type === $type) {
                return $token;
            }
        }
    }

    public function prev(int $step = 1): Token
    {
        return $this->read($this->seq - $step);
    }

    public function prevType(string $type): Token
    {
        while (true) {
            $token = $this->prev();
            if ($token->type === $type) {
                return $token;
            }
        }
    }
}

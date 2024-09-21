<?php

declare(strict_types=1);

namespace Chiroruxxxx\LuckyLint\Tokens;

use RuntimeException;

readonly class NameToken
{
    public string $type;
    public string $content;

    public function __construct(
        Token $token,
        public string $keywordType,
        public int $keywordSeq,
    ) {
        $this->type = $token->type;
        $this->content = $token->content;
    }

    public function getName(): string
    {
        switch ($this->type) {
            case 'T_VARIABLE':
                return ltrim($this->content, '$');
            case 'T_CONSTANT_ENCAPSED_STRING':
                $keyword = substr($this->content, 0, 1);
                return trim($this->content, $keyword);
            case 'T_STRING':
                return $this->content;
            default:
                throw new RuntimeException("type $this->type is not supported");
        }
    }

    public function __toString(): string
    {
        return "$this->type: $this->content";
    }
}

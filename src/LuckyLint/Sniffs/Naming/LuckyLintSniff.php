<?php

declare(strict_types=1);

namespace Chiroruxxxx\LuckyLint\LuckyLint\Sniffs\Naming;

use InvalidArgumentException;
use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use RuntimeException;

class LuckyLintSniff implements Sniff
{
    public int $minLevel = 3;

    public function register(): array
    {
        return [
            T_AS,
            T_CLASS,
            T_CONST,
            T_FUNCTION,
            T_INTERFACE,
            T_NAMESPACE,
            T_STRING,
            T_TRAIT,
            T_VARIABLE,
        ];
    }

    public function process(File $phpcsFile, $stackPtr): ?int
    {
        $minLevel = LuckyLevel::tryFrom($this->minLevel);
        if ($minLevel === null || $minLevel === LuckyLevel::LEVEL_0) {
            throw new InvalidArgumentException('minLevel is invalid.');
        }

        $keywordToken = $this->getToken($phpcsFile, $stackPtr);
        $tokens = $this->getTargetTokens($phpcsFile, $keywordToken);
        if ($tokens === null) {
            return null;
        }

        foreach ($tokens as $token) {
            $name = $token->getName();
            $count = StrokeCounter::count($name);
            $level = LuckyChecker::check($count);
            if ($level->lessThan($minLevel)) {
                $list = implode(',', LuckyChecker::list($minLevel));
                $phpcsFile->addError(
                    '"%s" is not lucky; stroke count: %d, lucky counts: %s',
                    $stackPtr,
                    $token->keywordType,
                    [$name, $count, $list]
                );
            }
        }

        return null;
    }

    public function getToken(File $file, int $stackPtr): Token
    {
        $phpcsTokens = $file->getTokens();
        if (!isset($phpcsTokens[$stackPtr])) {
            throw new RuntimeException("token is not found at {$stackPtr}");
        }
        $phpcsToken = $phpcsTokens[$stackPtr];

        if (count($phpcsToken) < 2) {
            throw new RuntimeException('Invalid token size');
        }
        $expectedKeys = [
            'type',
            'content',
        ];
        foreach ($expectedKeys as $expectedKey) {
            if (!array_key_exists($expectedKey, $phpcsToken)) {
                throw new RuntimeException("{$expectedKey} is not found");
            }
        }

        return new Token(
            $phpcsToken['type'],
            $phpcsToken['content'],
            $stackPtr,
        );
    }

    /** @return array<int, TargetToken>|null */
    private function getTargetTokens(File $file, Token $token): ?array
    {
        switch ($token->type) {
            case 'T_VARIABLE':
                // $var
                return [
                    new TargetToken(
                        $token,
                        $token->type,
                    )
                ];
            case 'T_STRING':
                // define('CONST')
                if ($token->content !== 'define') {
                    return null;
                }
                $next = $this->getToken($file, $token->seq + 1);
                if ($next->type !== 'T_OPEN_PARENTHESIS') {
                    return null;
                }
                $next = $this->getToken($file, $next->seq + 1);
                if ($next->type !== 'T_CONSTANT_ENCAPSED_STRING') {
                    return null;
                }
                return [
                    new TargetToken(
                        $next,
                        'T_DEFINE',
                    )
                ];
            case 'T_CLASS':
            case 'T_INTERFACE':
            case 'T_TRAIT':
            case 'T_FUNCTION':
            case 'T_AS':
                // class A {
                $next = $this->getToken($file, $token->seq + 2);
                if ($next->type !== 'T_STRING') {
                    return null;
                }
                return [
                    new TargetToken(
                        $next,
                        $token->type,
                    )
                ];
            case 'T_NAMESPACE':
                // namespace A\B {
                $seq = $token->seq + 2;
                $tokens = [];
                while (true) {
                    $next = $this->getToken($file, $seq);
                    if ($next->type === 'T_STRING') {
                        $tokens[] = new TargetToken(
                            $next,
                            $token->type,
                        );
                    } elseif ($next->type === 'T_SEMICOLON') {
                        break;
                    }
                    $seq++;
                }
                return $tokens;
            case 'T_CONST':
                // const CONST_ITEM = 0;
                // const int CONST_ITEM = 0;
                for ($seq1 = $token->seq + 1; ; $seq1++) {
                    $next = $this->getToken($file, $seq1);
                    if ($next->type === 'T_EQUAL') {
                        break;
                    }
                }
                for ($seq2 = $seq1 - 1; ; $seq2--) {
                    $prev = $this->getToken($file, $seq2);
                    if ($prev->type === 'T_STRING') {
                        return [
                            new TargetToken(
                                $prev,
                                $token->type,
                            )
                        ];
                    }
                }
            default:
                throw new RuntimeException("Unexpected token type: {$token->type}");
        }
    }
}

class Token
{
    public function __construct(
        public readonly string $type,
        public readonly string $content,
        public readonly int $seq,
    ) {
    }

    public function __toString(): string
    {
        return "{$this->type}: {$this->content}";
    }
}

class TargetToken
{
    public readonly string $type;
    public readonly string $content;

    public function __construct(
        Token $token,
        public readonly string $keywordType,
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
                throw new RuntimeException("type {$this->type} is not supported");
        }
    }

    public function __toString(): string
    {
        return "{$this->type}: {$this->content}";
    }
}

class StrokeCounter
{
    // see https://meimeimaker.com/articles/strokes-alphabet.php
    private const array COUNT_MAP = [
        'a' => 2,
        'b' => 2,
        'c' => 1,
        'd' => 2,
        'e' => 2,
        'f' => 2,
        'g' => 2,
        'h' => 2,
        'i' => 2,
        'j' => 2,
        'k' => 3,
        'l' => 1,
        'm' => 3,
        'n' => 2,
        'o' => 1,
        'p' => 2,
        'q' => 2,
        'r' => 2,
        's' => 1,
        't' => 2,
        'u' => 2,
        'v' => 2,
        'w' => 4,
        'x' => 2,
        'y' => 2,
        'z' => 3,
        'A' => 3,
        'B' => 3,
        'C' => 1,
        'D' => 2,
        'E' => 4,
        'F' => 3,
        'G' => 2,
        'H' => 3,
        'I' => 1,
        'J' => 1,
        'K' => 3,
        'L' => 2,
        'M' => 4,
        'N' => 3,
        'O' => 1,
        'P' => 2,
        'Q' => 2,
        'R' => 3,
        'S' => 1,
        'T' => 2,
        'U' => 1,
        'V' => 2,
        'W' => 4,
        'X' => 2,
        'Y' => 3,
        'Z' => 3,
        '_' => 1,
        '-' => 1,
    ];

    public static function count(string $name): int
    {
        $count = 0;
        $chars = str_split($name);

        foreach ($chars as $char) {
            if (!isset(self::COUNT_MAP[$char])) {
                throw new RuntimeException("{$char} is not found in count map");
            }
            $count += self::COUNT_MAP[$char];
        }

        return $count;
    }
}

enum LuckyLevel: int
{
    case LEVEL_0 = 0;
    case LEVEL_1 = 1;
    case LEVEL_2 = 2;
    case LEVEL_3 = 3;

    public function lessThan(self $another): bool
    {
        return $this->value < $another->value;
    }
}

class LuckyChecker
{
    private const array LEVEL_MAP = [
        0 => LuckyLevel::LEVEL_3,
        15 => LuckyLevel::LEVEL_3,
        24 => LuckyLevel::LEVEL_3,
        31 => LuckyLevel::LEVEL_3,
        32 => LuckyLevel::LEVEL_3,

        11 => LuckyLevel::LEVEL_2,
        16 => LuckyLevel::LEVEL_2,
        21 => LuckyLevel::LEVEL_2,
        23 => LuckyLevel::LEVEL_2,
        41 => LuckyLevel::LEVEL_2,

        3 => LuckyLevel::LEVEL_1,
        5 => LuckyLevel::LEVEL_1,
        6 => LuckyLevel::LEVEL_1,
        8 => LuckyLevel::LEVEL_1,
        13 => LuckyLevel::LEVEL_1,
        18 => LuckyLevel::LEVEL_1,
        25 => LuckyLevel::LEVEL_1,
        29 => LuckyLevel::LEVEL_1,
        33 => LuckyLevel::LEVEL_1,
        37 => LuckyLevel::LEVEL_1,
        39 => LuckyLevel::LEVEL_1,
        44 => LuckyLevel::LEVEL_1,
        45 => LuckyLevel::LEVEL_1,
        47 => LuckyLevel::LEVEL_1,
        48 => LuckyLevel::LEVEL_1,
        51 => LuckyLevel::LEVEL_1,
    ];

    public static function check(int $count): LuckyLevel
    {
        $count = $count % 52;

        if (!isset(self::LEVEL_MAP[$count])) {
            return LuckyLevel::LEVEL_0;
        }

        return self::LEVEL_MAP[$count];
    }

    public static function list(LuckyLevel $min): array
    {
        $counts = [];
        foreach (self::LEVEL_MAP as $count => $level) {
            if ($level->lessThan($min)) {
                break;
            }
            $counts[] = $count;
        }

        return $counts;
    }
}

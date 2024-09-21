<?php

declare(strict_types=1);

namespace Chiroruxxxx\LuckyLint\Sniffs\Naming;

use Chiroruxxxx\LuckyLint\Strokes\LuckyChecker;
use Chiroruxxxx\LuckyLint\Strokes\LuckyLevel;
use Chiroruxxxx\LuckyLint\Strokes\StrokeCounter;
use Chiroruxxxx\LuckyLint\Tokens\TargetToken;
use Chiroruxxxx\LuckyLint\Tokens\Token;
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

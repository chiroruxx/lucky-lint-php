<?php

declare(strict_types=1);

namespace Chiroruxxxx\LuckyLint\Tokens;

use PHP_CodeSniffer\Files\File;
use RuntimeException;

readonly class NameTokenReader
{
    /** @return array<int, NameToken> */
    private static function readByKeywordToken(Token $keyword, TokenReader $tokenReader): array
    {
        switch ($keyword->type) {
            case 'T_VARIABLE':
                // $var
                return [
                    new NameToken(
                        $keyword,
                        $keyword->type,
                        $keyword->seq,
                    )
                ];
            case 'T_STRING':
                // define('CONST')
                if ($keyword->content !== 'define') {
                    return [];
                }
                $op = $tokenReader->next();
                if ($op->type !== 'T_OPEN_PARENTHESIS') {
                    return [];
                }
                $target = $tokenReader->next();
                if ($target->type !== 'T_CONSTANT_ENCAPSED_STRING') {
                    return [];
                }
                return [
                    new NameToken(
                        $target,
                        'T_DEFINE',
                        $keyword->seq,
                    )
                ];
            case 'T_CLASS':
            case 'T_INTERFACE':
            case 'T_TRAIT':
            case 'T_FUNCTION':
            case 'T_AS':
                // class A {
                $next = $tokenReader->next(2);
                if ($next->type !== 'T_STRING') {
                    return [];
                }
                return [
                    new NameToken(
                        $next,
                        $keyword->type,
                        $keyword->seq,
                    )
                ];
            case 'T_NAMESPACE':
                // namespace A\B {
                $tokens = [];
                $next = $tokenReader->next(2);
                while (true) {
                    if ($next->type === 'T_STRING') {
                        $tokens[] = new NameToken(
                            $next,
                            $keyword->type,
                            $keyword->seq,
                        );
                    } elseif ($next->type === 'T_SEMICOLON') {
                        break;
                    }
                    $next = $tokenReader->next();
                }
                return $tokens;
            case 'T_CONST':
                // const CONST_ITEM = 0;
                // const int CONST_ITEM = 0;
                $tokenReader->nextType('T_EQUAL');
                $target = $tokenReader->prevType('T_STRING');
                return [
                    new NameToken(
                        $target,
                        $keyword->type,
                        $keyword->seq,
                    )
                ];
            default:
                throw new RuntimeException("Unexpected token type: $keyword->type");
        }
    }

    /** @return array<int, NameToken> */
    public static function readTokens(File $file, int $seq): array
    {
        $tokenReader = new TokenReader($file);
        $keywordToken = $tokenReader->read($seq);
        return self::readByKeywordToken($keywordToken, $tokenReader);
    }
}

<?php

declare(strict_types=1);

namespace Chiroruxxxx\LuckyLint\Sniffs\Naming;

use Chiroruxxxx\LuckyLint\Strokes\CheckResult;
use Chiroruxxxx\LuckyLint\Strokes\LuckyChecker;
use Chiroruxxxx\LuckyLint\Strokes\LuckyLevel;
use Chiroruxxxx\LuckyLint\Tokens\NameTokenReader;
use DomainException;
use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

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

    public function process(File $phpcsFile, $stackPtr): void
    {
        $minLevel = $this->getMinLevel();

        $tokens = NameTokenReader::readTokens($phpcsFile, $stackPtr);

        foreach ($tokens as $token) {
            $result = LuckyChecker::check($token);
            if ($result->getLevel()->lessThan($minLevel)) {
                $this->addError($phpcsFile, $result, $minLevel);
            }
        }
    }

    private function getMinLevel(): LuckyLevel
    {
        $level = LuckyLevel::tryFrom($this->minLevel);
        if ($level === null || $level === LuckyLevel::LEVEL_0) {
            throw new DomainException('minLevel is invalid.');
        }

        return $level;
    }

    private function addError(File $phpcsFile, CheckResult $result, LuckyLevel $minLevel): void
    {
        $list = implode(',', LuckyChecker::list($minLevel));
        $phpcsFile->addError(
            '"%s" is not lucky; stroke count: %d, lucky counts: %s',
            $result->getSeq(),
            $result->getType(),
            [$result->getName(), $result->getCount(), $list]
        );
    }
}

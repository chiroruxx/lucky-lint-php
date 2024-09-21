<?php

declare(strict_types=1);

namespace Chiroruxxxx\LuckyLint\Strokes;

use Chiroruxxxx\LuckyLint\Tokens\NameToken;

class LuckyChecker
{
    /** @see https://meimeimaker.com/articles/strokes-alphabet.php */
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

    public static function check(NameToken $token): CheckResult
    {
        $name = $token->getName();
        $count = StrokeCounter::count($name);
        $level = self::getLevel($count);
        return new CheckResult($token, $count, $level);
    }

    private static function getLevel(int $count): LuckyLevel
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

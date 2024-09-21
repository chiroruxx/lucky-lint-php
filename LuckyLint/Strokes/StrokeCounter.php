<?php

declare(strict_types=1);

namespace Chiroruxxxx\LuckyLint\Strokes;

use RuntimeException;

class StrokeCounter
{
    /** @see https://meimeimaker.com/articles/strokes-alphabet.php */
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
                throw new RuntimeException("$char is not found in count map");
            }
            $count += self::COUNT_MAP[$char];
        }

        return $count;
    }
}
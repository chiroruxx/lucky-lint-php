<?php

declare(strict_types=1);

namespace Chiroruxxxx\LuckyLint\Strokes;

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

    public function toString(): string
    {
        return match ($this) {
            self::LEVEL_3 => 'HIGH',
            self::LEVEL_2 => 'MIDDLE',
            self::LEVEL_1 => 'LOW',
            self::LEVEL_0 => 'BAD',
        };
    }
}

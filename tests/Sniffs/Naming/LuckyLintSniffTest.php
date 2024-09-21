<?php

declare(strict_types=1);

namespace Tests\Chiroruxxxx\LuckyLint\Sniffs\Naming;

use Chiroruxxxx\LuckyLint\Sniffs\Naming\LuckyLintSniff;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Chiroruxxxx\LuckyLint\TestCase;

class LuckyLintSniffTest extends TestCase
{
    #[DataProvider('providers')]
    public function testProcess(string $data, array $errorNames): void
    {
        $sniff = new LuckyLintSniff();
        $file = $this->loadFile($data, $sniff);
        $this->assertErrors($file, $errorNames);
    }

    public static function providers(): array
    {
        return [
            'class.php' => ['data' => 'class.php', 'errorNames' => ['A', 'a']],
            'define.php' => ['data' => 'define.php', 'errorNames' => ['A']],
            'function.php' => ['data' => 'function.php', 'errorNames' => ['a', 'b', 'c']],
            'interface.php' => ['data' => 'interface.php', 'errorNames' => ['a']],
            'trait.php' => ['data' => 'trait.php', 'errorNames' => ['a']],
            'use.php' => ['data' => 'use.php', 'errorNames' => ['MyNaming', 'a']],
            'variable.php' => ['data' => 'variable.php', 'errorNames' => ['a']],
        ];
    }
}

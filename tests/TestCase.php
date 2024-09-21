<?php

declare(strict_types=1);

namespace Tests\Chiroruxxxx\LuckyLint;

use PHP_CodeSniffer\Config;
use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Files\LocalFile;
use PHP_CodeSniffer\Runner;
use PHP_CodeSniffer\Sniffs\Sniff;

class TestCase extends \PHPUnit\Framework\TestCase
{
    protected function loadFile(string $filePath, Sniff $sniff): File
    {
        $phpcs = new Runner();
        $phpcs->config = new Config(['-s']);
        $phpcs->init();

        $phpcs->ruleset->sniffs = [$sniff::class => $sniff];

        $phpcs->ruleset->populateTokenListeners();

        $file = new LocalFile($filePath, $phpcs->ruleset, $phpcs->config);
        $file->process();

        return $file;
    }

    protected function assertErrors(File $file, array $names): void
    {
        $errors = $this->getErrorMessages($file);
        $this->assertCount(count($names), $errors);

        foreach ($names as $name) {
            $found = false;
            foreach ($errors as $error) {
                if (str_starts_with($error, "\"$name\" is not lucky;")) {
                    $found = true;
                    break;
                }
            }
            $this->assertTrue($found, "name $name is not appeared in error message");
        }
    }

    private function getErrorMessages(File $file): array {
        $results = [];
        foreach ($file->getErrors() as $error) {
            foreach ($error as $errorArray1) {
                foreach ($errorArray1 as $errorArray2) {
                    $results[] = $errorArray2['message'];
                }
            }
        }
        return $results;
    }
}
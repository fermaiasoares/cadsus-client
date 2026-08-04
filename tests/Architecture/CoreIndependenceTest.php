<?php

declare(strict_types=1);

namespace SolucaoInternet\Cadsus\Tests\Architecture;

use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

final class CoreIndependenceTest extends TestCase
{
    public function testCoreDoesNotDependOnLaravelOrApplicationModels(): void
    {
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(dirname(__DIR__, 2) . '/src'));

        foreach ($iterator as $file) {
            if (!$file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }

            $contents = file_get_contents($file->getPathname());
            self::assertStringNotContainsString('Illuminate\\', $contents, $file->getPathname());
            self::assertStringNotContainsString('App\\Models\\', $contents, $file->getPathname());
        }
    }
}

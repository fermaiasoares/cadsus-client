<?php

declare(strict_types=1);

namespace SolucaoInternet\Cadsus\Tests\Contract;

use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SolucaoInternet\Cadsus\Soap\ContractPaths;

final class ContractArtifactsTest extends TestCase
{
    public function testAllRecordedArtifactsExistAndMatchTheirHash(): void
    {
        $manifest = require ContractPaths::root() . '/manifest.php';

        self::assertSame('2.1', $manifest['manual_version']);
        self::assertCount(75, $manifest['files']);

        foreach ($manifest['files'] as $relativePath => $expectedHash) {
            $path = ContractPaths::root() . '/' . $relativePath;
            self::assertFileExists($path);
            self::assertSame($expectedHash, hash_file('sha256', $path), $relativePath);
        }
    }

    public function testNoUnrecordedContractFileExists(): void
    {
        $manifest = require ContractPaths::root() . '/manifest.php';
        $recorded = array_keys($manifest['files']);
        $actual = [];
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(ContractPaths::root()));

        foreach ($iterator as $file) {
            if (!$file->isFile() || $file->getFilename() === 'manifest.php') {
                continue;
            }

            $actual[] = str_replace(ContractPaths::root() . '/', '', $file->getPathname());
        }

        sort($actual);
        self::assertSame($recorded, $actual);
    }
}

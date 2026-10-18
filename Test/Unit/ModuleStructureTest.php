<?php
/**
 * Copyright © DMLab. All rights reserved.
 * Licensed under the Open Software License version 3.0 (OSL-3.0). See LICENSE.
 */
declare(strict_types=1);

namespace DmLab\AdminScimOkta\Test\Unit;

use PHPUnit\Framework\TestCase;

/**
 * The core handles Okta's SCIM shapes itself; guards against re-adding a dead normalizer.
 */
class ModuleStructureTest extends TestCase
{
    private const NORMALIZER_INTERFACE = 'DmLab\\AdminScim\\Api\\RequestNormalizerInterface';

    public function testNoDiXml(): void
    {
        self::assertFileDoesNotExist($this->moduleRoot() . '/etc/di.xml');
    }

    public function testNoClassImplementsRequestNormalizer(): void
    {
        $files = $this->nonTestPhpFiles();
        self::assertNotEmpty($files);

        foreach ($files as $file) {
            self::assertStringNotContainsString(
                self::NORMALIZER_INTERFACE,
                (string)file_get_contents($file),
                $file . ' references the request normalizer interface'
            );
        }
    }

    /**
     * @return string[]
     */
    private function nonTestPhpFiles(): array
    {
        $root = $this->moduleRoot();
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($root, \FilesystemIterator::SKIP_DOTS)
        );

        $files = [];
        foreach ($iterator as $file) {
            $relative = substr($file->getPathname(), strlen($root) + 1);
            if ($file->getExtension() !== 'php' || preg_match('#^(Test|vendor|docs)/#', $relative)) {
                continue;
            }
            $files[] = $file->getPathname();
        }

        return $files;
    }

    private function moduleRoot(): string
    {
        return dirname(__DIR__, 2);
    }
}

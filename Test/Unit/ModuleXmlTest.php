<?php
/**
 * Copyright © DMLab. All rights reserved.
 * Licensed under the Open Software License version 3.0 (OSL-3.0). See LICENSE.
 */
declare(strict_types=1);

namespace DmLab\AdminScimOkta\Test\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Every Magento module the code imports must be declared in composer.json and module.xml.
 */
class ModuleXmlTest extends TestCase
{
    public function testSequenceListsRequiredModules(): void
    {
        $xml = simplexml_load_file($this->moduleRoot() . '/etc/module.xml');
        self::assertNotFalse($xml);

        $sequence = [];
        foreach ($xml->module->sequence->module as $module) {
            $sequence[] = (string)$module['name'];
        }

        self::assertContains('DmLab_AdminScim', $sequence);
        foreach ($this->importedMagentoModules() as $module => $file) {
            self::assertContains('Magento_' . $module, $sequence, $file . ' uses Magento\\' . $module);
        }
    }

    public function testImportedMagentoModulesAreRequiredInComposer(): void
    {
        $require = $this->composer()['require'];

        foreach ($this->importedMagentoModules() as $module => $file) {
            $package = 'magento/module-' . strtolower((string)preg_replace('/(?<!^)[A-Z]/', '-$0', $module));
            self::assertArrayHasKey($package, $require, $file . ' uses Magento\\' . $module);
        }
    }

    public function testRequiresAdminScimCoreZeroTwo(): void
    {
        self::assertSame('^0.2.0', $this->composer()['require']['dmlab/module-admin-scim'] ?? null);
    }

    /**
     * Magento modules (other than Framework) imported by non-test code, mapped to an importing file.
     *
     * @return array<string, string>
     */
    private function importedMagentoModules(): array
    {
        $imported = [];
        foreach ($this->nonTestPhpFiles() as $file) {
            preg_match_all('/^use Magento\\\\(\w+)\\\\/m', (string)file_get_contents($file), $matches);
            foreach ($matches[1] as $module) {
                if ($module !== 'Framework') {
                    $imported[$module] = $file;
                }
            }
        }
        self::assertNotEmpty($imported);

        return $imported;
    }

    /**
     * @return array<string, mixed>
     */
    private function composer(): array
    {
        return json_decode(
            (string)file_get_contents($this->moduleRoot() . '/composer.json'),
            true,
            512,
            JSON_THROW_ON_ERROR
        );
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

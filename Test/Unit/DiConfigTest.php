<?php
/**
 * Copyright © MageDevGroup. All rights reserved.
 */
declare(strict_types=1);

namespace MageDevGroup\AdminScimOkta\Test\Unit;

use MageDevGroup\AdminScim\Model\Normalization\RequestNormalizerChain;
use MageDevGroup\AdminScimOkta\Model\Normalization\OktaRequestNormalizer;
use PHPUnit\Framework\TestCase;

/**
 * Verifies etc/di.xml wires the Okta normalizer into admin-scim's chain, so the
 * plugin actually plugs into the core's provider-quirk seam when installed.
 */
class DiConfigTest extends TestCase
{
    /** @var \DOMXPath */
    private \DOMXPath $xpath;

    protected function setUp(): void
    {
        $diXml = dirname(__DIR__, 2) . '/etc/di.xml';
        self::assertFileExists($diXml);

        $dom = new \DOMDocument();
        self::assertTrue($dom->load($diXml));
        $this->xpath = new \DOMXPath($dom);
    }

    public function testOktaNormalizerIsMergedIntoTheChain(): void
    {
        $items = $this->xpath->query(sprintf(
            '//type[@name="%s"]/arguments/argument[@name="normalizers"]/item',
            RequestNormalizerChain::class
        ));

        self::assertNotFalse($items);
        self::assertGreaterThan(0, $items->length, 'No normalizer item registered on the chain.');

        $registered = [];
        foreach ($items as $item) {
            $registered[] = trim($item->textContent);
        }

        self::assertContains(OktaRequestNormalizer::class, $registered);
    }

    public function testRegisteredNormalizerImplementsTheExtensionPoint(): void
    {
        self::assertContains(
            \MageDevGroup\AdminScim\Api\RequestNormalizerInterface::class,
            class_implements(OktaRequestNormalizer::class)
        );
    }
}

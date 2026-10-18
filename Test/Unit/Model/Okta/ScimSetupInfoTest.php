<?php
/**
 * Copyright © DMLab. All rights reserved.
 * Licensed under the Open Software License version 3.0 (OSL-3.0). See LICENSE.
 */
declare(strict_types=1);

namespace DmLab\AdminScimOkta\Test\Unit\Model\Okta;

use Magento\Framework\Escaper;
use Magento\Framework\Phrase;
use Magento\Framework\Phrase\Renderer\Placeholder;
use Magento\Framework\Phrase\RendererInterface;
use DmLab\AdminScim\Model\Discovery\EndpointUrlBuilder;
use DmLab\AdminScimOkta\Model\Okta\ScimSetupInfo;
use PHPUnit\Framework\TestCase;

/**
 * The setup surface must render the actual SCIM endpoint (from the core's URL
 * builder, HTML-escaped) plus the Okta wiring guidance an admin needs.
 */
class ScimSetupInfoTest extends TestCase
{
    private const ENDPOINT = 'https://magento.example/admin-scim/v2';

    /** Top-level `__()` phrases rendered by the panel. */
    private const TRANSLATED_PHRASES = 11;

    /** @var EndpointUrlBuilder */
    private EndpointUrlBuilder $urlBuilder;

    /** @var Escaper */
    private Escaper $escaper;

    /** @var ScimSetupInfo */
    private ScimSetupInfo $setupInfo;

    protected function setUp(): void
    {
        $this->urlBuilder = $this->createStub(EndpointUrlBuilder::class);
        $this->urlBuilder->method('baseUrl')->willReturn(self::ENDPOINT);

        $this->escaper = $this->createStub(Escaper::class);
        $this->escaper->method('escapeHtml')->willReturnArgument(0);

        $this->setupInfo = new ScimSetupInfo($this->urlBuilder, $this->escaper);
    }

    protected function tearDown(): void
    {
        Phrase::setRenderer(new Placeholder());
    }

    public function testEndpointUrlComesFromTheCoreBuilder(): void
    {
        self::assertSame(self::ENDPOINT, $this->setupInfo->getEndpointUrl());
    }

    public function testHtmlRendersTheEndpoint(): void
    {
        self::assertStringContainsString(self::ENDPOINT, $this->setupInfo->getHtml());
    }

    public function testHtmlEscapesTheEndpoint(): void
    {
        $urlBuilder = $this->createStub(EndpointUrlBuilder::class);
        $urlBuilder->method('baseUrl')->willReturn('https://h/admin-scim/v2?a=1&b=<x>');

        $html = (new ScimSetupInfo($urlBuilder, $this->htmlEscaper()))->getHtml();

        self::assertStringContainsString('https://h/admin-scim/v2?a=1&amp;b=&lt;x&gt;', $html);
        self::assertStringNotContainsString('<x>', $html);
    }

    public function testHtmlDocumentsBearerAndProvisioningGuidance(): void
    {
        $html = $this->setupInfo->getHtml();

        self::assertStringContainsString('Bearer', $html);
        self::assertStringContainsString('userName', $html);
        self::assertStringContainsString('Push New Users, Push Profile Updates, Push Groups', $html);
        self::assertStringContainsString('active = false', $html);
        self::assertStringContainsString('Default Role', $html);
        self::assertStringContainsString('Group → Role Map', $html);
        self::assertStringContainsString('Only SCIM-created admins are managed', $html);
    }

    public function testHtmlPointsToTheDmlabConfigPath(): void
    {
        self::assertStringContainsString(
            'Stores → Configuration → DMLab → Admin SCIM → Bearer Token',
            $this->setupInfo->getHtml()
        );
    }

    public function testTranslatedPhrasesAreEscaped(): void
    {
        Phrase::setRenderer(new class implements RendererInterface {
            public function render(array $source, array $arguments)
            {
                return '<i>' . end($source) . '</i>';
            }
        });

        $html = (new ScimSetupInfo($this->urlBuilder, $this->htmlEscaper()))->getHtml();

        self::assertSame(self::TRANSLATED_PHRASES, substr_count($html, '&lt;i&gt;'));
        self::assertStringContainsString('&lt;i&gt;Supported provisioning actions:&lt;/i&gt;', $html);
        self::assertStringNotContainsString('<i>', $html);
    }

    private function htmlEscaper(): Escaper
    {
        $escaper = $this->createStub(Escaper::class);
        $escaper->method('escapeHtml')->willReturnCallback(
            static fn (string $value): string => htmlspecialchars($value, ENT_QUOTES)
        );

        return $escaper;
    }
}

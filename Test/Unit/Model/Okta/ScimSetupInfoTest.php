<?php
/**
 * Copyright © MageDevGroup. All rights reserved.
 */
declare(strict_types=1);

namespace MageDevGroup\AdminScimOkta\Test\Unit\Model\Okta;

use Magento\Framework\Escaper;
use MageDevGroup\AdminScim\Model\Discovery\EndpointUrlBuilder;
use MageDevGroup\AdminScimOkta\Model\Okta\ScimSetupInfo;
use PHPUnit\Framework\TestCase;

/**
 * The setup surface must render the actual SCIM endpoint (from the core's URL
 * builder, HTML-escaped) plus the Okta wiring guidance an admin needs.
 */
class ScimSetupInfoTest extends TestCase
{
    private const ENDPOINT = 'https://magento.example/admin-scim/v2';

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

    public function testEndpointUrlComesFromTheCoreBuilder(): void
    {
        self::assertSame(self::ENDPOINT, $this->setupInfo->getEndpointUrl());
    }

    public function testHtmlRendersTheEndpoint(): void
    {
        self::assertStringContainsString(self::ENDPOINT, $this->setupInfo->getHtml());
    }

    public function testHtmlEscapesTheEndpointThroughTheEscaper(): void
    {
        $escaper = $this->createStub(Escaper::class);
        $escaper->method('escapeHtml')->willReturn('ESCAPED_ENDPOINT');

        $html = (new ScimSetupInfo($this->urlBuilder, $escaper))->getHtml();

        self::assertStringContainsString('ESCAPED_ENDPOINT', $html);
    }

    public function testHtmlDocumentsBearerAndProvisioningGuidance(): void
    {
        $html = $this->setupInfo->getHtml();

        self::assertStringContainsString('Bearer', $html);
        self::assertStringContainsString('userName', $html);
        self::assertStringContainsString('active = false', $html);
    }
}

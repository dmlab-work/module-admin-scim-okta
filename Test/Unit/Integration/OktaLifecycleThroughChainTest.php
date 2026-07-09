<?php
/**
 * Copyright © MageDevGroup. All rights reserved.
 */
declare(strict_types=1);

namespace MageDevGroup\AdminScimOkta\Test\Unit\Integration;

use MageDevGroup\AdminScim\Api\RequestNormalizerInterface;
use MageDevGroup\AdminScim\Model\Normalization\RequestNormalizerChain;
use MageDevGroup\AdminScimOkta\Model\Normalization\OktaRequestNormalizer;
use PHPUnit\Framework\TestCase;

/**
 * Acceptance check for the plugin's reason to exist: with the real admin-scim
 * {@see RequestNormalizerChain} carrying the Okta normalizer (as etc/di.xml wires
 * it), a full Okta-shaped user lifecycle — create, update, deactivate/reactivate,
 * plus group push — is normalized to strict RFC shape before the core sees it.
 *
 * Stubbed: it drives the chain directly with Okta-serialized bodies rather than a
 * live tenant (real provisioning is the manual Post-Completion step).
 */
class OktaLifecycleThroughChainTest extends TestCase
{
    /** @var RequestNormalizerChain */
    private RequestNormalizerChain $chain;

    protected function setUp(): void
    {
        // Exactly what etc/di.xml constructs when admin-scim is installed.
        $this->chain = new RequestNormalizerChain([new OktaRequestNormalizer()]);
    }

    public function testOktaNormalizerIsActiveInTheChain(): void
    {
        $result = $this->chain->apply(
            RequestNormalizerInterface::RESOURCE_USER,
            'POST',
            ['userName' => 'jane.doe', 'active' => 'true']
        );

        // A no-op/absent normalizer would leave the string; the Okta one coerces it.
        self::assertTrue($result['active']);
    }

    public function testCreateActivatesUserWithNativeBoolean(): void
    {
        $result = $this->chain->apply(
            RequestNormalizerInterface::RESOURCE_USER,
            'POST',
            [
                'schemas' => ['urn:ietf:params:scim:schemas:core:2.0:User'],
                'userName' => 'jane.doe',
                'active' => 'true',
                'name' => ['givenName' => 'Jane', 'familyName' => 'Doe'],
            ]
        );

        self::assertTrue($result['active']);
        self::assertSame('jane.doe', $result['userName']);
    }

    public function testUpdateViaPutCoercesStringifiedActive(): void
    {
        $result = $this->chain->apply(
            RequestNormalizerInterface::RESOURCE_USER,
            'PUT',
            ['userName' => 'jane.doe', 'active' => 'false']
        );

        self::assertFalse($result['active']);
    }

    public function testDeactivateViaOktaPathlessPatch(): void
    {
        // Okta deactivation: a path-less replace whose value object carries active.
        $result = $this->chain->apply(
            RequestNormalizerInterface::RESOURCE_USER,
            'PATCH',
            [
                'schemas' => ['urn:ietf:params:scim:api:messages:2.0:PatchOp'],
                'Operations' => [['op' => 'replace', 'value' => ['active' => 'false']]],
            ]
        );

        self::assertFalse($result['Operations'][0]['value']['active']);
    }

    public function testReactivateViaTargetedPatch(): void
    {
        $result = $this->chain->apply(
            RequestNormalizerInterface::RESOURCE_USER,
            'PATCH',
            ['Operations' => [['op' => 'replace', 'path' => 'active', 'value' => 'true']]]
        );

        self::assertTrue($result['Operations'][0]['value']);
    }

    public function testGroupPushPassesThroughTheChainUnchanged(): void
    {
        $payload = [
            'schemas' => ['urn:ietf:params:scim:schemas:core:2.0:Group'],
            'displayName' => 'Administrators',
            'members' => [['value' => '1'], ['value' => '2']],
        ];

        self::assertSame(
            $payload,
            $this->chain->apply(RequestNormalizerInterface::RESOURCE_GROUP, 'POST', $payload)
        );
    }
}

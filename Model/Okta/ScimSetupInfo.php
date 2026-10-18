<?php
/**
 * Copyright © DMLab. All rights reserved.
 * Licensed under the Open Software License version 3.0 (OSL-3.0). See LICENSE.
 */
declare(strict_types=1);

namespace DmLab\AdminScimOkta\Model\Okta;

use Magento\Framework\Escaper;
use Magento\Framework\Phrase;
use DmLab\AdminScim\Model\Discovery\EndpointUrlBuilder;

/**
 * Renders the admin setup surface for wiring an Okta SCIM app to this store.
 *
 * The one value an admin can't guess is the SCIM connector base URL — the
 * `admin-scim/v2` endpoint on this store's base URL — so it's read from the
 * core's {@see EndpointUrlBuilder}. The rest is fixed Okta-side guidance
 * (auth mode, unique identifier, supported provisioning actions, where the
 * bearer token lives). Instantiable without Magento's block stack, so it's
 * unit-testable on its own.
 */
class ScimSetupInfo
{
    /**
     * @param EndpointUrlBuilder $endpointUrlBuilder
     * @param Escaper $escaper
     */
    public function __construct(
        private readonly EndpointUrlBuilder $endpointUrlBuilder,
        private readonly Escaper $escaper
    ) {
    }

    /**
     * The SCIM connector base URL to paste into Okta, e.g. `https://host/admin-scim/v2`.
     */
    public function getEndpointUrl(): string
    {
        return $this->endpointUrlBuilder->baseUrl();
    }

    /**
     * Info-panel HTML for the admin config field: the endpoint plus Okta setup steps.
     */
    public function getHtml(): string
    {
        $endpoint = $this->escaper->escapeHtml($this->getEndpointUrl());
        $intro = $this->escape(
            __('In your Okta SCIM application (Provisioning → Integration), use these settings:')
        );
        $endpointLabel = $this->escape(__('SCIM connector base URL:'));
        $identifierLabel = $this->escape(__('Unique identifier field for users:'));
        $authLabel = $this->escape(__('Authentication Mode:'));
        $authMode = $this->escape(__('HTTP Header'));
        $actionsLabel = $this->escape(__('Supported provisioning actions:'));
        $actions = $this->escape(__('Push New Users, Push Profile Updates, Push Groups.'));
        $deactivation = $this->escape(
            __('Deactivating a user in Okta disables the admin (active = false); it is not deleted.')
        );
        $defaultRole = $this->escape(
            __('New users get the Admin SCIM Default Role unless a pushed group maps to a role via Group → Role Map.')
        );
        $scope = $this->escape(
            __('Only SCIM-created admins are managed — existing admins are not visible to or changed by Okta.')
        );
        $token = $this->escape(
            __(
                'Use the token configured under %1, and enable Admin SCIM there first.',
                __('Stores → Configuration → DMLab → Admin SCIM → Bearer Token')
            )
        );

        return <<<HTML
<div class="dmlab-admin-scim-okta-setup">
    <p>{$intro}</p>
    <ul>
        <li><strong>{$endpointLabel}</strong> <code>{$endpoint}</code></li>
        <li><strong>{$identifierLabel}</strong> <code>userName</code></li>
        <li><strong>{$authLabel}</strong> {$authMode} &mdash;
            <code>Authorization: Bearer &lt;token&gt;</code></li>
        <li><strong>{$actionsLabel}</strong> {$actions}</li>
        <li>{$deactivation}</li>
        <li>{$defaultRole}</li>
        <li>{$scope}</li>
    </ul>
    <p>{$token}</p>
</div>
HTML;
    }

    /**
     * Render a phrase and HTML-escape it.
     *
     * @param Phrase $phrase
     * @return string
     */
    private function escape(Phrase $phrase): string
    {
        return $this->escaper->escapeHtml((string)$phrase);
    }
}

<?php
/**
 * Copyright © DMLab. All rights reserved.
 */
declare(strict_types=1);

namespace DmLab\AdminScimOkta\Model\Okta;

use Magento\Framework\Escaper;
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

        return <<<HTML
<div class="dmlab-admin-scim-okta-setup">
    <p>In your Okta SCIM application (Provisioning &rarr; Integration), use these settings:</p>
    <ul>
        <li><strong>SCIM connector base URL:</strong> <code>{$endpoint}</code></li>
        <li><strong>Unique identifier field for users:</strong> <code>userName</code></li>
        <li><strong>Authentication Mode:</strong> HTTP Header &mdash;
            <code>Authorization: Bearer &lt;token&gt;</code></li>
        <li><strong>Supported provisioning actions:</strong> Push New Users,
            Push Profile Updates, Push Groups (deactivation is sent as
            <code>active = false</code>).</li>
    </ul>
    <p>Use the token configured under
        <em>Stores &rarr; Configuration &rarr; DmLab &rarr; Admin SCIM &rarr; Bearer Token</em>,
        and enable Admin SCIM there first.</p>
</div>
HTML;
    }
}

# DmLab_AdminScimOkta

> Okta SCIM provisioning for Magento 2 admin users.

[![CI](https://github.com/dmlab-work/module-admin-scim-okta/actions/workflows/ci.yml/badge.svg)](https://github.com/dmlab-work/module-admin-scim-okta/actions/workflows/ci.yml) [![codecov](https://codecov.io/gh/dmlab-work/module-admin-scim-okta/graph/badge.svg)](https://codecov.io/gh/dmlab-work/module-admin-scim-okta) [![Packagist Version](https://img.shields.io/packagist/v/dmlab/module-admin-scim-okta)](https://packagist.org/packages/dmlab/module-admin-scim-okta) [![Downloads](https://img.shields.io/packagist/dt/dmlab/module-admin-scim-okta)](https://packagist.org/packages/dmlab/module-admin-scim-okta) [![PHP](https://img.shields.io/packagist/dependency-v/dmlab/module-admin-scim-okta/php)](https://packagist.org/packages/dmlab/module-admin-scim-okta) [![License](https://img.shields.io/packagist/l/dmlab/module-admin-scim-okta)](LICENSE) ![Magento](https://img.shields.io/badge/Magento-2.4-orange)

Automatic admin offboarding: when IT removes or deactivates a user in Okta, the matching Magento admin account is deactivated (disabled, not deleted).
New hires get their admin role from their Okta groups before they first log in.
Install this plugin and the SCIM server comes with it.

![Okta SCIM setup in Magento admin: connector base URL, userName identifier and bearer-token header](.github/images/config.png)

## Install

```bash
composer require dmlab/module-admin-scim-okta
bin/magento setup:upgrade
```

The `require` pulls `dmlab/module-admin-scim` — the whole provisioning chain installs at once.

## Set up the Okta SCIM app

1. In Magento, open **Stores → Configuration → DMLab → Admin SCIM**, enable Admin SCIM, set a **Bearer Token**, and leave **Provider** at **— None —** (Okta needs no request normalizer). With another `admin-scim-<idp>` plugin installed, its normalizer applies under None — uninstall it if this store provisions from Okta.
2. Read the connector base URL from **Stores → Configuration → DMLab → Admin SCIM → Okta Setup**. It is this store's SCIM endpoint, e.g. `https://your-host/admin-scim/v2`.
3. In Okta, create a SCIM application, then under **Provisioning → Integration** enter:

   | Setting | Value |
   |---|---|
   | SCIM connector base URL | the URL from step 2 |
   | Unique identifier field for users | `userName` |
   | Authentication Mode | HTTP Header — `Authorization: Bearer <token>` |
   | Supported provisioning actions | Push New Users · Push Profile Updates · Push Groups |

4. Enable the provisioning-to-app actions in Okta and assign users/groups.

## Supported provisioning actions

- **Create** — the new admin gets the **Default Role**, unless a pushed group maps a role.
- **Profile update** — Okta sends `PUT`.
- **Deactivate / reactivate** — `active = false` / `true`; disabled, not deleted.
- **Group push** — create, rename, member add/remove, delete; the role is re-derived via **Group → Role Map**.

Only SCIM-created admins are managed; existing admins are not visible to or changed by Okta.

## Requirements

Magento 2.4.7+ · PHP 8.2–8.5 · `dmlab/module-admin-scim` ^0.2.0 (installed automatically)

Pairs with [admin-sso-okta](https://github.com/dmlab-work/module-admin-sso-okta) — Okta login for the same admins.

## Architecture

Okta is RFC-standard enough that `admin-scim` handles its requests directly; this plugin adds the Okta Setup panel and pulls the core.

## License

[OSL-3.0](LICENSE) © DMLab. Commercial licensing and support: <https://dmlab.work>.

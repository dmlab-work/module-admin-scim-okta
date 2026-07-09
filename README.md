# MageDevGroup_AdminScimOkta

> Okta SCIM provisioning for Magento 2 admin users.

![License](https://img.shields.io/badge/license-OSL--3.0-green) ![Magento](https://img.shields.io/badge/Magento-2.4-orange) ![PHP](https://img.shields.io/badge/PHP-8.3--8.5-blue) ![Version](https://img.shields.io/badge/version-0.0.1-lightgrey)

A thin Okta provider plugin for the [`admin-scim`](../module-admin-scim) SCIM 2.0 server. It normalizes Okta's SCIM specifics and documents the Okta-side setup. Okta is near RFC-standard, so the module is small: a mostly pass-through normalizer plus an admin setup surface.

## Install

```bash
composer require magedevgroup/module-admin-scim-okta
bin/magento module:enable MageDevGroup_AdminScimOkta
bin/magento setup:upgrade
```

The single `require` pulls `magedevgroup/module-admin-scim` — the whole provisioning chain installs at once.

## Set up the Okta SCIM app

1. In Magento, open **Stores → Configuration → MageDevGroup → Admin SCIM**, enable Admin SCIM, and set a **Bearer Token**.
2. Read the connector base URL from **Stores → Configuration → MageDevGroup → Admin SCIM → Okta Setup**. It is this store's SCIM endpoint, e.g. `https://your-host/admin-scim/v2`.
3. In Okta, create a SCIM application, then under **Provisioning → Integration** enter:

   | Setting | Value |
   |---|---|
   | SCIM connector base URL | the URL from step 2 |
   | Unique identifier field for users | `userName` |
   | Authentication Mode | HTTP Header — `Authorization: Bearer <token>` |
   | Supported provisioning actions | Push New Users · Push Profile Updates · Push Groups |

4. Enable the provisioning-to-app actions in Okta and assign users/groups.

## Supported provisioning actions

- **Create** — Okta pushes a new user → admin_user created.
- **Update** — profile changes → admin_user updated.
- **Deactivate / reactivate** — sent by Okta as `active = false` / `active = true`.
- **Group push** — group membership → role mapping (standard SCIM, handled by `admin-scim`).

## How it works

Okta pushes SCIM 2.0 requests to the `admin-scim` endpoint. This plugin registers an Okta request normalizer into `admin-scim`'s `RequestNormalizerInterface` seam and coerces Okta's one deviation — an `active` toggle sometimes serialized as a stringified boolean (`"true"`/`"false"`) — into a native boolean, on the top-level body (POST/PUT) and inside PATCH operations. Everything else passes through unchanged; the actual admin-user provisioning and role mapping happen in `admin-scim`.

## Requirements

- Magento **2.4.x**
- PHP **8.3 – 8.5**
- `magedevgroup/module-admin-scim` (installed automatically)

## License

[OSL-3.0](LICENSE) © MageDevGroup. Commercial licensing and support: <https://magedevgroup.com>.

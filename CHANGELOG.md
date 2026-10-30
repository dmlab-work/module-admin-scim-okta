# Changelog

## 0.2.1

- Supports Magento 2.4.7+ and PHP 8.2–8.5 (bounded 2.4.7 floors).
- CI tests PHP 8.2–8.5; CI installs are no longer counted as Packagist downloads.
- Composer metadata: keywords, `support.source`, suggests `dmlab/module-admin-sso-okta`.
- README restructured around admin offboarding, with a screenshot.

## 0.2.0

- Removed the Okta request normalizer — `admin-scim` handles Okta's request shapes itself.
- Setup panel: translatable strings (`i18n/en_US.csv`), correct `DMLab` config path, guidance on
  group push, Default Role on create, and deactivation (disabled, not deleted).
- Declared `magento/module-backend` and `magento/module-config` dependencies.
- Requires `dmlab/module-admin-scim ^0.2.0`.

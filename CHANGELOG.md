# Changelog

## 0.2.0

- Removed the Okta request normalizer — `admin-scim` handles Okta's request shapes itself.
- Setup panel: translatable strings (`i18n/en_US.csv`), correct `DMLab` config path, guidance on
  group push, Default Role on create, and deactivation (disabled, not deleted).
- Declared `magento/module-backend` and `magento/module-config` dependencies.
- Requires `dmlab/module-admin-scim ^0.2.0`.

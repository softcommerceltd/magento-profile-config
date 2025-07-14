# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.3.1] - 2025-07-14
### Changed
- ## softcommerce/module-profile-config [1.3.0] - **Enhancement**: Included `servicepoint` type to account for `pakshop` facility.
- ## softcommerce/module-profile-config [1.2.13] - **Fix**: Apply a fix where profile config scope writer saves value as array opposed to serialised data [#29]

## [1.3.0] - 2024-03-13
### Changed
- **Enhancement**: Included `servicepoint` type to account for `pakshop` facility.

## [1.2.13] - 2024-01-13
### Fixed
- Apply a fix where profile config scope writer saves value as array opposed to serialised data [#29]

## [1.2.12] - 2024-08-17
### Changed
- **Compatibility**: Introduce support for Magento 2.4.7 [#4]

## [1.2.11] - 2024-03-21
### Changed
- **Compatibility**: Introduced support for PHP 8.3 [#2]

## [1.2.10] - 2024-02-01
### Changed
- **Enhancement**: General codebase improvements [#3]

## [1.2.9] - 2023-11-30
### Changed
- **Compatibility**: Add compatibility for Magento 2.4.6-p3 and Magento 2.4.7

## [1.2.8] - 2023-06-24
### Changed
- **Compatibility**: Add compatibility for PHP 8.2 and Magento 2.4.6-p1 [#2]
- **Enhancement**: Ability to export / import profile config data. [#1]

## [1.2.7] - 2022-12-31
### Fixed
- Applied a fix to `SoftCommerce\ProfileConfig\Model\AbstractConfig::getConfigDataSerialized` where return type must be an array.

## [1.2.6] - 2022-11-28
### Fixed
- Applied a fix to composer.json license compatibility.

## [1.2.5] - 2022-11-10
### Changed
- **Compatibility**: Compatibility with Magento [OS/AC] 2.4.5 and PHP 8

## [1.2.4] - 2022-08-30
### Changed
- **Enhancement**: Changed `SoftCommerce\ProfileConfig\Model\AbstractConfig::getTypeId` to public to allow other modules access this method.

## [1.2.3] - 2022-08-24
### Changed
- **Enhancement**: Improvements to config data provider.

## [1.2.2] - 2022-07-22
### Changed
- **Compatibility**: Compatibility with Magento Extension Quality Program (EQP).

## [1.2.1] - 2022-07-03
### Changed
- **Enhancement**: Changes to PDT.

## [1.2.0] - 2022-06-08
### Changed
- **Compatibility**: Compatibility with Magento Open Source 2.4.4 [#4]

## [1.0.0] - 2022-06-03
### Added
- [SCP-2] New module used to handle profile configuration.

[Unreleased]: https://github.com/softcommerceltd/magento-profile-config/compare/v1.3.0...HEAD
[1.3.0]: https://github.com/softcommerceltd/magento-profile-config/compare/v1.2.13...v1.3.0
[1.2.13]: https://github.com/softcommerceltd/magento-profile-config/compare/v1.2.12...v1.2.13
[1.2.12]: https://github.com/softcommerceltd/magento-profile-config/compare/v1.2.11...v1.2.12
[1.2.11]: https://github.com/softcommerceltd/magento-profile-config/compare/v1.2.10...v1.2.11
[1.2.10]: https://github.com/softcommerceltd/magento-profile-config/compare/v1.2.9...v1.2.10
[1.2.9]: https://github.com/softcommerceltd/magento-profile-config/compare/v1.2.8...v1.2.9
[1.2.8]: https://github.com/softcommerceltd/magento-profile-config/compare/v1.2.7...v1.2.8
[1.2.7]: https://github.com/softcommerceltd/magento-profile-config/compare/v1.2.6...v1.2.7
[1.2.6]: https://github.com/softcommerceltd/magento-profile-config/compare/v1.2.5...v1.2.6
[1.2.5]: https://github.com/softcommerceltd/magento-profile-config/compare/v1.2.4...v1.2.5
[1.2.4]: https://github.com/softcommerceltd/magento-profile-config/compare/v1.2.3...v1.2.4
[1.2.3]: https://github.com/softcommerceltd/magento-profile-config/compare/v1.2.2...v1.2.3
[1.2.2]: https://github.com/softcommerceltd/magento-profile-config/compare/v1.2.1...v1.2.2
[1.2.1]: https://github.com/softcommerceltd/magento-profile-config/compare/v1.2.0...v1.2.1
[1.2.0]: https://github.com/softcommerceltd/magento-profile-config/compare/v1.0.0...v1.2.0
[1.0.0]: https://github.com/softcommerceltd/magento-profile-config/releases/tag/v1.0.0

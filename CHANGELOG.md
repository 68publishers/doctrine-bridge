# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [2.0.0] - 2026-10-28

### Added
- Support for `doctrine/orm` `^3.0` (with DBAL 4).
- Tests with plain service definitions (without Nettrine) that run on `doctrine/orm` 2 and 3.

### Changed
- Minimal PHP version is 8.3.
- ❗ The property `DatabaseTypeConfig::$mappingType` was renamed to `$mapping_type`.
- When the option `services.*` resolves to multiple autowired services of the type, the friendly "Can't resolve service definition…" exception is thrown instead of the Nette's `ServiceCreationException`.
- README: `serverVersion` of the connection is needed when a mapping type is used, otherwise the creation of the Connection service connects to the database.
- Dev tooling: PHPStan 2, Nette Tester 2.6+, PHP-CS-Fixer 3.89+, CI matrix PHP 8.3, 8.4 and 8.5. The tests report deprecations.

### Removed
- ❗ Support for PHP < 8.3.

### Fixed
- The option `mapping_type` of `DatabaseTypeProviderExtension` was ignored (the schema was cast into a dynamic property `mapping_type` while the code read `mappingType`).

## [1.1.0] - 2023-08-21

### Added
- Compiler extension `DatabaseTypeProviderExtension`.

## [1.0.0] - 2022-12-16

### Added
- Support for PHP `^8.1`.
- Dockerfile, docker-compose.yml, Makefile, tests with Nettrine and Nettrine/Migrations, PHPStan and GitHub Actions.

### Changed
- ❗ All DI classes and interfaces moved from `SixtyEightPublishers\DoctrineBridge\DI` to `SixtyEightPublishers\DoctrineBridge\Bridge\Nette\DI`.
- ❗ Options under the key `services.drivers` are disabled with `false` instead of `null`.
- Improved coding style, updated README.

### Removed
- Support for PHP 7.
- ❗ Property `DatabaseType::$commented` (deprecated in Doctrine).

## [0.2.1] - 2022-04-23

### Fixed
- Setup of custom database types.

## [0.2.0] - 2022-04-20

### Added
- PHP 8 support.
- Support for PHP 8 attributes mapping.

### Changed
- All integrations are handled by a common compiler extension.

### Removed
- ❗ The compiler extension for Neutrino.
- YAML mapping.

## [0.1.2] - 2022-04-19

### Added
- Provider interface `MigrationsDirectoriesProviderInterface`, custom migration paths can be configured.

## [0.1.1] - 2020-12-10

### Added
- Argument `$context` for custom DBAL types.

## [0.1] - 2020-11-30

- Initial release.

[Unreleased]: https://github.com/68publishers/doctrine-bridge/compare/v2.0.0...HEAD
[2.0.0]: https://github.com/68publishers/doctrine-bridge/compare/v1.1.0...v2.0.0
[1.1.0]: https://github.com/68publishers/doctrine-bridge/compare/v1.0.0...v1.1.0
[1.0.0]: https://github.com/68publishers/doctrine-bridge/compare/v0.2.1...v1.0.0
[0.2.1]: https://github.com/68publishers/doctrine-bridge/compare/v0.2.0...v0.2.1
[0.2.0]: https://github.com/68publishers/doctrine-bridge/compare/v0.1.2...v0.2.0
[0.1.2]: https://github.com/68publishers/doctrine-bridge/compare/v0.1.1...v0.1.2
[0.1.1]: https://github.com/68publishers/doctrine-bridge/compare/v0.1...v0.1.1
[0.1]: https://github.com/68publishers/doctrine-bridge/releases/tag/v0.1

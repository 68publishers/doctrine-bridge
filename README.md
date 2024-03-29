# Doctrine bridge

[![Build Status][ico-travis]][link-travis]
[![Quality Score][ico-code-quality]][link-code-quality]
[![Coverage Status][ico-scrutinizer]][link-scrutinizer]
[![Total Downloads][ico-downloads]][link-downloads]
[![Latest Version on Packagist][ico-version]][link-packagist]

A package contains bridges for the most used integrations of [doctrine/orm](https://github.com/doctrine/orm) into [Nette Framework](https://nette.org):

- [Nettrine](https://github.com/nettrine)

Bridges for this integrations will be implemented in the future:

- [Kdyby](https://github.com/Kdyby/Doctrine) (missing support for Nette 3)

Why? Because we want to keep our bundles independent from specific integrations so applications can use any of the integrations mentioned above and will be still compatible with our bundles.

## Installation

The best way to install 68publishers/doctrine-bridge is using Composer:

```bash
$ composer require 68publishers/doctrine-bridge
```

## Configuration

```neon
extensions:
    # if you are using Nettrine:
    doctrine_bridge: SixtyEightPublishers\DoctrineBridge\Bridge\Nettrine\DI\DoctrineBridgeExtension
```

## Usage

### Database Type Provider

```php
<?php

use Nette\DI\CompilerExtension;
use SixtyEightPublishers\DoctrineBridge\DI\DatabaseType;
use SixtyEightPublishers\DoctrineBridge\DI\DatabaseTypeProviderInterface;
use Ramsey\Uuid\Doctrine\UuidBinaryOrderedTimeType;

class FooExtension extends CompilerExtension implements DatabaseTypeProviderInterface
{
    public function getDatabaseTypes() : array
    {
        return [
            new DatabaseType('uuid_binary_ordered_time', Ramsey\Uuid\Doctrine\UuidBinaryOrderedTimeType::class, 'binary'),
            new DatabaseType('my_custom_type', MyCustomType::class),
        ];
    }
}
```

#### Services in Doctrine Types

Doctrine DBAL types don't have access to services by default. 
With this extension, you can receive DI Container in your custom types when the Connection is created.

```php
<?php

use Nette\DI\Container;
use Doctrine\DBAL\Types\StringType;
use SixtyEightPublishers\DoctrineBridge\Type\ContainerAwareTypeInterface;

final class CustomType extends StringType implements ContainerAwareTypeInterface
{
    private $service;

    public function setContainer(Container $container) : void
    {
        $this->service = $container->getByType(MyService::class);
    }
}
```

### Entity Mapping Provider

```php
<?php

use Nette\DI\CompilerExtension;
use SixtyEightPublishers\DoctrineBridge\DI\EntityMapping;
use SixtyEightPublishers\DoctrineBridge\DI\EntityMappingProviderInterface;

class FooExtension extends CompilerExtension implements EntityMappingProviderInterface
{
    public function getEntityMappings() : array
    {
        return [
            new EntityMapping(EntityMapping::DRIVER_ANNOTATIONS, 'App\\Entity', __DIR__ . '/../Entity'),
            new EntityMapping(EntityMapping::DRIVER_YAML, 'App\\Entity', __DIR__ . '/../Entity/yaml'),
            new EntityMapping(EntityMapping::DRIVER_ANNOTATIONS, 'App\\Entity', __DIR__ . '/../Entity/xml'),
        ];
    }
}
```

### Target Entity Provider

```php
<?php

use Nette\DI\CompilerExtension;
use SixtyEightPublishers\DoctrineBridge\DI\TargetEntity;
use SixtyEightPublishers\DoctrineBridge\DI\TargetEntityProviderInterface;

class FooExtension extends CompilerExtension implements TargetEntityProviderInterface
{
    public function getTargetEntities() : array
    {
        return [
            new TargetEntity(ProductInterface::class, ProductEntity::class),
        ];
    }
}
```

## Contributing

Before committing any changes, don't forget to run

```bash
$ vendor/bin/php-cs-fixer fix --config=.php_cs.dist -v --dry-run
```

and

```bash
$ composer run tests
```

[ico-version]: https://img.shields.io/packagist/v/68publishers/doctrine-bridge.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/68publishers/doctrine-bridge/master.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/68publishers/doctrine-bridge.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/68publishers/doctrine-bridge.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/68publishers/doctrine-bridge.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/68publishers/doctrine-bridge
[link-travis]: https://travis-ci.org/68publishers/doctrine-bridge
[link-scrutinizer]: https://scrutinizer-ci.com/g/68publishers/doctrine-bridge/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/68publishers/doctrine-bridge
[link-downloads]: https://packagist.org/packages/68publishers/doctrine-bridge

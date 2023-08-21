<h1 align="center">Doctrine bridge</h1>

<p align="center">Register custom DBAL types, entity mappings, target entities and migration directories directly inside your CompilerExtensions!</p>

<p align="center">
<a href="https://github.com/68publishers/doctrine-bridge/actions"><img alt="Checks" src="https://badgen.net/github/checks/68publishers/doctrine-bridge/master"></a>
<a href="https://coveralls.io/github/68publishers/doctrine-bridge?branch=master"><img alt="Coverage Status" src="https://coveralls.io/repos/github/68publishers/doctrine-bridge/badge.svg?branch=master"></a>
<a href="https://packagist.org/packages/68publishers/doctrine-bridge"><img alt="Total Downloads" src="https://badgen.net/packagist/dt/68publishers/doctrine-bridge"></a>
<a href="https://packagist.org/packages/68publishers/doctrine-bridge"><img alt="Latest Version" src="https://badgen.net/packagist/v/68publishers/doctrine-bridge"></a>
<a href="https://packagist.org/packages/68publishers/doctrine-bridge"><img alt="PHP Version" src="https://badgen.net/packagist/php/68publishers/doctrine-bridge"></a>
</p>

## Installation

The best way to install 68publishers/doctrine-bridge is using Composer:

```sh
$ composer require 68publishers/doctrine-bridge
```

## Configuration

```neon
extensions:
    68publishers.doctrine_bridge: SixtyEightPublishers\DoctrineBridge\Bridge\Nette\DI\DoctrineBridgeExtension

# The default configuration:
68publishers.doctrine_bridge:
    database_types_enabled: yes # Enables/disables registration of acustom DBAL types
    entity_mappings_enabled: yes # Enables/disables registration of entity mappings
    target_entities_enabled: yes # Enables/disables resolving of target entities
    migration_directories_enabled: yes # Enables/disables registration of migrations for doctrine/migrations

    # Dependent services. Allowed are classname strings (for autowired services) or references e.g. @myService
    services:
        dbal_connection: Doctrine\DBAL\Connection
        drivers:
            # For drivers, you can use `false`. In this case, mappings for the drive will be omitted
            chain: Doctrine\Persistence\Mapping\Driver\MappingDriverChain
            annotation: Doctrine\ORM\Mapping\Driver\AnnotationDriver
            xml: Doctrine\ORM\Mapping\Driver\XmlDriver
            simplified_xml: Doctrine\ORM\Mapping\Driver\SimplifiedXmlDriver
            attribute: Doctrine\ORM\Mapping\Driver\AttributeDriver
        migrations_configuration: Doctrine\Migrations\Configuration\Configuration
```

The package is fully tested in combination with [nettrine/orm](https://github.com/contributte/doctrine-orm) and [nettrine/migrations](https://github.com/contributte/doctrine-migrations), however it can be plugged into almost any Doctrine integration into the Nette Framework using the `services` options.

## Usage

### Database Type Provider

```php
use Doctrine\DBAL\Types\Types;
use Nette\DI\CompilerExtension;
use Ramsey\Uuid\Doctrine\UuidBinaryOrderedTimeType;
use SixtyEightPublishers\DoctrineBridge\Bridge\Nette\DI\DatabaseType;
use SixtyEightPublishers\DoctrineBridge\Bridge\Nette\DI\DatabaseTypeProviderInterface;

class MyExtension extends CompilerExtension implements DatabaseTypeProviderInterface
{
    public function getDatabaseTypes() : array
    {
        return [
            new DatabaseType('uuid_binary_ordered_time', Ramsey\Uuid\Doctrine\UuidBinaryOrderedTimeType::class, Types::BINARY),
            new DatabaseType('my_custom_type', MyCustomType::class),
        ];
    }
}
```

#### Services in Doctrine Types

Doctrine DBAL types don't have access to services by default. 
With this extension, you can receive the DI Container in custom types when the Connection is created.

```php
use Nette\DI\Container;
use Doctrine\DBAL\Types\StringType;
use SixtyEightPublishers\DoctrineBridge\Type\ContainerAwareTypeInterface;

final class MyExtension extends StringType implements ContainerAwareTypeInterface
{
    private MyService $service;

    public function setContainer(Container $container, array $context = []) : void
    {
        $this->service = $container->getByType(MyService::class);
    }
}
```

#### Registering Doctrine Types via bundled DatabaseTypeProviderExtension

To register custom types, it is not necessary to create a custom extension, but the class `DatabaseTypeProviderExtension` can be used.

```neon
extensions:
    68publishers.doctrine_bridge.database_type_provider: SixtyEightPublishers\DoctrineBridge\Bridge\Nette\DI\DatabaseTypeProviderExtension

68publishers.doctrine_bridge.database_type_provider:
    # inline notation:
    my_type_1: App\DbalType\MyType1

    # structured notation:
    my_type_2:
        class: App\DbalType\MyType2
        mapping_type: text
        context: []
```

### Entity Mapping Provider

```php
use Nette\DI\CompilerExtension;
use SixtyEightPublishers\DoctrineBridge\Bridge\Nette\DI\EntityMapping;
use SixtyEightPublishers\DoctrineBridge\Bridge\Nette\DI\EntityMappingProviderInterface;

class MyExtension extends CompilerExtension implements EntityMappingProviderInterface
{
    public function getEntityMappings() : array
    {
        return [
            new EntityMapping(EntityMapping::DRIVER_ANNOTATION, 'App\\Entity', __DIR__ . '/../Entity'),
            new EntityMapping(EntityMapping::DRIVER_ATTRIBUTE, 'App\\Entity', __DIR__ . '/../Entity'),
            new EntityMapping(EntityMapping::DRIVER_XML, 'App\\Entity', __DIR__ . '/../Mapping/xml'),
            # or
            new EntityMapping(EntityMapping::DRIVER_SIMPLIFIED_XML, 'App\\Entity', __DIR__ . '/../Mapping/xml'),
        ];
    }
}
```

### Target Entity Provider

```php
use Nette\DI\CompilerExtension;
use SixtyEightPublishers\DoctrineBridge\Bridge\Nette\DI\TargetEntity;
use SixtyEightPublishers\DoctrineBridge\Bridge\Nette\DI\TargetEntityProviderInterface;

class MyExtension extends CompilerExtension implements TargetEntityProviderInterface
{
    public function getTargetEntities() : array
    {
        return [
            new TargetEntity(ProductInterface::class, ProductEntity::class),
        ];
    }
}
```

### Migration directories

```php
use Nette\DI\CompilerExtension;
use SixtyEightPublishers\DoctrineBridge\Bridge\Nette\DI\TargetEntity;
use SixtyEightPublishers\DoctrineBridge\Bridge\Nette\DI\MigrationsDirectoriesProviderInterface;

class MyExtension extends CompilerExtension implements MigrationsDirectoriesProviderInterface
{
    public function getMigrationsDirectories() : array
    {
        return [
            new MigrationsDirectory('App\\Bundle\\MyBundle\\Migrations', __DIR__ . '/../Migrations'),
        ];
    }
}
```

## Contributing

Before opening a pull request, please check your changes using the following commands

```bash
$ make init # to pull and start all docker images

$ make cs.check
$ make stan
$ make tests.all
```

<?php

declare(strict_types=1);

namespace SixtyEightPublishers\DoctrineBridge\Tests\Bridge\Nette\DI;

use Tester\Assert;
use Tester\TestCase;
use RuntimeException;
use ReflectionProperty;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;
use Tester\CodeCoverage\Collector;
use Doctrine\ORM\Mapping\Driver\XmlDriver;
use Doctrine\ORM\Mapping\Driver\AttributeDriver;
use Doctrine\ORM\Tools\ResolveTargetEntityListener;
use Doctrine\Migrations\Configuration\Configuration;
use Doctrine\Persistence\Mapping\Driver\MappingDriverChain;
use SixtyEightPublishers\DoctrineBridge\Tests\Fixtures\Entity\EntityInterface;
use SixtyEightPublishers\DoctrineBridge\Tests\Fixtures\Entity\EntityWithAttributesMapping;
use SixtyEightPublishers\DoctrineBridge\Tests\Fixtures\DatabaseType\CustomTypeWithContainer;
use function assert;
use function realpath;
use function array_map;
use function array_keys;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Runs with the root dependencies (doctrine/orm 2 or 3) and plain service definitions, without Nettrine.
 *
 * @testCase
 */
final class PlainServicesDoctrineBridgeExtensionTest extends TestCase
{
	public function testIntegrationWithPlainServices(): void
	{
		$container = ContainerFactory::create(__DIR__ . '/Plain/config.neon');
		$connection = $container->getByType(Connection::class);
		assert($connection instanceof Connection);

		# database types
		Assert::true(Type::hasType('test_1'));
		Assert::same(Types::TEXT, $connection->getDatabasePlatform()->getDoctrineTypeMapping('test_2'));
		Assert::same('integer', $connection->getDatabasePlatform()->getDoctrineTypeMapping('custom_type_extended_2'));
		Assert::equal(CustomTypeWithContainer::create($container, []), Type::getType('custom_type_inline'));
		Assert::equal(CustomTypeWithContainer::create($container, ['default_value' => 3]), Type::getType('custom_type_extended_2'));

		# entity mappings
		$namespace = 'SixtyEightPublishers\DoctrineBridge\Tests\Fixtures\Entity';
		$chain = $container->getByType(MappingDriverChain::class);
		$attributeDriver = $container->getByType(AttributeDriver::class);
		$xmlDriver = $container->getService('xmlDriver');
		$simplifiedXmlDriver = $container->getService('simplifiedXmlDriver');
		assert($chain instanceof MappingDriverChain && $attributeDriver instanceof AttributeDriver && $xmlDriver instanceof XmlDriver && $simplifiedXmlDriver instanceof XmlDriver);

		Assert::same([$namespace], array_keys($chain->getDrivers()));
		Assert::contains(EntityWithAttributesMapping::class, $attributeDriver->getAllClassNames());
		Assert::same([realpath(__DIR__ . '/../../../Fixtures/Mapping/Xml')], array_map('realpath', $xmlDriver->getLocator()->getPaths()));
		Assert::same([realpath(__DIR__ . '/../../../Fixtures/Mapping/SimplifiedXml')], array_map('realpath', $simplifiedXmlDriver->getLocator()->getPaths()));

		# target entities
		$listener = $container->getByType(ResolveTargetEntityListener::class);
		assert($listener instanceof ResolveTargetEntityListener);
		$resolveTargetEntities = (new ReflectionProperty(ResolveTargetEntityListener::class, 'resolveTargetEntities'))->getValue($listener);

		Assert::same([EntityInterface::class], array_keys($resolveTargetEntities));
		Assert::same(EntityWithAttributesMapping::class, $resolveTargetEntities[EntityInterface::class]['targetEntity']);

		# migrations directories
		$configuration = $container->getByType(Configuration::class);
		assert($configuration instanceof Configuration);

		Assert::same(['Bundle' => realpath(__DIR__ . '/../../../Fixtures/Extension')], array_map('realpath', $configuration->getMigrationDirectories()));
	}

	public function testExceptionShouldBeThrownIfDbalConnectionServiceIsAmbiguous(): void
	{
		Assert::exception(
			static fn () => ContainerFactory::create(__DIR__ . '/Plain/config.error.multipleDbalConnectionServices.neon'),
			RuntimeException::class,
			'Can\'t resolve service definition by identifier "Doctrine\DBAL\Connection" from option bridge.services.dbal_connection. Please specify it manually.'
		);
	}

	protected function tearDown(): void
	{
		# save manually partial code coverage to free memory
		if (Collector::isStarted()) {
			Collector::save();
		}
	}
}

(new PlainServicesDoctrineBridgeExtensionTest())->run();

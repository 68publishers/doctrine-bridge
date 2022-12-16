<?php

declare(strict_types=1);

namespace SixtyEightPublishers\TranslationBridge\Tests\Bridge\Nette\DI;

use Tester\Assert;
use Tester\TestCase;
use RuntimeException;
use Nette\DI\Container;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;
use Tester\CodeCoverage\Collector;
use SixtyEightPublishers\DoctrineBridge\Tests\Bridge\Nette\DI\ContainerFactory;
use SixtyEightPublishers\DoctrineBridge\Tests\Fixtures\DatabaseType\CustomTypeWithContainer;
use function assert;

require __DIR__ . '/../../../bootstrap.nettrine.php';

/**
 * @testCase
 */
final class NettrineDatabaseTypeDoctrineBridgeExtensionTest extends TestCase
{
	public function testExceptionShouldBeThrownIfDbalConnectionServiceIsMissing(): void
	{
		Assert::exception(
			static fn () => ContainerFactory::create(__DIR__ . '/Nettrine/DatabaseType/config.error.missingDbalConnectionService.neon'),
			RuntimeException::class,
			'Can\'t resolve service definition by identifier "Doctrine\DBAL\Connection" from option bridge.services.dbal_connection. Please specify it manually.'
		);
	}

	public function testDatabaseTypesShouldBeRegistered(): void
	{
		$container = ContainerFactory::create(__DIR__ . '/Nettrine/DatabaseType/config.minimal.neon');
		$connection = $container->getByType(Connection::class);
		assert($connection instanceof Connection);

		Assert::true(Type::hasType('test_1'));
		Assert::true(Type::hasType('test_2'));
		Assert::true(Type::hasType('test_3'));
		Assert::true(Type::hasType('test_4'));

		Assert::same(Types::TEXT, $connection->getDatabasePlatform()->getDoctrineTypeMapping('test_2'));

		$type3 = Type::getType('test_3');
		$type4 = Type::getType('test_4');
		assert($type3 instanceof CustomTypeWithContainer && $type4 instanceof CustomTypeWithContainer);

		Assert::type(Container::class, $type3->container);
		Assert::type(Container::class, $type4->container);

		Assert::same([], $type3->context);
		Assert::same(['test_key' => 'test_value'], $type4->context);
	}

	public function testDatabaseTypesShouldNotBeRegisteredIfDatabaseTypesOptionIsDisabled(): void
	{
		$container = ContainerFactory::create(__DIR__ . '/Nettrine/DatabaseType/config.withDatabaseTypesDisabled.neon');
		$connection = $container->getByType(Connection::class);
		assert($connection instanceof Connection);

		Assert::false(Type::hasType('test_1'));
		Assert::false(Type::hasType('test_2'));
		Assert::false(Type::hasType('test_3'));
		Assert::false(Type::hasType('test_4'));
	}

	protected function tearDown(): void
	{
		# save manually partial code coverage to free memory
		if (Collector::isStarted()) {
			Collector::save();
		}
	}
}

(new NettrineDatabaseTypeDoctrineBridgeExtensionTest())->run();

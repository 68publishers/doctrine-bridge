<?php

declare(strict_types=1);

namespace SixtyEightPublishers\TranslationBridge\Tests\Bridge\Nette\DI;

use Tester\Assert;
use Tester\TestCase;
use RuntimeException;
use Nette\DI\Container;
use Tester\CodeCoverage\Collector;
use Doctrine\Migrations\Configuration\Configuration as MigrationsConfiguration;
use SixtyEightPublishers\DoctrineBridge\Tests\Bridge\Nette\DI\ContainerFactory;
use function assert;
use function realpath;

require __DIR__ . '/../../../bootstrap.nettrine.php';

final class NettrineMigrationDirectoriesDoctrineBridgeExtensionTest extends TestCase
{
	public function testExceptionShouldBeThrownIfMigrationsConfigurationIsMissing(): void
	{
		Assert::exception(
			static fn () => ContainerFactory::create(__DIR__ . '/Nettrine/MigrationDirectories/config.error.missingConfigurationService.neon'),
			RuntimeException::class,
			'Can\'t resolve service definition by identifier "Doctrine\Migrations\Configuration\Configuration" from option bridge.services.migrations_configuration. Please specify it manually.'
		);
	}

	public function testMigrationDirectoriesShouldBeRegistered(): void
	{
		$container = ContainerFactory::create(__DIR__ . '/Nettrine/MigrationDirectories/config.minimal.neon');

		$this->assertMigrationDirectories($container, [
			'Migrations' => __DIR__,
			'Bundle' => realpath(__DIR__ . '/../../../Fixtures/Extension'),
		]);
	}

	public function testMigrationDirectoriesShouldNotBeRegisteredIfMigrationDirectoriesOptionIsDisabled(): void
	{
		$container = ContainerFactory::create(__DIR__ . '/Nettrine/MigrationDirectories/config.withMigrationDirectoriesDisabled.neon');

		$this->assertMigrationDirectories($container, [
			'Migrations' => __DIR__,
		]);
	}

	protected function tearDown(): void
	{
		# save manually partial code coverage to free memory
		if (Collector::isStarted()) {
			Collector::save();
		}
	}

	private function assertMigrationDirectories(Container $container, array $directories): void
	{
		$configuration = $container->getByType(MigrationsConfiguration::class);
		assert($configuration instanceof MigrationsConfiguration);

		Assert::same($directories, $configuration->getMigrationDirectories());
	}
}

(new NettrineMigrationDirectoriesDoctrineBridgeExtensionTest())->run();

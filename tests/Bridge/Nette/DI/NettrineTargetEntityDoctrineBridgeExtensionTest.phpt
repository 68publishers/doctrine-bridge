<?php

declare(strict_types=1);

namespace SixtyEightPublishers\TranslationBridge\Tests\Bridge\Nette\DI;

use Closure;
use Tester\Assert;
use Tester\TestCase;
use Nette\DI\Container;
use Tester\CodeCoverage\Collector;
use Doctrine\ORM\Tools\ResolveTargetEntityListener;
use SixtyEightPublishers\DoctrineBridge\Tests\Fixtures\Entity\EntityInterface;
use SixtyEightPublishers\DoctrineBridge\Tests\Bridge\Nette\DI\ContainerFactory;
use SixtyEightPublishers\DoctrineBridge\Tests\Fixtures\Entity\EntityWithAttributesMapping;
use function assert;
use function call_user_func;

require __DIR__ . '/../../../bootstrap.nettrine.php';

final class NettrineTargetEntityDoctrineBridgeExtensionTest extends TestCase
{
	public function testTargetEntitiesShouldBeRegistered(): void
	{
		$container = ContainerFactory::create(__DIR__ . '/Nettrine/TargetEntity/config.minimal.neon');

		$this->assertResolvedEntitiesRegistered($container, [
			EntityInterface::class => [
				'targetEntity' => EntityWithAttributesMapping::class,
			],
		]);
	}

	public function testTargetEntitiesWithCustomMappingShouldBeRegistered(): void
	{
		$container = ContainerFactory::create(__DIR__ . '/Nettrine/TargetEntity/config.withMapping.neon');

		$this->assertResolvedEntitiesRegistered($container, [
			EntityInterface::class => [
				'test_key' => 'test_value',
				'targetEntity' => EntityWithAttributesMapping::class,
			],
		]);
	}

	public function testTargetEntitiesShouldBeRegisteredOnExistingListener(): void
	{
		$container = ContainerFactory::create(__DIR__ . '/Nettrine/TargetEntity/config.existingListener.neon');

		Assert::false($container->hasService('bridge.resolve_target_entity_listener'));

		$this->assertResolvedEntitiesRegistered($container, [
			EntityInterface::class => [
				'targetEntity' => EntityWithAttributesMapping::class,
			],
		]);
	}

	public function testListenerShouldNotBeRegisteredIfTargetEntitiesOptionIsDisabled(): void
	{
		$container = ContainerFactory::create(__DIR__ . '/Nettrine/TargetEntity/config.withTargetEntitiesDisabled.neon');

		Assert::null($container->getByType(ResolveTargetEntityListener::class, false));
	}

	public function testTargetEntitiesShouldNotBeRegisteredOnExistingListenerIfTargetEntitiesOptionIsDisabled(): void
	{
		$container = ContainerFactory::create(__DIR__ . '/Nettrine/TargetEntity/config.withTargetEntitiesDisabledAndExistingListener.neon');

		$this->assertResolvedEntitiesRegistered($container, []);
	}

	protected function tearDown(): void
	{
		# save manually partial code coverage to free memory
		if (Collector::isStarted()) {
			Collector::save();
		}
	}

	private function getListener(Container $container): ResolveTargetEntityListener
	{
		$listener = $container->getByType(ResolveTargetEntityListener::class);
		assert($listener instanceof ResolveTargetEntityListener);

		return $listener;
	}

	private function assertResolvedEntitiesRegistered(Container $container, array $targetEntities): void
	{
		$listener = $this->getListener($container);

		call_user_func(Closure::bind(
			static function () use ($listener, $targetEntities): void {
				Assert::same($targetEntities, $listener->resolveTargetEntities);
			},
			null,
			ResolveTargetEntityListener::class
		));
	}
}

(new NettrineTargetEntityDoctrineBridgeExtensionTest())->run();

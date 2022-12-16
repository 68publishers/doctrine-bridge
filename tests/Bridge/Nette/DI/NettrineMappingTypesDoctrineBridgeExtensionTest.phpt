<?php

declare(strict_types=1);

namespace SixtyEightPublishers\TranslationBridge\Tests\Bridge\Nette\DI;

use Tester\Assert;
use Tester\TestCase;
use RuntimeException;
use Nette\DI\Container;
use Tester\CodeCoverage\Collector;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\Mapping\MappingException;
use SixtyEightPublishers\DoctrineBridge\Tests\Bridge\Nette\DI\ContainerFactory;
use SixtyEightPublishers\DoctrineBridge\Tests\Fixtures\Entity\EntityWithXmlMapping;
use SixtyEightPublishers\DoctrineBridge\Tests\Fixtures\Entity\EntityWithAttributesMapping;
use SixtyEightPublishers\DoctrineBridge\Tests\Fixtures\Entity\EntityWithAnnotationsMapping;
use SixtyEightPublishers\DoctrineBridge\Tests\Fixtures\Entity\EntityWithSimplifiedXmlMapping;
use function assert;

require __DIR__ . '/../../../bootstrap.nettrine.php';

final class NettrineMappingTypesDoctrineBridgeExtensionTest extends TestCase
{
	public function testExceptionShouldBeThrownIfAnnotationDriverServiceIsMissing(): void
	{
		Assert::exception(
			static fn () => ContainerFactory::create(__DIR__ . '/Nettrine/EntityMapping/config.error.missingAnnotationDriverService.neon'),
			RuntimeException::class,
			'Can\'t resolve service definition by identifier "Doctrine\ORM\Mapping\Driver\AnnotationDriver" from option bridge.services.drivers.annotation. Please specify it manually.'
		);
	}

	public function testExceptionShouldBeThrownIfAttributeDriverServiceIsMissing(): void
	{
		Assert::exception(
			static fn () => ContainerFactory::create(__DIR__ . '/Nettrine/EntityMapping/config.error.missingAttributeDriverService.neon'),
			RuntimeException::class,
			'Can\'t resolve service definition by identifier "Doctrine\ORM\Mapping\Driver\AttributeDriver" from option bridge.services.drivers.attribute. Please specify it manually.'
		);
	}

	public function testExceptionShouldBeThrownIfXmlDriverServiceIsMissing(): void
	{
		Assert::exception(
			static fn () => ContainerFactory::create(__DIR__ . '/Nettrine/EntityMapping/config.error.missingXmlDriverService.neon'),
			RuntimeException::class,
			'Can\'t resolve service definition by identifier "Doctrine\ORM\Mapping\Driver\XmlDriver" from option bridge.services.drivers.xml. Please specify it manually.'
		);
	}

	public function testExceptionShouldBeThrownIfSimplifiedDriverServiceIsMissing(): void
	{
		Assert::exception(
			static fn () => ContainerFactory::create(__DIR__ . '/Nettrine/EntityMapping/config.error.missingSimplifiedXmlDriverService.neon'),
			RuntimeException::class,
			'Can\'t resolve service definition by identifier "Doctrine\ORM\Mapping\Driver\SimplifiedXmlDriver" from option bridge.services.drivers.simplified_xml. Please specify it manually.'
		);
	}

	public function testAnnotationEntityMappingShouldBeRegistered(): void
	{
		$container = ContainerFactory::create(__DIR__ . '/Nettrine/EntityMapping/config.withAnnotationEntityMappingProvider.neon');

		$this->assertExistingClassMetadata($container, EntityWithAnnotationsMapping::class);
	}

	public function testAttributeEntityMappingShouldBeRegistered(): void
	{
		$container = ContainerFactory::create(__DIR__ . '/Nettrine/EntityMapping/config.withAttributeEntityMappingProvider.neon');

		$this->assertExistingClassMetadata($container, EntityWithAttributesMapping::class);
	}

	public function testXmlEntityMappingShouldBeRegistered(): void
	{
		$container = ContainerFactory::create(__DIR__ . '/Nettrine/EntityMapping/config.withXmlEntityMappingProvider.neon');

		$this->assertExistingClassMetadata($container, EntityWithXmlMapping::class);
	}

	public function testSimplifiedXmlEntityMappingShouldBeRegistered(): void
	{
		$container = ContainerFactory::create(__DIR__ . '/Nettrine/EntityMapping/config.withSimplifiedXmlEntityMappingProvider.neon');

		$this->assertExistingClassMetadata($container, EntityWithSimplifiedXmlMapping::class);
	}

	public function testNoEntityMappingsShouldNotBeRegisteredIfEntityMappingsOptionIsDisabled(): void
	{
		$container = ContainerFactory::create(__DIR__ . '/Nettrine/EntityMapping/config.withEntityMappingsDisabled.neon');

		$this->assertMissingClassMetadata($container, EntityWithAttributesMapping::class);
		$this->assertMissingClassMetadata($container, EntityWithXmlMapping::class);
	}

	public function testAnnotationEntityMappingShouldNotBeRegisteredIfDriverIsDisabled(): void
	{
		$container = ContainerFactory::create(__DIR__ . '/Nettrine/EntityMapping/config.withAnnotationDriverDisabled.neon');

		$this->assertMissingClassMetadata($container, EntityWithAnnotationsMapping::class);
	}

	public function testAttributeEntityMappingShouldNotBeRegisteredIfDriverIsDisabled(): void
	{
		$container = ContainerFactory::create(__DIR__ . '/Nettrine/EntityMapping/config.withAttributeDriverDisabled.neon');

		$this->assertMissingClassMetadata($container, EntityWithAttributesMapping::class);
	}

	public function testXmlEntityMappingShouldNotBeRegisteredIfDriverIsDisabled(): void
	{
		$container = ContainerFactory::create(__DIR__ . '/Nettrine/EntityMapping/config.withXmlDriverDisabled.neon');

		$this->assertMissingClassMetadata($container, EntityWithXmlMapping::class);
	}

	public function testSimpleXmlEntityMappingShouldNotBeRegisteredIfDriverIsDisabled(): void
	{
		$container = ContainerFactory::create(__DIR__ . '/Nettrine/EntityMapping/config.withSimplifiedXmlDriverDisabled.neon');

		$this->assertMissingClassMetadata($container, EntityWithSimplifiedXmlMapping::class);
	}

	protected function tearDown(): void
	{
		# save manually partial code coverage to free memory
		if (Collector::isStarted()) {
			Collector::save();
		}
	}

	private function getClassMetadata(Container $container, string $entityClassname): ClassMetadata
	{
		$entityManager = $container->getByType(EntityManagerInterface::class);
		assert($entityManager instanceof EntityManagerInterface);
		$metadata = $entityManager->getClassMetadata($entityClassname);
		assert($metadata instanceof ClassMetadata);

		return $metadata;
	}

	private function assertExistingClassMetadata(Container $container, string $entityClassname): void
	{
		$metadata = $this->getClassMetadata($container, $entityClassname);

		Assert::same($entityClassname, $metadata->getName());
	}

	private function assertMissingClassMetadata(Container $container, string $entityClassname): void
	{
		Assert::exception(
			fn () => $this->getClassMetadata($container, $entityClassname),
			MappingException::class,
			'The class %A% was not found in the chain configured namespaces%A?%'
		);
	}
}

(new NettrineMappingTypesDoctrineBridgeExtensionTest())->run();

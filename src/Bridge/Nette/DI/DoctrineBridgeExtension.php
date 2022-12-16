<?php

declare(strict_types=1);

namespace SixtyEightPublishers\DoctrineBridge\Bridge\Nette\DI;

use RuntimeException;
use Nette\Schema\Expect;
use Nette\Schema\Schema;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;
use Nette\DI\ContainerBuilder;
use Nette\DI\CompilerExtension;
use Nette\DI\Definitions\Reference;
use Nette\DI\Definitions\Statement;
use Nette\DI\Definitions\Definition;
use Nette\DI\MissingServiceException;
use Doctrine\ORM\Mapping\Driver\XmlDriver;
use Nette\DI\Definitions\ServiceDefinition;
use Doctrine\ORM\Mapping\Driver\AttributeDriver;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\ORM\Tools\ResolveTargetEntityListener;
use Doctrine\ORM\Mapping\Driver\SimplifiedXmlDriver;
use Doctrine\Persistence\Mapping\Driver\MappingDriverChain;
use SixtyEightPublishers\DoctrineBridge\Type\ContainerAwareTypeInterface;
use SixtyEightPublishers\DoctrineBridge\Bridge\Nette\DI\Config\DriversConfig;
use SixtyEightPublishers\DoctrineBridge\Bridge\Nette\DI\Config\ServicesConfig;
use Doctrine\Migrations\Configuration\Configuration as MigrationsConfiguration;
use SixtyEightPublishers\DoctrineBridge\Bridge\Nette\DI\Config\DoctrineBridgeConfig;
use function count;
use function assert;
use function substr;
use function sprintf;
use function strncmp;
use function array_shift;
use function class_exists;
use function is_subclass_of;
use function interface_exists;

final class DoctrineBridgeExtension extends CompilerExtension
{
	public function getConfigSchema(): Schema
	{
		return Expect::structure([
			'database_types_enabled' => Expect::bool(true),
			'entity_mappings_enabled' => Expect::bool(true),
			'target_entities_enabled' => Expect::bool(true),
			'migration_directories_enabled' => Expect::bool(true),
			'services' => Expect::structure([
				'dbal_connection' => Expect::string(Connection::class),
				'drivers' => Expect::structure([
					'chain' => Expect::anyOf(Expect::string(), false)
						->default(MappingDriverChain::class),
					'annotation' => Expect::anyOf(Expect::string(), false)
						->default(AnnotationDriver::class),
					'xml' => Expect::anyOf(Expect::string(), false)
						->default(XmlDriver::class),
					'simplified_xml' => Expect::anyOf(Expect::string(), false)
						->default(SimplifiedXmlDriver::class),
					'attribute' => Expect::anyOf(Expect::string(), false)
						->default(AttributeDriver::class),
				])->castTo(DriversConfig::class),
				'migrations_configuration' => Expect::string(MigrationsConfiguration::class),
			])->castTo(ServicesConfig::class),
		])->castTo(DoctrineBridgeConfig::class);
	}

	public function loadConfiguration(): void
	{
		$config = $this->getConfig();
		assert($config instanceof DoctrineBridgeConfig);

		$this->loadTargetEntitiesService($config);
	}

	public function beforeCompile(): void
	{
		$config = $this->getConfig();
		assert($config instanceof DoctrineBridgeConfig);

		$this->processDatabaseTypeProviders($config);
		$this->processEntityMappings($config);
		$this->processTargetEntities($config);
		$this->processMigrationsDirectories($config);
	}

	private function loadTargetEntitiesService(DoctrineBridgeConfig $config): void
	{
		if (!$config->target_entities_enabled || !$this->isExtensionLoaded(TargetEntityProviderInterface::class)) {
			return;
		}

		$this->getContainerBuilder()
			->addDefinition($this->prefix('resolve_target_entity_listener'))
			->setAutowired(false)
			->setType(ResolveTargetEntityListener::class);
	}

	private function processDatabaseTypeProviders(DoctrineBridgeConfig $config): void
	{
		if (!$config->database_types_enabled || !$this->isExtensionLoaded(DatabaseTypeProviderInterface::class)) {
			return;
		}

		$connection = $this->getDependentDefinition('services.dbal_connection', $config->services->dbal_connection);
		assert($connection instanceof ServiceDefinition);

		foreach ($this->compiler->getExtensions(DatabaseTypeProviderInterface::class) as $extension) {
			assert($extension instanceof DatabaseTypeProviderInterface);

			foreach ($extension->getDatabaseTypes() as $databaseType) {
				$connection->addSetup('if (!?::hasType(?)) ?::addType(?, ?)', [
					ContainerBuilder::literal(Type::class),
					$databaseType->name,
					ContainerBuilder::literal(Type::class),
					$databaseType->name,
					$databaseType->class,
				]);

				if (null !== $databaseType->mappingType) {
					$connection->addSetup('$service->getDatabasePlatform()->registerDoctrineTypeMapping(?, ?)', [
						$databaseType->name,
						$databaseType->mappingType,
					]);
				}

				if (!is_subclass_of($databaseType->class, ContainerAwareTypeInterface::class, true)) {
					continue;
				}

				$connection->addSetup('?::getType(?)->setContainer(?, ?)', [
					ContainerBuilder::literal(Type::class),
					$databaseType->name,
					new Reference(ContainerBuilder::THIS_CONTAINER),
					$databaseType->context,
				]);
			}
		}
	}

	private function processEntityMappings(DoctrineBridgeConfig $config): void
	{
		if (!$config->entity_mappings_enabled || !$this->isExtensionLoaded(EntityMappingProviderInterface::class)) {
			return;
		}

		$driverChain = $config->services->drivers->chain
			? $this->getDependentDefinition('services.drivers.chain', $config->services->drivers->chain)
			: null;

		foreach ($this->compiler->getExtensions(EntityMappingProviderInterface::class) as $extension) {
			assert($extension instanceof EntityMappingProviderInterface);

			foreach ($extension->getEntityMappings() as $entityMapping) {
				switch ($entityMapping->driver) {
					case EntityMapping::DRIVER_ANNOTATION:
						$driver = $config->services->drivers->annotation
							? $this->getDependentDefinition('services.drivers.annotation', $config->services->drivers->annotation)
							: null;

						if (!$driver instanceof ServiceDefinition) {
							break;
						}

						$driver->addSetup('addPaths', [
							[$entityMapping->path],
						]);

						break;
					case EntityMapping::DRIVER_XML:
						$driver = $config->services->drivers->xml
							? $this->getDependentDefinition('services.drivers.xml', $config->services->drivers->xml)
							: null;

						if (!$driver instanceof ServiceDefinition) {
							break;
						}

						$driver->addSetup(new Statement('$service->getLocator()->addPaths([?])', [
							$entityMapping->path,
						]));

						break;
					case EntityMapping::DRIVER_SIMPLIFIED_XML:
						$driver = $config->services->drivers->simplified_xml
							? $this->getDependentDefinition('services.drivers.simplified_xml', $config->services->drivers->simplified_xml)
							: null;

						if (!$driver instanceof ServiceDefinition) {
							break;
						}

						$driver->addSetup(new Statement('$service->getLocator()->addNamespacePrefixes([? => ?])', [
							$entityMapping->path,
							$entityMapping->namespace,
						]));

						break;
					case EntityMapping::DRIVER_ATTRIBUTE:
						$driver = $config->services->drivers->attribute
							? $this->getDependentDefinition('services.drivers.attribute', $config->services->drivers->attribute)
							: null;

						if (!$driver instanceof ServiceDefinition) {
							break;
						}

						$driver->addSetup('addPaths', [
							[$entityMapping->path],
						]);
				}

				if ($driverChain instanceof ServiceDefinition && isset($driver)) {
					$driverChain->addSetup('addDriver', [
						$driver,
						$entityMapping->namespace,
					]);
				}
			}
		}
	}

	private function processTargetEntities(DoctrineBridgeConfig $config): void
	{
		if (!$config->target_entities_enabled || !$this->isExtensionLoaded(TargetEntityProviderInterface::class)) {
			return;
		}

		$builder = $this->getContainerBuilder();
		$listenerDefinition = null;

		foreach ($builder->findByType(ResolveTargetEntityListener::class) as $name => $def) {
			$listenerDefinition = $def;

			if ($name !== $this->prefix('resolve_target_entity_listener')) {
				$builder->removeDefinition($this->prefix('resolve_target_entity_listener'));

				break;
			}
		}

		if (!$listenerDefinition instanceof ServiceDefinition) {
			return;
		}

		if ($listenerDefinition->getName() === $this->prefix('resolve_target_entity_listener')) {
			$listenerDefinition->setAutowired();
		}

		foreach ($this->compiler->getExtensions(TargetEntityProviderInterface::class) as $extension) {
			assert($extension instanceof TargetEntityProviderInterface);

			foreach ($extension->getTargetEntities() as $targetEntity) {
				$listenerDefinition->addSetup('addResolveTargetEntity', [
					$targetEntity->originalEntity,
					$targetEntity->newEntity,
					$targetEntity->mapping,
				]);
			}
		}
	}

	private function processMigrationsDirectories(DoctrineBridgeConfig $config): void
	{
		if (!$config->migration_directories_enabled || !$this->isExtensionLoaded(MigrationsDirectoriesProviderInterface::class)) {
			return;
		}

		$configuration = $this->getDependentDefinition('services.migrations_configuration', $config->services->migrations_configuration);
		assert($configuration instanceof ServiceDefinition);

		foreach ($this->compiler->getExtensions(MigrationsDirectoriesProviderInterface::class) as $extension) {
			assert($extension instanceof MigrationsDirectoriesProviderInterface);

			foreach ($extension->getMigrationsDirectories() as $migrationsDirectory) {
				$configuration->addSetup('addMigrationsDirectory', [
					$migrationsDirectory->namespace,
					$migrationsDirectory->directory,
				]);
			}
		}
	}

	/**
	 * @param class-string $classname
	 */
	private function isExtensionLoaded(string $classname): bool
	{
		return 0 < count($this->compiler->getExtensions($classname));
	}

	private function getDependentDefinition(string $optionPath, string $typeOrName): Definition
	{
		$definitionName = $typeOrName;

		if (0 === strncmp($definitionName, '@', 1)) {
			$definitionName = substr($definitionName, 1);
		}

		$builder = $this->getContainerBuilder();

		if (!class_exists($definitionName) && !interface_exists($definitionName)) {
			return $builder->getDefinition($definitionName);
		}

		try {
			return $builder->getDefinitionByType($definitionName);
		} catch (MissingServiceException $e) {
		}

		$definitions = $builder->findByType($definitionName);

		if (1 === count($definitions)) {
			return array_shift($definitions);
		}

		throw new RuntimeException(sprintf(
			'Can\'t resolve service definition by identifier "%s" from option %s.%s. Please specify it manually.',
			$typeOrName,
			$this->name,
			$optionPath
		));
	}
}

<?php

declare(strict_types=1);

namespace SixtyEightPublishers\DoctrineBridge\DI;

use RuntimeException;
use Nette\Schema\Expect;
use Nette\Schema\Schema;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;
use Nette\DI\ContainerBuilder;
use Nette\DI\CompilerExtension;
use Nette\PhpGenerator\PhpLiteral;
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
use Doctrine\Migrations\Configuration\Configuration as MigrationsConfiguration;

final class DoctrineBridgeExtension extends CompilerExtension
{
	/**
	 * {@inheritDoc}
	 */
	public function getConfigSchema(): Schema
	{
		return Expect::structure([
			'database_types_enabled' => Expect::bool(TRUE),
			'entity_mappings_enabled' => Expect::bool(TRUE),
			'target_entities_enabled' => Expect::bool(TRUE),
			'migration_directories_enabled' => Expect::bool(TRUE),
			'services' => Expect::structure([
				'dbal_connection' => Expect::string(Connection::class),
				'drivers' => Expect::structure([
					'chain' => Expect::string(MappingDriverChain::class)->nullable(),
					'annotation' => Expect::string(AnnotationDriver::class)->nullable(),
					'xml' => Expect::string(XmlDriver::class)->nullable(),
					'simplified_xml' => Expect::string(SimplifiedXmlDriver::class)->nullable(),
					'attribute' => Expect::string(AttributeDriver::class)->nullable(),
				]),
				'migrations_configuration' => Expect::string(MigrationsConfiguration::class),
			]),
		]);
	}

	/**
	 * {@inheritDoc}
	 */
	public function loadConfiguration(): void
	{
		$this->loadTargetEntitiesService();
	}

	/**
	 * {@inheritDoc}
	 */
	public function beforeCompile(): void
	{
		$this->processDatabaseTypeProviders();
		$this->processEntityMappings();
		$this->processTargetEntities();
		$this->processMigrationsDirectories();
	}

	/**
	 * @return void
	 */
	private function loadTargetEntitiesService(): void
	{
		if (!$this->config->target_entities_enabled || !$this->isExtensionLoaded(TargetEntityProviderInterface::class)) {
			return;
		}

		$this->getContainerBuilder()
			->addDefinition($this->prefix('resolve_target_entity_listener'))
			->setType(ResolveTargetEntityListener::class);
	}

	/**
	 * @return void
	 */
	private function processDatabaseTypeProviders(): void
	{
		if (!$this->config->database_types_enabled || !$this->isExtensionLoaded(DatabaseTypeProviderInterface::class)) {
			return;
		}

		$connection = $this->getDependentDefinition('services.dbal_connection', $this->config->services->dbal_connection);

		assert($connection instanceof ServiceDefinition);

		foreach ($this->compiler->getExtensions(DatabaseTypeProviderInterface::class) as $extension) {
			assert($extension instanceof DatabaseTypeProviderInterface);

			foreach ($extension->getDatabaseTypes() as $databaseType) {
				$connection->addSetup('if (!?::hasType(?)) ?::addType(?, ?)', [
					new PhpLiteral(Type::class),
					$databaseType->name,
					new PhpLiteral(Type::class),
					$databaseType->name,
					$databaseType->class,
				]);

				if ($databaseType->commented) {
					$connection->addSetup('@self->getDatabasePlatform()->markDoctrineTypeCommented(?::getType(?))', [
						new PhpLiteral(Type::class),
						$databaseType->name,
					]);
				}

				if (NULL !== $databaseType->mappingType) {
					$connection->addSetup('@self->getDatabasePlatform()->registerDoctrineTypeMapping(?, ?)', [
						$databaseType->name,
						$databaseType->mappingType,
					]);
				}

				if (!is_subclass_of($databaseType->class, ContainerAwareTypeInterface::class, TRUE)) {
					continue;
				}

				$connection->addSetup('?::getType(?)->setContainer(?, ?)', [
					new PhpLiteral(Type::class),
					$databaseType->name,
					new Reference(ContainerBuilder::THIS_CONTAINER),
					$databaseType->context,
				]);
			}
		}
	}

	/**
	 * @return void
	 */
	private function processEntityMappings(): void
	{
		if (!$this->config->entity_mappings_enabled || !$this->isExtensionLoaded(EntityMappingProviderInterface::class)) {
			return;
		}

		$driverChain = $this->getDependentDefinition('services.drivers.chain', $this->config->services->drivers->chain);

		foreach ($this->compiler->getExtensions(EntityMappingProviderInterface::class) as $extension) {
			assert($extension instanceof EntityMappingProviderInterface);

			foreach ($extension->getEntityMappings() as $entityMapping) {
				switch ($entityMapping->driver) {
					case EntityMapping::DRIVER_ANNOTATION:
						$driver = $this->getDependentDefinition('services.drivers.annotation', $this->config->services->drivers->annotation);

						if (!$driver instanceof ServiceDefinition) {
							break;
						}

						$driver->addSetup('addPaths', [
							[$entityMapping->path],
						]);

						break;
					case EntityMapping::DRIVER_XML:
						$driver = $this->getDependentDefinition('services.drivers.xml', $this->config->services->drivers->xml);

						if (!$driver instanceof ServiceDefinition) {
							break;
						}

						$driver->addSetup(new Statement('$service->getLocator()->addPaths([?])', [
							$entityMapping->path,
						]));

						break;
					case EntityMapping::DRIVER_SIMPLIFIED_XML:
						$driver = $this->getDependentDefinition('services.drivers.simplified_xml', $this->config->services->drivers->simplified_xml);

						if (!$driver instanceof ServiceDefinition) {
							break;
						}

						$driver->addSetup(new Statement('$service->getLocator()->addNamespacePrefixes([? => ?])', [
							$entityMapping->path,
							$entityMapping->namespace,
						]));

						break;
					case EntityMapping::DRIVER_ATTRIBUTE:
						$driver = $this->getDependentDefinition('services.drivers.attribute', $this->config->services->drivers->attribute);

						if (!$driver instanceof ServiceDefinition) {
							break;
						}

						$driver->addSetup('addPaths', [
							[$entityMapping->path],
						]);
				}

				if ($driverChain instanceof ServiceDefinition && isset($driver)) {
					$driverChain->addSetup('addDriver', [$driver, $entityMapping->namespace]);
				}
			}
		}
	}

	/**
	 * @return void
	 */
	private function processTargetEntities(): void
	{
		if (!$this->config->target_entities_enabled || !$this->isExtensionLoaded(TargetEntityProviderInterface::class)) {
			return;
		}

		$builder = $this->getContainerBuilder();
		$listenerDefinition = NULL;

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

		foreach ($this->compiler->getExtensions(TargetEntityProviderInterface::class) as $extension) {
			assert($extension instanceof TargetEntityProviderInterface);

			foreach ($extension->getTargetEntities() as $targetEntity) {
				$listenerDefinition->addSetup('addResolveTargetEntity', [$targetEntity->originalEntity, $targetEntity->newEntity, $targetEntity->mapping]);
			}
		}
	}

	/**
	 * @return void
	 */
	private function processMigrationsDirectories(): void
	{
		if (!$this->config->migration_directories_enabled || !$this->isExtensionLoaded(MigrationsDirectoriesProviderInterface::class)) {
			return;
		}

		$configuration = $this->getDependentDefinition('migrations_configuration', $this->config->services->migrations_configuration);

		assert($configuration instanceof ServiceDefinition);

		foreach ($this->compiler->getExtensions(MigrationsDirectoriesProviderInterface::class) as $extension) {
			assert($extension instanceof MigrationsDirectoriesProviderInterface);

			foreach ($extension->getMigrationsDirectories() as $migrationsDirectory) {
				$configuration->addSetup('addMigrationsDirectory', [$migrationsDirectory->namespace, $migrationsDirectory->directory]);
			}
		}
	}

	/**
	 * @param string $classname
	 *
	 * @return bool
	 */
	private function isExtensionLoaded(string $classname): bool
	{
		return 0 < count($this->compiler->getExtensions($classname));
	}

	/**
	 * @param string      $optionPath
	 * @param string|NULL $typeOrName
	 *
	 * @return \Nette\DI\Definitions\Definition|NULL
	 */
	private function getDependentDefinition(string $optionPath, ?string $typeOrName): ?Definition
	{
		if (NULL === $typeOrName) {
			return NULL;
		}

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

		if (0 === count($definitions)) {
			throw new RuntimeException(sprintf(
				'Can\'t resolve service definition by identifier "%s" from option %s.%s. Please specify it manually.',
				$typeOrName,
				$this->name,
				$optionPath
			));
		}

		throw new RuntimeException(sprintf(
			'Can\'t resolve service definition by identifier "%s" from option %s.%s because multiple definitions found. Please specify it manually.',
			$typeOrName,
			$this->name,
			$optionPath
		));
	}
}

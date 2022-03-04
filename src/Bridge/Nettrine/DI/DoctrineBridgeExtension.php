<?php

declare(strict_types=1);

namespace SixtyEightPublishers\DoctrineBridge\Bridge\Nettrine\DI;

use Doctrine\DBAL\Types\Type;
use Nette\DI\CompilerExtension;
use Nette\PhpGenerator\PhpLiteral;
use Nette\DI\Definitions\Reference;
use Nettrine\DBAL\DI\DbalExtension;
use Nettrine\ORM\DI\Helpers\MappingHelper;
use Nette\DI\Definitions\ServiceDefinition;
use Nettrine\Migrations\DI\MigrationsExtension;
use Doctrine\ORM\Tools\ResolveTargetEntityListener;
use Doctrine\Migrations\Configuration\Configuration;
use SixtyEightPublishers\DoctrineBridge\DI\EntityMapping;
use SixtyEightPublishers\DoctrineBridge\DI\DatabaseTypeProviderInterface;
use SixtyEightPublishers\DoctrineBridge\DI\TargetEntityProviderInterface;
use SixtyEightPublishers\DoctrineBridge\Type\ContainerAwareTypeInterface;
use SixtyEightPublishers\DoctrineBridge\DI\EntityMappingProviderInterface;
use SixtyEightPublishers\DoctrineBridge\DI\MigrationsDirectoriesProviderInterface;

final class DoctrineBridgeExtension extends CompilerExtension
{
	/**
	 * {@inheritDoc}
	 */
	public function loadConfiguration(): void
	{
		if (!$this->isExtensionLoaded(TargetEntityProviderInterface::class)) {
			return;
		}

		$this->getContainerBuilder()
			->addDefinition($this->prefix('resolve_target_entity_listener'))
			->setType(ResolveTargetEntityListener::class);
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
	private function processDatabaseTypeProviders(): void
	{
		$dbalExtensions = $this->compiler->getExtensions(DbalExtension::class);

		if (0 >= count($dbalExtensions)) {
			return;
		}

		$dbalExtensionName = key($dbalExtensions);
		$builder = $this->getContainerBuilder();

		$connectionFactory = $builder->getDefinition($dbalExtensionName . '.connectionFactory');

		assert($connectionFactory instanceof ServiceDefinition);

		$factory = $connectionFactory->getFactory();
		[$types, $typesMapping] = $factory->arguments;
		$contexts = [];

		/** @var \SixtyEightPublishers\DoctrineBridge\DI\DatabaseTypeProviderInterface $extension */
		foreach ($this->compiler->getExtensions(DatabaseTypeProviderInterface::class) as $extension) {
			foreach ($extension->getDatabaseTypes() as $databaseType) {
				$types[$databaseType->name] = [
					'class' => $databaseType->class,
					'commented' => FALSE,
				];

				if (NULL !== $databaseType->mappingType) {
					$typesMapping[$databaseType->name] = $databaseType->mappingType;
				}

				$contexts[$databaseType->name] = $databaseType->context;
			}
		}

		$factory->arguments[0] = $types;
		$factory->arguments[1] = $typesMapping;

		$connection = $builder->getDefinition($dbalExtensionName . '.connection');

		assert($connection instanceof ServiceDefinition);

		foreach ($types as $typeName => $typeOptions) {
			$typeClassName = $typeOptions['class'];

			if (!is_subclass_of($typeClassName, ContainerAwareTypeInterface::class, TRUE)) {
				continue;
			}

			$connection->addSetup('?::getType(?)->setContainer(?, ?)', [
				new PhpLiteral(Type::class),
				$typeName,
				new Reference($builder::THIS_CONTAINER),
				$contexts[$typeName] ?? [],
			]);
		}
	}

	/**
	 * @return void
	 */
	private function processEntityMappings(): void
	{
		$mappingHelper = MappingHelper::of($this);

		/** @var \SixtyEightPublishers\DoctrineBridge\DI\EntityMappingProviderInterface $extension */
		foreach ($this->compiler->getExtensions(EntityMappingProviderInterface::class) as $extension) {
			foreach ($extension->getEntityMappings() as $entityMapping) {
				switch ($entityMapping->driver) {
					case EntityMapping::DRIVER_ANNOTATIONS:
						$mappingHelper->addAnnotation($entityMapping->namespace, $entityMapping->path);

						break;
					case EntityMapping::DRIVER_YAML:
						$mappingHelper->addYaml($entityMapping->namespace, $entityMapping->path);

						break;
					case EntityMapping::DRIVER_XML:
						$mappingHelper->addXml($entityMapping->namespace, $entityMapping->path);

						break;
				}
			}
		}
	}

	/**
	 * @return void
	 */
	private function processTargetEntities(): void
	{
		$builder = $this->getContainerBuilder();

		if (!$builder->hasDefinition($this->prefix('resolve_target_entity_listener'))) {
			return;
		}

		$listener = $builder->getDefinition($this->prefix('resolve_target_entity_listener'));

		/** @var \SixtyEightPublishers\DoctrineBridge\DI\TargetEntityProviderInterface $extension */
		foreach ($this->compiler->getExtensions(TargetEntityProviderInterface::class) as $extension) {
			foreach ($extension->getTargetEntities() as $targetEntity) {
				$listener->addSetup('addResolveTargetEntity', [$targetEntity->originalEntity, $targetEntity->newEntity, $targetEntity->mapping]);
			}
		}
	}

	/**
	 * @return void
	 */
	private function processMigrationsDirectories(): void
	{
		if (!$this->isExtensionLoaded(MigrationsExtension::class)) {
			return;
		}

		$builder = $this->getContainerBuilder();
		$configuration = $builder->getDefinitionByType(Configuration::class);

		assert($configuration instanceof ServiceDefinition);

		/** @var \SixtyEightPublishers\DoctrineBridge\DI\MigrationsDirectoriesProviderInterface $extension */
		foreach ($this->compiler->getExtensions(MigrationsDirectoriesProviderInterface::class) as $extension) {
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
}

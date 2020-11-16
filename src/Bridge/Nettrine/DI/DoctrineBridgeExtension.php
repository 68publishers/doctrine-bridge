<?php

declare(strict_types=1);

namespace SixtyEightPublishers\DoctrineBridge\Bridge\Nettrine\DI;

use Nette\DI\CompilerExtension;
use Nettrine\DBAL\DI\DbalExtension;
use Nettrine\ORM\DI\Helpers\MappingHelper;
use Doctrine\ORM\Tools\ResolveTargetEntityListener;
use SixtyEightPublishers\DoctrineBridge\DI\EntityMapping;
use SixtyEightPublishers\DoctrineBridge\DI\DatabaseTypeProviderInterface;
use SixtyEightPublishers\DoctrineBridge\DI\TargetEntityProviderInterface;
use SixtyEightPublishers\DoctrineBridge\DI\EntityMappingProviderInterface;

final class DoctrineBridgeExtension extends CompilerExtension
{
	/**
	 * {@inheritDoc}
	 */
	public function loadConfiguration(): void
	{
		if (0 >= count($this->compiler->getExtensions(TargetEntityProviderInterface::class))) {
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

		/** @var \Nette\DI\Definitions\ServiceDefinition $connectionFactory */
		$connectionFactory = $this->getContainerBuilder()->getDefinition($dbalExtensionName . '.connectionFactory');
		$factory = $connectionFactory->getFactory();
		[$types, $typesMapping] = $factory->arguments;

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
			}
		}

		$factory->arguments[0] = $types;
		$factory->arguments[1] = $typesMapping;
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
}

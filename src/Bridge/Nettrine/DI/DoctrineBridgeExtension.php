<?php

declare(strict_types=1);

namespace SixtyEightPublishers\DoctrineBridge\Bridge\Nettrine\DI;

use Nette\DI\CompilerExtension;
use Nette\DI\Definitions\Statement;
use Nettrine\DBAL\DI\DbalExtension;
use Nettrine\ORM\DI\OrmXmlExtension;
use Doctrine\ORM\Mapping\Driver\XmlDriver;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\ORM\Tools\ResolveTargetEntityListener;
use Doctrine\ORM\Mapping\Driver\SimplifiedXmlDriver;
use Doctrine\ORM\Mapping\Driver\SimplifiedYamlDriver;
use SixtyEightPublishers\DoctrineBridge\DI\EntityMapping;
use Doctrine\Persistence\Mapping\Driver\MappingDriverChain;
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
		$ormXmlExtension = $this->compiler->getExtensions(OrmXmlExtension::class);
		$ormXmlExtension = 0 < count($ormXmlExtension) ? array_shift($ormXmlExtension) : NULL;
		$simpleXml = $ormXmlExtension instanceof OrmXmlExtension ? $ormXmlExtension->getConfig()->simple : FALSE;

		$drivers = [
			EntityMapping::DRIVER_ANNOTATIONS => AnnotationDriver::class,
			EntityMapping::DRIVER_YAML => SimplifiedYamlDriver::class,
			EntityMapping::DRIVER_XML => $simpleXml ? SimplifiedXmlDriver::class : XmlDriver::class,
		];

		$builder = $this->getContainerBuilder();
		$chain = $builder->getDefinitionByType(MappingDriverChain::class);

		/** @var \SixtyEightPublishers\DoctrineBridge\DI\EntityMappingProviderInterface $extension */
		foreach ($this->compiler->getExtensions(EntityMappingProviderInterface::class) as $extension) {
			foreach ($extension->getEntityMappings() as $entityMapping) {
				$driver = $builder->getDefinitionByType($drivers[$entityMapping->driver]);

				switch ($entityMapping->driver) {
					case EntityMapping::DRIVER_ANNOTATIONS:
						$driver->addSetup('addPaths', [[$entityMapping->path]]);

						break;
					case EntityMapping::DRIVER_YAML:
						$driver->addSetup(new Statement('$service->getLocator()->addNamespacePrefixes([? => ?])', [$entityMapping->path, $entityMapping->namespace]));

						break;
					case EntityMapping::DRIVER_XML:
						$driver->addSetup($simpleXml ? new Statement('$service->getLocator()->addNamespacePrefixes([? => ?])', [$entityMapping->path, $entityMapping->namespace]) : new Statement('$service->getLocator()->addPaths([?])', [$entityMapping->path]));

						break;
				}
				$chain->addSetup('addDriver', [$driver, $entityMapping->namespace]);
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

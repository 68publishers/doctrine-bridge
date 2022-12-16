<?php

declare(strict_types=1);

namespace SixtyEightPublishers\DoctrineBridge\Tests\Fixtures\Extension;

use Nette\DI\CompilerExtension;
use SixtyEightPublishers\DoctrineBridge\Bridge\Nette\DI\EntityMapping;
use SixtyEightPublishers\DoctrineBridge\Bridge\Nette\DI\EntityMappingProviderInterface;

final class SimplifiedXmlEntityMappingProviderExtension extends CompilerExtension implements EntityMappingProviderInterface
{
	public function getEntityMappings(): array
	{
		return [
			new EntityMapping(EntityMapping::DRIVER_SIMPLIFIED_XML, 'SixtyEightPublishers\DoctrineBridge\Tests\Fixtures\Entity', __DIR__ . '/../Mapping/SimplifiedXml'),
		];
	}
}

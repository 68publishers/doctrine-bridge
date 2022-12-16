<?php

declare(strict_types=1);

namespace SixtyEightPublishers\DoctrineBridge\Bridge\Nette\DI;

interface EntityMappingProviderInterface
{
	/**
	 * @return array<EntityMapping>
	 */
	public function getEntityMappings(): array;
}

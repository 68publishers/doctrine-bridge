<?php

declare(strict_types=1);

namespace SixtyEightPublishers\DoctrineBridge\DI;

interface EntityMappingProviderInterface
{
	/**
	 * @return \SixtyEightPublishers\DoctrineBridge\DI\EntityMapping[]
	 */
	public function getEntityMappings(): array;
}

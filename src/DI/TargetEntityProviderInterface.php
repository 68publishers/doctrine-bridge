<?php

declare(strict_types=1);

namespace SixtyEightPublishers\DoctrineBridge\DI;

interface TargetEntityProviderInterface
{
	/**
	 * @return \SixtyEightPublishers\DoctrineBridge\DI\TargetEntity[]
	 */
	public function getTargetEntities(): array;
}

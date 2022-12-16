<?php

declare(strict_types=1);

namespace SixtyEightPublishers\DoctrineBridge\Bridge\Nette\DI;

interface TargetEntityProviderInterface
{
	/**
	 * @return array<TargetEntity>
	 */
	public function getTargetEntities(): array;
}

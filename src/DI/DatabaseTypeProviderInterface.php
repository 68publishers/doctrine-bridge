<?php

declare(strict_types=1);

namespace SixtyEightPublishers\DoctrineBridge\DI;

interface DatabaseTypeProviderInterface
{
	/**
	 * @return \SixtyEightPublishers\DoctrineBridge\DI\DatabaseType[]
	 */
	public function getDatabaseTypes(): array;
}

<?php

declare(strict_types=1);

namespace SixtyEightPublishers\DoctrineBridge\DI;

interface MigrationsDirectoriesProviderInterface
{
	/**
	 * @return \SixtyEightPublishers\DoctrineBridge\DI\MigrationsDirectory[]
	 */
	public function getMigrationsDirectories(): array;
}

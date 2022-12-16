<?php

declare(strict_types=1);

namespace SixtyEightPublishers\DoctrineBridge\Bridge\Nette\DI;

interface MigrationsDirectoriesProviderInterface
{
	/**
	 * @return array<MigrationsDirectory>
	 */
	public function getMigrationsDirectories(): array;
}

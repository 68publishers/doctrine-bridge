<?php

declare(strict_types=1);

namespace SixtyEightPublishers\DoctrineBridge\Bridge\Nette\DI;

interface DatabaseTypeProviderInterface
{
	/**
	 * @return array<DatabaseType>
	 */
	public function getDatabaseTypes(): array;
}

<?php

declare(strict_types=1);

namespace SixtyEightPublishers\DoctrineBridge\Bridge\Nette\DI;

final class MigrationsDirectory
{
	public function __construct(
		public readonly string $namespace,
		public readonly string $directory,
	) {
	}
}

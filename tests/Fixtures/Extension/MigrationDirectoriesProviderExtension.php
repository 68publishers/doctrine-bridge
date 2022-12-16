<?php

declare(strict_types=1);

namespace SixtyEightPublishers\DoctrineBridge\Tests\Fixtures\Extension;

use Nette\DI\CompilerExtension;
use SixtyEightPublishers\DoctrineBridge\Bridge\Nette\DI\MigrationsDirectory;
use SixtyEightPublishers\DoctrineBridge\Bridge\Nette\DI\MigrationsDirectoriesProviderInterface;

final class MigrationDirectoriesProviderExtension extends CompilerExtension implements MigrationsDirectoriesProviderInterface
{
	public function getMigrationsDirectories(): array
	{
		return [
			new MigrationsDirectory('Bundle', __DIR__),
		];
	}
}

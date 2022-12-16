<?php

declare(strict_types=1);

namespace SixtyEightPublishers\DoctrineBridge\Tests\Fixtures\Extension;

use Doctrine\DBAL\Types\Types;
use Nette\DI\CompilerExtension;
use SixtyEightPublishers\DoctrineBridge\Bridge\Nette\DI\DatabaseType;
use SixtyEightPublishers\DoctrineBridge\Tests\Fixtures\DatabaseType\CustomType;
use SixtyEightPublishers\DoctrineBridge\Bridge\Nette\DI\DatabaseTypeProviderInterface;
use SixtyEightPublishers\DoctrineBridge\Tests\Fixtures\DatabaseType\CustomTypeWithContainer;

final class DatabaseTypeProviderExtension extends CompilerExtension implements DatabaseTypeProviderInterface
{
	public function getDatabaseTypes(): array
	{
		return [
			new DatabaseType('test_1', CustomType::class),
			new DatabaseType('test_2', CustomType::class, Types::TEXT),
			new DatabaseType('test_3', CustomTypeWithContainer::class),
			new DatabaseType('test_4', CustomTypeWithContainer::class, null, ['test_key' => 'test_value']),
		];
	}
}

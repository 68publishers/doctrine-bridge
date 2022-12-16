<?php

declare(strict_types=1);

namespace SixtyEightPublishers\DoctrineBridge\Tests\Fixtures\Extension;

use Nette\DI\CompilerExtension;
use SixtyEightPublishers\DoctrineBridge\Bridge\Nette\DI\TargetEntity;
use SixtyEightPublishers\DoctrineBridge\Tests\Fixtures\Entity\EntityInterface;
use SixtyEightPublishers\DoctrineBridge\Bridge\Nette\DI\TargetEntityProviderInterface;

final class TargetEntityProviderExtension extends CompilerExtension implements TargetEntityProviderInterface
{
	public function __construct(
		private readonly string $entityClassname,
		private readonly array $mapping = [],
	) {
	}

	public function getTargetEntities(): array
	{
		return [
			new TargetEntity(EntityInterface::class, $this->entityClassname, $this->mapping),
		];
	}
}

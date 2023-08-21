<?php

declare(strict_types=1);

namespace SixtyEightPublishers\DoctrineBridge\Tests\Fixtures\DatabaseType;

use Nette\DI\Container;
use Doctrine\DBAL\Types\StringType;
use SixtyEightPublishers\DoctrineBridge\Type\ContainerAwareTypeInterface;

final class CustomTypeWithContainer extends StringType implements ContainerAwareTypeInterface
{
	public ?Container $container = null;

	public ?array $context = null;

	public function setContainer(Container $container, array $context = []): void
	{
		$this->container = $container;
		$this->context = $context;
	}

	public static function create(Container $container, array $context): self
	{
		$type = new self();
		$type->setContainer($container, $context);

		return $type;
	}
}

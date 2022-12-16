<?php

declare(strict_types=1);

namespace SixtyEightPublishers\DoctrineBridge\Type;

use Nette\DI\Container;

interface ContainerAwareTypeInterface
{
	/**
	 * @internal
	 *
	 * @param array<mixed> $context
	 */
	public function setContainer(Container $container, array $context = []): void;
}

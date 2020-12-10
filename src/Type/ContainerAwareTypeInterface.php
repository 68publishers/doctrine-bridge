<?php

declare(strict_types=1);

namespace SixtyEightPublishers\DoctrineBridge\Type;

use Nette\DI\Container;

interface ContainerAwareTypeInterface
{
	/**
	 * @param \Nette\DI\Container $container
	 * @param array               $context
	 *
	 * @return void
	 */
	public function setContainer(Container $container, array $context = []): void;
}

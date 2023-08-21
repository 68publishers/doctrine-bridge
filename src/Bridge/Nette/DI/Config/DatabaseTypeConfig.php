<?php

declare(strict_types=1);

namespace SixtyEightPublishers\DoctrineBridge\Bridge\Nette\DI\Config;

final class DatabaseTypeConfig
{
	/** @var class-string */
	public string $class;

	public ?string $mappingType = null;

	/** @var array<string, mixed> */
	public array $context = [];
}

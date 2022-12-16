<?php

declare(strict_types=1);

namespace SixtyEightPublishers\DoctrineBridge\Bridge\Nette\DI;

final class DatabaseType
{
	/**
	 * @param class-string         $class
	 * @param array<string, mixed> $context
	 */
	public function __construct(
		public readonly string $name,
		public readonly string $class,
		public readonly ?string $mappingType = null,
		public readonly array $context = []
	) {
	}
}

<?php

declare(strict_types=1);

namespace SixtyEightPublishers\DoctrineBridge\Bridge\Nette\DI;

final class TargetEntity
{
	/**
	 * @param class-string         $originalEntity
	 * @param class-string         $newEntity
	 * @param array<string, mixed> $mapping
	 */
	public function __construct(
		public readonly string $originalEntity,
		public readonly string $newEntity,
		public readonly array $mapping = [],
	) {
	}
}

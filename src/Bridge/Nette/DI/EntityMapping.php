<?php

declare(strict_types=1);

namespace SixtyEightPublishers\DoctrineBridge\Bridge\Nette\DI;

final class EntityMapping
{
	public const DRIVER_ANNOTATION = 'annotation';
	public const DRIVER_XML = 'xml';
	public const DRIVER_SIMPLIFIED_XML = 'simplified_xml';
	public const DRIVER_ATTRIBUTE = 'attribute';

	public function __construct(
		public readonly string $driver,
		public readonly string $namespace,
		public readonly string $path
	) {
	}
}

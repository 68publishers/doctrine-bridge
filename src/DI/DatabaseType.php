<?php

declare(strict_types=1);

namespace SixtyEightPublishers\DoctrineBridge\DI;

final class DatabaseType
{
	public string $name;

	public string $class;

	public ?string $mappingType;

	public array $context;

	/**
	 * @param string      $name
	 * @param string      $class
	 * @param string|NULL $mappingType
	 * @param array       $context
	 */
	public function __construct(string $name, string $class, ?string $mappingType = NULL, array $context = [])
	{
		$this->name = $name;
		$this->class = $class;
		$this->mappingType = $mappingType;
		$this->context = $context;
	}
}

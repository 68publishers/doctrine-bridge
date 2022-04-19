<?php

declare(strict_types=1);

namespace SixtyEightPublishers\DoctrineBridge\DI;

final class DatabaseType
{
	public string $name;

	public string $class;

	public ?string $mappingType;

	public bool $commented;

	public array $context;

	/**
	 * @param string      $name
	 * @param string      $class
	 * @param string|null $mappingType
	 * @param bool        $commented
	 * @param array       $context
	 */
	public function __construct(string $name, string $class, ?string $mappingType = NULL, bool $commented = FALSE, array $context = [])
	{
		$this->name = $name;
		$this->class = $class;
		$this->mappingType = $mappingType;
		$this->commented = $commented;
		$this->context = $context;
	}
}

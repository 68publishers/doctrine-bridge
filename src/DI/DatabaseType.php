<?php

declare(strict_types=1);

namespace SixtyEightPublishers\DoctrineBridge\DI;

final class DatabaseType
{
	/** @var string  */
	public $name;

	/** @var string  */
	public $class;

	/** @var string|NULL  */
	public $mappingType;

	/**
	 * @param string      $name
	 * @param string      $class
	 * @param string|NULL $mappingType
	 */
	public function __construct(string $name, string $class, ?string $mappingType = NULL)
	{
		$this->name = $name;
		$this->class = $class;
		$this->mappingType = $mappingType;
	}
}

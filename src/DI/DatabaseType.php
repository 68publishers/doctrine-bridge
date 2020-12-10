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

	/** @var array  */
	public $context;

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

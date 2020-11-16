<?php

declare(strict_types=1);

namespace SixtyEightPublishers\DoctrineBridge\DI;

final class EntityMapping
{
	public const DRIVER_ANNOTATIONS = 'annotations';
	public const DRIVER_YAML = 'yaml';
	public const DRIVER_XML = 'xml';

	/** @var string  */
	public $driver;

	/** @var string  */
	public $namespace;

	/** @var string  */
	public $path;

	/**
	 * @param string $driver
	 * @param string $namespace
	 * @param string $path
	 */
	public function __construct(string $driver, string $namespace, string $path)
	{
		$this->driver = $driver;
		$this->namespace = $namespace;
		$this->path = $path;
	}
}

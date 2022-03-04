<?php

declare(strict_types=1);

namespace SixtyEightPublishers\DoctrineBridge\DI;

final class MigrationsDirectory
{
	public string $namespace;

	public string $directory;

	/**
	 * @param string $namespace
	 * @param string $directory
	 */
	public function __construct(string $namespace, string $directory)
	{
		$this->namespace = $namespace;
		$this->directory = $directory;
	}
}

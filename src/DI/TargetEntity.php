<?php

declare(strict_types=1);

namespace SixtyEightPublishers\DoctrineBridge\DI;

final class TargetEntity
{
	public string $originalEntity;

	public string $newEntity;

	public array$mapping;

	/**
	 * @param string $originalEntity
	 * @param string $newEntity
	 * @param array  $mapping
	 */
	public function __construct(string $originalEntity, string $newEntity, array $mapping = [])
	{
		$this->originalEntity = $originalEntity;
		$this->newEntity = $newEntity;
		$this->mapping = $mapping;
	}
}

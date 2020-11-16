<?php

declare(strict_types=1);

namespace SixtyEightPublishers\DoctrineBridge\DI;

final class TargetEntity
{
	/** @var string  */
	public $originalEntity;

	/** @var string  */
	public $newEntity;

	/** @var array  */
	public $mapping;

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

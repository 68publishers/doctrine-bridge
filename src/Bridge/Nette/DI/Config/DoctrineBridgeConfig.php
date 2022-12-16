<?php

declare(strict_types=1);

namespace SixtyEightPublishers\DoctrineBridge\Bridge\Nette\DI\Config;

final class DoctrineBridgeConfig
{
	public bool $database_types_enabled;

	public bool $entity_mappings_enabled;

	public bool $target_entities_enabled;

	public bool $migration_directories_enabled;

	public ServicesConfig $services;
}

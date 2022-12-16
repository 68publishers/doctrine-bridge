<?php

declare(strict_types=1);

namespace SixtyEightPublishers\DoctrineBridge\Bridge\Nette\DI\Config;

final class ServicesConfig
{
	public string $dbal_connection;

	public DriversConfig $drivers;

	public string $migrations_configuration;
}

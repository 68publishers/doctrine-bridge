<?php

declare(strict_types=1);

namespace SixtyEightPublishers\DoctrineBridge\Bridge\Nette\DI;

use Nette\Schema\Expect;
use Nette\Schema\Schema;
use Nette\DI\CompilerExtension;
use SixtyEightPublishers\DoctrineBridge\Bridge\Nette\DI\Config\DatabaseTypeConfig;
use function assert;
use function is_array;
use function is_string;

final class DatabaseTypeProviderExtension extends CompilerExtension implements DatabaseTypeProviderInterface
{
	public function getConfigSchema(): Schema
	{
		return Expect::arrayOf(
			Expect::anyOf(
				Expect::string(),
				Expect::structure([
					'class' => Expect::string()->required(),
					'mapping_type' => Expect::string()->nullable(),
					'context' => Expect::arrayOf('mixed', 'string'),
				]),
			)->before(
				static fn (string|array $databaseType): array => is_array($databaseType)
				? $databaseType
				: ['class' => $databaseType, 'mapping_type' => null, 'context' => []]
			)->castTo(DatabaseTypeConfig::class),
			'string',
		);
	}

	public function getDatabaseTypes(): array
	{
		$databaseTypes = [];
		$config = $this->getConfig();
		assert(is_array($config));

		foreach ($config as $databaseTypeName => $databaseTypeConfig) {
			assert(is_string($databaseTypeName) && $databaseTypeConfig instanceof DatabaseTypeConfig);

			$databaseTypes[] = new DatabaseType(
				name: $databaseTypeName,
				class: $databaseTypeConfig->class,
				mappingType: $databaseTypeConfig->mappingType,
				context: $databaseTypeConfig->context,
			);
		}

		return $databaseTypes;
	}
}

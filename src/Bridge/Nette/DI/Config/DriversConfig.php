<?php

declare(strict_types=1);

namespace SixtyEightPublishers\DoctrineBridge\Bridge\Nette\DI\Config;

final class DriversConfig
{
	public string|false $chain;

	public string|false $annotation;

	public string|false $xml;

	public string|false $simplified_xml;

	public string|false $attribute;
}

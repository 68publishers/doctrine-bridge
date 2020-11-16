<?php

declare(strict_types=1);

Tester\Environment::setup();

if (!defined('TEMP_PATH')) {
	define('TEMP_PATH', __DIR__ . '/temp');
}

if (!defined('CONFIG_DIR')) {
	define('CONFIG_DIR', __DIR__ . '/config');
}

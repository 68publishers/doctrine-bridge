<?php

declare(strict_types=1);

use Tester\Environment;

if (@!include __DIR__ . '/../vendor-bin/nettrine/vendor/autoload.php') {
	echo 'Install Nette Tester using `composer install`';
	exit(1);
}

Environment::setup();

require __DIR__ . '/deprecations.php';

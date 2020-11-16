<?php

declare(strict_types=1);

if (@!include __DIR__ . '/../vendor-bin/nettrine/vendor/autoload.php') {
	echo 'Please run `composer install && composer bin all install`';
	exit(1);
}

require __DIR__ . '/bootstrap.php';

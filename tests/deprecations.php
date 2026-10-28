<?php

declare(strict_types=1);

# Deprecations are reported. E_DEPRECATED raised by the code of a third-party package (e.g. old lowest dependencies on new PHP)
# is only logged (to the file from the env variable DEPRECATIONS_LOG or to STDERR), everything else fails the test.
error_reporting(E_ALL);

$previousErrorHandler = set_error_handler(static function (int $severity, string $message, string $file = '', int $line = 0) use (&$previousErrorHandler): bool {
	if (E_DEPRECATED === $severity && (str_contains($file, '/vendor/') || str_contains($file, '/vendor-bin/'))) {
		file_put_contents(getenv('DEPRECATIONS_LOG') ?: 'php://stderr', "E_DEPRECATED: $message in $file:$line\n", FILE_APPEND);

		return true;
	}

	return null !== $previousErrorHandler && (bool) $previousErrorHandler($severity, $message, $file, $line);
});

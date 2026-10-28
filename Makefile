init:
	make stop
	make start

stop:
	docker compose stop

start:
	docker compose up -d

down:
	docker compose down

restart:
	make stop
	make start

tests.all:
	PHP=83 make tests.run
	PHP=84 make tests.run
	PHP=85 make tests.run

cs.fix:
	PHP=83 make composer.update
	docker exec 68publishers.doctrine-bridge.83 vendor/bin/php-cs-fixer fix -v

cs.check:
	PHP=83 make composer.update
	docker exec 68publishers.doctrine-bridge.83 vendor/bin/php-cs-fixer fix -v --dry-run

stan:
	PHP=83 make composer.update
	docker exec 68publishers.doctrine-bridge.83 vendor/bin/phpstan analyse

coverage:
	PHP=83 make composer.update
	docker exec 68publishers.doctrine-bridge.83 vendor/bin/tester -p phpdbg -C -s --coverage ./coverage.xml --coverage-src ./src ./tests

composer.update:
ifndef PHP
	$(error "PHP argument not set.")
endif
	@echo "========== Installing dependencies with PHP $(PHP) ==========" >&2
	docker exec 68publishers.doctrine-bridge.$(PHP) composer update --no-progress --prefer-dist --prefer-stable --optimize-autoloader --quiet
	docker exec 68publishers.doctrine-bridge.$(PHP) composer update --no-progress --prefer-dist --prefer-stable --optimize-autoloader --quiet --working-dir=vendor-bin/nettrine

composer.update-lowest:
ifndef PHP
	$(error "PHP argument not set.")
endif
	@echo "========== Installing dependencies with PHP $(PHP) (prefer lowest dependencies) ==========" >&2
	docker exec 68publishers.doctrine-bridge.$(PHP) composer update --no-progress --prefer-dist --prefer-lowest --prefer-stable --optimize-autoloader --quiet
	docker exec 68publishers.doctrine-bridge.$(PHP) composer update --no-progress --prefer-dist --prefer-lowest --prefer-stable --optimize-autoloader --quiet --working-dir=vendor-bin/nettrine

tests.run:
ifndef PHP
	$(error "PHP argument not set.")
endif
	PHP=$(PHP) make composer.update
	@echo "========== Running tests with PHP $(PHP) ==========" >&2
	docker exec 68publishers.doctrine-bridge.$(PHP) vendor/bin/tester -C -s ./tests
	PHP=$(PHP) make composer.update-lowest
	@echo "========== Running tests with PHP $(PHP) (prefer lowest dependencies) ==========" >&2
	docker exec 68publishers.doctrine-bridge.$(PHP) vendor/bin/tester -C -s ./tests

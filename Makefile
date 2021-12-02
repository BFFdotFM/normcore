# Building and testing

.PHONY: dev
dev:
	@echo "Updating dependencies..."
	@composer install
	@composer dumpautoload

.PHONY: test
test:
	@echo "Running tests..."
	@echo
	@./vendor/bin/phpunit --testdox test

.PHONY: generate
generate:
	@echo "Generated reference data..."
	@echo
	@php ./script/generate-reference-data.php

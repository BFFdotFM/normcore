# Building and testing

.PHONY: dev
dev:
	@echo "Updating dependencies..."
	@composer install
	@composer dumpautoload

.PHONY: test
test:
	@echo "Running unit tests..."
	@echo
	@./vendor/bin/phpunit --exclude-group Batch --testdox test

.PHONY: testall
testall:
	@echo "Running all tests including batch comparisons..."
	@echo
	@./vendor/bin/phpunit --testdox test

.PHONY: generate
generate:
	@echo "Generated reference data..."
	@echo
	@php ./script/generate-reference-data.php

.PHONY: benchmark
benchmark:
	@echo "Running benchmark..."
	@echo
	@php ./script/benchmark.php

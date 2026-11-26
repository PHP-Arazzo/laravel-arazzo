.PHONY: help test test-coverage format analyse phpmd verify hooks-install

# Laravel package tooling — run from packages/laravel/ directory

help: ## Show this help
	@awk 'BEGIN {FS = ":.*?## "} /^[a-zA-Z_-]+:.*?## / {printf "\033[36m%-20s\033[0m %s\n", $$1, $$2}' $(MAKEFILE_LIST)

test: ## Run Pest tests
	@vendor/bin/pest

test-coverage: ## Run Pest tests with coverage report
	@vendor/bin/pest --coverage

format: ## Format code using Laravel Pint
	@vendor/bin/pint

format-test: ## Check code style without modifying files
	@vendor/bin/pint --test

analyse: ## Run PHPStan static analysis
	@vendor/bin/phpstan analyse -c phpstan.neon.dist --memory-limit=1G

phpmd: ## Run PHP Mess Detector
	@vendor/bin/phpmd analyze src --format ansi --ruleset phpmd.xml --baseline-file phpmd.baseline.xml --threads 1

phpmd-baseline: ## Regenerate PHPMD baseline
	@tmp=$$(mktemp); \
	if vendor/bin/phpmd analyze src --ruleset phpmd.xml --generate-baseline --baseline-file "$$tmp" --threads 1; then \
		mv "$$tmp" phpmd.baseline.xml; \
	else \
		rm -f "$$tmp"; \
		echo "phpmd-baseline: generation failed; phpmd.baseline.xml left untouched"; \
		exit 1; \
	fi

verify: ## Run all gates (format-test, analyse, phpmd, tests)
	@vendor/bin/pint --test
	@vendor/bin/phpstan analyse -c phpstan.neon.dist --memory-limit=1G
	@vendor/bin/phpmd analyze src --format ansi --ruleset phpmd.xml --baseline-file phpmd.baseline.xml --threads 1
	@vendor/bin/pest

hooks-install: ## Point git at .githooks/ (one-time per clone)
	@git config core.hooksPath .githooks
	@echo "hooks installed: pre-commit will run pint --test + phpstan + pest before committing"

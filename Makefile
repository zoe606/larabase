.PHONY: dev test test-coverage lint analyse format fresh install setup help check build crud

help: ## Show available commands
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-20s\033[0m %s\n", $$1, $$2}'

dev: ## Start dev server (app, queue, logs, vite)
	composer run dev

install: ## Install all dependencies
	composer install
	pnpm install

setup: ## Fresh install + migrate + seed
	composer install
	pnpm install
	php artisan migrate:fresh --seed

test: ## Run tests in parallel
	./vendor/bin/pest --parallel

test-coverage: ## Run tests with parallel coverage (min 85%)
	./vendor/bin/pest --parallel --coverage-html=coverage/ --min=85

analyse: ## Run PHPStan static analysis
	./vendor/bin/phpstan analyse --memory-limit=2G

lint: ## Lint PHP + JS (fix mode)
	./vendor/bin/pint --dirty
	pnpm run lint

format: ## Format PHP + JS
	./vendor/bin/pint
	pnpm run format

check: ## Run all quality checks (lint, analyse, test)
	./vendor/bin/pint --dirty
	pnpm run lint:check
	pnpm run format:check
	./vendor/bin/phpstan analyse --memory-limit=2G
	./vendor/bin/pest --parallel

fresh: ## Reset database with seeds
	php artisan migrate:fresh --seed

build: ## Build frontend assets
	pnpm run build

crud: ## Generate CRUD (usage: make crud name=Post)
	php artisan make:crud $(name)

# xboard/php — common developer tasks
# Usage: make <target>   (run `make help` for the list)
#
# Examples:
#   make run-example create-customer-post

SHELL := /bin/bash
.DEFAULT_GOAL := help

COMPOSER ?= composer
PHP ?= php

EXAMPLE_NAME :=
ifneq ($(filter run-example,$(firstword $(MAKECMDGOALS))),)
  EXAMPLE_NAME := $(word 2,$(MAKECMDGOALS))
  ifneq ($(EXAMPLE_NAME),)
    $(eval $(EXAMPLE_NAME):;@:)
  endif
endif

.PHONY: help install test lint lint-fix analyse validate env-check require-example run-example smoke

help: ## Show this help
	@awk 'BEGIN {FS = ":.*##"; printf "\nTargets:\n"} \
		/^[a-zA-Z0-9_-]+:.*?##/ { printf "  \033[36m%-16s\033[0m %s\n", $$1, $$2 }' $(MAKEFILE_LIST)
	@printf "\nExamples (loads .env; example name is required):\n"
	@printf "  make run-example list-customers\n"
	@printf "  make run-example list-posts\n"
	@printf "  make run-example create-customer-post\n"
	@printf "  make run-example compose-create\n"
	@printf "  make run-example compose-update   # needs XBOARD_POST_ID\n"
	@printf "  make run-example list-notes       # needs XBOARD_POST_ID\n"
	@printf "  XBOARD_POST_ID=<id> make run-example compose-update\n"
	@printf "  make smoke                        # all use cases; threads compose-create id into update/notes\n\n"

install: ## Install dependencies (Composer)
	$(COMPOSER) install

test: ## Run unit tests
	$(COMPOSER) test

lint: ## PHP-CS-Fixer dry-run
	$(COMPOSER) lint

lint-fix: ## PHP-CS-Fixer write
	$(COMPOSER) lint:fix

analyse: ## PHPStan
	$(COMPOSER) analyse

validate: ## Full CI check (test, phpstan, php-cs-fixer)
	$(COMPOSER) validate-ci

env-check: ## Ensure .env exists with required vars
	@test -f .env || (echo "Missing .env — copy .env.example and set XBOARD_API_KEY / XBOARD_BASE_URL"; exit 1)
	@grep -q '^XBOARD_API_KEY=.\+' .env || (echo "XBOARD_API_KEY is empty in .env"; exit 1)
	@grep -q '^XBOARD_BASE_URL=.\+' .env || (echo "XBOARD_BASE_URL is empty in .env"; exit 1)

require-example:
	@test -n "$(EXAMPLE_NAME)" || (echo "Usage: make run-example <name>   e.g. make run-example create-customer-post"; exit 1)

run-example: require-example env-check ## Run an example: make run-example <name>
	@script="examples/$(EXAMPLE_NAME).php"; \
	test -f "$$script" || (echo "Missing $$script"; exit 1); \
	echo "→ $$script"; \
	set -a && source .env && set +a && $(PHP) "$$script"

smoke: env-check ## Run every partner example against .env (creates posts)
	@set -euo pipefail; \
	set -a && source .env && set +a; \
	echo "→ examples/list-customers.php"; \
	$(PHP) examples/list-customers.php; \
	echo "→ examples/create-customer-post.php"; \
	$(PHP) examples/create-customer-post.php; \
	echo "→ examples/compose-create.php"; \
	post_id="$$($(PHP) examples/compose-create.php)"; \
	echo "$$post_id"; \
	test -n "$$post_id" || (echo "compose-create printed no post id"; exit 1); \
	echo "→ examples/list-posts.php"; \
	$(PHP) examples/list-posts.php; \
	echo "→ examples/compose-update.php"; \
	XBOARD_POST_ID="$$post_id" $(PHP) examples/compose-update.php; \
	echo "→ examples/list-notes.php"; \
	XBOARD_POST_ID="$$post_id" $(PHP) examples/list-notes.php

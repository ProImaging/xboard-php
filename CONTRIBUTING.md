# Contributing to xboard/php

Thank you for improving the xBoard PHP SDK.

## Development setup

```bash
git clone https://github.com/ProImaging/xboard-php.git
cd xboard-php
make install
```

Requires PHP 8.2+ and Composer.

## Commands

| Command | Purpose |
| ------- | ------- |
| `make help` | List Make targets |
| `make install` | `composer install` |
| `make validate` | Full CI-equivalent check |
| `make test` / `make lint` / `make analyse` | Common tasks |
| `make run-example create-customer-post` | Run `examples/create-customer-post.php` |
| `make run-example compose-create` | Run `examples/compose-create.php` |
| `make run-example compose-update` | Run `examples/compose-update.php` (`XBOARD_POST_ID`) |
| `make smoke` | Run every partner example against `.env` |
| `composer validate-ci` | Same as `make validate` |

## Code guidelines

- PHP 8.2+; `declare(strict_types=1)`
- Collocate partner types under `src/Customers/`
- Use `HttpClient` internally; keep the public surface limited to the documented resources
- Tests for behavior changes; mock HTTP with Guzzle `MockHandler`

## Pull requests

1. Branch from `main`
2. Ensure `make validate` passes
3. Fill out the PR template

## Commits

Use clear, imperative commit messages. Conventional Commits are welcome (`feat:`, `fix:`, `docs:`, `chore:`).

## Releasing

See [docs/publishing.md](docs/publishing.md).

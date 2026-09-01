# Publishing `xboard/php`

PHP packages are distributed on [Packagist](https://packagist.org). Composer installs from **git tags**; there is no npm-style tarball upload.

## One-time Packagist registration

1. Create or sign in at [packagist.org](https://packagist.org).
2. Submit the GitHub repository: `https://github.com/ProImaging/xboard-php`.
3. Enable the Packagist GitHub app (or a repository webhook) so new tags sync automatically.
4. In this repository, add GitHub Actions secrets:
   - `PACKAGIST_USERNAME`
   - `PACKAGIST_TOKEN` (Packagist API token)

Claim the `xboard` vendor namespace promptly so `xboard/php` is not taken.

## Install (consumers)

```bash
composer require xboard/php
```

Until the first tag is published, consumers can require from VCS:

```json
{
  "repositories": [
    {
      "type": "vcs",
      "url": "https://github.com/ProImaging/xboard-php"
    }
  ],
  "require": {
    "xboard/php": "dev-main"
  }
}
```

## Release a version

1. Bump `XBoard\Version::VERSION` in `src/Version.php`.
2. Update `CHANGELOG.md`.
3. Merge to `main`.
4. Create an annotated tag and push it:

```bash
git tag -a v0.1.0 -m "v0.1.0"
git push origin v0.1.0
```

Pushing a `v*` tag runs `.github/workflows/release.yml`:

- Re-runs PHPUnit, PHPStan, and PHP-CS-Fixer
- Creates a GitHub Release from the tag
- `POST`s Packagist `update-package` so the new tag is indexed (skipped if secrets are unset)

Packagist then serves `composer require xboard/php:^0.1`.

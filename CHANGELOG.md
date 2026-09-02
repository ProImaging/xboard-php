# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [0.1.0] - Unreleased

### Added

- Partner customer posts: `$client->customers->posts()->create()` / `compose()->create()` (auto-ensure board)
- `$post->compose()->update()` to append notes/files and replace the title
- Composer chain names match `Post`: `setTitle()`, `addNote()`, `addFile()`
- `$posts->list()` returns `Post` objects (`$listed[0]->notes()->list()`)
- `$post->notes()->list()` includes notes and files
- Optional `$client->customers->board($externalCustomerId, $boardType)` ensure-only path
- `XBoard\BoardType` (`Shared` / `Private`) for partner `boardType`
- `$client->customers->list()` — CRM customer list (`POST /people/account/customers`, `customers:read`)

### Removed

- Public generic boards/posts/notes/files CRUD
- Note/file delete
- `save()` — use `create()` vs `update()` only

[0.1.0]: https://github.com/ProImaging/xboard-php/releases/tag/v0.1.0

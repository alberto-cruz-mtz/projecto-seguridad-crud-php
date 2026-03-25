# AGENTS.md

Operating guide for coding agents working in this repository.

## Repository Summary

- Project: PHP CRUD/security module with MVC-style architecture.
- Entrypoint: `public/index.php`.
- Dependency manager: Composer.
- Runtime packages:
  - `alberto-cruz-mtz/vanilla-router`
  - `phpmailer/phpmailer`
- Core code currently present in `src/Core/`.
- Additional project guidance lives in:
  - `docs/overview.md`
  - `docs/guia_directrices_proyecto.md`
  - `docs/reglas_estandar_css.md`

## Cursor / Copilot Rules

Rule files checked at repo root:

- `.cursor/rules/`: not found
- `.cursorrules`: not found
- `.github/copilot-instructions.md`: not found

If any of these files are added later, treat them as highest-priority instructions.

## Build, Run, Lint, and Test Commands

## Setup / Build

```bash
composer install
composer dump-autoload -o
composer validate
```

Notes:
- No JS/Node build pipeline is configured.
- "Build" here is dependency install and autoload generation.

## Run Locally

```bash
php -S 127.0.0.1:8000 -t public
```

Visit `http://127.0.0.1:8000`.

## Lint / Static Checks (Current State)

No root linter (PHPStan/Psalm/PHPCS) is configured yet.

Use syntax lint for touched PHP files:

```bash
php -l public/index.php
php -l src/Core/Database.php
php -l src/Core/UUID.php
```

For new files, run `php -l <path>` explicitly.

## Tests (Current State)

- No root `tests/` directory exists.
- No root `phpunit.xml` exists.
- No first-party test suite is currently configured.

## Test Commands to Use Once PHPUnit Is Added

```bash
# run entire test suite
vendor/bin/phpunit

# run a single test file
vendor/bin/phpunit tests/Feature/Auth/LoginTest.php

# run a single test method (preferred fast loop)
vendor/bin/phpunit --filter testLoginWithValidCredentials tests/Feature/Auth/LoginTest.php

# run with explicit config file
vendor/bin/phpunit -c phpunit.xml
vendor/bin/phpunit -c phpunit.xml --filter testLoginWithValidCredentials tests/Feature/Auth/LoginTest.php
```

Single-test recommendation:
- Prefer `--filter <methodName>` with a specific file path.

## Optional Tooling to Introduce Later

```bash
composer require --dev phpunit/phpunit phpstan/phpstan friendsofphp/php-cs-fixer
vendor/bin/phpstan analyse src
vendor/bin/php-cs-fixer fix --dry-run --diff
```

## Code Style Guidelines (PHP)

## Baseline Standards

- Follow PSR-12 formatting.
- Follow PSR-4 autoloading from `composer.json`.
- Use 4 spaces for indentation; do not use tabs.
- Use UTF-8 and Unix line endings.
- Keep one class/interface/trait per file.

## File Preamble Order

Use this order in PHP source files:

1. `<?php`
2. `declare(strict_types=1);`
3. `namespace ...;`
4. `use ...;`
5. class/interface/trait declaration

Note: Existing files are not fully consistent yet; new and edited files should move toward this.

## Imports

- Place imports after `namespace`.
- Keep imports clean and stable (alphabetical preferred).
- Remove unused imports.
- Avoid inline fully qualified class names when imports improve readability.

## Types and Signatures

- Type all properties, method parameters, and return values.
- Prefer precise types over `mixed`.
- Use nullable types only when a value can legitimately be absent.
- Prefer value objects for critical identifiers (e.g., UUID).
- Use PHPDoc only when native types are insufficient (array shapes, generics, intent).

## Naming Conventions

- Classes/interfaces/traits: `PascalCase`.
- Methods/properties/variables: `camelCase`.
- Constants: `UPPER_SNAKE_CASE`.
- Boolean predicates: `is*`, `has*`, `can*`.
- Use domain names that communicate purpose (`AuthService`, `UserRepository`).

## Formatting and Structure

- Prefer short, focused methods.
- Prefer early returns over deep nested conditionals.
- Extract repeated literals to constants.
- Use trailing commas in multiline arrays/argument lists.
- Avoid unnecessary comments; make code self-explanatory with good names.

## Error Handling and Security

- Throw specific exceptions (`InvalidArgumentException`, `RuntimeException`, etc.).
- Wrap low-level exceptions with contextual messages.
- Never expose secrets in logs or exception messages.
- Validate and sanitize all external input at boundaries.
- Never concatenate untrusted input into SQL.
- Use prepared statements via PDO for all DB queries.
- Hash passwords with `password_hash()` and verify with `password_verify()`.
- Enforce authorization checks on protected endpoints/actions.
- Escape rendered output to reduce XSS risk.

## Architecture Guidelines

- Keep controllers thin: translate HTTP and orchestrate only.
- Keep business rules in services.
- Keep persistence and SQL in repositories.
- Keep entities/value objects independent from persistence concerns.
- Keep frontend views/static assets separate from backend logic.

Namespace consistency warning:
- `composer.json` maps `Tito\\CrudUsers\\` -> `src/`.
- Existing code uses `namespace Tito\App\Core;`.
- Choose and enforce one namespace strategy before expanding modules.

## CSS / Frontend Rules

From `docs/reglas_estandar_css.md`:

- Respect layer order: `theme`, `base`, `components`, `pages`.
- Define reusable tokens in `public/assets/css/global/theme.css`.
- Prefer semantic class selectors over IDs for styling.
- Avoid `!important` except documented exceptional cases.
- Use mobile-first responsive behavior.
- Promote repeated page styles into reusable components.

## Agent Execution Checklist

1. Read relevant docs and nearby code before editing.
2. Make the smallest safe change that satisfies the request.
3. Run available checks for changed files.
4. Report what changed, why, and how it was validated.
5. If tests are missing, say so explicitly and suggest next verification steps.

Update this file when tooling, architecture, or repository rules change.

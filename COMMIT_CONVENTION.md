# Commit Convention — HEAL Website

We follow [Conventional Commits](https://www.conventionalcommits.org/).
It is **enforced automatically**: a Git hook (husky + commitlint) rejects commits that break these rules.

## Format

```
<type>(<optional scope>): <short summary in lowercase>
```

Examples:

- `feat(results): show status of public deliverables`
- `fix(layout): keep footer funding strip visible on mobile`
- `docs: add user stories to REQUIREMENTS.md`

## Types

- `feat` — new user-facing feature, page or component
- `fix` — bug fix
- `docs` — documentation only (REQUIREMENTS.md, README, guides)
- `style` — formatting only, no logic change
- `refactor` — restructuring code without changing behaviour
- `test` — adding or fixing tests
- `chore` — tooling, dependencies, configuration
- `perf` — performance improvement
- `build` — build or deploy configuration
- `ci` — automated checks on GitHub
- `revert` — undo a previous commit

## Scopes (optional)

`config`, `deps`, `content`, `schema`, `i18n`, `cms`, `ui`, `layout`, `a11y`, `seo`, `security`, `contact`, `results`, `ci`, `release`

## Before each commit

The pre-commit hook runs `npm run lint` and `npm run format:check`.
If it fails, run `npm run lint:fix` and `npm run format`, then commit again.

## Setup for new team members

Run `npm install` once after cloning. The hooks are installed automatically.

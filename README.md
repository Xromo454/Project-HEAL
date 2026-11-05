# HEAL Website

Official website of the **HEAL** project — COM-B & Exposome Partnership for Health Engagement (RIF POST-DOC/0925/ROandOther/0027).

Built with [Astro](https://astro.build) + TypeScript as a static site.

## Getting started

Requires Node.js 22+.

```sh
npm install      # installs dependencies and git hooks
npm run dev      # dev server at http://localhost:4321
```

## Scripts

| Command            | Action                                 |
| :----------------- | :------------------------------------- |
| `npm run dev`      | Start the local dev server             |
| `npm run build`    | Build the production site to `./dist/` |
| `npm run preview`  | Preview the production build           |
| `npm run check`    | Typecheck, lint and format check       |
| `npm run lint:fix` | Fix lint issues                        |
| `npm run format`   | Format all files with Prettier         |

## Workflow

- `main` — released code; `develop` — integration branch.
- Create a feature branch from `develop`, open a pull request back into `develop`.
- Commits follow [Conventional Commits](COMMIT_CONVENTION.md) and are checked automatically.

## Documentation

- [REQUIREMENTS.md](REQUIREMENTS.md) — user stories (MoSCoW) and non-functional requirements
- [COMMIT_CONVENTION.md](COMMIT_CONVENTION.md) — commit message rules

# HEAL Website — Architecture (draft v1)

```mermaid
flowchart LR
  subgraph Authors
    PI["PI (Product Owner)"]
    DEV["Developers"]
  end

  subgraph Repo["GitHub repository"]
    CONTENT["src/content<br/>JSON + Markdown data"]
    SCHEMA["src/content.config.ts<br/>schema + rules"]
    CODE["src/pages, components"]
  end

  CMS["Decap CMS<br/>/admin"]
  BUILD["Astro build<br/>validates data, outputs static HTML"]
  HOST["Static hosting<br/>CUT server or static host (TBD)"]
  FORM["Contact form handler<br/>serverless function or EU form service (TBD)"]
  MAIL["Institutional mailbox"]
  VISITOR["Visitor"]

  PI -->|edits news, deliverable PDFs| CMS
  CMS -->|commits files| CONTENT
  DEV -->|pull request into develop| CODE
  CONTENT --> BUILD
  SCHEMA --> BUILD
  CODE --> BUILD
  BUILD -->|deploy| HOST
  VISITOR -->|HTTPS| HOST
  VISITOR -->|sends message| FORM
  FORM -->|email, nothing stored| MAIL
```

## Key decisions

- **Static site (Astro + TypeScript).** No server or database to maintain after handover.
- **Content in Git.** Work packages, deliverables, milestones, news and team are versioned JSON/Markdown files with a schema; invalid data fails the build.
- **Decap CMS.** The PI edits content in the browser; every change is a Git commit and can be reverted.
- **No personal data stored.** The contact form forwards messages to the institutional email; no tracking cookies.

## Open items

- Hosting: CUT server or static hosting (PI + CUT IT).
- CMS login in production: GitHub OAuth, depends on hosting (Day 7).
- Contact form provider: serverless function or EU form service (Day 8).

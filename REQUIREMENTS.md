# HEAL Website — Project Requirements

## 1. Project Purpose

Official static website for the HEAL project (COM-B & Exposome Partnership for Health Engagement, RIF POST-DOC/0925/ROandOther/0027). The site supports public dissemination (WP2), recruitment of 20 Limassol adults (WP4), and publication of project deliverables.

## 2. Roles

- **Visitor** — member of the public, researcher, journalist or funder reading about the project.
- **Prospective participant** — Limassol adult interested in joining the pilot.
- **PI** — Principal Investigator; edits content without writing code.
- **Developer** — maintains the codebase.
- **Funder / reviewer** — RIF officer checking compliance and deliverables.

## 3. User Stories (MoSCoW)

Each story has an ID used in GitHub issues and commits.

### Must Have (v1.0 Launch)

| ID    | User story                                                                                                                                                                                                            | Acceptance criteria                                                                                                     |
| ----- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ----------------------------------------------------------------------------------------------------------------------- |
| US-01 | As a **visitor**, I want a clear menu (Home, Project — Overview / How it works / Limassol living lab, Work plan, Consortium & team, Results, News & events, Participate, Contact) so that I find information quickly. | All sections reachable from every page, on desktop and mobile; active page indicated.                                   |
| US-02 | As a **funder**, I want the official funding strip in the footer so that the project meets RIF publicity rules.                                                                                                       | EU / Republic of Cyprus / RIF logos, RESTART 2016–2020 logo and number POST-DOC/0925/ROandOther/0027 on every page.     |
| US-03 | As a **visitor on a phone**, I want the site to adapt to my screen so that I can read it comfortably.                                                                                                                 | No horizontal scroll or broken layout at 360px, 768px and 1280px.                                                       |
| US-04 | As a **funder / researcher**, I want a Results page listing public deliverables (D4, D10, D11, D12, D14) with their status so that I can track progress.                                                              | Only public deliverables shown; each shows status (planned / in progress / published) and a PDF link when published.    |
| US-05 | As a **visitor**, I want a contact form that reaches the project team so that I can ask questions.                                                                                                                    | Message delivered to the institutional email; nothing stored on the site; consent notice shown; spam protection active. |
| US-06 | As the **PI**, I want all content to state the correct study facts so that the site never contradicts the protocol.                                                                                                   | 6-month intervention (M10–M15), feasibility pilot RCT N=20, Limassol Municipality named as End User / Stakeholder only. |
| US-07 | As a **visitor**, I want to read a Privacy Policy and Accessibility Statement so that I know how my data and access are handled.                                                                                      | Both pages published and linked from the footer.                                                                        |

### Should Have (Sprint Goal)

| ID    | User story                                                                                                                         | Acceptance criteria                                                                            |
| ----- | ---------------------------------------------------------------------------------------------------------------------------------- | ---------------------------------------------------------------------------------------------- |
| US-08 | As the **PI**, I want to publish news and upload deliverable PDFs through a CMS (Decap CMS) so that I do not need a developer.     | PI logs in, edits, previews and publishes; change goes live after the automatic build.         |
| US-09 | As the **PI**, I want deliverable status to update automatically from the project calendar so that the Results page stays current. | Status computed at build time from due month and project start date; manual override possible. |
| US-10 | As a **follower**, I want an RSS feed of news and events so that I get updates automatically.                                      | Valid RSS at `/rss.xml`, linked in the page `<head>`.                                          |
| US-11 | As a **visitor sharing a link**, I want a rich preview card and search-engine-friendly data so that the project is easy to find.   | Open Graph / Twitter tags on all pages; ResearchProject JSON-LD on Home; sitemap generated.    |

### Could Have (If time permits)

| ID    | User story                                                                                                                     | Acceptance criteria                                                        |
| ----- | ------------------------------------------------------------------------------------------------------------------------------ | -------------------------------------------------------------------------- |
| US-12 | As a **visitor**, I want subtle animations so that the site feels polished, but not if I prefer reduced motion.                | All animation disabled under `prefers-reduced-motion`.                     |
| US-13 | As a **prospective participant**, I want an interactive map of the Limassol green route so that I see where activities happen. | Only after written approvals; keyboard-accessible with a text alternative. |

### Won't Have Right Now (Deferred to v1.1)

| ID    | User story                                                                  | Reason                                                            |
| ----- | --------------------------------------------------------------------------- | ----------------------------------------------------------------- |
| US-14 | As a **Greek-speaking visitor**, I want the site in Greek (EL).             | Infrastructure prepared now (see NFR-I18N); translations in v1.1. |
| US-15 | As a **prospective participant**, I want to submit an interest form online. | Requires Ethics Committee & DPO approval.                         |

## 4. Non-Functional Requirements

### Performance (NFR-PERF)

- Mobile Lighthouse score ≥ 90 in Performance, Accessibility, Best Practices and SEO.
- Static generation; no client-side JavaScript framework unless justified; images optimised (Astro `<Image>`, modern formats, lazy loading).
- Page weight target < 1 MB on Home; LCP < 2.5 s, CLS < 0.1 on a mid-range mobile.

### Accessibility (NFR-A11Y)

- WCAG 2.1 AA compliance: zero axe errors on every page.
- Full keyboard navigation with visible focus and a "skip to content" link.
- Screen reader compatible: semantic landmarks, correct heading order, alt text, labelled form fields.
- Colour contrast ≥ 4.5:1 for text (3:1 for large text and UI components).

### Privacy & GDPR (NFR-GDPR)

- No tracking or third-party cookies; no cookie banner needed. Any analytics must be cookieless and privacy-friendly.
- No database of personal data on the site; contact form messages go straight to the institutional mailbox.
- Published Privacy Policy (controller, purpose, legal basis, retention, rights, DPO contact) and Accessibility Statement.
- No third-party embeds (fonts, maps, video) that send visitor data without consent; fonts self-hosted.

### Security (NFR-SEC)

- HTTPS only, with HSTS.
- Security headers: Content-Security-Policy, X-Content-Type-Options, Referrer-Policy, Permissions-Policy, frame-ancestors.
- Contact form: server-side validation, rate limiting and spam protection (honeypot or privacy-friendly CAPTCHA).
- CMS access restricted to authorised accounts with two-factor authentication; no secrets in the repository.
- Dependencies checked (`npm audit`, Dependabot) and updated regularly; protected `main` branch with reviewed pull requests.

### Internationalisation (NFR-I18N)

- English (EN) at launch; routing and content structure ready for Greek (EL) without rework (Astro i18n routing, `/el/` prefix).
- All UI strings kept in translation files, not hard-coded in components.
- Correct `lang` attribute on every page; dates and numbers formatted per locale; `hreflang` tags once EL exists.

### Content Editing by the PI (NFR-EDIT)

- The PI can add/edit news, events, team members and deliverables without coding, via Decap CMS or Markdown/JSON in GitHub.
- Content lives in versioned Astro content collections with a schema; invalid content fails the build with a clear message.
- Every change is tracked in Git and can be reverted; a short PI editing guide is provided.

### Architecture & Maintainability (NFR-ARCH)

- Static site generation using Astro + TypeScript (strict), content collections in Markdown/JSON.
- Code style enforced by ESLint, Prettier and Conventional Commits (see `COMMIT_CONVENTION.md`).
- Branching: `develop` for integration, `main` for releases; changes via pull requests.

## 5. Definition of Done

A story is done when it meets its acceptance criteria, passes `npm run check` and `npm run build`, has no axe errors, works at 360/768/1280px, and is merged into `develop` via a reviewed pull request.

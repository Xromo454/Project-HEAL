# HEAL Website — Project Requirements

## 1. Project Purpose

Official static website for the HEAL project (COM-B & Exposome Partnership for Health Engagement, RIF POST-DOC/0925/ROandOther/0027). The site supports public dissemination (WP2), recruitment of 20 Limassol adults (WP4), and publication of project deliverables.

## 2. MoSCoW Prioritisation

### Must Have (v1.0 Launch)

- 7 core menu sections: Home, Project (Overview, How it works, Limassol living lab), Work plan, Consortium & team, Results, News & events, Participate, Contact.
- Footer with mandatory official funding strip: European Union / Republic of Cyprus / RIF, RESTART 2016–2020 logo, project number POST-DOC/0925/ROandOther/0027.
- Fully responsive design (tested at 360px, 768px, 1280px).
- Results page listing only public deliverables (D4, D10, D11, D12, D14) with dynamic status.
- Contact form delivering messages directly to institutional email (no personal data stored on the site).
- Strict adherence to Content Rules: 6-month intervention (M10–M15), feasibility pilot RCT N=20, Limassol Municipality as End User / Stakeholder (not Partner).

### Should Have (Sprint Goal)

- Git-based CMS (Decap CMS) allowing the PI to publish news and upload public deliverable PDFs without coding.
- Automated deliverable status logic based on project calendar.
- RSS feed for news and events.
- Structured SEO data (ResearchProject schema) and Open Graph preview cards.

### Could Have (If time permits)

- Micro-animations respecting `prefers-reduced-motion`.
- Extended interactive map of the Limassol green route (pending written approvals).

### Won't Have Right Now (Deferred to v1.1)

- Greek language version (EL) — infrastructure prepared, translations in v1.1.
- Participant interest intake form (requires Ethics Committee & DPO approval).

## 3. Non-Functional Requirements

- **Performance:** Mobile Lighthouse score >= 90 across Performance, Accessibility, Best Practices, and SEO.
- **Accessibility:** Compliance with WCAG 2.1 AA (zero axe errors, full keyboard navigation, screen reader compatibility).
- **Privacy & GDPR:** No tracking cookies, no local database of personal information, published Privacy Policy and Accessibility Statement.
- **Architecture:** Static site generation using Astro + TypeScript, versioned content collections in Markdown/JSON.

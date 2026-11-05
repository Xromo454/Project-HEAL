#!/usr/bin/env bash
# Creates MoSCoW labels and one issue per user story from REQUIREMENTS.md.
# Requires GitHub CLI: install, then run `gh auth login` once.
# Usage: bash scripts/create-issues.sh
set -euo pipefail

REPO="Xromo454/Project-HEAL"

label() { gh label create "$1" --color "$2" --description "$3" --repo "$REPO" --force; }
label "must-have" "b60205" "MoSCoW: Must have (v1.0)"
label "should-have" "d93f0b" "MoSCoW: Should have"
label "could-have" "fbca04" "MoSCoW: Could have"
label "wont-have" "c5def5" "MoSCoW: Won't have now (v1.1)"
label "user-story" "0e8a16" "User story from REQUIREMENTS.md"
label "nfr" "5319e7" "Non-functional requirement"

issue() { gh issue create --repo "$REPO" --title "$1" --label "$2" --body "$3"; }

R="See REQUIREMENTS.md for acceptance criteria."
issue "US-01 Main navigation with all core sections" "user-story,must-have" "$R"
issue "US-02 Footer with official funding strip" "user-story,must-have" "$R"
issue "US-03 Responsive layout (360/768/1280px)" "user-story,must-have" "$R"
issue "US-04 Results page with public deliverables and status" "user-story,must-have" "$R"
issue "US-05 Contact form to institutional email" "user-story,must-have" "$R"
issue "US-06 Content matches study facts" "user-story,must-have" "$R"
issue "US-07 Privacy Policy and Accessibility Statement" "user-story,must-have" "$R"
issue "US-08 Decap CMS for PI editing" "user-story,should-have" "$R"
issue "US-09 Automatic deliverable status from calendar" "user-story,should-have" "$R"
issue "US-10 RSS feed for news and events" "user-story,should-have" "$R"
issue "US-11 SEO structured data and Open Graph cards" "user-story,should-have" "$R"
issue "US-12 Micro-animations respecting reduced motion" "user-story,could-have" "$R"
issue "US-13 Interactive Limassol green route map" "user-story,could-have" "$R"
issue "US-14 Greek language version" "user-story,wont-have" "$R"
issue "US-15 Participant interest intake form" "user-story,wont-have" "$R"
issue "NFR-PERF Lighthouse mobile >= 90" "nfr,must-have" "$R"
issue "NFR-A11Y WCAG 2.1 AA compliance" "nfr,must-have" "$R"
issue "NFR-GDPR No tracking, privacy by design" "nfr,must-have" "$R"
issue "NFR-SEC Security headers, HTTPS, form protection" "nfr,must-have" "$R"
issue "NFR-I18N i18n-ready structure (EN now, EL later)" "nfr,must-have" "$R"
issue "NFR-EDIT Content editable by the PI" "nfr,should-have" "$R"

echo "Done. Add these issues to a GitHub Project board (Projects tab) to track them."

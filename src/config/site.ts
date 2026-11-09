export const SITE = {
  name: 'HEAL',
  fullName: 'COM-B & Exposome Partnership for Health Engagement',
  description:
    'HEAL is a research project in Limassol testing personalised, context-aware nudges to support active mobility and health.',
  projectNumber: 'POST-DOC/0925/ROandOther/0027',
  contactEmail: 'TODO@cut.ac.cy',
};

/** Project start date (YYYY-MM-DD). Unknown yet: while null, every deliverable shows "Planned". */
export const PROJECT_START_DATE: string | null = null;

export interface NavItem {
  label: string;
  href: string;
  children?: NavItem[];
}

export const NAV: NavItem[] = [
  { label: 'Home', href: '/' },
  {
    label: 'Project',
    href: '/project/overview',
    children: [
      { label: 'Overview & aims', href: '/project/overview' },
      { label: 'How it works', href: '/project/how-it-works' },
      { label: 'Limassol living lab', href: '/project/living-lab' },
    ],
  },
  { label: 'Work plan', href: '/work-plan' },
  { label: 'Consortium & team', href: '/consortium' },
  { label: 'Results', href: '/results' },
  { label: 'News & events', href: '/news' },
  { label: 'Participate', href: '/participate' },
  { label: 'Contact', href: '/contact' },
];

export const FOOTER_LINKS: NavItem[] = [
  { label: 'Privacy policy', href: '/privacy' },
  { label: 'Accessibility statement', href: '/accessibility' },
  { label: 'Legal notice', href: '/legal' },
];

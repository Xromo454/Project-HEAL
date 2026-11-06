import { defineCollection, reference } from 'astro:content';
import { file, glob } from 'astro/loaders';
import { z } from 'astro/zod';

/** Public deliverables per table B4.4. Every other deliverable is confidential. */
const PUBLIC_DELIVERABLES = ['D4', 'D10', 'D11', 'D12', 'D14'];

const month = z.number().int().min(1).max(24);

/** Deliverables D1–D14 (B4.4). One JSON array: src/content/deliverables.json */
const deliverables = defineCollection({
  loader: file('src/content/deliverables.json'),
  schema: z
    .object({
      id: z.string().regex(/^D(1[0-4]|[1-9])$/, 'Deliverable ID must be D1 to D14'),
      title: z.string(),
      workPackage: reference('workPackages'),
      dueMonth: month,
      public: z.boolean(),
      // Manual override; otherwise status is computed from dueMonth + project start date (US-09)
      status: z.enum(['planned', 'in-progress', 'published']).optional(),
      file: z.string().optional(),
    })
    .refine((d) => d.public === PUBLIC_DELIVERABLES.includes(d.id), {
      message: `Only ${PUBLIC_DELIVERABLES.join(', ')} may be public (B4.4).`,
      path: ['public'],
    })
    .refine((d) => d.public || !d.file, {
      message: 'Confidential deliverables must not have an attached file.',
      path: ['file'],
    }),
});

/** Work packages WP1–WP5 (B4.2–B4.5). One JSON array: src/content/workPackages.json */
const workPackages = defineCollection({
  loader: file('src/content/workPackages.json'),
  schema: z
    .object({
      id: z.enum(['WP1', 'WP2', 'WP3', 'WP4', 'WP5']),
      title: z.string(),
      lead: reference('partners'),
      startMonth: month,
      endMonth: month,
      objective: z.string(),
      tasks: z.array(
        z.object({
          id: z.string().regex(/^T[1-5]\.\d+$/, 'Task ID must look like T2.1'),
          title: z.string(),
          startMonth: month,
          endMonth: month,
        }),
      ),
    })
    .refine((wp) => wp.endMonth >= wp.startMonth, {
      message: 'endMonth must not be before startMonth',
      path: ['endMonth'],
    }),
});

/** Milestones M1.1–M5.3. One JSON array: src/content/milestones.json */
const milestones = defineCollection({
  loader: file('src/content/milestones.json'),
  schema: z.object({
    id: z.string().regex(/^M[1-5]\.\d+$/, 'Milestone ID must look like M1.1'),
    title: z.string(),
    workPackage: reference('workPackages'),
    month,
    meansOfVerification: z.string(),
  }),
});

/** Consortium (CUT = Host, NextBike = PA1) and stakeholders (Limassol = end user). One file per partner. */
const partners = defineCollection({
  loader: glob({ pattern: '**/*.json', base: './src/content/partners' }),
  schema: ({ image }) =>
    z.object({
      name: z.string(),
      shortName: z.string(),
      role: z.enum(['Host Organisation', 'Partner', 'End user / stakeholder']),
      description: z.string(),
      // Only with written permission (content rules)
      logo: image().optional(),
      website: z.url(),
    }),
});

/** Team profiles. One file per person. */
const team = defineCollection({
  loader: glob({ pattern: '**/*.json', base: './src/content/team' }),
  schema: ({ image }) =>
    z.object({
      name: z.string(),
      role: z.string(),
      organisation: reference('partners'),
      bio: z.string(),
      photo: image().optional(),
      photoAlt: z.string().optional(),
      orcid: z
        .string()
        .regex(/^\d{4}-\d{4}-\d{4}-\d{3}[\dX]$/, 'ORCID must look like 0000-0002-1825-0097')
        .optional(),
      order: z.number().int().default(0),
    }),
});

/** News posts (Markdown with a body). Locale folder first: news/en/..., news/el/... later. */
const news = defineCollection({
  loader: glob({ pattern: '**/*.md', base: './src/content/news' }),
  schema: ({ image }) =>
    z.object({
      title: z.string(),
      publishDate: z.coerce.date(),
      summary: z.string(),
      image: image().optional(),
      imageAlt: z.string().optional(),
      tags: z.array(z.string()).default([]),
      draft: z.boolean().default(false),
    }),
});

/** Events: kick-off, Public Consultation Event (T2.2), conferences. */
const events = defineCollection({
  loader: glob({ pattern: '**/*.{md,json}', base: './src/content/events' }),
  schema: z.object({
    title: z.string(),
    date: z.coerce.date(),
    endDate: z.coerce.date().optional(),
    location: z.string(),
    type: z.enum(['Meeting', 'Consultation', 'Conference', 'Workshop']),
    link: z.url().optional(),
    draft: z.boolean().default(false),
  }),
});

/** Publications (D11, T2.1). */
const publications = defineCollection({
  loader: glob({ pattern: '**/*.json', base: './src/content/publications' }),
  schema: z.object({
    title: z.string(),
    authors: z.array(z.string()).min(1),
    journal: z.string().optional(),
    year: z.number().int().min(2025),
    doi: z
      .string()
      .regex(/^10\.\d{4,9}\/\S+$/, 'DOI must look like 10.1234/abcd (no https://doi.org/)')
      .optional(),
    openAccess: z.boolean().default(true),
    deliverable: reference('deliverables').optional(),
  }),
});

export const collections = {
  deliverables,
  workPackages,
  milestones,
  partners,
  team,
  news,
  events,
  publications,
};

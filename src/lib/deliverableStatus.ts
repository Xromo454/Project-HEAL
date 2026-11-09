export type DeliverableStatus = 'planned' | 'in-progress' | 'published';

export const STATUS_LABEL: Record<DeliverableStatus, string> = {
  planned: 'Planned',
  'in-progress': 'In progress',
  published: 'Published',
};

/** Current project month (M1 = first month). Null if there is no start date or the project has not started. */
export function projectMonth(startDate: string | null, today: Date = new Date()): number | null {
  if (!startDate) return null;
  const start = new Date(startDate);
  let months =
    (today.getFullYear() - start.getFullYear()) * 12 + (today.getMonth() - start.getMonth());
  if (today.getDate() < start.getDate()) months -= 1;
  return months < 0 ? null : months + 1;
}

/**
 * Status of a deliverable:
 * 1. manual override in the data wins;
 * 2. a file exists → published;
 * 3. due month reached → in progress (being finalised);
 * 4. otherwise → planned.
 */
export function deliverableStatus(
  d: { dueMonth: number; file?: string; status?: DeliverableStatus },
  currentMonth: number | null,
): DeliverableStatus {
  if (d.status) return d.status;
  if (d.file) return 'published';
  if (currentMonth !== null && currentMonth >= d.dueMonth) return 'in-progress';
  return 'planned';
}

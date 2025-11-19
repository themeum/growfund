import { z } from 'zod';

const ActivityLogBase = z.object({
  id: z.coerce.string(),
  created_at: z.string(),
});

const ActivityLogTransactionSchema = ActivityLogBase.extend({
  type: z.literal('transaction'),
  status: z.enum(['in-progress', 'completed', 'failed', 'refund-requested', 'refunded']),
  amount: z.number(),
  campaign: z.string(),
});

const ActivityLogUserSchema = ActivityLogBase.extend({
  type: z.literal('user'),
  action: z.enum(['created', 'updated', 'deleted']),
  message: z.string(),
});

const ActivityLogDocumentSchema = ActivityLogBase.extend({
  type: z.literal('document'),
  message: z.string(),
});

const DonorActivityLog = z.union([
  ActivityLogTransactionSchema,
  ActivityLogUserSchema,
  ActivityLogDocumentSchema,
]);

export { DonorActivityLog };
export type DonorActivityLog = z.infer<typeof DonorActivityLog>;

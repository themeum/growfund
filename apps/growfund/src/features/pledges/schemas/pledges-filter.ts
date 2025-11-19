import { z } from 'zod';

import { PledgeStatusSchema } from '@/features/pledges/schemas/pledge';
import { DateRangeSchema } from '@/schemas/filter';

const PledgeFilterSchema = z.object({
  search: z.string().nullish(),
  status: PledgeStatusSchema.nullish(),
  campaign_id: z.string().nullish(),
  date_range: DateRangeSchema.nullish(),
});

type PledgeFilter = z.infer<typeof PledgeFilterSchema>;

export { PledgeFilterSchema, type PledgeFilter };

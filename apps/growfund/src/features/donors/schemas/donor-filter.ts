import { z } from 'zod';

import { DateRangeSchema } from '@/schemas/filter';

const DonorFilterSchema = z.object({
  search: z.string().nullish(),
  campaign_id: z.string().nullish(),
  date_range: DateRangeSchema.nullish(),
  status: z.string().nullish(),
});

type DonorFilter = z.infer<typeof DonorFilterSchema>;

export { DonorFilterSchema, type DonorFilter };

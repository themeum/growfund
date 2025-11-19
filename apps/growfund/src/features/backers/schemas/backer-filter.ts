import z from 'zod';

import { DateRangeSchema } from '@/schemas/filter';

const BackerFilterFormSchema = z.object({
  search: z.string().nullish(),
  status: z.string().nullish(),
  campaign_id: z.string().nullish(),
  date_range: DateRangeSchema.nullish(),
});

type BackerFilterForm = z.infer<typeof BackerFilterFormSchema>;

export { BackerFilterFormSchema, type BackerFilterForm };

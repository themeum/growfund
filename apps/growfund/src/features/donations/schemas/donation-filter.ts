import z from 'zod';

import { DonationStatusSchema } from '@/features/donations/schemas/donation';
import { DateRangeSchema } from '@/schemas/filter';

const DonationFiltersFormSchema = z.object({
  search: z.string().nullish(),
  status: DonationStatusSchema.nullish(),
  campaign_id: z.string().nullish(),
  fund_id: z.string().nullish(),
  date_range: DateRangeSchema.nullish(),
});

type DonationFiltersForm = z.infer<typeof DonationFiltersFormSchema>;

export { DonationFiltersFormSchema, type DonationFiltersForm };

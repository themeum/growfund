import { z } from 'zod';

import { CampaignStatusSchema } from '@/features/campaigns/schemas/campaign';
import { DateRangeSchema } from '@/schemas/filter';

const CampaignFilterSchema = z.object({
  search: z.string().nullish(),
  status: CampaignStatusSchema.nullish(),
  date_range: DateRangeSchema.nullish(),
});

type CampaignFilter = z.infer<typeof CampaignFilterSchema>;

export { CampaignFilterSchema, type CampaignFilter };

import { z } from 'zod';

import { RewardItemResponseSchema } from '@/features/campaigns/schemas/reward-item';
import { MediaSchema } from '@/schemas/media';

const RewardSchema = z.object({
  title: z.string({ message: 'The reward title is required.' }),
  amount: z.number({ message: 'The pledge amount is required.' }),
  description: z.string().nullish(),
  image: MediaSchema.nullish(),
  quantity_type: z.enum(['unlimited', 'limited']).default('unlimited'),
  quantity_limit: z.number().nullish(),
  time_limit_type: z.enum(['no-limit', 'specific-date']).default('specific-date'),
  limit_start_date: z.coerce.date().nullish(),
  limit_end_date: z.coerce.date().nullish(),
  reward_type: z
    .enum(['physical-goods', 'digital-goods', 'physical-and-digital-goods'])
    .default('physical-goods'),
  estimated_delivery_date: z.coerce.date().nullish(),
  shipping_costs: z
    .array(
      z.object({
        location: z.string(),
        cost: z.number(),
      }),
    )
    .nullish(),
  allow_local_pickup: z.boolean().default(false),
  local_pickup_instructions: z.string().nullish(),
  items: z
    .array(
      z.object({
        id: z.coerce.string(),
        quantity: z.number(),
      }),
    )
    .default([]),
});

const RewardResponseSchema = RewardSchema.omit({ items: true }).extend({
  id: z.coerce.string(),
  reward_left: z.number().nullish(),
  number_of_contributors: z.number().nullish(),
  created_at: z.string().nullish(),
  items: z.array(RewardItemResponseSchema),
});

type Reward = z.infer<typeof RewardResponseSchema>;
type RewardForm = z.infer<typeof RewardSchema>;
type RewardPayload = z.infer<typeof RewardSchema>;

export { RewardResponseSchema, RewardSchema, type Reward, type RewardForm, type RewardPayload };

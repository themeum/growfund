import { __ } from '@wordpress/i18n';
import { z } from 'zod';

import { MediaSchema } from '@/schemas/media';
import { type Prettify } from '@/types';

const RewardItemSchema = z.object({
  id: z.coerce.string(),
  title: z.string({ message: __('The reward item title is required.', 'growfund') }),
  quantity: z.number().nullish(),
  image: MediaSchema.nullish(),
  created_at: z.string(),
});

const RewardItemFormSchema = RewardItemSchema.omit({
  id: true,
  created_at: true,
});

const RewardItemPayloadSchema = RewardItemFormSchema;
const RewardItemResponseSchema = RewardItemSchema;

type RewardItem = Prettify<z.infer<typeof RewardItemResponseSchema>>;
type RewardItemForm = z.infer<typeof RewardItemFormSchema>;
type RewardItemPayload = z.infer<typeof RewardItemPayloadSchema>;

export {
  RewardItemFormSchema,
  RewardItemPayloadSchema,
  RewardItemResponseSchema,
  RewardItemSchema,
  type RewardItem,
  type RewardItemForm,
  type RewardItemPayload,
};

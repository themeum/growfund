import { __ } from '@wordpress/i18n';
import { z } from 'zod';

import { MediaSchema } from '@/schemas/media';
import { type Prettify } from '@/types';
import { isDefined, isValidUrl } from '@/utils';

const RewardItemSchema = z.object({
  id: z.coerce.string(),
  title: z.string({ message: __('The reward item title is required.', 'growfund') }),
  type: z.enum(['digital', 'physical'], {
    errorMap: () => ({
      message: __('The reward item type is required.', 'growfund'),
    }),
  }),
  image: MediaSchema.nullish(),
  asset_type: z.enum(['file', 'url']).nullish(),
  can_download: z.boolean().nullish(),
  asset: MediaSchema.nullish(),
  asset_url: z.string().nullish(),
  description: z.string().nullish(),
  quantity: z.number().nullish(),
  created_at: z.string().optional(),
});

const RewardItemFormSchema = RewardItemSchema.omit({
  id: true,
  created_at: true,
}).superRefine((data, ctx) => {
  if (data.type === 'digital') {
    if (!isDefined(data.asset_type)) {
      ctx.addIssue({
        code: z.ZodIssueCode.custom,
        message: __('Please select an asset type.', 'growfund'),
        path: ['asset_type'],
      });
      return;
    }

    if (data.asset_type === 'file' && !isDefined(data.asset)) {
      ctx.addIssue({
        code: z.ZodIssueCode.custom,
        message: __('File is required.', 'growfund'),
        path: ['asset'],
      });
    }

    if (data.asset_type === 'url' && (!isDefined(data.asset_url) || data.asset_url.trim() === '')) {
      ctx.addIssue({
        code: z.ZodIssueCode.custom,
        message: __('URL is required.', 'growfund'),
        path: ['asset_url'],
      });
    }

    if (data.asset_type === 'url') {
      if (!isDefined(data.asset_url) || data.asset_url.trim() === '') {
        ctx.addIssue({
          code: z.ZodIssueCode.custom,
          message: __('URL is required.', 'growfund'),
          path: ['asset_url'],
        });
      } else if (!isValidUrl(data.asset_url)) {
        ctx.addIssue({
          code: z.ZodIssueCode.custom,
          message: __('Please enter a valid URL', 'growfund'),
          path: ['asset_url'],
        });
      }
    }
  }
});

const RewardItemPayloadSchema = RewardItemFormSchema;
const RewardItemResponseSchema = RewardItemSchema;
const RewardItemBaseSchema = RewardItemSchema;

type RewardItem = Prettify<z.infer<typeof RewardItemResponseSchema>>;
type RewardItemForm = z.infer<typeof RewardItemFormSchema>;
type RewardItemPayload = z.infer<typeof RewardItemPayloadSchema>;

export {
  RewardItemBaseSchema,
  RewardItemFormSchema,
  RewardItemPayloadSchema,
  RewardItemResponseSchema,
  RewardItemSchema,
  type RewardItem,
  type RewardItemForm,
  type RewardItemPayload,
};

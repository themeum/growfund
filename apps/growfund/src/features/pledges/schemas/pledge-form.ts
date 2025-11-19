import { __ } from '@wordpress/i18n';
import { z } from 'zod';

import { PledgePaymentSchema, PledgeSchema } from '@/features/pledges/schemas/pledge';
import { isDefined } from '@/utils';

const PledgeFormSchema = PledgeSchema.omit({
  id: true,
  created_at: true,
  updated_at: true,
})
  .merge(
    PledgePaymentSchema.pick({
      amount: true,
      bonus_support_amount: true,
    }),
  )
  .superRefine((data, ctx) => {
    if (!data.campaign_id) {
      ctx.addIssue({
        path: ['campaign_id'],
        code: z.ZodIssueCode.custom,
        message: __('Campaign is required.', 'growfund'),
      });
    }
    if (!data.user_id) {
      ctx.addIssue({
        path: ['user_id'],
        code: z.ZodIssueCode.custom,
        message: __('Backer is required.', 'growfund'),
      });
    }
    if (data.pledge_option === 'without-rewards' && !isDefined(data.amount)) {
      ctx.addIssue({
        path: ['amount'],
        code: z.ZodIssueCode.custom,
        message: __('Pledge amount is required.', 'growfund'),
      });
    }

    if (data.pledge_option === 'with-rewards' && !isDefined(data.reward_id)) {
      ctx.addIssue({
        path: ['reward_id'],
        code: z.ZodIssueCode.custom,
        message: __('Reward is required.', 'growfund'),
      });
    }
  });
const PledgePayloadSchema = PledgeFormSchema;

type PledgePayload = z.infer<typeof PledgePayloadSchema>;
type PledgeForm = z.infer<typeof PledgeFormSchema>;

export { PledgeFormSchema, PledgePayloadSchema, type PledgeForm, type PledgePayload };

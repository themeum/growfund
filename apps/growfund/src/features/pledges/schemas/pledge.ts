import { __ } from '@wordpress/i18n';
import { z } from 'zod';

import { BackerSchema } from '@/features/backers/schemas/backer';
import { CampaignResponseSchema } from '@/features/campaigns/schemas/campaign';
import { RewardResponseSchema } from '@/features/campaigns/schemas/reward';

const PledgeStatusSchema = z.enum([
  'pending',
  'completed',
  'backed',
  'failed',
  'cancelled',
  'refunded',
  'trashed',
  'in-progress',
]);

type PledgeStatus = z.infer<typeof PledgeStatusSchema>;

const PledgeOptionSchema = z.enum(['with-rewards', 'without-rewards'], {
  errorMap: () => ({ message: 'Please select a pledge option.' }),
});

type PledgeOption = z.infer<typeof PledgeOptionSchema>;

const PaymentStatusSchema = z.enum([
  'paid',
  'unpaid',
  'partially-refunded',
  'refunded',
  'failed',
  'pending',
]);

type PaymentStatus = z.infer<typeof PaymentStatusSchema>;

const PledgePaymentSchema = z.object({
  amount: z.number().nullish(),
  bonus_support_amount: z.number().nullish(),
  shipping_cost: z.number().nullish(),
  recovery_fee: z.number().nullish(),
  total: z.number().nullish(),
  payment_method: z.object({
    name: z.string(),
    logo: z.string(),
    type: z.enum(['online-payment', 'manual-payment']),
    label: z.string(),
    instruction: z.string().nullish(),
  }),
  payment_status: PaymentStatusSchema.nullish(),
  transaction_id: z.string().nullish(),
});

type PledgePayment = z.infer<typeof PledgePaymentSchema>;

const BaseSchema = z.object({
  id: z.coerce.string(),
  status: PledgeStatusSchema.default('pending'),
  pledge_option: PledgeOptionSchema.nullish(),
  notes: z.string().nullish(),
  is_manual: z.boolean().default(false),
  created_at: z.string(),
  updated_at: z.string().nullish(),
});

const PledgeSchema = BaseSchema.extend({
  campaign_id: z.string().nullish(),
  user_id: z.string().nullish(),
  reward_id: z.string().nullish(),
  payment_method: z.string({ message: __('Payment method is required.', 'growfund') }),
});

const PledgeResponseSchema = BaseSchema.extend({
  campaign: CampaignResponseSchema,
  backer: BackerSchema,
  reward: RewardResponseSchema.nullish(),
  payment: PledgePaymentSchema,
});

type Pledge = z.infer<typeof PledgeResponseSchema>;

export {
  PaymentStatusSchema,
  PledgeOptionSchema,
  PledgePaymentSchema,
  PledgeResponseSchema,
  PledgeSchema,
  PledgeStatusSchema,
  type PaymentStatus,
  type Pledge,
  type PledgeOption,
  type PledgePayment,
  type PledgeStatus,
};

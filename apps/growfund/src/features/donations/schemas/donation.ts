import { __ } from '@wordpress/i18n';
import { z } from 'zod';

import { CampaignResponseSchema } from '@/features/campaigns/schemas/campaign';
import { DonorResponseSchema } from '@/features/donors/schemas/donor';
import { PaymentStatusSchema } from '@/features/pledges/schemas/pledge';
import { AddressSchema } from '@/schemas/address';
import { isDefined } from '@/utils';

const DonationStatusSchema = z.enum([
  'pending',
  'completed',
  'failed',
  'cancelled',
  'refunded',
  'trashed',
]);

type DonationStatus = z.infer<typeof DonationStatusSchema>;

const DonationPaymentSchema = z.object({
  amount: z.number().nullish(),
  payment_status: PaymentStatusSchema.nullish(),
  payment_method: z.object({
    name: z.string(),
    logo: z.string(),
    type: z.enum(['online-payment', 'manual-payment']),
    label: z.string(),
    instruction: z.string().nullish(),
  }),
  transaction_id: z.string().nullish(),
});

type DonationPayment = z.infer<typeof DonationPaymentSchema>;

const TributeSchema = z.object({
  tribute_type: z.string().nullish(),
  tribute_salutation: z.string().nullish(),
  tribute_to: z.string().nullish(),
  tribute_notification_type: z
    .enum(['send-ecard', 'send-post-mail', 'send-ecard-and-post-mail'])
    .nullish(),
  tribute_notification_recipient_name: z.string().nullish(),
  tribute_notification_recipient_phone: z.string().nullish(),
  tribute_notification_recipient_email: z
    .string()
    .email(__('Invalid email address', 'growfund'))
    .nullish(),
  tribute_notification_recipient_address: AddressSchema.nullish(),
});

const useTributeRefine = (data: z.infer<typeof TributeSchema>, ctx: z.RefinementCtx) => {
  if (!isDefined(data.tribute_type) && !isDefined(data.tribute_notification_type)) return;

  if (!data.tribute_type && data.tribute_notification_type) {
    ctx.addIssue({
      path: ['tribute_type'],
      code: z.ZodIssueCode.custom,
      message: __('Tribute type is required', 'growfund'),
    });
  }

  if (!data.tribute_salutation) {
    ctx.addIssue({
      path: ['tribute_salutation'],
      code: z.ZodIssueCode.custom,
      message: __('Tribute prepended label is required', 'growfund'),
    });
  }

  if (!data.tribute_to) {
    ctx.addIssue({
      path: ['tribute_to'],
      code: z.ZodIssueCode.custom,
      message: __('Tribute to is required', 'growfund'),
    });
  }

  if (!data.tribute_notification_type) {
    ctx.addIssue({
      path: ['tribute_notification_type'],
      code: z.ZodIssueCode.custom,
      message: __('Notification type is required', 'growfund'),
    });
  }

  if (!data.tribute_notification_recipient_name) {
    ctx.addIssue({
      path: ['tribute_notification_recipient_name'],
      code: z.ZodIssueCode.custom,
      message: __('Recipient name is required', 'growfund'),
    });
  }

  const hasPostMail =
    data.tribute_notification_type === 'send-post-mail' ||
    data.tribute_notification_type === 'send-ecard-and-post-mail';

  if (!hasPostMail) {
    if (!data.tribute_notification_recipient_email) {
      ctx.addIssue({
        path: ['tribute_notification_recipient_email'],
        code: z.ZodIssueCode.custom,
        message: __('Recipient email is required', 'growfund'),
      });
    }

    if (!data.tribute_notification_recipient_phone) {
      ctx.addIssue({
        path: ['tribute_notification_recipient_phone'],
        code: z.ZodIssueCode.custom,
        message: __('Recipient phone is required', 'growfund'),
      });
    }
  }

  const isAddressRequired = hasPostMail
    ? !isDefined(data.tribute_notification_recipient_address) ||
      !data.tribute_notification_recipient_address.address ||
      !data.tribute_notification_recipient_address.city ||
      !data.tribute_notification_recipient_address.zip_code ||
      !data.tribute_notification_recipient_address.country
    : false;

  if (isAddressRequired) {
    ctx.addIssue({
      path: ['tribute_notification_recipient_address'],
      code: z.ZodIssueCode.custom,
      message: __('Address is required for mail notifications', 'growfund'),
    });
  }
};

const TributeFieldSchema = TributeSchema.superRefine(useTributeRefine);

const BaseSchema = z.object({
  id: z.coerce.string().nullish(),
  status: DonationStatusSchema.default('pending'),
  campaign_id: z.string({
    message: 'Campaign is required',
  }),
  user_id: z.string({
    message: 'Donor is required',
  }),
  fund_id: z.string().nullish(),
  amount: z.number({
    message: 'The amount is required.',
  }),
  notes: z.string().nullish(),
  created_at: z.string().nullish(),
  updated_at: z.string().nullish(),
  is_anonymous: z.boolean().default(false),
});

const DonationSchema = BaseSchema.merge(DonationPaymentSchema);

const DonationResponseSchema = z
  .object({
    id: z.coerce.string(),
    donor: DonorResponseSchema,
    fund: z
      .object({
        id: z.coerce.string(),
        title: z.string(),
      })
      .nullish(),
    campaign: CampaignResponseSchema.pick({
      id: true,
      title: true,
      status: true,
      fund_raised: true,
      goal_type: true,
      goal_amount: true,
      images: true,
      start_date: true,
      created_by: true,
      is_launched: true,
    }),
    status: DonationStatusSchema.default('pending'),
    notes: z.string().nullish(),
    is_anonymous: z.boolean().default(false),
    is_manual: z.boolean().default(false),
    updated_at: z.string().nullish(),
    created_at: z.string(),
  })
  .merge(TributeFieldSchema._def.schema)
  .merge(
    DonationPaymentSchema.omit({ amount: true }).extend({
      amount: z.number(),
    }),
  );

type Donation = z.infer<typeof DonationResponseSchema>;
type TributeFields = z.infer<typeof TributeFieldSchema>;

export {
  BaseSchema,
  DonationPaymentSchema,
  DonationResponseSchema,
  DonationSchema,
  DonationStatusSchema,
  TributeFieldSchema,
  useTributeRefine,
  type Donation,
  type DonationPayment,
  type DonationStatus,
  type TributeFields,
};

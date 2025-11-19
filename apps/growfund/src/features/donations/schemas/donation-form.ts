import { __ } from '@wordpress/i18n';
import { z } from 'zod';

import {
  DonationSchema,
  TributeFieldSchema,
  useTributeRefine,
} from '@/features/donations/schemas/donation';

const DonationFormSchema = DonationSchema.merge(TributeFieldSchema._def.schema)
  .omit({
    id: true,
    transaction_id: true,
  })
  .extend({
    payment_method: z.string({ message: __('Payment method is required.', 'growfund') }),
  })
  .superRefine(useTributeRefine);

const DonationPayloadSchema = DonationFormSchema;

type DonationPayload = z.infer<typeof DonationPayloadSchema>;
type DonationForm = z.infer<typeof DonationFormSchema>;

export { DonationFormSchema, DonationPayloadSchema, type DonationForm, type DonationPayload };

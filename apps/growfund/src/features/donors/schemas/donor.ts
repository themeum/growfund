import { __ } from '@wordpress/i18n';
import { z } from 'zod';

import { AddressSchema } from '@/schemas/address';
import { MediaSchema } from '@/schemas/media';
import { ActivitySchema } from '@/types/activity';

const DonorSchema = z.object({
  id: z.coerce.string(),
  first_name: z.string(),
  last_name: z.string(),
  email: z.string(),
  phone: z.string().nullish(),
  billing_address: AddressSchema.nullish(),
  image: MediaSchema.nullish(),
  joined_at: z.string(),
  is_verified: z.coerce.boolean().default(false),
  created_by: z.string().nullish(),
});

const DonorResponseSchema = DonorSchema.extend({
  number_of_contributions: z.number().optional().default(0),
  total_contributions: z.number().optional().default(0.0),
  latest_donation_date: z.string().nullish(),
});

const DonorOverviewSchema = z.object({
  number_of_contributions: z.number().default(0),
  total_contributions: z.number().default(0.0),
  average_donation: z.number().default(0.0),
  donated_campaigns: z.number().default(0),
  profile: DonorResponseSchema,
  activity_logs: ActivitySchema.array(),
});

const DonorFormSchema = DonorSchema.omit({
  id: true,
  joined_at: true,
  created_by: true,
}).extend({
  password: z
    .string({ message: __('Password is required', 'growfund') })
    .min(6, { message: __('Password must be at least 6 characters long', 'growfund') })
    .nullish(),
});
const DonorPayloadSchema = DonorFormSchema;

type DonorForm = z.infer<typeof DonorFormSchema>;
type DonorPayload = z.infer<typeof DonorPayloadSchema>;
type Donor = z.infer<typeof DonorResponseSchema>;
type DonorOverview = z.infer<typeof DonorOverviewSchema>;

export {
  DonorFormSchema,
  DonorOverviewSchema,
  DonorPayloadSchema,
  DonorResponseSchema,
  DonorSchema,
  type Donor,
  type DonorForm,
  type DonorOverview,
  type DonorPayload,
};

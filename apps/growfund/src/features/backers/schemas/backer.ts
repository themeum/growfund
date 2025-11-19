import { __ } from '@wordpress/i18n';
import { z } from 'zod';

import { AddressSchema } from '@/schemas/address';
import { MediaSchema } from '@/schemas/media';
import { ActivitySchema } from '@/types/activity';
import { User as CurrentUser } from '@/utils/user';

const BackerSchema = z.object({
  id: z.coerce.string(),
  first_name: z.string({ message: __('First name is required', 'growfund') }),
  last_name: z.string({ message: __('Last name is required', 'growfund') }),
  email: z.string({ message: __('Email address is required', 'growfund') }).email({
    message: __('Invalid email address', 'growfund'),
  }),
  joined_at: z.string().nullish(),
  phone: z.string().nullish(),
  image: MediaSchema.nullish(),
  shipping_address: AddressSchema.nullish(),
  billing_address: z
    .object({
      address: z.string().nullish(),
      address_2: z.string().nullish(),
      city: z.string().nullish(),
      state: z.string().nullish(),
      zip_code: z.string().nullish(),
      country: z.string().nullish(),
    })
    .nullish(),
  is_billing_address_same: z.boolean().default(false),
  is_verified: z.coerce.boolean().default(false),
  created_by: z.string().nullish(),
});

const BackerFormSchema = BackerSchema.omit({
  id: true,
  joined_at: true,
  created_by: true,
})
  .extend({
    password: z
      .string({ message: __('Password is required', 'growfund') })
      .min(6, { message: __('Password must be at least 6 characters long', 'growfund') })
      .nullish(),
  })
  .superRefine((data, ctx) => {
    if (CurrentUser.isBacker() && !data.is_billing_address_same) {
      if (!data.billing_address?.address) {
        ctx.addIssue({
          code: z.ZodIssueCode.custom,
          message: __('The address field is required', 'growfund'),
          path: ['billing_address', 'address'],
        });
      }

      if (!data.billing_address?.city) {
        ctx.addIssue({
          code: z.ZodIssueCode.custom,
          message: __('The city field is required', 'growfund'),
          path: ['billing_address', 'city'],
        });
      }

      if (!data.billing_address?.zip_code) {
        ctx.addIssue({
          code: z.ZodIssueCode.custom,
          message: __('The zip code field is required', 'growfund'),
          path: ['billing_address', 'zip_code'],
        });
      }

      if (!data.billing_address?.country) {
        ctx.addIssue({
          code: z.ZodIssueCode.custom,
          message: __('The country field is required', 'growfund'),
          path: ['billing_address', 'country'],
        });
      }
    }
  });

const BackerOverviewSchema = z.object({
  pledged_amount: z.number().default(0),
  backed_amount: z.number().default(0),
  pledged_campaigns: z.number().default(0),
  backed_campaigns: z.number().default(0),
  backer_information: BackerSchema,
  activity_logs: ActivitySchema.array(),
});
const BackerResponseSchema = BackerSchema.extend({
  number_of_contributions: z.number().default(0),
  total_contributions: z.number().default(0.0),
  latest_pledge_date: z.coerce.date().nullish(),
  joined_at: z.string().nullish(),
});

type BackerForm = z.infer<typeof BackerFormSchema>;
type Address = z.infer<typeof AddressSchema>;
type BackerInfo = z.infer<typeof BackerSchema>;
type Backer = z.infer<typeof BackerResponseSchema>;
type BackerPayload = z.infer<typeof BackerFormSchema>;
type BackerOverview = z.infer<typeof BackerOverviewSchema>;

export {
  BackerFormSchema,
  BackerOverviewSchema,
  BackerResponseSchema,
  BackerSchema,
  type Address,
  type Backer,
  type BackerForm,
  type BackerInfo,
  type BackerOverview,
  type BackerPayload,
};

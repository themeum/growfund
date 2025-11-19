import { __ } from '@wordpress/i18n';
import { z } from 'zod';

import { GallerySchema, VideoSchema } from '@/schemas/media';

const AmountSchema = z.object({
  id: z.coerce.string().nullish(),
  amount: z.number().min(1),
  is_editing: z.boolean().nullish().default(false),
});
const AmountDescSchema = z.object({
  id: z.coerce.string().nullish(),
  amount: z.number().min(1),
  description: z.string(),
  is_editing: z.boolean().nullish().default(false),
});
const AmountDescriptionSchema = z.object({
  suggested_option: z.enum(['amount-only', 'amount-description']).nullish(),
  amounts: z.array(AmountSchema).nullish(),
  amount_descriptions: z.array(AmountDescSchema).nullish(),
  default_id: z.string().nullish(),
});

const CampaignStatusSchema = z.enum([
  'draft',
  'published',
  'pending',
  'funded',
  'declined',
  'completed',
  'trashed',
]);

const CampaignGoalTypeSchema = z.enum([
  'raised-amount',
  'number-of-contributions',
  'number-of-contributors',
]);

const BaseSchema = z.object({
  id: z.coerce.string(),
  title: z.string({ message: __('The title field is required.', 'growfund') }).max(255, {
    message: __('The campaign title could have max 255 characters.', 'growfund'),
  }),
  slug: z
    .string()
    .max(300, {
      message: __('The campaign slug could have max 300 characters.', 'growfund'),
    })
    .nullish(),
  description: z.string().nullish(),
  story: z.string().nullish(),
  images: GallerySchema.nullish(),
  video: VideoSchema.nullish(),
  is_featured: z.boolean().default(false),
  category: z
    .string({
      message: __('The category field is required.', 'growfund'),
    })
    .nullish(),
  sub_category: z.string().nullish(),
  start_date: z.coerce.date({ message: __('The launch date is required.', 'growfund') }).nullish(),
  end_date: z.coerce
    .date({
      message: __('The end date is required.', 'growfund'),
    })
    .nullish(),
  location: z.string().nullish(),
  tags: z.array(z.string()).nullish(),
  collaborators: z.array(z.string()).nullish(),
  show_collaborator_list: z.boolean().default(true),
  status: CampaignStatusSchema.default('draft'),
  risks: z.string().nullish(),

  // goal
  has_goal: z.boolean().default(true),
  goal_type: CampaignGoalTypeSchema.nullish(),
  goal_amount: z.number().nullish(),
  reaching_action: z.enum(['close', 'continue']).nullish(),

  // settings
  confirmation_title: z.string().nullish(),
  confirmation_description: z.string().nullish(),
  provide_confirmation_pdf_receipt: z.boolean().default(true),
  faqs: z
    .array(
      z.object({
        question: z.string(),
        answer: z.string(),
      }),
    )
    .nullish(),
});

const SharedCommonFieldsSchema = z.object({
  created_at: z.string().datetime().nullish(),
  updated_at: z.string().datetime().nullish(),
  created_by: z.string().nullish(),
  updated_by: z.string().nullish(),
});

const CampaignCommonFieldsSchema = z.object({
  number_of_contributions: z.number().nullish(),
  number_of_contributors: z.number().nullish(),
  fund_raised: z.number().nullish(),
});

const AppreciationMessageSchema = z.object({
  pledge_from: z.number({
    message: __('Pledge from is required', 'growfund'),
  }),
  pledge_to: z.number({
    message: __('Pledge to is required', 'growfund'),
  }),
  appreciation_message: z.string({
    message: __('Appreciation Message is required', 'growfund'),
  }),
});

const CampaignRewardsSchema = z.object({
  appreciation_type: z.enum(['goodies', 'giving-thanks']).nullish(),
  giving_thanks: z.array(AppreciationMessageSchema).nullish(),
  rewards: z.array(z.string()).nullish(),
  allow_pledge_without_reward: z.boolean().default(false),
  min_pledge_amount: z.number().nullish(),
  max_pledge_amount: z.number().nullish(),
});

const DonationBaseSchema = z.object({
  goal_amount: z.number().nullish(),
  number_of_donations: z.number().nullish(),
  number_of_donors: z.number().nullish(),
  allow_custom_donation: z.boolean().nullish().default(false),
  min_donation_amount: z.number().nullish(),
  max_donation_amount: z.number().nullish(),
  suggested_option_type: z
    .enum(['amount-only', 'amount-description'])
    .nullish()
    .default('amount-only'),
  suggested_options: z
    .array(
      z.object({
        amount: z.number(),
        description: z.string().nullish(),
        is_default: z.boolean().default(false),
      }),
    )
    .nullish()
    .default([]),
  has_tribute: z.boolean().default(true),
  tribute_requirement: z.enum(['required', 'optional']).default('optional').nullish(),
  tribute_title: z.string().nullish(),
  tribute_options: z
    .array(
      z.object({
        message: z.string(),
        is_editing: z.boolean().default(false),
        is_default: z.boolean().default(false),
      }),
    )
    .nullish()
    .default([]),
  tribute_notification_preference: z
    .enum(['send-ecard', 'send-post-mail', 'send-ecard-and-post-mail', 'donor-decide'])
    .nullish()
    .default('donor-decide'),
  fund_selection_type: z.enum(['fixed', 'donor-decide']).nullish().default('fixed'),
  default_fund: z.string().nullish(),
  fund_choices: z.array(z.string()).nullish(),
  donation_count: z.number().nullish(),
});

const DonationCommonFieldsSchema = z.object({
  donation_count: z.number().nullish(),
});

const CampaignSchema = BaseSchema.merge(SharedCommonFieldsSchema)
  .merge(CampaignRewardsSchema)
  .merge(DonationBaseSchema)
  .merge(CampaignCommonFieldsSchema);

const DonationSchema = BaseSchema.merge(SharedCommonFieldsSchema)
  .merge(DonationBaseSchema)
  .merge(DonationCommonFieldsSchema);

const CampaignResponseSchema = CampaignSchema.extend({
  is_interactive: z.boolean().default(false),
  is_launched: z.boolean().default(false),
  last_decline_reason: z.string().nullish(),
  preview_url: z.string().nullish(),
  number_of_individual_contributions: z.number().default(0),
  uncharged_pledge_count: z.number().default(0),
  is_backed: z.boolean().default(false),
  is_paused: z.boolean().default(false),
  is_hidden: z.boolean().default(false),
  is_ended: z.boolean().default(false),
});

const DonationResponseSchema = DonationSchema.omit({
  video: true,
}).extend({
  video: z.string().nullish(),
  images: z.array(z.string()).nullish(),
});

const CampaignFormSchema = BaseSchema.merge(CampaignRewardsSchema);
const DonationFormSchema = BaseSchema.merge(DonationBaseSchema);
const CampaignBuilderFormSchema = CampaignFormSchema.merge(DonationFormSchema);

type CampaignForm = z.infer<typeof CampaignFormSchema>;
type DonationForm = z.infer<typeof DonationFormSchema>;
type Campaign = z.infer<typeof CampaignResponseSchema>;
type Donation = z.infer<typeof DonationResponseSchema>;
type CampaignBuilderForm = z.infer<typeof CampaignBuilderFormSchema>;
type CampaignPayload = z.infer<typeof CampaignFormSchema>;

type AppreciationMessageSchemaType = z.infer<typeof AppreciationMessageSchema>;
type CampaignStatus = z.infer<typeof CampaignStatusSchema>;
type SuggestedAmount = z.infer<typeof AmountDescriptionSchema>;
type AmountSchema = z.infer<typeof AmountSchema>;
type AmountDescSchema = z.infer<typeof AmountDescSchema>;
type CampaignGoalType = z.infer<typeof CampaignGoalTypeSchema>;

export {
  AmountDescriptionSchema,
  CampaignBuilderFormSchema,
  CampaignFormSchema,
  CampaignGoalTypeSchema,
  CampaignResponseSchema,
  CampaignStatusSchema,
  DonationFormSchema,
  DonationResponseSchema,
  type AmountDescSchema,
  type AmountSchema,
  type AppreciationMessageSchemaType,
  type Campaign,
  type CampaignBuilderForm,
  type CampaignForm,
  type CampaignGoalType,
  type CampaignPayload,
  type CampaignStatus,
  type Donation,
  type DonationForm,
  type SuggestedAmount,
};

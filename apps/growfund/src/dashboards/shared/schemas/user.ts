import { type z } from 'zod';

import {
  BackerEmailSettingsSchema,
  DonorEmailSettingsSchema,
  FundraiserEmailSettingsSchema,
} from '@/features/settings/schemas/settings';

const BackerNotificationsFormSchema = BackerEmailSettingsSchema.pick({
  is_enabled_backer_email_campaign_post_update: true,
  is_enabled_backer_email_campaign_half_funded: true,
  is_enabled_backer_email_reward_delivered: true,
});

type BackerNotificationsForm = z.infer<typeof BackerNotificationsFormSchema>;
type BackerNotificationsPayload = BackerNotificationsForm & {
  id: string;
};

const DonorNotificationsFormSchema = DonorEmailSettingsSchema.pick({
  is_enabled_donor_email_campaign_post_update: true,
  is_enabled_donor_email_campaign_half_milestone_achieved: true,
});

type DonorNotificationsForm = z.infer<typeof DonorNotificationsFormSchema>;
type DonorNotificationsPayload = DonorNotificationsForm & {
  id: string;
};

const FundraiserNotificationsFormSchema = FundraiserEmailSettingsSchema.pick({
  is_enabled_fundraiser_email_campaign_declined: true,
  is_enabled_fundraiser_email_campaign_approved: true,
  is_enabled_fundraiser_email_campaign_funded: true,
  is_enabled_fundraiser_email_pledge_cancelled: true,
  is_enabled_fundraiser_email_new_pledge: true,
  is_enabled_fundraiser_email_new_donation: true,
  is_enabled_fundraiser_email_reward_delivered: true,
  is_enabled_fundraiser_email_campaign_post_update: true,
});

type FundraiserNotificationsForm = z.infer<typeof FundraiserNotificationsFormSchema>;
type FundraiserNotificationsPayload = FundraiserNotificationsForm & {
  id: string;
};

export {
  BackerNotificationsFormSchema,
  DonorNotificationsFormSchema,
  FundraiserNotificationsFormSchema,
  type BackerNotificationsForm,
  type BackerNotificationsPayload,
  type DonorNotificationsForm,
  type DonorNotificationsPayload,
  type FundraiserNotificationsForm,
  type FundraiserNotificationsPayload,
};

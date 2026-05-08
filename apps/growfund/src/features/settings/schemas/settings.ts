import { __ } from '@wordpress/i18n';
import { z } from 'zod';

import { Role } from '@/constants/role';
import { AppConfigKeys } from '@/features/settings/context/settings-context';
import { PaymentSchema } from '@/features/settings/features/payments/schemas/payment';
import { FundraiserPayoutSettingsSchema } from '@/features/settings/schemas/payout-setting';
import { AddressSchema } from '@/schemas/address';
import { MediaSchema } from '@/schemas/media';
import { isDefined } from '@/utils';

const GeneralSettingsSchema = z
  .object({
    country: z.string().nullish(),
    state: z.string().nullish(),
    organization: z
      .object({
        name: z.string().nullish(),
        location: z.string().nullish(),
        contact_email: z.string().nullish(),
      })
      .default({
        name: null,
        location: null,
        contact_email: null,
      }),
    registration_page: z.string().nullish(),
    login_page: z.string().nullish(),
    privacy_policy_page: z.string().nullish(),
    terms_and_conditions_page: z.string().nullish(),
    company_field_visibility: z.enum(['disabled', 'optional', 'required']).default('disabled'),
    title_prefix_visibility: z.enum(['disabled', 'optional', 'required']).default('disabled'),
    salutations: z.array(z.string()).nullish().default([]),
    tnc_text: z.string().default(''),
  })
  .superRefine((data, ctx) => {
    if (
      data.title_prefix_visibility === 'required' &&
      (!isDefined(data.salutations) || data.salutations.length === 0)
    ) {
      ctx.addIssue({
        code: z.ZodIssueCode.custom,
        message: __('You have to add salutations when title prefix is required', 'growfund'),
        path: ['salutations'],
      });
    }
  });

const PageSettingsSchema = z.object({
  login_page: z.string().optional().nullable(),
  registration_page: z.string().optional().nullable(),
  fundraiser_registration_page: z.string().optional().nullable(),
  campaigns_page: z.string().optional().nullable(),
  checkout_page: z.string().optional().nullable(),
  privacy_policy_page: z.string().optional().nullable(),
  terms_and_conditions_page: z.string().optional().nullable(),
});

const SocialShareProviders = z.enum(['facebook', 'x', 'linkedin', 'whatsapp', 'telegram']);

const CampaignSettingsSchema = z.object({
  is_login_required_to_view_campaign_detail: z.boolean().default(false),
  display_contributor_list_publicly: z.boolean().default(false),
  display_contributor_option: z
    .enum(['show-amount', 'show-name', 'show-amount-and-name'])
    .default('show-amount-and-name'),
  display_contributor_option_order_by: z
    .enum(['top-and-recent', 'top-only', 'recent-only'])
    .default('top-and-recent'),
  display_contributor_option_limit: z.enum(['20', '50', '100', 'all']).default('20'),
  campaign_update_visibility: z
    .enum(['public', 'contributors', 'logged-in-users'])
    .default('public'),
  social_shares: z.array(SocialShareProviders).nullish(),
  allow_comments: z.boolean().default(true),
  comment_moderation: z.enum(['immediate', 'need-approval']).default('immediate'),
  comment_visibility: z.enum(['public', 'contributors', 'logged-in-users']).default('public'),
  allow_tribute: z.boolean().default(false),
  allow_fund: z.boolean().default(false),
});

const UserPermissionsSettingsSchema = z.object({
  fundraisers_can_publish_campaigns: z.boolean().default(false),
  fundraisers_can_delete_campaigns: z.boolean().default(false),
  allow_anonymous_contributions: z.boolean().default(false),
  allow_contributor_comments: z.boolean().default(true),
});

const PaymentSettingsSchema = z.object({
  payments: z.array(PaymentSchema).nullish(),
  e_commerce_engine: z.enum(['native', 'woo-commerce']).default('native'),
  payment_gateway: z.enum(['stripe', 'paypal']).default('stripe'),
  currency: z.string().nullish(),
  currency_position: z.enum(['before', 'after']).default('before'),
  decimal_separator: z.enum(['.', ',', '\u00A0']).default('.'),
  thousand_separator: z.enum(['.', ',', '\u00A0']).default(','),
  decimal_places: z.number().default(2),
  enable_platform_fee: z.boolean().default(false),
  platform_fee_type: z.enum(['percent', 'flat']).default('percent'),
  platform_fee: z.number().nullish(),
  minimum_balance_to_request_withdrawal: z.number().nullish(),
  fundraiser_withdrawal_options: z
    .object({
      is_active_paypal: z.boolean().default(false),
      is_active_bank_transfer: z.boolean().default(true),
      is_active_others: z.boolean().default(false),
    })
    .nullish(),
  enable_guest_checkout: z.boolean().default(false),
});

const PaymentSettingsFormSchema = PaymentSettingsSchema.superRefine((data, ctx) => {
  if (isDefined(data.fundraiser_withdrawal_options)) {
    if (
      !data.fundraiser_withdrawal_options.is_active_bank_transfer &&
      !data.fundraiser_withdrawal_options.is_active_paypal &&
      !data.fundraiser_withdrawal_options.is_active_others
    ) {
      ctx.addIssue({
        code: z.ZodIssueCode.custom,
        message: __('Please select at least one withdrawal option', 'growfund'),
        path: ['fundraiser_withdrawal_options'],
      });
    }
  }
});

const PDFReceiptSettingsSchema = z.object({
  enable_annual_receipt: z.boolean().default(true),
});

const AdminEmailSettingsSchema = z.object({
  is_enabled_admin_email_password_reset_request: z.boolean().default(true),
  is_enabled_admin_email_campaign_submitted_for_review: z.boolean().default(true),
  is_enabled_admin_email_campaign_funded: z.boolean().default(true),
  is_enabled_admin_email_new_user_registration: z.boolean().default(true),
  is_enabled_admin_email_campaign_post_update: z.boolean().default(true),
  is_enabled_admin_email_new_pledge: z.boolean().default(true),
  is_enabled_admin_email_new_donation: z.boolean().default(true),
  is_enabled_admin_email_contribution_charged: z.boolean().default(true),
  is_enabled_admin_email_new_offline_pledge: z.boolean().default(true),
  is_enabled_admin_email_new_offline_donation: z.boolean().default(true),
  is_enabled_admin_email_contribution_cancellation_notification: z.boolean().default(true),
  is_enabled_admin_email_campaign_ended: z.boolean().default(true),
  is_enabled_admin_email_reward_delivered: z.boolean().default(true),
  is_enabled_admin_email_withdrawal_request_received: z.boolean().default(true),
});

const FundraiserEmailSettingsSchema = z.object({
  is_enabled_fundraiser_email_new_user_registration: z.boolean().default(false),
  is_enabled_fundraiser_email_account_approved: z.boolean().default(true),
  is_enabled_fundraiser_email_account_declined: z.boolean().default(true),
  is_enabled_fundraiser_email_password_reset_request: z.boolean().default(true),
  is_enabled_fundraiser_email_campaign_approved: z.boolean().default(true),
  is_enabled_fundraiser_email_campaign_declined: z.boolean().default(true),
  is_enabled_fundraiser_email_campaign_post_update: z.boolean().default(false),
  is_enabled_fundraiser_email_campaign_funded: z.boolean().default(true),
  is_enabled_fundraiser_email_new_pledge: z.boolean().default(true),
  is_enabled_fundraiser_email_new_donation: z.boolean().default(true),
  is_enabled_fundraiser_email_pledge_amount_charged: z.boolean().default(true),
  is_enabled_fundraiser_email_donation_amount_charged: z.boolean().default(true),
  is_enabled_fundraiser_email_pledge_cancelled: z.boolean().default(true),
  is_enabled_fundraiser_email_donation_cancelled: z.boolean().default(true),
  is_enabled_fundraiser_email_new_offline_pledge: z.boolean().default(true),
  is_enabled_fundraiser_email_new_offline_donation: z.boolean().default(true),
  is_enabled_fundraiser_email_reward_delivered: z.boolean().default(true),
  is_enabled_fundraiser_email_withdrawal_request_accepted: z.boolean().default(true),
  is_enabled_fundraiser_email_withdrawal_request_rejected: z.boolean().default(true),
});

const BackerEmailSettingsSchema = z.object({
  is_enabled_backer_email_pledge_created: z.boolean().default(true),
  is_enabled_backer_email_pledge_paid_with_giving_thanks: z.boolean().default(true),
  is_enabled_backer_email_offline_pledge_request: z.boolean().default(true),
  is_enabled_backer_email_pledge_paid: z.boolean().default(true),
  is_enabled_backer_email_new_backer_registration: z.boolean().default(true),
  is_enabled_backer_email_campaign_post_update: z.boolean().default(false),
  is_enabled_backer_email_payment_unsuccessful: z.boolean().default(true),
  is_enabled_backer_email_password_reset_request: z.boolean().default(true),
  is_enabled_backer_email_pledge_cancelled: z.boolean().default(true),
  is_enabled_backer_email_reward_delivered: z.boolean().default(true),
  is_enabled_backer_email_campaign_half_funded: z.boolean().default(false),
  is_enabled_backer_email_send_local_pickup_instructions: z.boolean().default(false),
});

const DonorEmailSettingsSchema = z.object({
  is_enabled_donor_email_donation_receipt: z.boolean().default(true),
  is_enabled_donor_email_donation_failed: z.boolean().default(true),
  is_enabled_donor_email_new_donor_registration: z.boolean().default(true),
  is_enabled_donor_email_offline_donation_instructions: z.boolean().default(true),
  is_enabled_donor_email_campaign_post_update: z.boolean().default(true),
  is_enabled_donor_email_campaign_half_milestone_achieved: z.boolean().default(false),
  is_enabled_donor_email_tribute_mail: z.boolean().default(true),
  is_enabled_donor_email_password_reset_request: z.boolean().default(true),
});

const MailBaseSchema = z.object({
  from_name: z.string().nullish(),
  from_email: z.string().nullish(),
});

const PHPMailSettingsSchema = MailBaseSchema.extend({
  mailer: z.literal('php-mail'),
});

const SMTPSettingsSchema = MailBaseSchema.extend({
  mailer: z.literal('smtp'),
  host: z.string(),
  port: z.string(),
  encryption: z.enum(['none', 'ssl', 'tls']),
  enable_authentication: z.boolean().default(true),
  username: z.string().nullish(),
  password: z.string().nullish(),
});

const MailServerSchema = z
  .discriminatedUnion('mailer', [
    PHPMailSettingsSchema,
    SMTPSettingsSchema,
    MailBaseSchema.extend({
      mailer: z.null(),
    }),
  ])
  .superRefine((data, ctx) => {
    if (data.mailer === 'smtp' && !!data.enable_authentication) {
      if (!data.username) {
        ctx.addIssue({
          code: z.ZodIssueCode.custom,
          message: __('Username is required when authentication is enabled', 'growfund'),
          path: ['username'],
        });
      }
      if (!data.password) {
        ctx.addIssue({
          code: z.ZodIssueCode.custom,
          message: __('Password is required when authentication is enabled', 'growfund'),
          path: ['password'],
        });
      }
    }
  });

const EmailSettingsSchema = AdminEmailSettingsSchema.merge(FundraiserEmailSettingsSchema)
  .merge(BackerEmailSettingsSchema)
  .merge(DonorEmailSettingsSchema)
  .merge(z.object({ mail: MailServerSchema.nullish() }));

const SecuritySettingsSchema = z
  .object({
    is_enabled_email_verification: z.boolean().default(false),
    enable_recaptcha_on_user_registration: z.enum(['disabled', 'enabled']).default('disabled'),
    enable_recaptcha_on_campaign_submit: z.enum(['disabled', 'enabled']).default('disabled'),
    recaptcha_site_key: z.string().nullish(),
    recaptcha_secret_key: z.string().nullish(),
  })
  .superRefine((data, ctx) => {
    if (
      data.enable_recaptcha_on_campaign_submit === 'enabled' ||
      data.enable_recaptcha_on_user_registration === 'enabled'
    ) {
      if (!data.recaptcha_secret_key) {
        ctx.addIssue({
          code: z.ZodIssueCode.custom,
          message: __('Recaptcha secret key is required when reCAPTCHA is enabled', 'growfund'),
          path: ['recaptcha_secret_key'],
        });
      }
      if (!data.recaptcha_site_key) {
        ctx.addIssue({
          code: z.ZodIssueCode.custom,
          message: __('Recaptcha site key is required when reCAPTCHA is enabled', 'growfund'),
          path: ['recaptcha_site_key'],
        });
      }
    }
  });

const BrandingSettingsSchema = z.object({
  logo: MediaSchema.nullish(),
  logo_height: z.coerce.number().default(28),
  button_primary_color: z.string().nullish(),
  button_hover_color: z.string().nullish(),
  button_text_color: z.string().nullish(),
});

const AdvancedSettingsSchema = z.object({
  campaign_permalink: z.string().default('/campaigns'),
  enable_cache_clearing: z.boolean().default(false),
  erase_data_upon_uninstallation: z.boolean().default(false),
});

const LicenseSettingsSchema = z.object({
  license_key: z.string().nullish(),
});

const UserSchema = z.object({
  id: z.coerce.string(),
  email: z.string(),
  username: z.string(),
  display_name: z.string(),
  first_name: z.string(),
  last_name: z.string(),
  image: MediaSchema.nullish(),
  phone: z.string().nullish(),
  active_role: z.nativeEnum(Role),
  shipping_address: AddressSchema.nullish(),
  billing_address: AddressSchema.nullish(),
  is_soft_deleted: z.coerce.boolean().default(false),
  is_billing_address_same: z.coerce.boolean().default(false),
  is_verified: z.coerce.boolean().default(false),
  notification_settings: BackerEmailSettingsSchema.pick({
    is_enabled_backer_email_campaign_post_update: true,
    is_enabled_backer_email_campaign_half_funded: true,
    is_enabled_backer_email_reward_delivered: true,
  })
    .extend(
      DonorEmailSettingsSchema.pick({
        is_enabled_donor_email_campaign_half_milestone_achieved: true,
        is_enabled_donor_email_campaign_post_update: true,
      }).shape,
    )
    .extend(
      FundraiserEmailSettingsSchema.pick({
        is_enabled_fundraiser_email_campaign_declined: true,
        is_enabled_fundraiser_email_campaign_approved: true,
        is_enabled_fundraiser_email_campaign_funded: true,
        is_enabled_fundraiser_email_pledge_cancelled: true,
        is_enabled_fundraiser_email_new_pledge: true,
        is_enabled_fundraiser_email_new_donation: true,
        is_enabled_fundraiser_email_reward_delivered: true,
        is_enabled_fundraiser_email_campaign_post_update: true,
      }).shape,
    )
    .nullish(),
  joined_at: z.coerce.date(),
  last_contribution_at: z.coerce.date().nullish(),
  total_number_of_contributions: z.number().default(0),
  payout_method: FundraiserPayoutSettingsSchema.nullish(),
});

const AppConfigSchema = z.object({
  [AppConfigKeys.General]: GeneralSettingsSchema.nullish(),
  [AppConfigKeys.Page]: PageSettingsSchema.nullish(),
  [AppConfigKeys.Campaign]: CampaignSettingsSchema.nullish(),
  [AppConfigKeys.UserAndPermissions]: UserPermissionsSettingsSchema.nullish(),
  [AppConfigKeys.Payment]: PaymentSettingsSchema.nullish(),
  [AppConfigKeys.Receipt]: PDFReceiptSettingsSchema.nullish(),
  [AppConfigKeys.EmailAndNotifications]: EmailSettingsSchema.nullish(),
  [AppConfigKeys.Security]: SecuritySettingsSchema.nullish(),
  [AppConfigKeys.Integrations]: z.array(z.string()).default([]),
  [AppConfigKeys.Branding]: BrandingSettingsSchema.nullish(),
  [AppConfigKeys.Advanced]: AdvancedSettingsSchema.nullish(),
  [AppConfigKeys.DonationMode]: z.enum(['0', '1']),
});

type GeneralSettingsForm = z.infer<typeof GeneralSettingsSchema>;
type CampaignSettingsForm = z.infer<typeof CampaignSettingsSchema>;
type UserPermissionsSettingsForm = z.infer<typeof UserPermissionsSettingsSchema>;
type PaymentSettingsForm = z.infer<typeof PaymentSettingsFormSchema>;
type PDFReceiptSettingsForm = z.infer<typeof PDFReceiptSettingsSchema>;
type EmailSettingsForm = z.infer<typeof EmailSettingsSchema>;
type SecuritySettingsForm = z.infer<typeof SecuritySettingsSchema>;
type BrandingSettingsForm = z.infer<typeof BrandingSettingsSchema>;
type AdvancedSettingsForm = z.infer<typeof AdvancedSettingsSchema>;
type LicenseSettingsForm = z.infer<typeof LicenseSettingsSchema>;
type AppConfig = z.infer<typeof AppConfigSchema>;
type SocialShareProviders = z.infer<typeof SocialShareProviders>;
type User = z.infer<typeof UserSchema>;
type MailServerForm = z.infer<typeof MailServerSchema>;
type PageSettingsSchemaForm = z.infer<typeof PageSettingsSchema>;

export {
  AdvancedSettingsSchema,
  AppConfigSchema,
  BackerEmailSettingsSchema,
  BrandingSettingsSchema,
  CampaignSettingsSchema,
  DonorEmailSettingsSchema,
  EmailSettingsSchema,
  FundraiserEmailSettingsSchema,
  GeneralSettingsSchema,
  LicenseSettingsSchema,
  MailServerSchema,
  PageSettingsSchema,
  PaymentSettingsFormSchema,
  PaymentSettingsSchema,
  PDFReceiptSettingsSchema,
  SecuritySettingsSchema,
  UserPermissionsSettingsSchema,
  UserSchema,
  type AdvancedSettingsForm,
  type AppConfig,
  type BrandingSettingsForm,
  type CampaignSettingsForm,
  type EmailSettingsForm,
  type GeneralSettingsForm,
  type LicenseSettingsForm,
  type MailServerForm,
  type PageSettingsSchemaForm,
  type PaymentSettingsForm,
  type PDFReceiptSettingsForm,
  type SecuritySettingsForm,
  type SocialShareProviders,
  type User,
  type UserPermissionsSettingsForm,
};

import { __ } from '@wordpress/i18n';
import { z } from 'zod';

export interface EmailTemplateConfig {
  key: string;
  optionKey: string;
  label: string;
}

export const emailTemplates: EmailTemplateConfig[] = [
  // Admin Emails
  {
    key: 'admin_campaign_submitted_for_review',
    optionKey: 'growfund_admin_email_campaign_submitted_for_review',
    label: __('Campaign Submitted for Review', 'growfund'),
  },
  {
    key: 'admin_campaign_funded',
    optionKey: 'growfund_admin_email_campaign_funded',
    label: __('Campaign Funded', 'growfund'),
  },
  {
    key: 'admin_new_user_registration',
    optionKey: 'growfund_admin_email_new_user_registration',
    label: __('New User Registration', 'growfund'),
  },
  {
    key: 'admin_campaign_post_update',
    optionKey: 'growfund_admin_email_campaign_post_update',
    label: __('Campaign Update', 'growfund'),
  },
  {
    key: 'admin_new_donation',
    optionKey: 'growfund_admin_email_new_donation',
    label: __('New Donation', 'growfund'),
  },
  {
    key: 'admin_new_offline_donation',
    optionKey: 'growfund_admin_email_new_offline_donation',
    label: __('New Offline Donation', 'growfund'),
  },
  {
    key: 'admin_new_pledge',
    optionKey: 'growfund_admin_email_new_pledge',
    label: __('New Pledge', 'growfund'),
  },
  {
    key: 'admin_new_offline_pledge',
    optionKey: 'growfund_admin_email_new_offline_pledge',
    label: __('New Offline Pledge', 'growfund'),
  },
  {
    key: 'admin_campaign_ended',
    optionKey: 'growfund_admin_email_campaign_ended',
    label: __('Campaign Ended', 'growfund'),
  },
  {
    key: 'admin_reward_delivered',
    optionKey: 'growfund_admin_email_reward_delivered',
    label: __('Rewards Delivered', 'growfund'),
  },
  {
    key: 'admin_password_reset_request',
    optionKey: 'growfund_admin_email_password_reset_request',
    label: __('Password Reset Request', 'growfund'),
  },
  {
    key: 'admin_withdrawal_request_received',
    optionKey: 'growfund_admin_email_withdrawal_request_received',
    label: __('Withdrawal Request', 'growfund'),
  },

  // Fundraiser Emails
  {
    key: 'fundraiser_new_user_registration',
    optionKey: 'growfund_fundraiser_email_new_user_registration',
    label: __('New User Registration', 'growfund'),
  },
  {
    key: 'fundraiser_account_approved',
    optionKey: 'growfund_fundraiser_email_account_approved',
    label: __('Account Approved', 'growfund'),
  },
  {
    key: 'fundraiser_account_declined',
    optionKey: 'growfund_fundraiser_email_account_declined',
    label: __('Account Declined', 'growfund'),
  },
  {
    key: 'fundraiser_new_donation',
    optionKey: 'growfund_fundraiser_email_new_donation',
    label: __('New Donation', 'growfund'),
  },
  {
    key: 'fundraiser_donation_amount_charged',
    optionKey: 'growfund_fundraiser_email_donation_amount_charged',
    label: __('Donation Charged', 'growfund'),
  },
  {
    key: 'fundraiser_donation_cancelled',
    optionKey: 'growfund_fundraiser_email_donation_cancelled',
    label: __('Donation Cancelled', 'growfund'),
  },
  {
    key: 'fundraiser_new_pledge',
    optionKey: 'growfund_fundraiser_email_new_pledge',
    label: __('New Pledge', 'growfund'),
  },
  {
    key: 'fundraiser_pledge_amount_charged',
    optionKey: 'growfund_fundraiser_email_pledge_amount_charged',
    label: __('Pledge Amount Charged', 'growfund'),
  },
  {
    key: 'fundraiser_pledge_cancelled',
    optionKey: 'growfund_fundraiser_email_pledge_cancelled',
    label: __('Pledge Cancelled', 'growfund'),
  },
  {
    key: 'fundraiser_password_reset_request',
    optionKey: 'growfund_fundraiser_email_password_reset_request',
    label: __('Password Reset Request', 'growfund'),
  },
  {
    key: 'fundraiser_campaign_approved',
    optionKey: 'growfund_fundraiser_email_campaign_approved',
    label: __('Campaign Approved', 'growfund'),
  },
  {
    key: 'fundraiser_campaign_declined',
    optionKey: 'growfund_fundraiser_email_campaign_declined',
    label: __('Campaign Declined', 'growfund'),
  },
  {
    key: 'fundraiser_campaign_post_update',
    optionKey: 'growfund_fundraiser_email_campaign_post_update',
    label: __('Campaign Update', 'growfund'),
  },
  {
    key: 'fundraiser_campaign_funded',
    optionKey: 'growfund_fundraiser_email_campaign_funded',
    label: __('Campaign Funded', 'growfund'),
  },
  {
    key: 'fundraiser_new_offline_donation',
    optionKey: 'growfund_fundraiser_email_new_offline_donation',
    label: __('New Offline Donation', 'growfund'),
  },
  {
    key: 'fundraiser_new_offline_pledge',
    optionKey: 'growfund_fundraiser_email_new_offline_pledge',
    label: __('New Offline Pledge', 'growfund'),
  },
  {
    key: 'fundraiser_reward_delivered',
    optionKey: 'growfund_fundraiser_email_reward_delivered',
    label: __('Rewards Delivered', 'growfund'),
  },
  {
    key: 'fundraiser_withdrawal_request_accepted',
    optionKey: 'growfund_fundraiser_email_withdrawal_request_accepted',
    label: __('Withdrawal Request Accepted', 'growfund'),
  },
  {
    key: 'fundraiser_withdrawal_request_rejected',
    optionKey: 'growfund_fundraiser_email_withdrawal_request_rejected',
    label: __('Withdrawal Request Rejected', 'growfund'),
  },

  // Backer Emails
  {
    key: 'backer_pledge_created',
    optionKey: 'growfund_backer_email_pledge_created',
    label: __('New Pledge', 'growfund'),
  },
  {
    key: 'backer_pledge_paid_with_giving_thanks',
    optionKey: 'growfund_backer_email_pledge_paid_with_giving_thanks',
    label: __('Pledge Paid with Giving Thanks', 'growfund'),
  },
  {
    key: 'backer_offline_pledge_request',
    optionKey: 'growfund_backer_email_offline_pledge_request',
    label: __('Offline Pledge Request', 'growfund'),
  },
  {
    key: 'backer_pledge_paid',
    optionKey: 'growfund_backer_email_pledge_paid',
    label: __('Pledge Paid', 'growfund'),
  },
  {
    key: 'backer_send_local_pickup_instructions',
    optionKey: 'growfund_backer_email_send_local_pickup_instructions',
    label: __('Send Local Pickup Instruction', 'growfund'),
  },
  {
    key: 'backer_campaign_half_funded',
    optionKey: 'growfund_backer_email_campaign_half_funded',
    label: __('Campaign Half Funded', 'growfund'),
  },
  {
    key: 'backer_new_backer_registration',
    optionKey: 'growfund_backer_email_new_backer_registration',
    label: __('New Backer Registration', 'growfund'),
  },
  {
    key: 'backer_campaign_post_update',
    optionKey: 'growfund_backer_email_campaign_post_update',
    label: __('Campaign Update Email', 'growfund'),
  },
  {
    key: 'backer_payment_unsuccessful',
    optionKey: 'growfund_backer_email_payment_unsuccessful',
    label: __('Payment Unsuccessful', 'growfund'),
  },
  {
    key: 'backer_password_reset_request',
    optionKey: 'growfund_backer_email_password_reset_request',
    label: __('Password Reset Request', 'growfund'),
  },
  {
    key: 'backer_pledge_cancelled',
    optionKey: 'growfund_backer_email_pledge_cancelled',
    label: __('Pledge Cancelled', 'growfund'),
  },
  {
    key: 'backer_reward_delivered',
    optionKey: 'growfund_backer_email_reward_delivered',
    label: __('Rewards Delivered', 'growfund'),
  },

  // Donor Emails
  {
    key: 'donor_donation_receipt',
    optionKey: 'growfund_donor_email_donation_receipt',
    label: __('Donation Receipt', 'growfund'),
  },
  {
    key: 'donor_donation_failed',
    optionKey: 'growfund_donor_email_donation_failed',
    label: __('Donation Failed', 'growfund'),
  },
  {
    key: 'donor_new_donor_registration',
    optionKey: 'growfund_donor_email_new_donor_registration',
    label: __('New Donor Registration', 'growfund'),
  },
  {
    key: 'donor_password_reset_request',
    optionKey: 'growfund_donor_email_password_reset_request',
    label: __('Password Reset Request', 'growfund'),
  },
  {
    key: 'donor_offline_donation_instructions',
    optionKey: 'growfund_donor_email_offline_donation_instructions',
    label: __('Offline Donation Instructions', 'growfund'),
  },
  {
    key: 'donor_campaign_post_update',
    optionKey: 'growfund_donor_email_campaign_post_update',
    label: __('Campaign Update', 'growfund'),
  },
  {
    key: 'donor_campaign_half_milestone_achieved',
    optionKey: 'growfund_donor_email_campaign_half_milestone_achieved',
    label: __('50% Milestone Achieved', 'growfund'),
  },
  {
    key: 'donor_tribute_mail',
    optionKey: 'growfund_donor_email_tribute_mail',
    label: __('Tribute Mail', 'growfund'),
  },
];

const ShortcodeSchema = z.object({
  label: z.string(),
  value: z.string(),
});

const EmailContentSchema = z.object({
  subject: z.string(),
  heading: z.string().nullish(),
  message: z.string(),
  is_default: z.boolean().nullish(),
  shortcodes: z.array(ShortcodeSchema).nullish(),
  subject_shortcodes: z.array(ShortcodeSchema).nullish(),
});

const EmailContentFormSchema = EmailContentSchema.omit({ is_default: true });

type EmailContentForm = z.infer<typeof EmailContentFormSchema>;
type EmailContentPayload = z.infer<typeof EmailContentFormSchema>;
type EmailContent = z.infer<typeof EmailContentSchema>;

export {
  EmailContentFormSchema,
  type EmailContent,
  type EmailContentForm,
  type EmailContentPayload,
};

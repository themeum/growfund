import { __ } from '@wordpress/i18n';
import { useFormContext } from 'react-hook-form';
import { useNavigate } from 'react-router';

import ElementWrapper from '@/components/element-wrapper';
import { SwitchField } from '@/components/form/switch-field';
import { ProSwitchInput } from '@/components/pro-fallbacks/form/pro-switch-input';
import { RouteConfig } from '@/config/route-config';
import { useAppConfig } from '@/contexts/app-config';
import { type EmailTemplateConfig } from '@/features/settings/schemas/email-content';
import { type EmailSettingsForm } from '@/features/settings/schemas/settings';
import { registry } from '@/lib/registry';

const AdminEmailSwitchFields = () => {
  const navigate = useNavigate();
  const { isDonationMode } = useAppConfig();
  const form = useFormContext<EmailSettingsForm>();

  const navigateOnEdit = async (emailKey: EmailTemplateConfig['key']) => {
    await navigate(
      RouteConfig.EmailContents.buildLink({
        userType: 'admin',
        emailType: emailKey.replace(/^admin_/, '').replace(/_/g, '-'),
      }),
    );
  };

  const AdminEmailCampaignSubmittedForReviewSwitchField = registry.get(
    'AdminEmailCampaignSubmittedForReviewSwitchField',
  );
  const AdminEmailCampaignPostUpdateSwitchField = registry.get(
    'AdminEmailCampaignPostUpdateSwitchField',
  );
  const AdminEmailWithdrawalRequestSwitchField = registry.get(
    'AdminEmailWithdrawalRequestSwitchField',
  );

  return (
    <>
      <ElementWrapper
        fallback={
          <ProSwitchInput
            label={__('New Campaign Submitted for Review', 'growfund')}
            description={__(
              'Get notified when a fundraiser submits a new campaign for review.',
              'growfund',
            )}
            className="growfund-p-2"
            showProBadge
          />
        }
      >
        {AdminEmailCampaignSubmittedForReviewSwitchField && (
          <AdminEmailCampaignSubmittedForReviewSwitchField />
        )}
      </ElementWrapper>
      <SwitchField
        control={form.control}
        name="is_enabled_admin_email_campaign_funded"
        label={__('Campaign Funded', 'growfund')}
        description={__('Get notified when a fundraising campaign ends.', 'growfund')}
        allowEdit
        onEdit={() => navigateOnEdit('admin_campaign_funded')}
        allowHoverEffect
      />
      <SwitchField
        control={form.control}
        name="is_enabled_admin_email_new_user_registration"
        label={__('New User Registration', 'growfund')}
        description={__('Receive an email when a new user registers.', 'growfund')}
        allowEdit
        onEdit={() => navigateOnEdit('admin_new_user_registration')}
        allowHoverEffect
      />
      <ElementWrapper
        fallback={
          <ProSwitchInput
            label={__('Campaign Update', 'growfund')}
            description={__('Get notified when an update is posted in a campaign.', 'growfund')}
            className="growfund-p-2"
            showProBadge
          />
        }
      >
        {AdminEmailCampaignPostUpdateSwitchField && <AdminEmailCampaignPostUpdateSwitchField />}
      </ElementWrapper>
      {isDonationMode ? (
        <>
          <SwitchField
            control={form.control}
            name="is_enabled_admin_email_new_donation"
            label={__('New Donation', 'growfund')}
            description={__('Alert the fundraiser when a new donation is received.', 'growfund')}
            allowEdit
            onEdit={() => navigateOnEdit('admin_new_donation')}
            allowHoverEffect
          />
          <SwitchField
            control={form.control}
            name="is_enabled_admin_email_new_offline_donation"
            label={__('New Offline Donation', 'growfund')}
            description={__('Notify the fundraiser about a new offline donation.', 'growfund')}
            allowEdit
            onEdit={() => navigateOnEdit('admin_new_offline_donation')}
            allowHoverEffect
          />
        </>
      ) : (
        <>
          <SwitchField
            control={form.control}
            name="is_enabled_admin_email_new_pledge"
            label={__('New Pledge', 'growfund')}
            description={__('Alert the fundraiser when a new pledge is received.', 'growfund')}
            allowEdit
            onEdit={() => navigateOnEdit('admin_new_pledge')}
            allowHoverEffect
          />
          <SwitchField
            control={form.control}
            name="is_enabled_admin_email_new_offline_pledge"
            label={__('New Offline Pledge', 'growfund')}
            description={__('Notify the fundraiser about a new offline pledge.', 'growfund')}
            allowEdit
            onEdit={() => navigateOnEdit('admin_new_offline_pledge')}
            allowHoverEffect
          />
        </>
      )}

      <SwitchField
        control={form.control}
        name="is_enabled_admin_email_campaign_ended"
        label={__('Campaign Ended Successfully', 'growfund')}
        description={__('Notify the fundraiser when their campaign reaches its goal.', 'growfund')}
        allowEdit
        onEdit={() => navigateOnEdit('admin_campaign_ended')}
        allowHoverEffect
      />
      {!isDonationMode && (
        <SwitchField
          control={form.control}
          name="is_enabled_admin_email_reward_delivered"
          label={__('Rewards Delivered', 'growfund')}
          description={__('Get notified when rewards are delivered to the backer.', 'growfund')}
          allowEdit
          onEdit={() => navigateOnEdit('admin_reward_delivered')}
          allowHoverEffect
        />
      )}
      <ElementWrapper
        fallback={
          <ProSwitchInput
            label={__('Withdrawal Request', 'growfund')}
            description={__('Get notified when a withdrawal request is submitted.', 'growfund')}
            className="growfund-p-2"
            showProBadge
          />
        }
      >
        {AdminEmailWithdrawalRequestSwitchField && <AdminEmailWithdrawalRequestSwitchField />}
      </ElementWrapper>
    </>
  );
};

export default AdminEmailSwitchFields;

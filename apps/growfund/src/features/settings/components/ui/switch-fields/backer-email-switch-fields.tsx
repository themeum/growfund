import { __ } from '@wordpress/i18n';
import { useFormContext } from 'react-hook-form';
import { useNavigate } from 'react-router';

import FeatureGuard from '@/components/feature-guard';
import { SwitchField } from '@/components/form/switch-field';
import { ProSwitchInput } from '@/components/pro-fallbacks/form/pro-switch-input';
import { RouteConfig } from '@/config/route-config';
import { type EmailTemplateConfig } from '@/features/settings/schemas/email-content';
import { type EmailSettingsForm } from '@/features/settings/schemas/settings';
import { registry } from '@/lib/registry';

const BackerEmailSwitchFields = () => {
  const navigate = useNavigate();
  const form = useFormContext<EmailSettingsForm>();

  const navigateOnEdit = async (emailKey: EmailTemplateConfig['key']) => {
    await navigate(
      RouteConfig.EmailContents.buildLink({
        userType: 'backer',
        emailType: emailKey.replace(/^backer_/, '').replace(/_/g, '-'),
      }),
    );
  };

  const BackerEmailCampaignPostUpdateSwitchField = registry.get(
    'BackerEmailCampaignPostUpdateSwitchField',
  );
  const BackerEmailHalfFundedSwitchField = registry.get('BackerEmailHalfFundedSwitchField');

  return (
    <>
      <SwitchField
        control={form.control}
        name="is_enabled_backer_email_pledge_created"
        label={__('New Pledge', 'growfund')}
        description={__('Send a confirmation receipt when a pledge includes rewards.', 'growfund')}
        allowEdit
        onEdit={() => navigateOnEdit('backer_pledge_created')}
        allowHoverEffect
      />
      <SwitchField
        control={form.control}
        name="is_enabled_backer_email_offline_pledge_request"
        label={__('Offline Pledge Request', 'growfund')}
        description={__('Send a receipt to request a pledge.', 'growfund')}
        allowEdit
        onEdit={() => navigateOnEdit('backer_offline_pledge_request')}
        allowHoverEffect
      />
      <SwitchField
        control={form.control}
        name="is_enabled_backer_email_pledge_paid"
        label={__('Pledge Paid', 'growfund')}
        description={__('Confirms that a pledge is paid.', 'growfund')}
        allowEdit
        onEdit={() => navigateOnEdit('backer_pledge_paid')}
        allowHoverEffect
      />
      <SwitchField
        control={form.control}
        name="is_enabled_backer_email_pledge_paid_with_giving_thanks"
        label={__('Pledge Paid With Giving Thanks', 'growfund')}
        description={__(
          'Send a receipt confirming the pledge payment with giving thanks.',
          'growfund',
        )}
        allowEdit
        onEdit={() => navigateOnEdit('backer_pledge_paid_with_giving_thanks')}
        allowHoverEffect
      />
      <FeatureGuard
        feature="settings.email_notifications.backer_email_campaign_half_funded"
        fallback={
          <ProSwitchInput
            label={__('Campaign 50% funded', 'growfund')}
            description={__('Notifies the backer when the campaign is 50% funded.', 'growfund')}
            className="growfund-p-2"
            showProBadge
          />
        }
      >
        {BackerEmailHalfFundedSwitchField && <BackerEmailHalfFundedSwitchField />}
      </FeatureGuard>
      <SwitchField
        control={form.control}
        name="is_enabled_backer_email_new_backer_registration"
        label={__('New Backer Registration', 'growfund')}
        description={__('Confirms that a backer has registered in your site.', 'growfund')}
        allowEdit
        onEdit={() => navigateOnEdit('backer_new_backer_registration')}
        allowHoverEffect
      />
      <FeatureGuard
        feature="settings.email_notifications.backer_email_campaign_post_update"
        fallback={
          <ProSwitchInput
            label={__('Campaign Updates', 'growfund')}
            description={__('Notify backers when an update is posted to a campaign.', 'growfund')}
            className="growfund-p-2"
            showProBadge
          />
        }
      >
        {BackerEmailCampaignPostUpdateSwitchField && <BackerEmailCampaignPostUpdateSwitchField />}
      </FeatureGuard>
      <SwitchField
        control={form.control}
        name="is_enabled_backer_email_payment_unsuccessful"
        label={__('Payment Unsuccessful', 'growfund')}
        description={__('Notifies the backer when a pledge has failed.', 'growfund')}
        allowEdit
        onEdit={() => navigateOnEdit('backer_payment_unsuccessful')}
        allowHoverEffect
      />
      <SwitchField
        control={form.control}
        name="is_enabled_backer_email_password_reset_request"
        label={__('Password Reset Request', 'growfund')}
        description={__(
          'Confirms that a backer has requested to reset their password.',
          'growfund',
        )}
        allowEdit
        onEdit={() => navigateOnEdit('backer_password_reset_request')}
        allowHoverEffect
        hideToggle
      />
      <SwitchField
        control={form.control}
        name="is_enabled_backer_email_pledge_cancelled"
        label={__('Pledge Cancelled', 'growfund')}
        description={__('Notifies the backer the pledge has been cancelled.', 'growfund')}
        allowEdit
        onEdit={() => navigateOnEdit('backer_pledge_cancelled')}
        allowHoverEffect
      />
      <SwitchField
        control={form.control}
        name="is_enabled_backer_email_reward_delivered"
        label={__('Rewards Delivered', 'growfund')}
        description={__('Confirms from the user that the rewards have been delivered.', 'growfund')}
        allowEdit
        onEdit={() => navigateOnEdit('backer_reward_delivered')}
        allowHoverEffect
      />
    </>
  );
};

export default BackerEmailSwitchFields;

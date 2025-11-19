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

const DonorEmailSwitchFields = () => {
  const navigate = useNavigate();
  const form = useFormContext<EmailSettingsForm>();

  const navigateOnEdit = async (emailType: EmailTemplateConfig['key']) => {
    await navigate(
      RouteConfig.EmailContents.buildLink({
        userType: 'donor',
        emailType: emailType.replace(/^donor_/, '').replace(/_/g, '-'),
      }),
    );
  };

  const DonorEmailCampaignPostUpdateSwitchField = registry.get(
    'DonorEmailCampaignPostUpdateSwitchField',
  );
  const DonorEmailHalfMilestoneSwitchField = registry.get('DonorEmailHalfMilestoneSwitchField');
  const DonorEmailTributeSwitchField = registry.get('DonorEmailTributeSwitchField');

  return (
    <>
      <SwitchField
        control={form.control}
        name="is_enabled_donor_email_donation_receipt"
        label={__('Donation Receipt', 'growfund')}
        description={__('Send a receipt after a donation has been made.', 'growfund')}
        allowEdit
        onEdit={() => navigateOnEdit('donor_donation_receipt')}
        allowHoverEffect
      />
      <SwitchField
        control={form.control}
        name="is_enabled_donor_email_donation_failed"
        label={__('Donation Failed', 'growfund')}
        description={__('Inform donors when a donation attempt fails.', 'growfund')}
        allowEdit
        onEdit={() => navigateOnEdit('donor_donation_failed')}
        allowHoverEffect
      />
      <SwitchField
        control={form.control}
        name="is_enabled_donor_email_new_donor_registration"
        label={__('New Donor Registration', 'growfund')}
        description={__('Welcome new donors and confirm their registration.', 'growfund')}
        allowEdit
        onEdit={() => navigateOnEdit('donor_new_donor_registration')}
        allowHoverEffect
      />
      <SwitchField
        control={form.control}
        name="is_enabled_donor_email_password_reset_request"
        label={__('Reset Password', 'growfund')}
        description={__('Receive an email when a password reset is requested.', 'growfund')}
        allowEdit
        hideToggle
        onEdit={() => navigateOnEdit('donor_password_reset_request')}
        allowHoverEffect
      />
      <SwitchField
        control={form.control}
        name="is_enabled_donor_email_offline_donation_instructions"
        label={__('Offline Donation Instructions', 'growfund')}
        description={__('Provide donors with instructions for offline donations', 'growfund')}
        allowEdit
        onEdit={() => navigateOnEdit('donor_offline_donation_instructions')}
        allowHoverEffect
      />
      <FeatureGuard
        feature="settings.email_notifications.donor_email_campaign_post_update"
        fallback={
          <ProSwitchInput
            label={__('Campaign Updates', 'growfund')}
            description={__('Notify donors when an update is posted to a campaign.', 'growfund')}
            className="growfund-p-2"
            showProBadge
          />
        }
      >
        {DonorEmailCampaignPostUpdateSwitchField && <DonorEmailCampaignPostUpdateSwitchField />}
      </FeatureGuard>
      <FeatureGuard
        feature="settings.email_notifications.donor_email_campaign_half_milestone_achieved"
        fallback={
          <ProSwitchInput
            label={__('50% Milestone Achieved', 'growfund')}
            description={__(
              'Celebrate and inform donors when a campaign reaches 50% of its goal.',
              'growfund',
            )}
            className="growfund-p-2"
            showProBadge
          />
        }
      >
        {DonorEmailHalfMilestoneSwitchField && <DonorEmailHalfMilestoneSwitchField />}
      </FeatureGuard>
      <FeatureGuard
        feature="settings.email_notifications.donor_email_tribute_mail"
        fallback={
          <ProSwitchInput
            label={__('Tribute Mail', 'growfund')}
            description={__(
              'The recipient of the tribute notification will get a link to their eCard.',
              'growfund',
            )}
            className="growfund-p-2"
            showProBadge
          />
        }
      >
        {DonorEmailTributeSwitchField && <DonorEmailTributeSwitchField />}
      </FeatureGuard>
    </>
  );
};

export default DonorEmailSwitchFields;

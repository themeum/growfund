import { EnvelopeClosedIcon } from '@radix-ui/react-icons';
import { __ } from '@wordpress/i18n';

import FeatureGuard from '@/components/feature-guard';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import BackerNotificationSettingsForm from '@/dashboards/backers/components/backer-notification-settings-form';
import DonorNotificationSettingsForm from '@/dashboards/donors/components/donor-notification-settings-form';
import { useUserSettingsContext } from '@/dashboards/shared/contexts/user-settings-context';
import { registry } from '@/lib/registry';

const UserNotificationsPage = () => {
  const { user } = useUserSettingsContext();

  if (!user) {
    return null;
  }

  const FundraiserNotificationSettingsForm = registry.get('FundraiserNotificationSettingsForm');

  return (
    <div className="growfund-w-full growfund-space-y-3">
      <p className="growfund-typo-small growfund-font-semibold growfund-text-fg-primary growfund-mt-2">
        {__('Notifications', 'growfund')}
      </p>
      <Card>
        <CardHeader>
          <CardTitle className="growfund-flex growfund-items-center growfund-gap-2">
            <EnvelopeClosedIcon />
            {__('Email Notifications', 'growfund')}
          </CardTitle>
          <CardDescription>
            {__('Choose which email notifications you want to receive.', 'growfund')}
          </CardDescription>
        </CardHeader>
        <CardContent>
          {user.active_role === 'growfund_backer' && <BackerNotificationSettingsForm />}
          {user.active_role === 'growfund_donor' && <DonorNotificationSettingsForm />}
          {user.active_role === 'growfund_fundraiser' && (
            <FeatureGuard feature="fundraisers">
              {FundraiserNotificationSettingsForm && <FundraiserNotificationSettingsForm />}
            </FeatureGuard>
          )}
        </CardContent>
      </Card>
    </div>
  );
};

export default UserNotificationsPage;

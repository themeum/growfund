import { __ } from '@wordpress/i18n';

import { ProSwitchInput } from '@/components/pro-fallbacks/form/pro-switch-input';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { ProBadge } from '@/components/ui/pro-badge';

const UserPermissionsFundraiserSettingsFallback = () => {
  return (
    <Card>
      <CardHeader>
        <CardTitle>
          {__('Fundraiser', 'growfund')} <ProBadge />
        </CardTitle>
        <CardDescription>
          {__('Manage fundraiser permissions and controls.', 'growfund')}
        </CardDescription>
      </CardHeader>
      <CardContent className="growfund-space-y-4">
        <ProSwitchInput
          label={__('Publishing Campaigns', 'growfund')}
          description={__('Toggle to allow direct campaign publishing.', 'growfund')}
        />
        <ProSwitchInput
          label={__('Campaign Deletion', 'growfund')}
          description={__('Toggle to allow deleting a campaign permanently.', 'growfund')}
        />
      </CardContent>
    </Card>
  );
};

export default UserPermissionsFundraiserSettingsFallback;

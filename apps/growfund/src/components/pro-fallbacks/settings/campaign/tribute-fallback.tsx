import { __ } from '@wordpress/i18n';

import { ProSwitchInput } from '@/components/pro-fallbacks/form/pro-switch-input';
import { Card, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { ProBadge } from '@/components/ui/pro-badge';

const CampaignSettingsTributeFallback = () => {
  return (
    <Card>
      <CardHeader>
        <div className="growfund-flex growfund-items-center growfund-justify-between">
          <CardTitle>
            {__('Tribute', 'growfund')} <ProBadge />
          </CardTitle>
          <ProSwitchInput />
        </div>
        <CardDescription>
          {__(
            'Choose whether tribute functionality will be available on all campaigns by default.',
            'growfund',
          )}
        </CardDescription>
      </CardHeader>
    </Card>
  );
};

export default CampaignSettingsTributeFallback;

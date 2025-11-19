import { __ } from '@wordpress/i18n';

import { ProSwitchInput } from '@/components/pro-fallbacks/form/pro-switch-input';
import { Card, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { ProBadge } from '@/components/ui/pro-badge';

const CampaignSettingsEnableCommentsFallback = () => {
  return (
    <Card>
      <CardHeader>
        <div className="growfund-flex growfund-items-center growfund-justify-between">
          <CardTitle>
            {__('Enable Comments on Campaign', 'growfund')} <ProBadge />
          </CardTitle>
          <ProSwitchInput />
        </div>
        <CardDescription>
          {__('If enabled, users can add comments to campaign.', 'growfund')}
        </CardDescription>
      </CardHeader>
    </Card>
  );
};

export default CampaignSettingsEnableCommentsFallback;

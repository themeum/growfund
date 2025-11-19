import { __ } from '@wordpress/i18n';

import { ProSwitchInput } from '@/components/pro-fallbacks/form/pro-switch-input';
import { Card, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { ProBadge } from '@/components/ui/pro-badge';

const PaymentSettingsAdminCommissionFallback = () => {
  return (
    <Card>
      <CardHeader>
        <div className="growfund-flex growfund-items-center growfund-justify-between">
          <CardTitle>
            {__(`Admin's Commission`, 'growfund')} <ProBadge />
          </CardTitle>
          <ProSwitchInput />
        </div>
        <CardDescription>
          {__('Define how much the site-admin receives from each campaign.', 'growfund')}
        </CardDescription>
      </CardHeader>
    </Card>
  );
};

export default PaymentSettingsAdminCommissionFallback;

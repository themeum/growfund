import { __ } from '@wordpress/i18n';

import { ProSwitchInput } from '@/components/pro-fallbacks/form/pro-switch-input';
import { Card, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { ProBadge } from '@/components/ui/pro-badge';

const PaymentSettingsGuestCheckoutFallback = () => {
  return (
    <Card>
      <CardHeader>
        <div className="growfund-flex growfund-items-center growfund-justify-between">
          <CardTitle>{__('Guest Checkout', 'growfund')} <ProBadge /></CardTitle>
          <ProSwitchInput />
        </div>
        <CardDescription>
          {__('Allow users to donate without creating an account.', 'growfund')}
        </CardDescription>
      </CardHeader>
    </Card>
  );
};

export default PaymentSettingsGuestCheckoutFallback;

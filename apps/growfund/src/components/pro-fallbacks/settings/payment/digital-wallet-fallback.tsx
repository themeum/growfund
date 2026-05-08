import { __ } from '@wordpress/i18n';
import { useFormContext } from 'react-hook-form';

import { CheckboxField } from '@/components/form/checkbox-field';
import { TextField } from '@/components/form/text-field';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import { ProBadge } from '@/components/ui/pro-badge';
import { type PaymentSettingsForm } from '@/features/settings/schemas/settings';
import { useCurrency } from '@/hooks/use-currency';

const PaymentSettingsDigitalWalletFallback = () => {
  const { toCurrency } = useCurrency();
  const form = useFormContext<PaymentSettingsForm>();

  return (
    <Card>
      <CardHeader>
        <div className="growfund-flex growfund-items-center">
          <CardTitle>
            {__('Wallet', 'growfund-pro')} <ProBadge />
          </CardTitle>
        </div>
        <CardDescription>{__('Wallet description goes here.', 'growfund-pro')}</CardDescription>
      </CardHeader>
      <CardContent className="growfund-space-y-4">
        <TextField
          control={form.control}
          type="number"
          name="minimum_balance_to_request_withdrawal"
          label={__('Minimum Withdrawal Amount', 'growfund-pro')}
          placeholder={`e.g. ${toCurrency(50)}`}
          disabled
        />

        <div className="growfund-space-y-2">
          <Label>{__('Fundraiser withdrawal options', 'growfund-pro')}</Label>
          <div className="growfund-flex growfund-items-center growfund-gap-2 growfund-w-full">
            <CheckboxField
              control={form.control}
              name="fundraiser_withdrawal_options.is_active_paypal"
              label={__('PayPal', 'growfund-pro')}
              wrapperClassName="growfund-w-auto"
              disabled
            />
            <CheckboxField
              control={form.control}
              name="fundraiser_withdrawal_options.is_active_bank_transfer"
              label={__('Bank Transfer', 'growfund-pro')}
              wrapperClassName="growfund-w-auto"
              disabled
            />
            <CheckboxField
              control={form.control}
              name="fundraiser_withdrawal_options.is_active_others"
              label={__('Others', 'growfund-pro')}
              wrapperClassName="growfund-w-auto"
              disabled
            />
          </div>
        </div>
      </CardContent>
    </Card>
  );
};

export default PaymentSettingsDigitalWalletFallback;

import { __ } from '@wordpress/i18n';

import { ProMultiSelectInput } from '@/components/pro-fallbacks/form/pro-multiselect-input';
import { ProRadioInput } from '@/components/pro-fallbacks/form/pro-radio-input';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { ProBadge } from '@/components/ui/pro-badge';

const GeneralSettingsDonationFormOptionsFallback = () => {
  return (
    <Card>
      <CardHeader>
        <CardTitle>
          {__('Donation Form Options', 'growfund')} <ProBadge />
        </CardTitle>
        <CardDescription>
          {__('Manage the fields available for all donation campaigns.', 'growfund')}
        </CardDescription>
      </CardHeader>
      <CardContent className="growfund-space-y-4">
        <ProRadioInput
          label={__('Company Field', 'growfund')}
          inline
          options={[
            __('Disabled', 'growfund'),
            __('Optional', 'growfund'),
            __('Required', 'growfund'),
          ]}
        />
        <ProRadioInput
          label={__('Name Title Prefix', 'growfund')}
          inline
          options={[
            __('Disabled', 'growfund'),
            __('Optional', 'growfund'),
            __('Required', 'growfund'),
          ]}
        />
        <ProMultiSelectInput
          label={__('Salutations', 'growfund')}
          placeholder={__('Type to add salutations...', 'growfund')}
          options={['Mr.', 'Mrs.', 'Ms.', 'Respected.', 'Dr.']}
        />
      </CardContent>
    </Card>
  );
};

export default GeneralSettingsDonationFormOptionsFallback;

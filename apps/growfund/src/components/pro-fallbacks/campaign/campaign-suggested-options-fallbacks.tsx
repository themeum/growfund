import { __ } from '@wordpress/i18n';
import { useFormContext } from 'react-hook-form';

import { RadioField } from '@/components/form/radio-field';
import { type CampaignBuilderForm } from '@/features/campaigns/schemas/campaign';

const CampaignSuggestedOptionsFallback = () => {
  const form = useFormContext<CampaignBuilderForm>();
  return (
    <RadioField
      control={form.control}
      name="suggested_option_type"
      label={__('Suggested Options', 'growfund')}
      inline
      options={[
        {
          value: 'amount-only',
          label: __('Amount Only', 'growfund'),
        },
      ]}
      featureOptions={[__('Amount & Description', 'growfund')]}
    />
  );
};

export default CampaignSuggestedOptionsFallback;

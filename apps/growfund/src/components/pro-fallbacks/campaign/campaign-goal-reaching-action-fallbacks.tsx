import { __ } from '@wordpress/i18n';
import { useFormContext } from 'react-hook-form';

import { RadioField } from '@/components/form/radio-field';
import { useAppConfig } from '@/contexts/app-config';
import { type CampaignBuilderForm } from '@/features/campaigns/schemas/campaign';

const CampaignGoalReachingActionFallback = () => {
  const { isDonationMode } = useAppConfig();
  const form = useFormContext<CampaignBuilderForm>();
  return (
    <RadioField
      control={form.control}
      name="reaching_action"
      label={
        isDonationMode
          ? __('When donation is reached the goal', 'growfund')
          : __('When pledge is reached the goal', 'growfund')
      }
      inline
      options={[
        {
          value: 'close',
          label: __('Auto close campaign', 'growfund'),
        },
      ]}
      featureOptions={[
        isDonationMode
          ? __('Keep receiving donations', 'growfund')
          : __('Keep receiving pledges', 'growfund'),
      ]}
    />
  );
};

export default CampaignGoalReachingActionFallback;

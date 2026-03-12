import { __ } from '@wordpress/i18n';
import { type UseFormReturn } from 'react-hook-form';

import { SelectField } from '@/components/form/select-field';
import { Box, BoxContent } from '@/components/ui/box';
import { type Reward } from '@/features/campaigns/schemas/reward';
import { type PledgeForm } from '@/features/pledges/schemas/pledge-form';
import { isDefined } from '@/utils';

interface ShippingMethodCardProps {
  form?: UseFormReturn<PledgeForm>;
  reward: Reward;
  deliveryOption: 'home-delivery' | 'local-pickup';
}

const ShippingMethodCard = ({ form, reward, deliveryOption }: ShippingMethodCardProps) => {
  if (!reward.allow_local_pickup) {
    return null;
  }

  return isDefined(form) ? (
    <Box>
      <BoxContent>
        <h6 className="growfund-typo-h6 growfund-font-medium growfund-text-fg-primary growfund-mb-3">
          {__('Delivery Option', 'growfund')}
        </h6>
        <SelectField
          control={form.control}
          name={'delivery_option'}
          options={[
            {
              value: 'home-delivery',
              label: __('Home Delivery', 'growfund'),
            },
            {
              value: 'local-pickup',
              label: __('Local Pickup', 'growfund'),
            },
          ]}
          placeholder={__('Select Delivery Method', 'growfund')}
        />
        {deliveryOption === 'local-pickup' && (
          <div
            className="growfund-rich-text-content growfund-mt-3 growfund-bg-background-surface-secondary growfund-rounded-sm growfund-p-4 growfund-typo-small growfund-text-fg-secondary"
            dangerouslySetInnerHTML={{ __html: reward.local_pickup_instructions ?? '' }}
          />
        )}
      </BoxContent>
    </Box>
  ) : (
    deliveryOption === 'local-pickup' && (
      <Box>
        <BoxContent>
          <div className="growfund-typo-small growfund-text-fg-primary growfund-mb-3">
            <div className="growfund-typo-small growfund-text-fg-primary growfund-mb-3">
              {__('Local Pickup', 'growfund')}
            </div>
          </div>
          <div
            className="growfund-rich-text-content growfund-mt-3 growfund-bg-background-surface-secondary growfund-rounded-sm growfund-p-4 growfund-typo-small growfund-text-fg-secondary"
            dangerouslySetInnerHTML={{ __html: reward.local_pickup_instructions ?? '' }}
          />
        </BoxContent>
      </Box>
    )
  );
};

export default ShippingMethodCard;

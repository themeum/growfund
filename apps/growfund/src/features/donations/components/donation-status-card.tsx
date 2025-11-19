import { __ } from '@wordpress/i18n';
import { useFormContext } from 'react-hook-form';

import { SelectField } from '@/components/form/select-field';
import { Box, BoxContent } from '@/components/ui/box';
import { type DonationForm } from '@/features/donations/schemas/donation-form';

const DonationStatusCard = () => {
  const form = useFormContext<DonationForm>();
  return (
    <Box>
      <BoxContent>
        <h6 className="growfund-typo-h6 growfund-font-medium growfund-text-fg-primary growfund-mb-3">
          {__('Status', 'growfund')}
        </h6>
        <SelectField
          control={form.control}
          name="status"
          options={[
            {
              label: __('Pending', 'growfund'),
              value: 'pending',
            },
            {
              label: __('Completed', 'growfund'),
              value: 'completed',
            },
            {
              label: __('Failed', 'growfund'),
              value: 'failed',
            },
            {
              label: __('Cancelled', 'growfund'),
              value: 'cancelled',
            },
          ]}
        />
      </BoxContent>
    </Box>
  );
};

export default DonationStatusCard;

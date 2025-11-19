import { __ } from '@wordpress/i18n';
import { useFormContext } from 'react-hook-form';

import { TextField } from '@/components/form/text-field';
import { Box, BoxContent } from '@/components/ui/box';
import { type PledgeForm } from '@/features/pledges/schemas/pledge-form';

const PledgeForGivingThanks = () => {
  const form = useFormContext<PledgeForm>();
  return (
    <Box>
      <BoxContent className="growfund-p-4">
        <TextField
          type="number"
          control={form.control}
          name="amount"
          label={__('Pledge Amount', 'growfund')}
          placeholder="0"
        />
      </BoxContent>
    </Box>
  );
};

export default PledgeForGivingThanks;

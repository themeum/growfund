import { __ } from '@wordpress/i18n';
import { Gift, Plus } from 'lucide-react';
import { useState } from 'react';
import { useFormContext } from 'react-hook-form';

import { Box, BoxContent } from '@/components/ui/box';
import { Button } from '@/components/ui/button';
import { FormControl, FormField, FormItem } from '@/components/ui/form';
import { type Reward } from '@/features/campaigns/schemas/reward';
import RewardSelectionDialog, {
    RewardSelectionDialogTrigger,
} from '@/features/pledges/components/dialogs/reward-selection-dialog';
import { type PledgeForm } from '@/features/pledges/schemas/pledge-form';
import { cn } from '@/lib/utils';

const AddRewardEmptyState = ({ onSelect }: { onSelect: (reward: Reward) => void }) => {
  const [open, setOpen] = useState(false);

  const form = useFormContext<PledgeForm>();

  return (
    <FormField
      control={form.control}
      name={'reward_id'}
      render={({ field, fieldState }) => {
        return (
          <FormItem>
            <FormControl>
              <Box
                className={cn(
                  'growfund-mt-3',
                  fieldState.error &&
                    'growfund-border-border-critical growfund-bg-background-fill-critical-secondary',
                )}
              >
                <BoxContent className="growfund-p-4 growfund-grid growfund-place-items-center growfund-gap-2">
                  <Gift className="growfund-size-6 growfund-text-icon-primary" />
                  <RewardSelectionDialog
                    open={open}
                    onOpenChange={setOpen}
                    onSelect={(reward) => {
                      field.onChange(reward.id);
                      onSelect(reward);
                    }}
                  >
                    <RewardSelectionDialogTrigger>
                      <Button variant="secondary">
                        <Plus />
                        {__('Select Reward', 'growfund')}
                      </Button>
                    </RewardSelectionDialogTrigger>
                  </RewardSelectionDialog>
                  {fieldState.error && (
                    <p className="growfund-text-[0.8rem] growfund-font-small growfund-text-fg-critical">
                      {__('Please select a reward.', 'growfund')}
                    </p>
                  )}
                </BoxContent>
              </Box>
            </FormControl>
          </FormItem>
        );
      }}
    />
  );
};

export default AddRewardEmptyState;

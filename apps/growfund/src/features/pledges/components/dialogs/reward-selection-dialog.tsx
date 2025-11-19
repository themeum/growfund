import { __ } from '@wordpress/i18n';
import { Gift } from 'lucide-react';
import React from 'react';
import { useFormContext, useWatch } from 'react-hook-form';

import {
    Dialog,
    DialogCloseButton,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { type Reward } from '@/features/campaigns/schemas/reward';
import { useCampaignRewardsQuery } from '@/features/campaigns/services/reward';
import RewardItem from '@/features/pledges/components/rewards/reward-item';
import { type PledgeForm } from '@/features/pledges/schemas/pledge-form';
import { useDebounce } from '@/hooks/use-debounce';

const RewardSelectionDialog = ({
  children,
  open,
  onOpenChange,
  onSelect,
}: React.PropsWithChildren<{
  open: boolean;
  onOpenChange: (value: boolean) => void;
  onSelect?: (reward: Reward) => void;
}>) => {
  const form = useFormContext<PledgeForm>();
  const campaignId = useDebounce(useWatch({ control: form.control, name: 'campaign_id' }));

  const rewardsQuery = useCampaignRewardsQuery(campaignId ?? undefined);

  return (
    <Dialog open={open} onOpenChange={onOpenChange}>
      {children}
      <DialogContent className="growfund-max-w-[43.75rem] growfund-max-h-[90svh] growfund-overflow-y-auto">
        <DialogHeader className="growfund-sticky growfund-top-0 growfund-z-header">
          <DialogTitle className="growfund-flex growfund-items-center growfund-gap-2">
            <Gift className="growfund-size-6 growfund-text-icon-primary" />
            {__('Select Reward', 'growfund')}
          </DialogTitle>
          <DialogCloseButton />
        </DialogHeader>

        <div className="growfund-p-4 growfund-pt-0 growfund-bg-background-surface-secondary growfund-grid growfund-gap-2">
          {rewardsQuery.data?.map((reward, index) => {
            return (
              <RewardItem
                key={index}
                reward={reward}
                onSelect={(reward) => {
                  onSelect?.(reward);
                  form.setValue('reward_id', reward.id);
                  onOpenChange(false);
                }}
              />
            );
          })}
        </div>
      </DialogContent>
    </Dialog>
  );
};

export const RewardSelectionDialogTrigger = ({ children }: React.PropsWithChildren) => {
  return <DialogTrigger asChild>{children}</DialogTrigger>;
};

RewardSelectionDialogTrigger.displayName = 'RewardSelectionDialogTrigger';

export default RewardSelectionDialog;

import { UpdateIcon } from '@radix-ui/react-icons';
import { __, sprintf } from '@wordpress/i18n';
import { useState } from 'react';

import { Box, BoxContent } from '@/components/ui/box';
import { Button } from '@/components/ui/button';
import { Image } from '@/components/ui/image';
import PreviewCard from '@/components/ui/preview-card';
import { type Reward } from '@/features/campaigns/schemas/reward';
import RewardSelectionDialog, {
  RewardSelectionDialogTrigger,
} from '@/features/pledges/components/dialogs/reward-selection-dialog';
import { useCurrency } from '@/hooks/use-currency';
import { cn } from '@/lib/utils';

const PledgeRewardPreview = ({
  reward,
  mode = 'edit',
  onSelect,
  showError = false,
  errorMessage,
}: {
  reward: Reward;
  showError?: boolean;
  errorMessage?: string;
  mode?: 'view' | 'edit';
  onSelect?: (reward: Reward) => void;
}) => {
  const [open, setOpen] = useState(false);
  const { toCurrency } = useCurrency();

  return (
    <Box
      className={cn(
        'growfund-mt-3',
        showError &&
          'growfund-border growfund-border-border-critical growfund-bg-background-fill-critical-secondary',
      )}
    >
      <BoxContent className="growfund-p-4 growfund-grid growfund-gap-3">
        <Box className="growfund-bg-background-surface-secondary">
          <BoxContent className="growfund-p-4 growfund-grid growfund-grid-cols-[5.5rem_1fr_3rem] growfund-gap-3">
            <Image
              src={reward.image?.url ?? null}
              alt={reward.title}
              aspectRatio="square"
              className="growfund-rounded-md"
            />
            <div className="growfund-flex growfund-flex-col growfund-gap-4">
              <div className="growfund-typo-small growfund-font-medium growfund-text-fg-primary">
                {reward.title}
              </div>
              <h6 className="growfund-typo-h6 growfund-font-semibold growfund-text-fg-secondary">
                {toCurrency(reward.amount)}
              </h6>
            </div>
            {mode === 'edit' && (
              <div className="growfund-justify-self-end growfund-h-full growfund-flex growfund-items-center">
                <RewardSelectionDialog open={open} onOpenChange={setOpen} onSelect={onSelect}>
                  <RewardSelectionDialogTrigger>
                    <Button variant="outline" size="icon">
                      <UpdateIcon />
                    </Button>
                  </RewardSelectionDialogTrigger>
                </RewardSelectionDialog>
              </div>
            )}
          </BoxContent>
        </Box>
        <div className="growfund-grid growfund-gap-2">
          <div className="growfund-typo-small growfund-font-medium growfund-text-fg-secondary">
            {/* translator: %s: number of items */}
            {sprintf(__('%s items included', 'growfund'), reward.items.length)}
          </div>
          <div>
            {reward.items.map((item, index) => {
              return (
                <PreviewCard
                  key={index}
                  title={item.title}
                  /* translator: %s: quantity */
                  subtitle={sprintf(__('Quantity: %s', 'growfund'), item.quantity ?? 1)}
                  image={item.image?.url}
                  className="growfund-px-0 growfund-shadow-none"
                />
              );
            })}
          </div>
        </div>
        {showError && errorMessage && (
          <p className="growfund-text-[0.8rem] growfund-font-small growfund-text-fg-critical">
            {__(errorMessage, 'growfund')}
          </p>
        )}
      </BoxContent>
    </Box>
  );
};

export default PledgeRewardPreview;

import { __, sprintf } from '@wordpress/i18n';
import { useEffect } from 'react';
import { useFormContext, useWatch } from 'react-hook-form';

import { RadioField } from '@/components/form/radio-field';
import { TextField } from '@/components/form/text-field';
import { Box, BoxContent } from '@/components/ui/box';
import { type Campaign } from '@/features/campaigns/schemas/campaign';
import { type Reward } from '@/features/campaigns/schemas/reward';
import AddRewardEmptyState from '@/features/pledges/components/rewards/empty-state';
import PledgeRewardPreview from '@/features/pledges/components/rewards/pledge-reward-preview';
import { type PledgeForm } from '@/features/pledges/schemas/pledge-form';
import { useCurrency } from '@/hooks/use-currency';
import { isDefined } from '@/utils';

const RewardSelector = ({
  selectedReward,
  onSelect,
  campaign,
}: {
  selectedReward?: Reward | null;
  onSelect: (reward?: Reward | null) => void;
  campaign: Campaign;
}) => {
  const { toCurrency } = useCurrency();
  const form = useFormContext<PledgeForm>();
  const pledgeOptionValue = useWatch({ control: form.control, name: 'pledge_option' });

  const handleSelectReward = (reward: Reward) => {
    onSelect(reward);
  };

  useEffect(() => {
    if (pledgeOptionValue === 'without-rewards') {
      form.resetField('reward_id');
      form.resetField('bonus_support_amount');
    } else {
      form.resetField('amount');
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [pledgeOptionValue]);

  const getPledgeAmountDescription = () => {
    if (campaign.min_pledge_amount && campaign.max_pledge_amount) {
      return sprintf(
        /* translators: 1: min pledge amount, 2: max pledge amount */
        __('Enter any amount between %1$s and %2$s', 'growfund'),
        toCurrency(campaign.min_pledge_amount),
        toCurrency(campaign.max_pledge_amount),
      );
    }

    if (campaign.min_pledge_amount) {
      return sprintf(
        /* translators: %s: min pledge amount */
        __('Enter any amount greater than or equal to %s', 'growfund'),
        toCurrency(campaign.min_pledge_amount),
      );
    }

    if (campaign.max_pledge_amount) {
      return sprintf(
        /* translators: %s: max pledge amount */
        __('Enter any amount less than or equal to %s', 'growfund'),
        toCurrency(campaign.max_pledge_amount),
      );
    }
  };

  return (
    <Box>
      <BoxContent className="growfund-p-4">
        <h6 className="growfund-typo-h6 growfund-font-medium">{__('Reward', 'growfund')}</h6>

        <div className="growfund-mt-4">
          {campaign.allow_pledge_without_reward && (
            <RadioField
              control={form.control}
              name="pledge_option"
              label={__('Pledge Option', 'growfund')}
              options={[
                {
                  value: 'without-rewards',
                  label: __('Pledge without a reward', 'growfund'),
                },
                {
                  value: 'with-rewards',
                  label: __('Pledge with a reward', 'growfund'),
                },
              ]}
            />
          )}

          {pledgeOptionValue === 'with-rewards' && isDefined(selectedReward) && (
            <>
              <PledgeRewardPreview
                reward={selectedReward}
                onSelect={handleSelectReward}
                showError={isDefined(form.formState.errors.reward_id)}
                errorMessage={form.formState.errors.reward_id?.message}
              />
              <div className="growfund-flex growfund-flex-col growfund-gap-4 growfund-mt-3">
                <TextField
                  type="number"
                  control={form.control}
                  name="bonus_support_amount"
                  label={__('Bonus Support', 'growfund')}
                  placeholder="0"
                  description={__('Enter any bonus amount', 'growfund')}
                />
              </div>
            </>
          )}

          {pledgeOptionValue === 'with-rewards' && !isDefined(selectedReward) && (
            <AddRewardEmptyState onSelect={handleSelectReward} />
          )}

          {pledgeOptionValue === 'without-rewards' && (
            <div className="growfund-mt-3 growfund-space-y-4">
              <TextField
                type="number"
                control={form.control}
                name="amount"
                label={__('Pledge Amount', 'growfund')}
                placeholder="0"
                description={getPledgeAmountDescription()}
              />
            </div>
          )}
        </div>
      </BoxContent>
    </Box>
  );
};

export default RewardSelector;

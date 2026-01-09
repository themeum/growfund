import { useMemo, useState } from 'react';
import { useFormContext, useWatch } from 'react-hook-form';

import CampaignCard from '@/components/campaigns/campaign-card';
import { Container } from '@/components/layouts/container';
import { Alert } from '@/components/ui/alert';
import { getBonusAmount, getPledgeAmount, getShippingCost } from '@/config/price-calculator';
import { type BackerInfo } from '@/features/backers/schemas/backer';
import { type Campaign } from '@/features/campaigns/schemas/campaign';
import { type Reward } from '@/features/campaigns/schemas/reward';
import BackerSelectionCard from '@/features/pledges/components/backer-selection-card';
import AddCampaignEmptyState from '@/features/pledges/components/campaigns/empty-state';
import PaymentCard from '@/features/pledges/components/payment-card';
import PaymentMethodCard from '@/features/pledges/components/payment-method-card';
import PledgeNotesCard from '@/features/pledges/components/pledge-notes-card';
import PledgeForGivingThanks from '@/features/pledges/components/rewards/pledge-for-giving-thanks';
import AddPledgeRewardLayout from '@/features/pledges/components/rewards/reward-selector';
import { PledgePaymentSchema } from '@/features/pledges/schemas/pledge';
import { type PledgeForm as PledgeFormType } from '@/features/pledges/schemas/pledge-form';
import { getDefaults } from '@/lib/zod';
import { isDefined } from '@/utils';

const PledgeForm = () => {
  const [campaign, setCampaign] = useState<Campaign | null>(null);
  const [backer, setBacker] = useState<BackerInfo | null>(null);
  const [reward, setReward] = useState<Reward | null>();

  const form = useFormContext<PledgeFormType>();
  const pledgeOption = useWatch({ control: form.control, name: 'pledge_option' });

  const amount = useWatch({ control: form.control, name: 'amount' });
  const bonusAmount = useWatch({ control: form.control, name: 'bonus_support_amount' });
  const notes = useWatch({ control: form.control, name: 'notes' });

  const pledgeAmount = useMemo(() => {
    if (!isDefined(pledgeOption)) {
      return 0;
    }
    return getPledgeAmount({ pledgeOption, reward, amount: amount ?? 0 });
  }, [pledgeOption, reward, amount]);

  const bonusSupportAmount = useMemo(() => {
    if (!isDefined(pledgeOption)) {
      return 0;
    }
    return getBonusAmount({ pledgeOption, bonusAmount: bonusAmount ?? 0 });
  }, [pledgeOption, bonusAmount]);

  const shippingCost = useMemo(() => {
    if (!isDefined(backer) || !isDefined(reward)) {
      return 0;
    }
    return getShippingCost({ reward, backer });
  }, [backer, reward]);

  const handleCampaignChange = (campaign: Campaign) => {
    setCampaign(campaign);
    if (campaign.appreciation_type === 'goodies' && !campaign.allow_pledge_without_reward) {
      form.setValue('pledge_option', 'with-rewards');
      return;
    }

    form.setValue('pledge_option', 'without-rewards');
  };

  const campaignError = form.getFieldState('campaign_id').error;

  return (
    <Container className="growfund-py-10">
      <div className="growfund-grid growfund-grid-cols-[auto_20rem] growfund-gap-4">
        <div className="growfund-space-y-4">
          {isDefined(campaign) ? (
            <div className="growfund-space-y-2">
              <CampaignCard
                campaign={campaign}
                onRemove={() => {
                  setCampaign(null);
                  form.setValue('campaign_id', null);
                  form.setValue('pledge_option', null);
                }}
                className={
                  campaignError &&
                  'growfund-border-border-critical growfund-bg-background-fill-critical-secondary/40'
                }
              />
              {campaignError && <Alert variant="destructive">{campaignError.message}</Alert>}
            </div>
          ) : (
            <AddCampaignEmptyState
              onSelectCampaign={(campaign) => {
                handleCampaignChange(campaign);
              }}
            />
          )}

          {isDefined(campaign) && (
            <>
              {campaign.appreciation_type === 'goodies' ? (
                <AddPledgeRewardLayout
                  selectedReward={reward}
                  onSelect={setReward}
                  campaign={campaign}
                />
              ) : (
                <PledgeForGivingThanks />
              )}
              <PaymentCard
                payment={{
                  ...getDefaults(PledgePaymentSchema),
                  amount: pledgeAmount,
                  shipping_cost: shippingCost,
                  bonus_support_amount: bonusSupportAmount,
                }}
                pledgeOption={pledgeOption}
              />
            </>
          )}
        </div>
        <div className="growfund-space-y-4">
          <PaymentMethodCard form={form} />
          <BackerSelectionCard selectedBacker={backer} setBacker={setBacker} />
          <PledgeNotesCard
            value={notes ?? null}
            onChange={(value) => {
              form.setValue('notes', value);
            }}
          />
        </div>
      </div>
    </Container>
  );
};

export default PledgeForm;

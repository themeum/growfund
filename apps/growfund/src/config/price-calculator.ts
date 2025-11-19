import { type BackerInfo } from '@/features/backers/schemas/backer';
import { type Reward } from '@/features/campaigns/schemas/reward';
import { type PledgeOption } from '@/features/pledges/schemas/pledge';
import { isDefined } from '@/utils';

export const RECOVERY_PERCENTAGE = 5;

export const ShippingRestOfTheWorld = 'rest-of-the-world';

export const calculateRecoveryFee = (amount: number, percentage: number) => {
  const feeRate = percentage / 100;

  if (percentage === 100) {
    return amount;
  }

  if (percentage > 100) {
    return amount * feeRate;
  }

  const total = amount / (1 - feeRate);

  return total - amount;
};

export const getPledgeAmount = ({
  pledgeOption,
  reward,
  amount = 0,
}: {
  pledgeOption: PledgeOption;
  reward?: Reward | null;
  amount?: number;
}): number => {
  if (pledgeOption === 'with-rewards') {
    return reward?.amount ?? 0;
  }

  return amount;
};

export const getBonusAmount = ({
  pledgeOption,
  bonusAmount = 0,
}: {
  pledgeOption: PledgeOption;
  bonusAmount: number;
}): number => {
  if (pledgeOption === 'with-rewards') {
    return bonusAmount;
  }

  return 0;
};

export const getShippingCost = ({
  reward,
  backer,
}: {
  reward?: Reward | null;
  backer?: BackerInfo | null;
}) => {
  if (
    !reward?.shipping_costs ||
    reward.shipping_costs.length === 0 ||
    !backer?.shipping_address?.country
  ) {
    return 0;
  }

  const backerCountry = backer.shipping_address.country;

  const estimatedShipping = reward.shipping_costs.find((cost) => cost.location === backerCountry);

  if (!isDefined(estimatedShipping)) {
    const restOfWorld = reward.shipping_costs.find(
      (cost) => cost.location === ShippingRestOfTheWorld,
    );

    return isDefined(restOfWorld) ? restOfWorld.cost : 0;
  }

  return estimatedShipping.cost;
};

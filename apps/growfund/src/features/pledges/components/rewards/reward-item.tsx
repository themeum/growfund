import { __ } from '@wordpress/i18n';

import { Box, BoxContent } from '@/components/ui/box';
import { Button } from '@/components/ui/button';
import { Image } from '@/components/ui/image';
import { type Reward } from '@/features/campaigns/schemas/reward';
import { useCurrency } from '@/hooks/use-currency';

interface RewardItemProps {
  reward: Reward;
  onSelect: (reward: Reward) => void;
}

const RewardItem = ({ reward, onSelect }: RewardItemProps) => {
  const { toCurrency } = useCurrency();

  return (
    <Box className="growfund-group/reward-item growfund-min-h-[8rem]">
      <BoxContent className="growfund-p-4 growfund-grid growfund-grid-cols-[1fr_2fr_2fr_1fr] growfund-gap-4 growfund-h-full">
        <h6 className="growfund-typo-h6 growfund-font-semibold">{toCurrency(reward.amount)}</h6>
        <div className="growfund-typo-small growfund-font-medium">{reward.title}</div>
        <div className="growfund-typo-tiny growfund-text-fg-secondary">
          <div className="growfund-mb-2">{__('Item includes', 'growfund')}</div>
          <div className="growfund-grid growfund-gap-2">
            {reward.items.map((item, index) => {
              return (
                <div key={index} className="growfund-flex growfund-items-start growfund-gap-1">
                  <span>{index + 1}.</span>
                  <span>{item.title}</span>
                </div>
              );
            })}
          </div>
        </div>
        <div className="growfund-max-w-[5.5rem] growfund-justify-self-end growfund-place-self-center">
          <Image
            src={reward.image?.url ?? null}
            alt={reward.title}
            aspectRatio="square"
            className="group-hover/reward-item:growfund-hidden"
          />
          <Button
            className="growfund-hidden group-hover/reward-item:growfund-inline-flex growfund-place-self-center"
            onClick={() => {
              onSelect(reward);
            }}
          >
            {__('Select', 'growfund')}
          </Button>
        </div>
      </BoxContent>
    </Box>
  );
};

export default RewardItem;

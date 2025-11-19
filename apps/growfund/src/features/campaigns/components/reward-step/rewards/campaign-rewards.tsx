import { Pencil2Icon } from '@radix-ui/react-icons';
import { __, _n, sprintf } from '@wordpress/i18n';
import { format } from 'date-fns';
import { ShoppingBag, Trash2, Users } from 'lucide-react';
import { useEffect, useState } from 'react';
import { useFormContext } from 'react-hook-form';

import { RewardEmptyStateIcon, ShippingFastIcon } from '@/app/icons';
import { EmptyState, EmptyStateDescription } from '@/components/empty-state';
import { Button } from '@/components/ui/button';
import { Image } from '@/components/ui/image';
import { Separator } from '@/components/ui/separator';
import ManageRewardDialog from '@/features/campaigns/components/dialogs/manage-reward-dialog/manage-reward-dialog';
import { useCampaignBuilderContext } from '@/features/campaigns/contexts/campaign-builder';
import { useCampaignReward } from '@/features/campaigns/contexts/campaign-reward';
import { useConsentDialog } from '@/features/campaigns/contexts/consent-dialog-context';
import { type CampaignForm } from '@/features/campaigns/schemas/campaign';
import { type Reward, type RewardForm } from '@/features/campaigns/schemas/reward';
import { useDeleteCampaignRewardMutation } from '@/features/campaigns/services/reward';
import { useCurrency } from '@/hooks/use-currency';
import { DATE_FORMATS } from '@/lib/date';
import { isDefined } from '@/utils';

const CampaignRewards = () => {
  const [editingReward, setEditingReward] = useState<Reward | null>(null);
  const { toCurrency } = useCurrency();
  const { rewards } = useCampaignReward();
  const { campaignId } = useCampaignBuilderContext();
  const { openDialog } = useConsentDialog();
  const form = useFormContext<CampaignForm>();

  const deleteCampaignRewardMutation = useDeleteCampaignRewardMutation();

  useEffect(() => {
    const rewardIds = rewards.map((reward) => reward.id);
    form.setValue('rewards', rewardIds);

    if (rewardIds.length > 0 && form.formState.errors.rewards) {
      form.clearErrors('rewards');
    }

    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [rewards]);

  return (
    <div className="growfund-grid growfund-gap-4 growfund-mt-4">
      {rewards.length === 0 ? (
        <EmptyState className="growfund-rounded-xl">
          <RewardEmptyStateIcon />
          <EmptyStateDescription>{__('No rewards added yet.', 'growfund')}</EmptyStateDescription>
        </EmptyState>
      ) : (
        rewards.map((reward) => {
          return (
            <div
              className="growfund-bg-background-surface growfund-rounded-md growfund-group/reward-item"
              key={reward.id}
            >
              <div className="growfund-grid growfund-grid-cols-[1fr_1.5fr_2fr_1fr] growfund-gap-8 growfund-p-4">
                <h6 className="growfund-typo-h6 growfund-text-fg-primary">{toCurrency(reward.amount)}</h6>
                <div className="growfund-grid growfund-gap-2 growfund-h-fit">
                  <p className="growfund-typo-small growfund-font-medium growfund-text-fg-primary">{reward.title}</p>
                  {!!reward.estimated_delivery_date && (
                    <div className="growfund-flex growfund-gap-2">
                      <ShippingFastIcon className="growfund-text-icon-secondary growfund-size-3 growfund-mt-[2px]" />
                      <div className="growfund-grid growfund-gap-1 growfund-text-fg-secondary growfund-typo-tiny">
                        <span>{__('Estimated Delivery', 'growfund')}</span>
                        <span className="growfund-font-medium growfund-text-fg-primary">
                          {format(
                            new Date(reward.estimated_delivery_date),
                            DATE_FORMATS.HUMAN_READABLE,
                          )}
                        </span>
                      </div>
                    </div>
                  )}

                  {reward.quantity_type === 'limited' ? (
                    <div className="growfund-flex growfund-gap-2">
                      <ShoppingBag className="growfund-text-icon-secondary growfund-size-3 growfund-mt-[2px]" />
                      <div className="growfund-grid growfund-gap-1 growfund-text-fg-secondary growfund-typo-tiny">
                        <span>{__('Limited Quantity', 'growfund')}</span>
                        <span className="growfund-font-medium growfund-text-fg-primary">
                          {sprintf(
                            /* translators: 1: reward quantity left, 2: quantity limit */
                            __('%1$s left of %2$s', 'growfund'),
                            reward.reward_left ?? 0,
                            reward.quantity_limit ?? 0,
                          )}
                        </span>
                      </div>
                    </div>
                  ) : (
                    <div className="growfund-flex growfund-gap-2">
                      <ShoppingBag className="growfund-text-icon-secondary growfund-size-3 growfund-mt-[2px]" />
                      <div className="growfund-grid growfund-gap-1 growfund-text-fg-secondary growfund-typo-tiny">
                        {__('Unlimited Available', 'growfund')}
                      </div>
                    </div>
                  )}
                </div>

                <div className="growfund-typo-tiny growfund-text-fg-secondary growfund-h-fit">
                  <p>{__('Item Includes', 'growfund')}</p>
                  {isDefined(reward.items) && reward.items.length > 0 ? (
                    <ol className="growfund-mt-2">
                      {reward.items.map((item, index) => {
                        return (
                          <li key={item.id}>
                            {index + 1}. {item.title}
                          </li>
                        );
                      })}
                    </ol>
                  ) : (
                    <div>{__('No items', 'growfund')}</div>
                  )}
                </div>

                <Image src={reward.image?.url ?? null} alt={reward.title} aspectRatio="square" />
              </div>
              <Separator />
              <div className="growfund-px-3 growfund-py-1 growfund-flex growfund-items-center growfund-justify-between">
                <div className="growfund-text-fg-secondary growfund-flex growfund-items-center growfund-gap-2 growfund-typo-tiny">
                  <Users className="growfund-text-icon-secondary growfund-w-4 growfund-h-4" />
                  <span>
                    {sprintf(
                      /* translators: %d: number of backers */
                      _n('%d Backer', '%d Backers', reward.number_of_contributors ?? 0, 'growfund'),
                      reward.number_of_contributors ?? 0,
                    )}
                  </span>
                </div>
                <div className="growfund-flex growfund-items-center growfund-gap-2 growfund-transition-opacity">
                  <Button
                    variant="ghost"
                    className="growfund-text-fg-critical"
                    size="sm"
                    onClick={() => {
                      openDialog({
                        title: __('Delete Reward', 'growfund'),
                        content: __(
                          'Are you sure to delete the campaign reward? This action cannot be undone.',
                        ),
                        confirmButtonVariant: 'destructive',
                        confirmText: __('Delete', 'growfund'),
                        declineText: __('Cancel', 'growfund'),
                        onConfirm: async (closeDialog) => {
                          if (!campaignId || !reward.id) {
                            return;
                          }
                          await deleteCampaignRewardMutation.mutateAsync({
                            campaign_id: campaignId,
                            reward_id: reward.id,
                          });
                          closeDialog();
                        },
                      });
                    }}
                  >
                    <Trash2 />
                    {__('Delete', 'growfund')}
                  </Button>
                  <ManageRewardDialog
                    defaultValues={editingReward as RewardForm & { id: string }}
                    rewardLeft={reward.reward_left}
                    numberOfContributors={reward.number_of_contributors}
                  >
                    <Button
                      variant="ghost"
                      size="sm"
                      onClick={() => {
                        setEditingReward(reward);
                      }}
                    >
                      <Pencil2Icon />
                      {__('Edit', 'growfund')}
                    </Button>
                  </ManageRewardDialog>
                </div>
              </div>
            </div>
          );
        })
      )}
    </div>
  );
};

export default CampaignRewards;

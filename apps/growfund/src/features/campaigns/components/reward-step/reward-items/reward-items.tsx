import { Pencil2Icon } from '@radix-ui/react-icons';
import { __, _n, sprintf } from '@wordpress/i18n';
import { Trash2 } from 'lucide-react';
import { useMemo } from 'react';

import { RewardItemEmptyStateIcon } from '@/app/icons';
import { EmptyState, EmptyStateDescription } from '@/components/empty-state';
import { Box, BoxContent } from '@/components/ui/box';
import { Button } from '@/components/ui/button';
import { Image } from '@/components/ui/image';
import AddRewardItemDialog from '@/features/campaigns/components/dialogs/manage-reward-item-dialog';
import { useCampaignBuilderContext } from '@/features/campaigns/contexts/campaign-builder';
import { useCampaignReward } from '@/features/campaigns/contexts/campaign-reward';
import { useConsentDialog } from '@/features/campaigns/contexts/consent-dialog-context';
import { useCampaignRewardsQuery } from '@/features/campaigns/services/reward';
import { useDeleteRewardItemMutation } from '@/features/campaigns/services/reward-item';

const RewardItems = () => {
  const { campaignId } = useCampaignBuilderContext();
  const { rewardItems } = useCampaignReward();
  const deleteRewardItemMutation = useDeleteRewardItemMutation();
  const { openDialog } = useConsentDialog();
  const rewardsQuery = useCampaignRewardsQuery(campaignId);

  const counts = useMemo(() => {
    return rewardItems.reduce<Record<string, number>>((result, current) => {
      result[current.id] ||=
        rewardsQuery.data?.reduce((sum, curr) => {
          if (curr.items.find((item) => item.id === current.id)) {
            return sum + 1;
          }

          return sum;
        }, 0) ?? 0;

      return result;
    }, {});
  }, [rewardItems, rewardsQuery.data]);

  if (rewardItems.length === 0) {
    return (
      <EmptyState className="growfund-rounded-xl growfund-mt-4">
        <RewardItemEmptyStateIcon />
        <EmptyStateDescription>{__('No items added yet.', 'growfund')}</EmptyStateDescription>
      </EmptyState>
    );
  }

  return (
    <div className="growfund-space-y-2 growfund-mt-3">
      {rewardItems.map((item) => {
        return (
          <Box key={item.id}>
            <BoxContent className="growfund-py-2 growfund-ps-2 growfund-pe-3 growfund-grid growfund-grid-cols-[3rem_auto] growfund-gap-2 growfund-relative growfund-group/reward-item">
              <Image
                src={item.image?.url ?? null}
                alt={item.title}
                className="growfund-w-12"
                aspectRatio="square"
              />
              <div className="growfund-grid growfund-items-between">
                <p className="growfund-typo-small growfund-font-medium growfund-text-fg-primary">{item.title}</p>
                <p className="growfund-typo-tiny growfund-text-fg-secondary">
                  {sprintf(
                    /* translators: %d: Number of rewards */
                    _n('Used in: %d Reward', 'Used in: %d Rewards', counts[item.id], 'growfund'),
                    counts[item.id],
                  )}
                </p>
              </div>
              <div className="growfund-flex growfund-items-center growfund-border growfund-border-border growfund-p-1 growfund-rounded-sm growfund-absolute growfund-top-[50%] growfund-right-4 growfund-translate-y-[-50%] growfund-opacity-0 group-hover/reward-item:growfund-opacity-100 growfund-transition-opacity growfund-duration-200">
                <AddRewardItemDialog defaultValues={item}>
                  <Button variant="ghost" size="icon" className="growfund-size-6">
                    <Pencil2Icon />
                  </Button>
                </AddRewardItemDialog>
                <Button
                  variant="ghost"
                  size="icon"
                  className="growfund-size-6 hover:growfund-text-icon-critical"
                  onClick={() => {
                    openDialog({
                      title: __('Delete Item', 'growfund'),
                      content: __(
                        'Are you sure you want to delete this item? Deleting it will automatically remove it from any reward bundles that include it, and this action cannot be undone.',
                        'growfund',
                      ),
                      confirmButtonVariant: 'destructive',
                      confirmText: __('Delete', 'growfund'),
                      declineText: __('Cancel', 'growfund'),
                      onConfirm: async (closeDialog) => {
                        if (!campaignId) {
                          return;
                        }

                        await deleteRewardItemMutation.mutateAsync({
                          campaign_id: campaignId,
                          id: item.id,
                        });
                        closeDialog();
                      },
                    });
                  }}
                >
                  <Trash2 />
                </Button>
              </div>
            </BoxContent>
          </Box>
        );
      })}
    </div>
  );
};

export default RewardItems;

import { ListBulletIcon } from '@radix-ui/react-icons';
import { __ } from '@wordpress/i18n';
import { File, PackageOpen, Plus, ShoppingBag } from 'lucide-react';
import { useState } from 'react';
import { useFormContext } from 'react-hook-form';

import ElementWrapper from '@/components/element-wrapper';
import CampaignRewardFallback from '@/components/pro-fallbacks/campaign/campaign-reward-fallback';
import { Button } from '@/components/ui/button';
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import ManageRewardDialog from '@/features/campaigns/components/dialogs/manage-reward-dialog/manage-reward-dialog';
import ManageRewardItemDialog from '@/features/campaigns/components/dialogs/manage-reward-item-dialog';
import RewardItems from '@/features/campaigns/components/reward-step/reward-items/reward-items';
import CampaignRewards from '@/features/campaigns/components/reward-step/rewards/campaign-rewards';
import { type CampaignForm } from '@/features/campaigns/schemas/campaign';
import { type RewardItem } from '@/features/campaigns/schemas/reward-item';
import { cn } from '@/lib/utils';

const GoodiesTab = () => {
  const [activeTab, setActiveTab] = useState<'rewards' | 'items'>('rewards');
  const [rewardItemType, setRewardItemType] = useState<'digital' | 'physical'>('physical');
  const [editingItem, setEditingItem] = useState<RewardItem | undefined>(undefined);
  const [isDialogOpen, setIsDialogOpen] = useState(false);

  const form = useFormContext<CampaignForm>();

  const openDialog = (type: 'digital' | 'physical', item?: RewardItem) => {
    setRewardItemType(type);
    setEditingItem(item);
    setIsDialogOpen(true);
  };

  const itemOptions = [
    {
      label: __('Physical Product', 'growfund'),
      value: 'physical' as const,
      icon: ShoppingBag,
    },
    {
      label: __('Digital Product', 'growfund'),
      value: 'digital' as const,
      icon: File,
    },
  ];

  return (
    <div className="growfund-space-y-2">
      <div
        className={cn(
          'growfund-bg-background-surface-tertiary growfund-rounded-lg growfund-pt-2 growfund-px-4 growfund-pb-4 growfund-border growfund-border-transparent',
          form.getFieldState('rewards').error &&
            'growfund-border-border-critical growfund-bg-background-fill-critical-secondary',
        )}
      >
        <Tabs
          value={activeTab}
          onValueChange={(value) => {
            setActiveTab(value as 'rewards' | 'items');
          }}
        >
          <div className="growfund-h-[3.125rem] growfund-flex growfund-flex-1 growfund-items-center growfund-justify-between growfund-relative">
            <TabsList>
              <TabsTrigger value="rewards">
                <div className="growfund-flex growfund-items-center growfund-gap-4 growfund-typo-small growfund-font-medium">
                  <PackageOpen className="growfund-text-inherit growfund-flex-shrink-0 growfund-w-4 growfund-h-4" />
                  <span>{__('Rewards', 'growfund')}</span>
                </div>
              </TabsTrigger>
              <TabsTrigger value="items">
                <div className="growfund-flex growfund-items-center growfund-gap-4 growfund-typo-small growfund-font-medium">
                  <ListBulletIcon className="growfund-text-inherit growfund-flex-shrink-0 growfund-w-4 growfund-h-4" />
                  <span>{__('Items', 'growfund')}</span>
                </div>
              </TabsTrigger>
            </TabsList>
            <div className="growfund-absolute growfund-right-0 growfund-top-0">
              {activeTab === 'rewards' ? (
                <ElementWrapper
                  fallback={
                    <CampaignRewardFallback
                      title={__('Unlock Unlimited Rewards', 'growfund')}
                      description={__(
                        "Maximize your campaign's appeal with more rewards. Upgrade to Pro for unlimited reward slots and attract more backers.",
                        'growfund',
                      )}
                    >
                      <Button variant="outline">
                        <Plus className="growfund-text-icon-primary growfund-w-4 growfund-h-4" />
                        {__('Add Reward', 'growfund')}
                      </Button>
                    </CampaignRewardFallback>
                  }
                >
                  <ManageRewardDialog>
                    <Button variant="outline">
                      <Plus className="growfund-text-icon-primary growfund-w-4 growfund-h-4" />
                      {__('Add Reward', 'growfund')}
                    </Button>
                  </ManageRewardDialog>
                </ElementWrapper>
              ) : (
                <ElementWrapper
                  fallback={
                    <CampaignRewardFallback
                      title={__('Unlock Unlimited Items', 'growfund')}
                      description={__(
                        "Maximize your campaign's appeal with more items in rewards. Upgrade to Pro for unlimited items attract more backers.",
                        'growfund',
                      )}
                    >
                      <Button variant="outline">
                        <Plus />
                        {__('Add Item', 'growfund')}
                      </Button>
                    </CampaignRewardFallback>
                  }
                >
                  <DropdownMenu>
                    <DropdownMenuTrigger asChild>
                      <Button variant="outline">
                        <Plus className="growfund-text-icon-primary growfund-w-4 growfund-h-4" />
                        {__('Add Item', 'growfund')}
                      </Button>
                    </DropdownMenuTrigger>

                    <DropdownMenuContent align="end">
                      {itemOptions.map((option) => {
                        const Icon = option.icon;

                        return (
                          <DropdownMenuItem
                            key={option.value}
                            onSelect={() => {
                              openDialog(option.value);
                            }}
                            className="growfund-flex growfund-gap-2"
                          >
                            <Icon />

                            {option.label}
                          </DropdownMenuItem>
                        );
                      })}
                    </DropdownMenuContent>
                  </DropdownMenu>

                  <ManageRewardItemDialog
                    open={isDialogOpen}
                    onOpenChange={setIsDialogOpen}
                    defaultValues={editingItem}
                    rewardItemType={rewardItemType}
                  />
                </ElementWrapper>
              )}
            </div>
          </div>
          <TabsContent value="rewards">
            <CampaignRewards />
          </TabsContent>
          <TabsContent value="items">
            <RewardItems
              onEdit={(item) => {
                openDialog(item.type, item);
              }}
            />
          </TabsContent>
        </Tabs>
      </div>
      {form.getFieldState('rewards').error && (
        <p className="growfund-typo-small growfund-text-fg-critical">
          {form.getFieldState('rewards').error?.message?.[0]}
        </p>
      )}
    </div>
  );
};

export default GoodiesTab;

import { DashIcon } from '@radix-ui/react-icons';
import { __, _n, sprintf } from '@wordpress/i18n';
import { Car, MapPin, ShoppingBag, Users } from 'lucide-react';
import { useMemo } from 'react';
import { useFormContext, useWatch } from 'react-hook-form';

import { Box } from '@/components/ui/box';
import { Image } from '@/components/ui/image';
import { ScrollArea } from '@/components/ui/scroll-area';
import { Skeleton } from '@/components/ui/skeleton';
import { SmartImage } from '@/components/ui/smart-image';
import { useCampaignReward } from '@/features/campaigns/contexts/campaign-reward';
import { type RewardForm } from '@/features/campaigns/schemas/reward';
import { useCurrency } from '@/hooks/use-currency';
import { DATE_FORMATS, formatDate } from '@/lib/date';

const InformationPanel = ({
  rewardLeft,
  numberOfContributors,
}: {
  rewardLeft?: number | null;
  numberOfContributors?: number | null;
}) => {
  const form = useFormContext<RewardForm>();
  const { rewardItems } = useCampaignReward();

  const { toCurrency } = useCurrency();

  const rewardType = useWatch({ control: form.control, name: 'reward_type' });
  const itemsValue = useWatch({ control: form.control, name: 'items' });
  const itemIds = itemsValue.map((item) => item.id);
  const itemQuantities = itemsValue.reduce<Record<string, number>>((result, item) => {
    result[item.id] = item.quantity;
    return result;
  }, {});
  const itemsData = rewardItems
    .filter((item) => itemIds.includes(item.id))
    .map((item) => ({
      ...item,
      quantity: itemQuantities[item.id],
    }));

  // eslint-disable-next-line react-hooks/exhaustive-deps
  const shippingCosts = useWatch({ control: form.control, name: 'shipping_costs' }) ?? [];

  const shippingAddress = useMemo(() => {
    if (shippingCosts.length === 0) {
      return null;
    }
    if (shippingCosts.some((cost) => cost.location === 'rest-of-the-world')) {
      return __('Anywhere of the world', 'growfund');
    }
    return sprintf(
      /* translators: %d: number of countries */
      _n('%d country', '%d countries', shippingCosts.length, 'growfund'),
      shippingCosts.length,
    );
  }, [shippingCosts]);

  const formData = {
    title: useWatch({ control: form.control, name: 'title' }),
    amount: useWatch({ control: form.control, name: 'amount' }),
    description: useWatch({ control: form.control, name: 'description' }),
    image: useWatch({ control: form.control, name: 'image' }),
    shipping_address: shippingAddress,
    backer_count: numberOfContributors,
    estimated_delivery: useWatch({ control: form.control, name: 'estimated_delivery_date' }),
    quantity_limit: useWatch({ control: form.control, name: 'quantity_limit' }),
    quantity_type: useWatch({ control: form.control, name: 'quantity_type' }),
    items: itemsData,
  } as const;

  return (
    <ScrollArea className="growfund-sticky">
      <Box className="growfund-overflow-hidden">
        <SmartImage
          src={formData.image?.url ?? null}
          alt={formData.title}
          className="growfund-rounded-none growfund-h-[13.25rem] growfund-w-full growfund-border-none"
        />

        <div className="growfund-px-4 growfund-py-2 growfund-pb-4 growfund-space-y-4">
          <div className="growfund-grid growfund-grid-cols-[2.5fr_1fr] growfund-gap-2">
            {formData.title ? (
              <h4 className="growfund-typo-h4 growfund-font-normal growfund-text-fg-primary">{formData.title}</h4>
            ) : (
              <Skeleton className="growfund-h-3 growfund-w-full" />
            )}
            {formData.amount ? (
              <h4 className="growfund-typo-h4 growfund-text-fg-primary">
                {toCurrency(form.getValues('amount'))}
              </h4>
            ) : (
              <Skeleton className="growfund-h-3 growfund-w-full" />
            )}
          </div>

          {!!formData.description && (
            <div className="growfund-typo-small growfund-text-fg-secondary">{formData.description}</div>
          )}

          <div className="growfund-grid growfund-grid-cols-2 growfund-gap-4">
            {rewardType !== 'digital-goods' && (
              <div className="growfund-flex growfund-gap-2 growfund-items-start">
                <MapPin className="growfund-text-icon-secondary growfund-size-4" />
                <div className="growfund-space-y-1 growfund-w-full">
                  <p className="growfund-typo-tiny growfund-text-fg-secondary">{__('Ships to', 'growfund')}</p>
                  <div className="growfund-typo-tiny growfund-font-medium growfund-text-fg-primary">
                    {formData.shipping_address ?? <DashIcon className="growfund-size-4" />}
                  </div>
                </div>
              </div>
            )}
            <div className="growfund-flex growfund-gap-2 growfund-items-start">
              <Users className="growfund-text-icon-secondary growfund-size-4" />
              <div className="growfund-space-y-1 growfund-w-full">
                <p className="growfund-typo-tiny growfund-text-fg-secondary">{__('Backers', 'growfund')}</p>
                <p className="growfund-typo-tiny growfund-font-medium growfund-text-fg-primary">
                  {formData.backer_count ?? <DashIcon className="growfund-size-4" />}
                </p>
              </div>
            </div>
            <div className="growfund-flex growfund-gap-2 growfund-items-start">
              <Car className="growfund-text-icon-secondary growfund-size-4" />
              <div className="growfund-space-y-1">
                <p className="growfund-typo-tiny growfund-text-fg-secondary">
                  {__('Estimated Delivery', 'growfund')}
                </p>
                <p className="growfund-typo-tiny growfund-font-medium growfund-text-fg-primary">
                  {(formData.estimated_delivery &&
                    formatDate(new Date(formData.estimated_delivery), DATE_FORMATS.DATE_FIELD)) ?? (
                    <DashIcon className="growfund-size-4" />
                  )}
                </p>
              </div>
            </div>
            <div className="growfund-flex growfund-gap-2 growfund-items-start">
              <ShoppingBag className="growfund-text-icon-secondary growfund-size-4" />
              <div className="growfund-space-y-1">
                <p className="growfund-typo-tiny growfund-text-fg-secondary">
                  {__('Limited Quantity', 'growfund')}
                </p>
                <p className="growfund-typo-tiny growfund-font-medium growfund-text-fg-primary">
                  {formData.quantity_type === 'limited' ? (
                    sprintf(
                      /* translators: 1: reward left, 2: reward quantity limit */
                      __('%1$s left of %2$s', 'growfund'),
                      rewardLeft ?? 0,
                      formData.quantity_limit,
                    )
                  ) : (
                    <DashIcon className="growfund-size-4" />
                  )}
                </p>
              </div>
            </div>
          </div>

          <div className="growfund-space-y-2">
            <p className="growfund-text-fg-primary growfund-font-medium growfund-typo-small">
              {/* translators: %s: number of items */}
              {sprintf(__('%s items included', 'growfund'), formData.items.length)}
            </p>

            {formData.items.map((item, index) => {
              return (
                <div
                  key={index}
                  className="growfund-bg-background-surface growfund-border growfund-border-border growfund-rounded-md growfund-p-2 growfund-flex growfund-items-center growfund-gap-4"
                >
                  <Image
                    src={item.image?.url ?? null}
                    alt={item.title}
                    className="growfund-rounded-md growfund-w-12 growfund-shrink-0"
                    aspectRatio="square"
                  />
                  <div>
                    <p className="growfund-typo-small growfund-font-medium growfund-text-fg-primary">{item.title}</p>
                    <span className="growfund-typo-tiny growfund-text-fg-muted">
                      {/* translators: %s: item quantity */}
                      {sprintf(__('Quantity: %s', 'growfund'), item.quantity)}
                    </span>
                  </div>
                </div>
              );
            })}
          </div>
        </div>
      </Box>
    </ScrollArea>
  );
};

export default InformationPanel;

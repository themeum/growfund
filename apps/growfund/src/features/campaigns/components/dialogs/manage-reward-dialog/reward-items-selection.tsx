import { DragHandleDots2Icon, Pencil2Icon } from '@radix-ui/react-icons';
import { __, sprintf } from '@wordpress/i18n';
import { Plus, Trash2 } from 'lucide-react';
import { useMemo, useState } from 'react';
import { useFieldArray, useFormContext, useWatch } from 'react-hook-form';

import digitalThumb from '@/assets/images/digital-thumb.svg';
import { SelectField } from '@/components/form/select-field';
import { Box } from '@/components/ui/box';
import { Button } from '@/components/ui/button';
import { Image } from '@/components/ui/image';
import { Input } from '@/components/ui/input';
import { SortableContainer, SortableItem } from '@/contexts/sortable';
import CreateRewardItem from '@/features/campaigns/components/dialogs/manage-reward-dialog/create-reward-item';
import { useCampaignReward } from '@/features/campaigns/contexts/campaign-reward';
import { type RewardForm } from '@/features/campaigns/schemas/reward';
import { type RewardItem } from '@/features/campaigns/schemas/reward-item';
import { cn } from '@/lib/utils';

import AddRewardItem from './add-reward-item';

export const SingleRewardItem = ({
  item,
  onRemove,
  onEdit,
  isOverlay = false,
}: {
  item: RewardItem;
  onRemove?: () => void;
  onEdit?: (values: { id: string; quantity: number }) => void;

  isOverlay?: boolean;
}) => {
  const [isEditing, setIsEditing] = useState(false);

  if (isEditing) {
    return (
      <CreateRewardItem
        mode="edit"
        defaultValues={item}
        onSave={(values) => {
          setIsEditing(false);
          onEdit?.(values);
        }}
        onCancel={() => {
          setIsEditing(false);
        }}
      />
    );
  }

  return (
    <div
      className={cn(
        'growfund-bg-background-surface growfund-border growfund-border-border growfund-rounded-md growfund-py-3 growfund-ps-2 growfund-pe-3 growfund-flex growfund-items-center growfund-justify-between growfund-group/item growfund-select-none growfund-relative',
        isOverlay && 'growfund-shadow-lg',
      )}
    >
      <div className="growfund-flex growfund-items-center growfund-gap-3 growfund-cursor-grab">
        <DragHandleDots2Icon className="growfund-text-icon-secondary growfund-shrink-0" />
        <Image
          src={item.image?.url ?? null}
          alt={item.title}
          className="growfund-size-14 growfund-shrink-0"
          fit="cover"
          fallbackSrc={item.type === 'digital' ? digitalThumb : undefined}
        />
        <div className="growfund-grid growfund-gap-2">
          <p className="growfund-typo-small growfund-font-medium growfund-text-fg-primary">
            {item.title}
          </p>
          <div className="growfund-min-h-9">
            <span className="growfund-typo-tiny growfund-text-fg-muted growfund-block group-hover/item:growfund-hidden">
              {/* translators: %s: reward item's quantity */}
              {sprintf(__('Quantity: %s', 'growfund'), item.quantity)}
            </span>
            <Input
              type="number"
              placeholder={__('Qty', 'growfund')}
              value={String(item.quantity)}
              onChange={(event) => {
                const value = event.target.valueAsNumber;
                onEdit?.({ ...item, quantity: value });
              }}
              className="growfund-hidden growfund-max-w-16 group-hover/item:growfund-block"
            />
          </div>
        </div>
      </div>
      <div className="growfund-flex growfund-transition-opacity growfund-opacity-0 group-hover/item:growfund-opacity-100 growfund-absolute growfund-top-4 growfund-right-4 growfund-border growfund-border-border growfund-p-1 growfund-rounded-md growfund-bg-background-surface">
        <Button
          variant="ghost"
          size="icon"
          className="growfund-size-8"
          onClick={() => {
            setIsEditing(true);
          }}
        >
          <Pencil2Icon />
        </Button>
        <Button
          variant="ghost"
          size="icon"
          className="hover:growfund-text-icon-critical growfund-size-8"
          onClick={onRemove}
        >
          <Trash2 />
        </Button>
      </div>
    </div>
  );
};

const RewardItemsSelection = () => {
  const [isOpen, setIsOpen] = useState(false);
  const { rewardItems } = useCampaignReward();

  const form = useFormContext<RewardForm>();
  const items = useWatch({ control: form.control, name: 'items' });
  const { fields, append, remove, update, replace } = useFieldArray({
    control: form.control,
    name: 'items',
    keyName: 'uuid',
  });

  const controlledFields = fields.map((field, index) => {
    return {
      ...field,
      ...items[index],
      id: field.id,
    };
  });

  const itemsError = form.getFieldState('items').error;
  const rewardType = useWatch({
    control: form.control,
    name: 'reward_type',
  });

  const filteredRewardItems = useMemo(() => {
    return rewardItems.filter((item) => {
      switch (rewardType) {
        case 'digital-goods':
          return item.type === 'digital';
        case 'physical-goods':
          return item.type === 'physical';
        default:
          return true;
      }
    });
  }, [rewardItems, rewardType]);

  const reward_type = useWatch({ control: form.control, name: 'reward_type' });

  return (
    <div className="growfund-space-y-2">
      <Box
        className={cn(
          'growfund-p-4 growfund-space-y-3',
          !!itemsError &&
            'growfund-border-border-critical growfund-bg-background-fill-critical-secondary',
        )}
      >
        <SelectField
          control={form.control}
          name="reward_type"
          label={__('Backers Receive', 'growfund')}
          options={[
            {
              label: __('Only Physical Goods', 'growfund'),
              value: 'physical-goods',
            },
            {
              label: __('Only Digital Goods', 'growfund'),
              value: 'digital-goods',
            },
            {
              label: __('Both Physical & Digital goods', 'growfund'),
              value: 'physical-and-digital-goods',
            },
          ]}
        />
        <p className="growfund-typo-small growfund-font-medium growfund-text-fg-primary">
          {__('Items', 'growfund')}
        </p>

        <div className="growfund-grid growfund-gap-3">
          <SortableContainer
            items={controlledFields}
            onSortCompleted={(items) => {
              replace(items);
            }}
            overlay={(item) => {
              const rewardItem = rewardItems.find((reward) => reward.id === item.id);
              if (!rewardItem) {
                return null;
              }

              return <SingleRewardItem item={rewardItem} isOverlay={true} />;
            }}
          >
            {controlledFields.map((item, index) => {
              const rewardItem = rewardItems.find((reward) => reward.id === item.id);
              if (!rewardItem) {
                return null;
              }

              return (
                <SortableItem id={item.id} key={item.id}>
                  <SingleRewardItem
                    item={{ ...rewardItem, quantity: item.quantity }}
                    onRemove={() => {
                      remove(index);
                    }}
                    onEdit={(values) => {
                      update(index, values);
                    }}
                  />
                </SortableItem>
              );
            })}
          </SortableContainer>
        </div>

        {isOpen ? (
          <AddRewardItem
            selectedItems={items}
            onOpenChange={setIsOpen}
            onSave={(value) => {
              append({
                id: value.id,
                quantity: value.quantity,
              });
            }}
            rewardType={
              reward_type !== 'physical-and-digital-goods'
                ? (rewardType as 'digital-goods' | 'physical-goods')
                : undefined
            }
            rewardItems={filteredRewardItems}
          />
        ) : (
          <Button
            variant="secondary"
            className="growfund-w-full"
            onClick={() => {
              setIsOpen(true);
            }}
          >
            <Plus />
            {__('Add', 'growfund')}
          </Button>
        )}
      </Box>
      {!!itemsError && (
        <p className="growfund-typo-small growfund-text-fg-critical">{itemsError.message?.[0]}</p>
      )}
    </div>
  );
};

RewardItemsSelection.displayName = 'RewardItems';

export default RewardItemsSelection;

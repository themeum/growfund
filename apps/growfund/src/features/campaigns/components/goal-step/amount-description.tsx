import { DragHandleDots2Icon } from '@radix-ui/react-icons';
import { __ } from '@wordpress/i18n';
import { Check, Circle, CircleDot, Edit, Plus, Trash2 } from 'lucide-react';
import { useEffect, useState } from 'react';
import { useFieldArray, useFormContext, useWatch } from 'react-hook-form';

import { TextField } from '@/components/form/text-field';
import { TextareaField } from '@/components/form/textarea-field';
import { Button } from '@/components/ui/button';
import { SortableContainer, SortableItem } from '@/contexts/sortable';
import { type CampaignBuilderForm } from '@/features/campaigns/schemas/campaign';
import { useCurrency } from '@/hooks/use-currency';
import { cn } from '@/lib/utils';
import { isDefined } from '@/utils';

const PresetItem = ({
  preset,
  isOverlay = false,
  index,
  onRemove,
  onSetDefault,
  isEditing,
  onSetEditing,
}: {
  preset: NonNullable<CampaignBuilderForm['suggested_options']>[number];
  isOverlay?: boolean;
  index: number;
  onRemove?: () => void;
  onSetDefault?: () => void;
  isEditing?: boolean;
  onSetEditing?: (value: boolean) => void;
}) => {
  const form = useFormContext<CampaignBuilderForm>();
  const [isOpen, setIsOpen] = useState(false);

  const suggestionType = useWatch({ control: form.control, name: 'suggested_option_type' });
  const { toCurrency } = useCurrency();

  const errors = form.getFieldState(`suggested_options.${index}`).error;

  useEffect(() => {
    if (errors) {
      setIsOpen(true);
    }
  }, [errors]);

  return (
    <div>
      <div
        className={cn(
          'growfund-relative growfund-border growfund-border-border growfund-rounded-md growfund-bg-background-white growfund-py-3 growfund-px-4 growfund-flex growfund-gap-3 growfund-items-center growfund-group growfund-min-h-16',
          isOverlay && 'growfund-shadow-lg',
          !!errors && 'growfund-border-border-critical',
        )}
      >
        <DragHandleDots2Icon className="growfund-shrink-0" />

        {!isEditing && !isOpen ? (
          <div className="growfund-h-full growfund-ms-1">
            <div className="growfund-w-full growfund-space-y-2">
              <div className="growfund-w-full growfund-font-semibold growfund-text-fg-primary growfund-items-center growfund-flex growfund-gap-1">
                {toCurrency(preset.amount)}
                {preset.is_default && (
                  <span className="growfund-font-regular growfund-text-fg-secondary">
                    {__(' (Default)', 'growfund')}
                  </span>
                )}
              </div>

              {suggestionType === 'amount-description' && !!preset.description && (
                <div className="growfund-typo-small growfund-text-fg-secondary growfund-items-center growfund-flex">
                  {preset.description}
                </div>
              )}
            </div>
            <div className="growfund-absolute growfund-top-3 growfund-right-4 growfund-h-9 growfund-w-[5.25rem] growfund-items-center growfund-justify-center growfund-p-1 growfund-shadow-md growfund-border growfund-rounded-md growfund-hidden group-hover:growfund-flex">
              <Button
                variant="ghost"
                size="icon"
                className="growfund-size-6 growfund-cursor-pointer"
                onClick={() => {
                  onSetEditing?.(true);
                }}
              >
                <Edit />
              </Button>

              <Button
                variant="ghost"
                size="icon"
                className="growfund-size-6 growfund-cursor-pointer"
                onClick={() => {
                  onSetDefault?.();
                }}
              >
                {preset.is_default ? <CircleDot /> : <Circle />}
              </Button>

              <Button
                variant="ghost"
                size="icon"
                className="growfund-size-6 growfund-cursor-pointer hover:growfund-text-icon-critical"
                onClick={() => {
                  onRemove?.();
                  setIsOpen(false);
                }}
              >
                <Trash2 />
              </Button>
            </div>
          </div>
        ) : (
          <div className="growfund-flex growfund-w-full growfund-flex-col growfund-gap-2">
            <div className="growfund-w-ful growfund-flex growfund-justify-between growfund-gap-2">
              <TextField
                control={form.control}
                type="number"
                name={`suggested_options.${index}.amount` as 'suggested_options.0.amount'}
                placeholder={__('5.00', 'growfund')}
                autoFocusVisible
              />
              <div className="growfund-flex growfund-top-2 growfund-right-2 growfund-h-9 growfund-w-[5.25rem] growfund-items-center growfund-justify-center growfund-p-1 growfund-border growfund-rounded-md">
                <Button
                  variant="ghost"
                  size="icon"
                  className="growfund-size-6 growfund-cursor-pointer"
                  onClick={() => {
                    onSetEditing?.(false);
                    setIsOpen(false);
                  }}
                >
                  <Check />
                </Button>

                <Button
                  variant="ghost"
                  size="icon"
                  className="growfund-size-6 growfund-cursor-pointer"
                  onClick={() => {
                    onSetDefault?.();
                  }}
                >
                  {preset.is_default ? <CircleDot /> : <Circle />}
                </Button>

                <Button
                  variant="ghost"
                  size="icon"
                  className="growfund-size-6 growfund-cursor-pointer hover:growfund-text-icon-critical"
                  onClick={() => {
                    onRemove?.();
                  }}
                >
                  <Trash2 />
                </Button>
              </div>
            </div>

            {suggestionType === 'amount-description' && (
              <TextareaField
                control={form.control}
                name={`suggested_options.${index}.description` as 'suggested_options.0.description'}
                placeholder={__(
                  'We truly appreciate your generous support! Your contributions make...',
                  'growfund',
                )}
              />
            )}
          </div>
        )}
      </div>
    </div>
  );
};

export const AmountDescription = () => {
  const form = useFormContext<CampaignBuilderForm>();
  const fieldArray = useFieldArray({ control: form.control, name: 'suggested_options' });
  const [editingIndex, setEditingIndex] = useState<number | null>(null);
  const { replace, fields } = fieldArray;
  const options =
    useWatch({
      control: form.control,
      name: 'suggested_options',
    }) ?? [];

  const controlledFields = fields.map((field, index) => ({
    ...field,
    ...options[index],
  }));

  const hasFieldError = form.getFieldState('suggested_options').error;

  return (
    <div className="growfund-space-y-2">
      <SortableContainer
        items={controlledFields}
        onSortCompleted={(items) => {
          const updatedItems = items.map((item) => ({
            id: item.id,
            amount: item.amount,
            description: item.description,
            is_default: item.is_default,
          }));

          replace(updatedItems);
        }}
        overlay={(item) => (
          <PresetItem
            preset={{
              amount: item.amount,
              description: item.description,
              is_default: item.is_default,
            }}
            isOverlay
            index={0}
          />
        )}
      >
        {controlledFields.map((field, index) => (
          <SortableItem id={field.id} key={field.id}>
            <PresetItem
              key={field.id}
              preset={{
                amount: field.amount,
                description: field.description,
                is_default: field.is_default,
              }}
              index={index}
              onRemove={() => {
                fieldArray.remove(index);
                setEditingIndex(null);
              }}
              onSetDefault={() => {
                fieldArray.replace(
                  controlledFields.map((value, idx) => ({
                    ...value,
                    is_default: index === idx,
                  })),
                );
              }}
              isEditing={editingIndex === index}
              onSetEditing={(value) => {
                setEditingIndex(value ? index : null);
              }}
            />
          </SortableItem>
        ))}
      </SortableContainer>

      <Button
        variant="secondary"
        className={cn(
          'growfund-w-full',
          hasFieldError && 'growfund-border-border-critical growfund-bg-background-fill-critical-secondary',
        )}
        disabled={isDefined(editingIndex)}
        onClick={() => {
          fieldArray.append({
            amount: 0,
            description: '',
            is_default: false,
          });
          setEditingIndex(fields.length);
        }}
      >
        <Plus />
        {__('Add Amount', 'growfund')}
      </Button>
      {hasFieldError && (
        <p className="growfund-text-[0.8rem] growfund-font-small growfund-text-fg-critical">
          {__('Suggestion is required.', 'growfund')}
        </p>
      )}
    </div>
  );
};

import { zodResolver } from '@hookform/resolvers/zod';
import { __ } from '@wordpress/i18n';
import { useForm } from 'react-hook-form';
import { z } from 'zod';

import { SelectField } from '@/components/form/select-field';
import { TextField } from '@/components/form/text-field';
import { Button } from '@/components/ui/button';
import { Form } from '@/components/ui/form';
import { type RewardItem } from '@/features/campaigns/schemas/reward-item';

const FormSchema = z.object({
  id: z.string({ message: __('Reward item is required', 'growfund') }),
  quantity: z.number({ message: __('Item quantity is required', 'growfund') }),
});

const SelectExistingItem = ({
  selectedItems,
  onSave,
  onCancel,
  rewardItems,
}: {
  selectedItems: { id: string; quantity: number }[];
  rewardItems: RewardItem[];
  onSave: (value: { id: string; quantity: number }) => void;
  onCancel: () => void;
}) => {
  const form = useForm<z.infer<typeof FormSchema>>({
    resolver: zodResolver(FormSchema),
  });

  const options = rewardItems
    .filter((item) => !selectedItems.find((value) => value.id === item.id))
    .map((item) => ({
      label: item.title,
      value: item.id,
    }));

  return (
    <Form {...form}>
      <SelectField
        control={form.control}
        name="id"
        placeholder={__('Select an item', 'growfund')}
        options={options}
        className="growfund-w-[414px] growfund-overflow-hidden"
      />
      <TextField
        control={form.control}
        name="quantity"
        type="number"
        label={__('Quantity', 'growfund')}
        placeholder={__('Enter quantity', 'growfund')}
      />
      <div className="growfund-flex growfund-justify-end growfund-gap-4 growfund-pt-2">
        <Button variant="ghost" onClick={onCancel}>
          {__('Cancel', 'growfund')}
        </Button>
        <Button
          variant="primary"
          disabled={!form.formState.isDirty}
          onClick={form.handleSubmit(
            (values) => {
              onSave({ id: values.id, quantity: values.quantity });
            },
            (errors) => {
              console.error(errors);
            },
          )}
        >
          {__('Save', 'growfund')}
        </Button>
      </div>
    </Form>
  );
};

export default SelectExistingItem;

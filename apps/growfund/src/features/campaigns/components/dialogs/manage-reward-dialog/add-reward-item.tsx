import { zodResolver } from '@hookform/resolvers/zod';
import { __ } from '@wordpress/i18n';
import { useState } from 'react';
import { useForm } from 'react-hook-form';

import { Box } from '@/components/ui/box';
import { Form } from '@/components/ui/form';
import { Label } from '@/components/ui/label';
import { RadioGroup, RadioGroupItem } from '@/components/ui/radio-group';
import CreateRewardItem from '@/features/campaigns/components/dialogs/manage-reward-dialog/create-reward-item';
import SelectExistingItem from '@/features/campaigns/components/dialogs/manage-reward-dialog/select-existing-item';
import {
    type RewardItemForm,
    RewardItemFormSchema,
} from '@/features/campaigns/schemas/reward-item';
import { cn } from '@/lib/utils';
import { getDefaults } from '@/lib/zod';

interface AddRewardItemProps {
  onSave: (value: { id: string; quantity: number }) => void;
  onOpenChange: (open: boolean) => void;
  mode?: 'create' | 'edit';
  defaultValues?: RewardItemForm;
  selectedItems?: { id: string; quantity: number }[];
}

const AddRewardItem = ({
  mode = 'create',
  onSave,
  onOpenChange,
  selectedItems,
}: AddRewardItemProps) => {
  const [addType, setAddType] = useState<'existing' | 'new'>('existing');

  const form = useForm<RewardItemForm>({
    resolver: zodResolver(RewardItemFormSchema),
    defaultValues: getDefaults(RewardItemFormSchema),
  });

  return (
    <Form {...form}>
      <Box className={cn('growfund-p-4 growfund-space-y-3 growfund-shadow-none')}>
        {mode !== 'edit' && (
          <RadioGroup
            defaultValue={addType}
            onValueChange={(value) => {
              setAddType(value as 'existing' | 'new');
            }}
          >
            <div className="growfund-flex growfund-items-center growfund-gap-3">
              <RadioGroupItem value="existing" id="r1" />
              <Label htmlFor="r1">{__('Existing items', 'growfund')}</Label>
            </div>
            <div className="growfund-flex growfund-items-center growfund-gap-3">
              <RadioGroupItem value="new" id="r2" />
              <Label htmlFor="r2">{__('Create a new one', 'growfund')}</Label>
            </div>
          </RadioGroup>
        )}

        {addType === 'new' ? (
          <CreateRewardItem
            onSave={(values) => {
              onSave(values);
              onOpenChange(false);
            }}
            onCancel={() => {
              onOpenChange(false);
            }}
            mode={mode}
          />
        ) : (
          <SelectExistingItem
            selectedItems={selectedItems ?? []}
            onSave={(values) => {
              onSave(values);
              onOpenChange(false);
            }}
            onCancel={() => {
              onOpenChange(false);
            }}
          />
        )}
      </Box>
    </Form>
  );
};

export default AddRewardItem;

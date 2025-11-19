import { useState } from 'react';
import { useFieldArray, useFormContext, useWatch } from 'react-hook-form';

import { SortableContainer, SortableItem } from '@/contexts/sortable';
import PaymentMethodItem from '@/features/settings/features/payments/components/payment-methods/payment-method-item';
import { type PaymentSettingsForm } from '@/features/settings/schemas/settings';

const PaymentMethods = () => {
  const form = useFormContext<PaymentSettingsForm>();
  const fieldArray = useFieldArray({ control: form.control, name: 'payments' });
  const paymentsValue = useWatch({ control: form.control, name: 'payments' }) ?? [];
  const [editingIndex, setEditingIndex] = useState<number | null>(null);
  const controlledFields = fieldArray.fields.map((field, index) => {
    return {
      ...field,
      ...paymentsValue[index],
    };
  });

  return (
    <div className="growfund-space-y-3">
      <SortableContainer
        items={controlledFields}
        onSortCompleted={(values) => {
          fieldArray.replace(values);
        }}
        overlay={(item) => <PaymentMethodItem field={item} index={0} editingIndex={null} />}
      >
        {controlledFields.map((field, index) => {
          return (
            <SortableItem key={field.id} id={field.id}>
              <PaymentMethodItem
                key={field.id}
                field={field}
                editingIndex={editingIndex}
                index={index}
                onApply={(config) => {
                  fieldArray.update(index, {
                    ...field,
                    config: {
                      ...field.config,
                      ...config,
                    },
                  });
                  setEditingIndex(null);
                }}
                onCancel={() => {
                  setEditingIndex(null);
                }}
                onEdit={() => {
                  setEditingIndex(index);
                }}
                onRemove={() => {
                  fieldArray.remove(index);
                  setEditingIndex(null);
                }}
              />
            </SortableItem>
          );
        })}
      </SortableContainer>
    </div>
  );
};

export default PaymentMethods;

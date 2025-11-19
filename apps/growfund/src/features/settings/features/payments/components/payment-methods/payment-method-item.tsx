import { DragHandleDots2Icon } from '@radix-ui/react-icons';
import { __ } from '@wordpress/i18n';
import { Edit } from 'lucide-react';
import { useFormContext } from 'react-hook-form';

import { SwitchField } from '@/components/form/switch-field';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Image } from '@/components/ui/image';
import EditPaymentConfig from '@/features/settings/features/payments/components/edit-payment-config';
import { type Payment } from '@/features/settings/features/payments/schemas/payment';
import { type PaymentSettingsForm } from '@/features/settings/schemas/settings';
import { cn } from '@/lib/utils';
import { isDefined } from '@/utils';
import { isMediaObject } from '@/utils/media';

interface PaymentMethodItemProps {
  index: number;
  editingIndex: number | null;
  field: Payment;
  onApply?: (value: Record<string, unknown>) => void;
  onEdit?: () => void;
  onCancel?: () => void;
  isOverlay?: boolean;
  onRemove?: () => void;
}

const PaymentMethodItem = ({
  index,
  editingIndex,
  field,
  isOverlay,
  onApply,
  onEdit,
  onCancel,
  onRemove,
}: PaymentMethodItemProps) => {
  const form = useFormContext<PaymentSettingsForm>();
  const isEditing = editingIndex === index;

  const logoSrc = isMediaObject(field.config.logo) ? field.config.logo.url : field.config.logo;

  if (!isEditing) {
    return (
      <div
        className={cn(
          'growfund-p-4 growfund-group growfund-flex growfund-items-center growfund-rounded-lg growfund-border growfund-bg-background-surface growfund-text-fg-primary',
          isOverlay && 'growfund-shadow-lg',
        )}
      >
        <div className="growfund-flex growfund-items-center growfund-justify-between growfund-w-full">
          <div className="growfund-flex growfund-items-center growfund-gap-2">
            <DragHandleDots2Icon className="growfund-size-5 growfund-text-fg-secondary growfund-hidden group-hover:growfund-flex" />
            <Image
              src={logoSrc}
              alt={field.config.label}
              className="growfund-size-5 group-hover:growfund-hidden growfund-border-none growfund-bg-transparent"
              fit="cover"
            />
            <span>{field.config.label}</span>
            {field.type === 'manual-payment' && (
              <Badge variant="secondary">{__('Manual Payment', 'growfund')}</Badge>
            )}
          </div>
          <div className="growfund-flex growfund-items-center growfund-gap-2 growfund-h-6">
            <div className="growfund-hidden group-hover:growfund-flex">
              <Button
                variant="ghost"
                size="icon"
                className="growfund-cursor-pointer"
                onClick={onEdit}
                disabled={isDefined(editingIndex)}
              >
                <Edit />
              </Button>
            </div>
            <SwitchField control={form.control} name={`payments.${index}.is_enabled`} />
          </div>
        </div>
      </div>
    );
  }

  return (
    <div
      key={index}
      className="growfund-p-4 growfund-group growfund-flex growfund-items-center growfund-rounded-lg growfund-border growfund-bg-background-surface growfund-text-fg-primary"
    >
      <EditPaymentConfig
        index={index}
        onCancel={onCancel}
        field={field}
        onSave={onApply}
        onRemove={onRemove}
      />
    </div>
  );
};

export default PaymentMethodItem;

import { __ } from '@wordpress/i18n';
import { useEffect } from 'react';
import { useForm } from 'react-hook-form';

import { Button } from '@/components/ui/button';
import { Form } from '@/components/ui/form';
import { Image } from '@/components/ui/image';
import Renderer from '@/features/settings/features/payments/components/fields/renderer';
import { type Payment } from '@/features/settings/features/payments/schemas/payment';
import { isDefined } from '@/utils';
import { isMediaObject } from '@/utils/media';

interface EditPaymentConfigProps {
  field: Payment;
  index: number;
  onCancel?: () => void;
  onSave?: (value: Record<string, unknown>) => void;
  onRemove?: () => void;
}

const EditPaymentConfig = ({ field, onCancel, onSave, onRemove }: EditPaymentConfigProps) => {
  const form = useForm();

  useEffect(() => {
    if (isDefined(field.config)) {
      form.reset.call(null, field.config);
    }
  }, [field.config, form.reset]);

  const logoSrc = isMediaObject(field.config.logo) ? field.config.logo.url : field.config.logo;

  return (
    <div className="growfund-flex growfund-gap-2 growfund-items-center growfund-w-full">
      <div className="growfund-space-y-6 growfund-w-full">
        <div className="growfund-flex growfund-items-center growfund-justify-between">
          <div className="growfund-flex growfund-items-center growfund-gap-2">
            <Image
              src={logoSrc}
              alt={field.config.label}
              className="growfund-size-5 growfund-border-none growfund-bg-transparent"
              fit="cover"
            />
            <span>{field.config.label}</span>
          </div>
        </div>
        <Form {...form}>
          <Renderer fields={field.fields} />
        </Form>
        <div className="growfund-flex growfund-justify-between">
          {field.type === 'manual-payment' && (
            <Button variant="secondary" onClick={onRemove} className="hover:growfund-text-fg-critical">
              {__('Remove', 'growfund')}
            </Button>
          )}
          <div className="growfund-flex growfund-gap-2 growfund-ms-auto">
            <Button variant="outline" onClick={onCancel}>
              {__('Cancel', 'growfund')}
            </Button>
            <Button
              onClick={() => {
                onSave?.(form.getValues());
              }}
            >
              {__('Save', 'growfund')}
            </Button>
          </div>
        </div>
      </div>
    </div>
  );
};
export default EditPaymentConfig;

import { __ } from '@wordpress/i18n';
import { useState } from 'react';
import { useFieldArray, useFormContext } from 'react-hook-form';

import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { ManualPaymentFormDialog } from '@/features/settings/features/payments/components/manual-payment-dialog';
import PaymentGatewaySelectionDialog from '@/features/settings/features/payments/components/new-gateway-dialog';
import PaymentOptions from '@/features/settings/features/payments/components/payment-dropdown';
import PaymentMethods from '@/features/settings/features/payments/components/payment-methods/payment-methods';
import { type PaymentSettingsForm } from '@/features/settings/schemas/settings';
import { generatePaymentNameFromTitle } from '@/utils/settings';

const PaymentMethodsCard = () => {
  const [open, setOpen] = useState(false);
  const [openManualDialog, setOpenManualDialog] = useState(false);
  const [dropdownOpen, setDropdownOpen] = useState(false);

  const form = useFormContext<PaymentSettingsForm>();

  const handleActionChange = (actionType: string) => {
    if (actionType === 'new-gateway') {
      setOpen(true);
    } else if (actionType == 'manual-payment') {
      setOpenManualDialog(true);
    }
    setDropdownOpen(false);
  };

  const fieldArray = useFieldArray({ control: form.control, name: 'payments' });

  return (
    <Card>
      <CardHeader>
        <div className="growfund-flex growfund-justify-between">
          <div className="growfund-flex growfund-flex-col growfund-gap-4">
            <CardTitle>{__('Payment Methods', 'growfund')}</CardTitle>
            <CardDescription>
              {__('Select the payment methods you want to use.', 'growfund')}
            </CardDescription>
          </div>

          <PaymentOptions
            onActionChange={handleActionChange}
            open={dropdownOpen}
            onOpenChange={setDropdownOpen}
          />

          <PaymentGatewaySelectionDialog open={open} onOpenChange={setOpen} />
          <ManualPaymentFormDialog
            open={openManualDialog}
            onOpenChange={setOpenManualDialog}
            onSave={(values) => {
              fieldArray.append({
                type: 'manual-payment',
                name: generatePaymentNameFromTitle(values.title),
                class: '',
                download_url: '',
                config: {
                  label: values.title,
                  title: values.title,
                  logo: values.logo,
                  instruction: values.instruction,
                },
                fields: [
                  {
                    label: __('Title', 'growfund'),
                    name: 'title',
                    placeholder: __('Enter title', 'growfund'),
                    type: 'text',
                    options: null,
                  },
                  {
                    label: __('Logo', 'growfund'),
                    name: 'logo',
                    placeholder: '',
                    type: 'media',
                    options: null,
                  },
                  {
                    label: __('Instructions', 'growfund'),
                    name: 'instruction',
                    placeholder: __('Enter instruction', 'growfund'),
                    type: 'textarea',
                    options: null,
                  },
                ],
                is_installed: true,
                is_enabled: false,
              });
            }}
          />
        </div>
      </CardHeader>
      <CardContent>
        <PaymentMethods key={fieldArray.fields.length} />
      </CardContent>
    </Card>
  );
};

export default PaymentMethodsCard;

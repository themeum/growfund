import { __ } from '@wordpress/i18n';
import { useMemo } from 'react';
import { type FieldValues, type Path, type UseFormReturn } from 'react-hook-form';

import { SelectField } from '@/components/form/select-field';
import { Box, BoxContent } from '@/components/ui/box';
import { useGetPaymentMethodsQuery } from '@/services/payment';

const PaymentMethodCard = <T extends FieldValues & { payment_method?: string | null }>({
  form,
}: {
  form: UseFormReturn<T>;
}) => {
  // const form = useFormContext<PledgeForm | DonationForm>();
  const getPaymentMethodsQuery = useGetPaymentMethodsQuery({
    type: 'manual-payment',
  });

  const paymentMethodOptions = useMemo(() => {
    if (!getPaymentMethodsQuery.data) {
      return [];
    }
    return getPaymentMethodsQuery.data.map((paymentMethod) => ({
      value: paymentMethod.name,
      label: paymentMethod.config.label,
    }));
  }, [getPaymentMethodsQuery.data]);

  return (
    <Box>
      <BoxContent>
        <h6 className="growfund-typo-h6 growfund-font-medium growfund-text-fg-primary growfund-mb-3">
          {__('Payment Method', 'growfund')}
        </h6>
        <SelectField
          control={form.control}
          name={'payment_method' as Path<T>}
          options={paymentMethodOptions}
          placeholder={__('Select Payment Method', 'growfund')}
        />
      </BoxContent>
    </Box>
  );
};

export default PaymentMethodCard;

import { type FieldValues } from 'react-hook-form';

import { AppConfigKeys } from '@/features/settings/context/settings-context';
import { type Payment } from '@/features/settings/features/payments/schemas/payment';
import { isMediaObject } from '@/utils/media';

function preProcessSettingsData(data: FieldValues, key: AppConfigKeys): FieldValues {
  if (key === AppConfigKeys.Payment) {
    return {
      ...data,
      payments: Array.isArray(data.payments)
        ? data.payments.map((payment: Payment) => {
            return {
              ...payment,
              config: {
                ...payment.config,
                logo: isMediaObject(payment.config.logo)
                  ? payment.config.logo.id.toString()
                  : payment.config.logo,
              },
            };
          })
        : [],
    };
  }

  return data;
}

function generatePaymentNameFromTitle(title: string) {
  return title.toLowerCase().replace(/\s+/g, '-');
}

export { generatePaymentNameFromTitle, preProcessSettingsData };

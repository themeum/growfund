import { __ } from '@wordpress/i18n';
import z from 'zod';

import { MediaSchema } from '@/schemas/media';

const PaymentFieldTypeSchema = z.enum([
  'text',
  'textarea',
  'select',
  'switch',
  'password',
  'media',
]);

const GatewayConfigFieldSchema = z.object({
  label: z.string(),
  name: z.string(),
  placeholder: z.string(),
  type: PaymentFieldTypeSchema,
  options: z
    .array(
      z.object({
        label: z.string(),
        value: z.string(),
      }),
    )
    .nullish(),
});

const PaymentGatewaySchema = z.object({
  base_path: z.string(),
  class: z.string(),
  fields: z.array(GatewayConfigFieldSchema).default([]),
  form_file: z.string().nullish(),
  label: z.string(),
  logo: MediaSchema.or(z.string()),
  name: z.string(),
  supports_future_payments: z.boolean(),
  is_installed: z.boolean(),
});

const PaymentSchema = z.object({
  type: z.enum(['manual-payment', 'online-payment']),
  name: z.string(),
  config: z
    .object({
      label: z.string(),
      logo: MediaSchema.or(z.string()).nullish(),
    })
    .catchall(z.unknown()),
  is_installed: z.boolean().default(false),
  is_enabled: z.boolean().default(false),
  fields: z.array(GatewayConfigFieldSchema).default([]),
  download_url: z.string(),
  class: z.string(),
  supports_future_payments: z.boolean().nullish(),
  frontend_script: z.string().nullish(),
  form_file: z.string().nullish(),
});

const ManualPaymentSchema = z.object({
  id: z.coerce.string(),
  title: z.string({ message: __('Title is required', 'growfund') }),
  logo: MediaSchema.nullish(),
  instruction: z.string().nullish(),
});

const ManualPaymentFormSchema = ManualPaymentSchema.omit({ id: true });

type PaymentGateway = z.infer<typeof PaymentGatewaySchema>;
type GatewayField = z.infer<typeof GatewayConfigFieldSchema>;
type PaymentFieldType = z.infer<typeof PaymentFieldTypeSchema>;
type ManualPayment = z.infer<typeof ManualPaymentSchema>;
type Payment = z.infer<typeof PaymentSchema>;
type ManualPaymentForm = z.infer<typeof ManualPaymentFormSchema>;

export {
  GatewayConfigFieldSchema,
  ManualPaymentFormSchema,
  ManualPaymentSchema,
  PaymentGatewaySchema,
  PaymentSchema,
  type GatewayField,
  type ManualPayment,
  type ManualPaymentForm,
  type Payment,
  type PaymentFieldType,
  type PaymentGateway,
};

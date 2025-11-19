import { __ } from '@wordpress/i18n';
import { z } from 'zod';

import { MediaSchema } from '@/schemas/media';

const ManualPaymentSchema = z.object({
  title: z.string({ message: __('Title is required', 'growfund') }),
  logo: MediaSchema.nullish(),
  instruction: z.string({ message: __('Instruction is required', 'growfund') }),
});
type ManualPayment = z.infer<typeof ManualPaymentSchema>;

export { ManualPaymentSchema, type ManualPayment };

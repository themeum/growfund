import z from 'zod';

import { DonationResponseSchema } from '@/features/donations/schemas/donation';
import { DonorResponseSchema } from '@/features/donors/schemas/donor';

const DonorAnnualReceiptSchema = z.object({
  id: z.coerce.string(),
  year: z.string(),
  total_donations: z.number(),
  number_of_donations: z.number(),
});

const DonorAnnualReceiptDetailSchema = z.object({
  donor: DonorResponseSchema,
  donations: DonationResponseSchema.omit({
    campaign: true,
    fund: true,
    donor: true,
  }).array(),
});

type DonorAnnualReceipt = z.infer<typeof DonorAnnualReceiptSchema>;
type DonorAnnualReceiptDetail = z.infer<typeof DonorAnnualReceiptDetailSchema>;

export {
  DonorAnnualReceiptDetailSchema,
  DonorAnnualReceiptSchema,
  type DonorAnnualReceipt,
  type DonorAnnualReceiptDetail,
};

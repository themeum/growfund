import { useQuery } from '@tanstack/react-query';

import { endpoints } from '@/config/endpoints';
import { type Donation } from '@/features/donations/schemas/donation';
import { type EcardTemplate } from '@/features/settings/schemas/ecard';
import { type PdfReceiptTemplate } from '@/features/settings/schemas/pdf-receipt';
import { apiClient } from '@/lib/api';

const getDonationReceipt = (uid: string) => {
  return apiClient
    .get<{ donation: Donation; template: PdfReceiptTemplate }>(endpoints.DONATION_RECEIPT(uid))
    .then((response) => response.data);
};

const useDonationReceiptQuery = (uid: string) => {
  return useQuery({
    queryKey: ['DonationReceipt', uid],
    queryFn: () => getDonationReceipt(uid),
  });
};

const getECard = (uid: string) => {
  return apiClient
    .get<{ donation: Donation; template: EcardTemplate }>(endpoints.ECard(uid))
    .then((response) => response.data);
};

const useECardQuery = (uid: string) => {
  return useQuery({
    queryKey: ['eCard', uid],
    queryFn: () => getECard(uid),
  });
};

export { useDonationReceiptQuery, useECardQuery };

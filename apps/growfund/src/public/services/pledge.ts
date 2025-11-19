import { useQuery } from '@tanstack/react-query';

import { endpoints } from '@/config/endpoints';
import { type Pledge } from '@/features/pledges/schemas/pledge';
import { type PdfReceiptTemplate } from '@/features/settings/schemas/pdf-receipt';
import { apiClient } from '@/lib/api';

const getPledgeReceipt = (uid: string) => {
  return apiClient
    .get<{ pledge: Pledge; template: PdfReceiptTemplate }>(endpoints.PLEDGE_RECEIPT(uid))
    .then((response) => response.data);
};

const usePledgeReceiptQuery = (uid: string) => {
  return useQuery({
    queryKey: ['PledgeReceipt', uid],
    queryFn: () => getPledgeReceipt(uid),
  });
};

export { usePledgeReceiptQuery };

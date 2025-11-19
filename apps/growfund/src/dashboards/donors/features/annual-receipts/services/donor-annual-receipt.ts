import { useQuery } from '@tanstack/react-query';

import { endpoints } from '@/config/endpoints';
import {
  type DonorAnnualReceipt,
  type DonorAnnualReceiptDetail,
} from '@/dashboards/donors/features/annual-receipts/schema/donor-annual-receipt';
import { apiClient } from '@/lib/api';
import { type BaseQueryParams, type PaginatedResponse } from '@/types';

const getAnnualReceipts = (params: BaseQueryParams) => {
  return apiClient
    .get<PaginatedResponse<DonorAnnualReceipt>>(endpoints.DONOR_ANNUAL_RECEIPTS, {
      params,
    })
    .then((response) => response.data);
};

const useGetAnnualReceipts = (params: BaseQueryParams) => {
  return useQuery({
    queryKey: ['DonorAnnualReceipts', params],
    queryFn: () => getAnnualReceipts(params),
  });
};

const getAnnualReceiptDonationsByYear = (year: string) => {
  return apiClient
    .get<DonorAnnualReceiptDetail>(endpoints.DONOR_ANNUAL_RECEIPT_DONATIONS_BY_YEAR(year))
    .then((response) => response.data);
};

const useAnnualReceiptDonationsByYear = (year: string, enabled = true) => {
  return useQuery({
    queryKey: ['DonorAnnualReceipts', year],
    queryFn: () => getAnnualReceiptDonationsByYear(year),
    enabled,
  });
};

export { useAnnualReceiptDonationsByYear, useGetAnnualReceipts };

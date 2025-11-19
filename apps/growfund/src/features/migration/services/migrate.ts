import { useMutation, useQuery } from '@tanstack/react-query';

import { endpoints } from '@/config/endpoints';
import { type MigrationProcess } from '@/features/migration/schemas/migrate';
import { type WoocommerceConfig } from '@/features/migration/schemas/woocommerce';
import { apiClient } from '@/lib/api';

const handleMigration = (step: MigrationProcess) => {
  return apiClient.post(endpoints.MIGRATION, { step });
};

const useMigrationMutation = () => {
  return useMutation({
    mutationFn: handleMigration,
  });
};

const getWoocommerceConfig = () => {
  return apiClient
    .get<WoocommerceConfig>(endpoints.WOOCOMMERCE_CONFIG)
    .then((response) => response.data);
};

const useWoocommerceConfigQuery = () => {
  return useQuery({
    queryKey: ['WoocommerceConfig'],
    queryFn: () => getWoocommerceConfig(),
  });
};

export { useMigrationMutation, useWoocommerceConfigQuery };

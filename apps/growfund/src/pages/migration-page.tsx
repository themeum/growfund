import { useEffect, useMemo } from 'react';
import { useNavigate } from 'react-router';

import { LoadingSpinnerOverlay } from '@/components/layouts/loading-spinner';
import { OptionKeys } from '@/constants/option-keys';
import { MigrationProvider } from '@/features/migration/contexts/migration-context';
import MigrationLayout from '@/features/migration/layouts/migration-layout';
import { useManageWordpressLayout } from '@/hooks/use-wp-layout';
import { useGetOptionQuery } from '@/services/app-config';

const MigrationPage = () => {
  const { hideWordpressLayout, showWordpressLayout } = useManageWordpressLayout();
  const navigate = useNavigate();

  const migrationConsentOptionQuery = useGetOptionQuery(OptionKeys.CHECKED_MIGRATION_CONSENT);
  const isMigratedOptionQuery = useGetOptionQuery(OptionKeys.IS_MIGRATED_FROM_CROWDFUNDING);

  useEffect(() => {
    hideWordpressLayout();
    const banner = document.getElementById('growfund_admin_migration_banner');
    if (banner) {
      banner.style.display = 'none';
    }
  }, [hideWordpressLayout]);

  useEffect(() => {
    if (isMigratedOptionQuery.data === '1') {
      showWordpressLayout();
      void navigate('/', { replace: true });
    }
  }, [isMigratedOptionQuery.data, navigate, showWordpressLayout]);

  const isCheckedMigrationConsent = useMemo(() => {
    if (migrationConsentOptionQuery.data === '1') return true;

    return false;
  }, [migrationConsentOptionQuery.data]);

  if (
    migrationConsentOptionQuery.isPending ||
    isMigratedOptionQuery.isPending ||
    isMigratedOptionQuery.data === '1'
  ) {
    return <LoadingSpinnerOverlay />;
  }

  return (
    <MigrationProvider isCheckedMigrationConsent={isCheckedMigrationConsent}>
      <MigrationLayout />
    </MigrationProvider>
  );
};

export default MigrationPage;

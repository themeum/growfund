import { __ } from '@wordpress/i18n';
import { Receipt } from 'lucide-react';
import { useEffect } from 'react';

import { Container } from '@/components/layouts/container';
import { useAppConfig } from '@/contexts/app-config';
import DonorAnnualReceiptTable from '@/dashboards/donors/features/annual-receipts/components/table/annual-receipt-table';
import { useDashboardLayoutContext } from '@/dashboards/shared/contexts/root-layout-context';
import { AppConfigKeys } from '@/features/settings/context/settings-context';
import { User as CurrentUser } from '@/utils/user';

const UserAnnualReceiptPage = () => {
  const { appConfig } = useAppConfig();
  const { setTopbar } = useDashboardLayoutContext();
  useEffect(() => {
    setTopbar({
      title: __('Annual Receipts', 'growfund'),
      icon: Receipt,
    });
  }, [setTopbar]);

  if (!CurrentUser.isDonor() || appConfig[AppConfigKeys.Receipt]?.enable_annual_receipt === false) {
    return <div>{__('You are not allowed to access this page.', 'growfund')}</div>;
  }

  return (
    <Container className="growfund-mt-10">
      <div className="growfund-flex growfund-items-center growfund-justify-between">
        <h4 className="growfund-typo-h5 growfund-font-semibold growfund-text-primary">
          {__('Annual Receipts', 'growfund')}
        </h4>
      </div>
      <div className="growfund-mt-4 growfund-space-y-4">
        <DonorAnnualReceiptTable />
      </div>
    </Container>
  );
};

export default UserAnnualReceiptPage;

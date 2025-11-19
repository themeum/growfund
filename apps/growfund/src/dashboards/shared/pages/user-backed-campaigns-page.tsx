import { __ } from '@wordpress/i18n';

import BackerBackedCampaignsPageContent from '@/dashboards/backers/components/backer-backed-campaigns-page-content';
import { User as CurrentUser } from '@/utils/user';

const UserBackedCampaignsPage = () => {
  if (!CurrentUser.isBacker()) {
    return <div>{__('You are not allowed to access this page.', 'growfund')}</div>;
  }

  return <BackerBackedCampaignsPageContent />;
};

export default UserBackedCampaignsPage;

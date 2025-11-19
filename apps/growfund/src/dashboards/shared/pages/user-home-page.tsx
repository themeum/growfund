import { __ } from '@wordpress/i18n';

import BackerHomePageContent from '@/dashboards/backers/components/backer-home-page-content';
import DonorHomePageContent from '@/dashboards/donors/components/donor-home-page-content';
import { User as CurrentUser } from '@/utils/user';

const UserHomePage = () => {
  if (!CurrentUser.isBacker() && !CurrentUser.isDonor()) {
    return <div>{__('You are not allowed to access this page.', 'growfund')}</div>;
  }

  return CurrentUser.isBacker() ? <BackerHomePageContent /> : <DonorHomePageContent />;
};

export default UserHomePage;

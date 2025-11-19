import { __ } from '@wordpress/i18n';

import { ProSwitchInput } from '@/components/pro-fallbacks/form/pro-switch-input';
import { useAppConfig } from '@/contexts/app-config';

const UserPermissionsContributorCommentsFallback = () => {
  const { isDonationMode } = useAppConfig();
  return (
    <ProSwitchInput
      label={isDonationMode ? __('Donor Comments', 'growfund') : __('Backer Comments', 'growfund')}
      description={
        isDonationMode
          ? __('Allow donors to add comments to campaign.', 'growfund')
          : __('Allow backers to add comments to campaign.', 'growfund')
      }
      showProBadge
    />
  );
};

export default UserPermissionsContributorCommentsFallback;

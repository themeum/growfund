import { __ } from '@wordpress/i18n';

import { ProSwitchInput } from '@/components/pro-fallbacks/form/pro-switch-input';

const UserPermissionsAnonymousContributionsFallback = () => {
  return (
    <ProSwitchInput
      label={__('Anonymous Donations', 'growfund')}
      description={__('Allow donors to donate anonymously.', 'growfund')}
      showProBadge
    />
  );
};

export default UserPermissionsAnonymousContributionsFallback;

import { __ } from '@wordpress/i18n';

import ProCheckboxInput from '@/components/pro-fallbacks/form/pro-checkbox-input';

const CampaignAllowCustomDonationFallback = () => {
  return <ProCheckboxInput label={__('Allow custom donation amount', 'growfund')} showProBadge />;
};

export default CampaignAllowCustomDonationFallback;

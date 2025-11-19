import { __ } from '@wordpress/i18n';

import ProCheckboxInput from '@/components/pro-fallbacks/form/pro-checkbox-input';

const PdfReceiptSettingsTaxInformationFallback = () => {
  return <ProCheckboxInput label={__('Add Tax Information', 'growfund')} showProBadge />;
};

export default PdfReceiptSettingsTaxInformationFallback;

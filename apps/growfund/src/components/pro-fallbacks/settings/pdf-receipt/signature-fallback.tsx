import { __ } from '@wordpress/i18n';

import ProCheckboxInput from '@/components/pro-fallbacks/form/pro-checkbox-input';

const PdfReceiptSettingsSignatureFallback = () => {
  return <ProCheckboxInput label={__('Add Signature', 'growfund')} showProBadge />;
};

export default PdfReceiptSettingsSignatureFallback;

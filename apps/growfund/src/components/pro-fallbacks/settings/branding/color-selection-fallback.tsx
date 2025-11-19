import { __ } from '@wordpress/i18n';

import { ProColorPickerInput } from '@/components/pro-fallbacks/form/pro-color-picker-input';

const BrandingColorSelectionFallback = () => {
  return (
    <div className="growfund-space-y-4">
      <ProColorPickerInput
        label={__('Button Primary Color', 'growfund')}
        placeholder={__('e.g. #000000', 'growfund')}
        showProBadge
      />
      <ProColorPickerInput
        label={__('Button Hover Color', 'growfund')}
        placeholder={__('e.g. #000000', 'growfund')}
        showProBadge
      />

      <ProColorPickerInput
        label={__('Button Text Color', 'growfund')}
        placeholder={__('e.g. #000000', 'growfund')}
        showProBadge
      />
    </div>
  );
};

export default BrandingColorSelectionFallback;

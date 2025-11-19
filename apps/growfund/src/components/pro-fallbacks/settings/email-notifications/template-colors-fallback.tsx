import { __ } from '@wordpress/i18n';

import { ProColorPickerInput } from '@/components/pro-fallbacks/form/pro-color-picker-input';
import { Box, BoxContent, BoxTitle } from '@/components/ui/box';
import { ProBadge } from '@/components/ui/pro-badge';

const EmailTemplateSettingsColorsFallback = () => {
  return (
    <Box className="growfund-p-4">
      <BoxTitle>
        {__('Colors', 'growfund')} <ProBadge />
      </BoxTitle>
      <p className="growfund-typo-small growfund-text-fg-secondary growfund-mt-1">
        {__('Style how the emails will look', 'growfund')}
      </p>
      <BoxContent className="growfund-p-0 growfund-mt-4 growfund-space-y-4">
        {[
          __('Background', 'growfund'),
          __('Text', 'growfund'),
          __('Link', 'growfund'),
          __('Label', 'growfund'),
          __('Button Color', 'growfund'),
          __('Button BG', 'growfund'),
        ].map((color, index) => (
          <ProColorPickerInput key={index} label={color} />
        ))}
      </BoxContent>
    </Box>
  );
};

export default EmailTemplateSettingsColorsFallback;

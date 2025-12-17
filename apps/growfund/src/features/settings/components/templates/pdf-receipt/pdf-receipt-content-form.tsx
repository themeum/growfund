import { __ } from '@wordpress/i18n';
import { useMemo } from 'react';
import { useFormContext } from 'react-hook-form';

import ElementWrapper from '@/components/element-wrapper';
import { EditorField } from '@/components/form/editor-field';
import PdfReceiptSettingsSignatureFallback from '@/components/pro-fallbacks/settings/pdf-receipt/signature-fallback';
import PdfReceiptSettingsTaxInformationFallback from '@/components/pro-fallbacks/settings/pdf-receipt/tax-information-fallback';
import { useAppConfig } from '@/contexts/app-config';
import { AppConfigKeys } from '@/features/settings/context/settings-context';
import { type PdfReceiptTemplateForm } from '@/features/settings/schemas/pdf-receipt';
import { registry } from '@/lib/registry';

const PdfReceiptContentForm = ({
  shortCodes,
}: {
  shortCodes?: { label: string; value: string }[];
}) => {
  const { isDonationMode, appConfig } = useAppConfig();
  const form = useFormContext<PdfReceiptTemplateForm>();

  const shortcodes = useMemo(() => {
    if (isDonationMode) {
      return shortCodes?.filter((shortcode) => {
        if (shortcode.value === '{fund_name}') {
          return appConfig[AppConfigKeys.Campaign]?.allow_fund;
        }
        return true;
      });
    }

    return shortCodes;
  }, [isDonationMode, appConfig, shortCodes]);

  const PdfReceiptSettingsSignature = registry.get('PdfReceiptSettingsSignature');
  const PdfReceiptSettingsTaxInformation = registry.get('PdfReceiptSettingsTaxInformation');

  return (
    <div className="growfund-w-full growfund-space-y-4">
      <EditorField
        control={form.control}
        name="content.greetings"
        label={__('Greetings', 'growfund')}
        isMinimal
        toolbar1="bold italic | alignleft aligncenter alignright | outdent indent | shortcode_button"
        shortCodes={shortcodes}
      />
      {isDonationMode && (
        <ElementWrapper fallback={<PdfReceiptSettingsSignatureFallback />}>
          {PdfReceiptSettingsSignature && <PdfReceiptSettingsSignature />}
        </ElementWrapper>
      )}
      <ElementWrapper fallback={<PdfReceiptSettingsTaxInformationFallback />}>
        {PdfReceiptSettingsTaxInformation && <PdfReceiptSettingsTaxInformation />}
      </ElementWrapper>
      <EditorField
        control={form.control}
        name="content.footer"
        label={__('Footer', 'growfund')}
        rows={4}
        isMinimal
        toolbar1="bold italic | alignleft aligncenter alignright | outdent indent"
      />
    </div>
  );
};

export default PdfReceiptContentForm;

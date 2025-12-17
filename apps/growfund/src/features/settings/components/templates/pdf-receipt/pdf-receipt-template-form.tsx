import { __ } from '@wordpress/i18n';
import { useEffect } from 'react';
import { useFormContext } from 'react-hook-form';

import ElementWrapper from '@/components/element-wrapper';
import { Container } from '@/components/layouts/container';
import PdfReceiptSettingsColorsFallback from '@/components/pro-fallbacks/settings/pdf-receipt/colors-fallback';
import {
  TemplateFormContentSection,
  TemplateFormImageSection,
} from '@/components/template-form/template-form';
import PdfAnnualReceiptContentForm from '@/features/settings/components/templates/pdf-receipt/pdf-annual-receipt-content-form';
import PdfAnnualReceiptPreview from '@/features/settings/components/templates/pdf-receipt/pdf-annual-receipt-preview';
import PdfReceiptContentForm from '@/features/settings/components/templates/pdf-receipt/pdf-receipt-content-form';
import PdfReceiptPreview from '@/features/settings/components/templates/pdf-receipt/pdf-receipt-preview';
import {
  type PdfReceiptTemplate,
  type PdfReceiptTemplateForm,
} from '@/features/settings/schemas/pdf-receipt';
import { registry } from '@/lib/registry';
import { isDefined } from '@/utils';

const PdfReceiptTemplateForm = ({
  pdfReceiptTemplate,
  isAnnualReceiptTemplate = false,
}: {
  pdfReceiptTemplate?: PdfReceiptTemplate | null;
  isAnnualReceiptTemplate?: boolean;
}) => {
  const form = useFormContext<PdfReceiptTemplateForm>();

  useEffect(() => {
    if (isDefined(pdfReceiptTemplate) && Object.keys(pdfReceiptTemplate).length !== 0) {
      form.reset.call(null, pdfReceiptTemplate);
    }
  }, [pdfReceiptTemplate, form.reset]);

  const values = form.watch();

  const PdfReceiptSettingsColors = registry.get('PdfReceiptSettingsColors');

  return (
    <Container className="growfund-mt-6">
      <div className="growfund-grid growfund-grid-cols-10 growfund-gap-4">
        <div className="growfund-col-span-4">
          <div className="growfund-space-y-4">
            <TemplateFormImageSection
              namePrefix="media"
              control={form.control}
              header={__('Logo', 'growfund')}
              description={__('Update the logo & style your way', 'growfund')}
            />
            <ElementWrapper fallback={<PdfReceiptSettingsColorsFallback />}>
              {PdfReceiptSettingsColors && <PdfReceiptSettingsColors />}
            </ElementWrapper>
            <TemplateFormContentSection
              header={__('Contents', 'growfund')}
              description={__('Manage the pdf contents from here', 'growfund')}
            >
              {isAnnualReceiptTemplate ? (
                <PdfAnnualReceiptContentForm shortCodes={pdfReceiptTemplate?.short_codes} />
              ) : (
                <PdfReceiptContentForm shortCodes={pdfReceiptTemplate?.short_codes} />
              )}
            </TemplateFormContentSection>
          </div>
        </div>
        <div className="growfund-col-span-6">
          <div className="growfund-sticky growfund-top-[calc(var(--growfund-topbar-height)+var(--growfund-spacing)_*_1.5)] growfund-h-[calc(100vh+var(--growfund-topbar-height)+var(--growfund-spacing)_*_1.5]">
            {isAnnualReceiptTemplate ? (
              <PdfAnnualReceiptPreview pdfReceipt={values} />
            ) : (
              <PdfReceiptPreview pdfReceipt={values} />
            )}
          </div>
        </div>
      </div>
    </Container>
  );
};

export default PdfReceiptTemplateForm;

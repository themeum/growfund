import { zodResolver } from '@hookform/resolvers/zod';
import { useEffect, useMemo, useRef } from 'react';
import { useForm } from 'react-hook-form';

import { LoadingSpinnerOverlay } from '@/components/layouts/loading-spinner';
import { TemplateForm } from '@/components/template-form/template-form';
import { OptionKeys } from '@/constants/option-keys';
import PdfReceiptTemplateForm from '@/features/settings/components/templates/pdf-receipt/pdf-receipt-template-form';
import { useTemplateLayoutContext } from '@/features/settings/context/template-layout-context';
import { useUpdateTemplateDirtyState } from '@/features/settings/hooks/use-update-dirty-state';
import {
  PdfReceiptTemplateSchema,
  type PdfReceiptTemplate,
  type PdfReceiptTemplateForm as PdfReceiptTemplateFormType,
} from '@/features/settings/schemas/pdf-receipt';
import { useGetOptionQuery } from '@/services/app-config';

const PdfAnnualReceiptTemplate = () => {
  const formRef = useRef<HTMLFormElement | null>(null);

  const pdfReceiptTemplateQuery = useGetOptionQuery(OptionKeys.PDF_ANNUAL_RECEIPT_TEMPLATE);

  const form = useForm<PdfReceiptTemplateFormType>({
    resolver: zodResolver(PdfReceiptTemplateSchema),
  });

  const { registerForm } = useTemplateLayoutContext<PdfReceiptTemplateFormType>();

  useEffect(() => {
    const cleanup = registerForm(OptionKeys.PDF_ANNUAL_RECEIPT_TEMPLATE, form);
    return () => {
      cleanup();
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [registerForm]);

  useUpdateTemplateDirtyState(form);

  const pdfReceiptTemplate = useMemo(() => {
    if (!pdfReceiptTemplateQuery.data) {
      return null;
    }
    return pdfReceiptTemplateQuery.data as PdfReceiptTemplate;
  }, [pdfReceiptTemplateQuery.data]);

  if (pdfReceiptTemplateQuery.isLoading || pdfReceiptTemplateQuery.isPending) {
    return <LoadingSpinnerOverlay />;
  }

  return (
    <TemplateForm form={form} ref={formRef}>
      <PdfReceiptTemplateForm pdfReceiptTemplate={pdfReceiptTemplate} isAnnualReceiptTemplate />
    </TemplateForm>
  );
};

export default PdfAnnualReceiptTemplate;

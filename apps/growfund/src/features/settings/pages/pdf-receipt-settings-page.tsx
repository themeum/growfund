import { zodResolver } from '@hookform/resolvers/zod';
import { __ } from '@wordpress/i18n';
import { Paintbrush } from 'lucide-react';
import { useEffect } from 'react';
import { useForm } from 'react-hook-form';
import { useNavigate } from 'react-router';

import FeatureGuard from '@/components/feature-guard';
import PdfReceiptSettingsAnnualReceiptFallback from '@/components/pro-fallbacks/settings/pdf-receipt/annual-receipt-fallback';
import { Button } from '@/components/ui/button';
import { Card, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Form } from '@/components/ui/form';
import { RouteConfig } from '@/config/route-config';
import { useAppConfig } from '@/contexts/app-config';
import { AppConfigKeys, useSettingsContext } from '@/features/settings/context/settings-context';
import { useUpdateDirtyState } from '@/features/settings/hooks/use-update-dirty-state';
import {
    PDFReceiptSettingsSchema,
    type PDFReceiptSettingsForm,
} from '@/features/settings/schemas/settings';
import { useRouteBlockerGuard } from '@/hooks/use-route-blocker-guard';
import { registry } from '@/lib/registry';
import { getDefaults } from '@/lib/zod';

const PDFReceiptSettingsPage = () => {
  const navigate = useNavigate();
  const { appConfig, isDonationMode } = useAppConfig();
  const form = useForm<PDFReceiptSettingsForm>({
    resolver: zodResolver(PDFReceiptSettingsSchema),
  });

  useEffect(() => {
    form.reset.call(null, {
      ...getDefaults(PDFReceiptSettingsSchema),
      ...(appConfig[AppConfigKeys.Receipt] ?? {}),
    });
  }, [appConfig, form.reset]);

  const { registerForm, isCurrentFormDirty } = useSettingsContext<PDFReceiptSettingsForm>();

  useEffect(() => {
    const cleanup = registerForm(AppConfigKeys.Receipt, form);
    return () => {
      cleanup();
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [registerForm]);

  useUpdateDirtyState(form);
  useRouteBlockerGuard({ isDirty: isCurrentFormDirty });

  const PdfReceiptSettingsAnnualReceipt = registry.get('PdfReceiptSettingsAnnualReceipt');

  return (
    <Form {...form}>
      <div className="growfund-grid growfund-gap-4">
        <Card>
          <CardHeader className="growfund-p-5">
            <div className="growfund-flex growfund-items-center growfund-justify-between">
              <div className="growfund-space-y-2">
                <CardTitle className="growfund-flex growfund-items-center growfund-gap-2">
                  <Paintbrush />
                  <div>{__('Default PDF Template', 'growfund')}</div>
                </CardTitle>
                <CardDescription className="growfund-w-[90%]">
                  {__(
                    'Customize your receipt template with your logo, colors, greetings, footer and more.',
                    'growfund',
                  )}
                </CardDescription>
              </div>
              <Button
                variant="outline"
                onClick={() => navigate(RouteConfig.PdfReceiptTemplate.buildLink())}
              >
                {__('Edit', 'growfund')}
              </Button>
            </div>
          </CardHeader>
        </Card>

        {/* Annual Receipt */}
        {isDonationMode && (
          <FeatureGuard
            feature="settings.pdf_receipt.annual_receipt"
            fallback={<PdfReceiptSettingsAnnualReceiptFallback />}
          >
            {PdfReceiptSettingsAnnualReceipt && <PdfReceiptSettingsAnnualReceipt />}
          </FeatureGuard>
        )}
      </div>
    </Form>
  );
};

export default PDFReceiptSettingsPage;

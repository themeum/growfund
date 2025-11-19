import { __ } from '@wordpress/i18n';
import { Paintbrush } from 'lucide-react';
import { Outlet, useNavigate } from 'react-router';
import { toast } from 'sonner';

import { LoadingSpinnerOverlay } from '@/components/layouts/loading-spinner';
import { Page, PageContent, PageHeader } from '@/components/layouts/page';
import { Button } from '@/components/ui/button';
import { RouteConfig } from '@/config/route-config';
import { useTemplateLayoutContext } from '@/features/settings/context/template-layout-context';
import { useCurrentPath } from '@/hooks/use-current-path';
import { useStoreOptionMutation } from '@/services/app-config';
import { isDefined } from '@/utils';

const pageHeaders = {
  [RouteConfig.EcardTemplate.template]: __('eCard Template', 'growfund'),
  [RouteConfig.PdfReceiptTemplate.template]: __('Pdf Receipt', 'growfund'),
  [RouteConfig.PdfAnnualReceiptTemplate.template]: __('Pdf Annual Receipt', 'growfund'),
  [RouteConfig.EmailNotificationTemplate.template]: __('Email Template', 'growfund'),
} as const;

const successMessages = {
  [RouteConfig.EcardTemplate.template]: __('eCard template saved successfully', 'growfund'),
  [RouteConfig.PdfReceiptTemplate.template]: __('Pdf Receipt saved successfully', 'growfund'),
  [RouteConfig.PdfAnnualReceiptTemplate.template]: __(
    'Pdf Annual Receipt saved successfully',
    'growfund',
  ),
  [RouteConfig.EmailNotificationTemplate.template]: __(
    'Email template saved successfully',
    'growfund',
  ),
} as const;

const TemplateLayoutContent = () => {
  const currentPath = useCurrentPath();
  const navigate = useNavigate();
  const { isLoading, getCurrentForm, getCurrentKey, isCurrentFormDirty } =
    useTemplateLayoutContext();
  const storeOptionMutation = useStoreOptionMutation();
  const optionKey = getCurrentKey();

  if (isLoading) {
    return <LoadingSpinnerOverlay />;
  }

  const form = getCurrentForm();

  const handleFormSubmit = () => {
    if (!form || !optionKey) return;

    void form.handleSubmit(
      (values) => {
        storeOptionMutation.mutate(
          {
            key: optionKey,
            data: values,
          },
          {
            onSuccess() {
              toast.success(
                isDefined(currentPath)
                  ? successMessages[currentPath as keyof typeof pageHeaders]
                  : __('Saved successfully', 'growfund'),
              );
            },
          },
        );
      },
      (errors) => {
        console.error(errors);
      },
    )();
  };

  const isSaving = storeOptionMutation.isPending;

  return (
    <Page>
      <PageHeader
        icon={<Paintbrush />}
        name={
          isDefined(currentPath)
            ? pageHeaders[currentPath as keyof typeof pageHeaders]
            : __('Template', 'growfund')
        }
        onGoBack={() => void navigate(-1)}
        variant="fluid"
        action={
          <div className="growfund-flex growfund-items-center growfund-gap-3">
            <Button
              variant="outline"
              onClick={() => {
                form?.reset();
              }}
              disabled={isSaving || !isCurrentFormDirty}
            >
              {__('Discard', 'growfund')}
            </Button>
            <Button
              onClick={handleFormSubmit}
              loading={isSaving}
              disabled={isSaving || !isCurrentFormDirty}
            >
              {__('Save', 'growfund')}
            </Button>
          </div>
        }
      />
      <PageContent>
        <Outlet />
      </PageContent>
    </Page>
  );
};

export default TemplateLayoutContent;

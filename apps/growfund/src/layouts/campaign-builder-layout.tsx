import { __ } from '@wordpress/i18n';
import { SquareArrowOutUpRight } from 'lucide-react';
import { useEffect, useMemo, useState } from 'react';
import { useFormContext } from 'react-hook-form';
import { Outlet, useNavigate } from 'react-router';

import { Page, PageContent, PageHeader } from '@/components/layouts/page';
import { Button } from '@/components/ui/button';
import { RouteConfig } from '@/config/route-config';
import { useAppConfig } from '@/contexts/app-config';
import CampaignNavigation from '@/features/campaigns/components/campaign-navigation';
import CampaignPublishedDialog from '@/features/campaigns/components/dialogs/campaign-published-dialog';
import CampaignSubmittedForReviewDialog from '@/features/campaigns/components/dialogs/campaign-submitted-for-review-dialog';
import {
    CampaignBuilderContextProvider,
    useCampaignBuilderContext,
} from '@/features/campaigns/contexts/campaign-builder';
import { useCampaign } from '@/features/campaigns/contexts/campaign-context';
import { type CampaignBuilderForm } from '@/features/campaigns/schemas/campaign';
import { useUpdateCampaignMutation } from '@/features/campaigns/services/campaign';
import { checkErrorOn } from '@/features/campaigns/utils/utils';
import { AppConfigKeys } from '@/features/settings/context/settings-context';
import useCurrentUser from '@/hooks/use-current-user';
import { useFormErrorHandler } from '@/hooks/use-form-error-handler';
import { isDefined } from '@/utils';

const CampaignBuilderLayoutContent = () => {
  const { isFundraiser } = useCurrentUser();
  const { appConfig } = useAppConfig();
  const [isPublished, setIsPublished] = useState(false);
  const [isSubmittedForReview, setIsSubmittedForReview] = useState(false);
  const [clickedOn, setClickedOn] = useState<'publish' | 'draft' | null>(null);
  const navigate = useNavigate();
  const { setErrorOn, activeStep } = useCampaignBuilderContext();
  const { campaign } = useCampaign();
  const updateCampaignMutation = useUpdateCampaignMutation();
  const form = useFormContext<CampaignBuilderForm>();
  const { createErrorHandler } = useFormErrorHandler(form);

  const errors = form.formState.errors;

  useEffect(() => {
    if (Object.keys(errors).length > 0) {
      const errorOn = checkErrorOn(errors as unknown as Record<string, string[]>);

      setErrorOn?.(errorOn.filter((step) => step !== activeStep));
    }
  }, [errors, activeStep, setErrorOn]);

  const isCampaignActive = useMemo(() => {
    return ['published', 'funded', 'completed'].includes(campaign.status);
  }, [campaign.status]);

  const onSubmit = (values: CampaignBuilderForm, status: 'draft' | 'published' | null) => {
    updateCampaignMutation.mutate(
      {
        ...values,
        ...(isDefined(status) && { status }),
      },
      {
        onError: (error) => {
          const errorOn = checkErrorOn(
            (error as unknown as { errors: Record<string, string[]> }).errors,
          );

          setErrorOn?.(errorOn.filter((step) => step !== activeStep));

          return createErrorHandler()(error);
        },
        onSuccess: () => {
          if (status === 'published') {
            if (
              isFundraiser &&
              !appConfig[AppConfigKeys.UserAndPermissions]?.fundraisers_can_publish_campaigns
            ) {
              setIsSubmittedForReview(true);
              return;
            }

            if (!isCampaignActive) {
              setIsPublished(true);
              return;
            }
          }
        },
      },
    );
  };

  return (
    <Page>
      <div>
        <PageHeader
          name={__('Edit Campaign', 'growfund')}
          onGoBack={() => void navigate(RouteConfig.Campaigns.buildLink())}
          variant="fluid"
          action={
            <div className="growfund-flex growfund-items-center growfund-gap-3">
              {campaign.preview_url && (
                <Button
                  variant="ghost"
                  onClick={() => {
                    if (campaign.preview_url) {
                      window.open(campaign.preview_url, '_blank');
                    }
                  }}
                >
                  {__('Preview', 'growfund')}
                  <SquareArrowOutUpRight />
                </Button>
              )}

              {!isCampaignActive && (
                <Button
                  variant="outline"
                  onClick={form.handleSubmit(
                    (values) => {
                      setClickedOn('draft');
                      onSubmit(values, 'draft');
                    },
                    (error) => {
                      console.error(error);
                    },
                  )}
                  disabled={updateCampaignMutation.isPending}
                  loading={updateCampaignMutation.isPending && clickedOn === 'draft'}
                >
                  {__('Save as Draft', 'growfund')}
                </Button>
              )}
              <Button
                onClick={form.handleSubmit(
                  (values) => {
                    setClickedOn('publish');
                    onSubmit(values, !isCampaignActive ? 'published' : null);
                  },
                  (errors) => {
                    console.error(errors);
                  },
                )}
                loading={updateCampaignMutation.isPending && clickedOn === 'publish'}
                disabled={updateCampaignMutation.isPending}
              >
                {isCampaignActive ? __('Save Changes', 'growfund') : __('Publish', 'growfund')}
              </Button>
            </div>
          }
        >
          <CampaignNavigation />
        </PageHeader>
        <PageContent className="growfund-mt-10">
          <Outlet />
        </PageContent>
      </div>
      <CampaignPublishedDialog
        open={isPublished}
        onOpenChange={setIsPublished}
        url={campaign.preview_url ?? ''}
      />
      <CampaignSubmittedForReviewDialog
        open={isSubmittedForReview}
        onOpenChange={setIsSubmittedForReview}
      />
    </Page>
  );
};

const CampaignBuilderLayout = () => {
  return (
    <CampaignBuilderContextProvider>
      <CampaignBuilderLayoutContent />
    </CampaignBuilderContextProvider>
  );
};

export default CampaignBuilderLayout;

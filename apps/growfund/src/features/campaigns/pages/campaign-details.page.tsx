import { __ } from '@wordpress/i18n';
import { useState } from 'react';
import { useNavigate } from 'react-router';

import { CampaignEmptyStateIcon } from '@/app/icons';
import { EmptyState, EmptyStateDescription } from '@/components/empty-state';
import { ErrorState, ErrorStateDescription } from '@/components/error-state';
import { LoadingSpinnerOverlay } from '@/components/layouts/loading-spinner';
import { RouteConfig } from '@/config/route-config';
import CampaignDetailsSheet from '@/features/campaigns/components/sheets/campaign-details-sheet';
import { useCampaignDetailsQuery } from '@/features/campaigns/services/campaign';
import { useRouteParams } from '@/hooks/use-route-params';
import { matchQueryStatus } from '@/utils/match-query-status';

const CampaignDetailsPage = () => {
  const [open, setOpen] = useState(true);
  const navigate = useNavigate();
  const { id } = useRouteParams(RouteConfig.CampaignDetails);
  const campaignDetailsQuery = useCampaignDetailsQuery(id);

  return matchQueryStatus(campaignDetailsQuery, {
    Loading: <LoadingSpinnerOverlay />,
    Error: (
      <ErrorState className="growfund-mt-10">
        <ErrorStateDescription>
          <CampaignEmptyStateIcon />
          <div>{__('Campaign not found.', 'growfund')}</div>
        </ErrorStateDescription>
      </ErrorState>
    ),
    Empty: (
      <EmptyState className="growfund-mt-10">
        <EmptyStateDescription className="growfund-flex growfund-flex-col growfund-items-center">
          <CampaignEmptyStateIcon />
          <div>{__('Campaign not found.', 'growfund')}</div>
        </EmptyStateDescription>
      </EmptyState>
    ),
    Success: (response) => (
      <CampaignDetailsSheet
        open={open}
        onOpenChange={(open) => {
          if (!open) {
            setOpen(false);
            setTimeout(() => {
              void navigate(-1);
            }, 300);
          }
        }}
        campaign={response.data}
      />
    ),
  });
};

export default CampaignDetailsPage;

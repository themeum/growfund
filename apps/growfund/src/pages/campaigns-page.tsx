import { __ } from '@wordpress/i18n';
import { Plus } from 'lucide-react';
import { useNavigate } from 'react-router';

import { Container } from '@/components/layouts/container';
import { Page, PageContent, PageHeader } from '@/components/layouts/page';
import { Button } from '@/components/ui/button';
import { RouteConfig } from '@/config/route-config';
import CampaignTable from '@/features/campaigns/components/campaign-table/campaign-table';
import { useCreateCampaignMutation } from '@/features/campaigns/services/campaign';

const CampaignsPage = () => {
  const navigate = useNavigate();
  const createCampaignMutation = useCreateCampaignMutation();

  return (
    <Page>
      <PageHeader
        name={__('Campaigns', 'growfund')}
        action={
          <>
            <Button
              onClick={async () => {
                const response = await createCampaignMutation.mutateAsync();
                void navigate(RouteConfig.CampaignBuilder.buildLink({ id: response.id }));
              }}
              loading={createCampaignMutation.isPending}
            >
              <Plus />
              {__('New Campaign', 'growfund')}
            </Button>
          </>
        }
      />

      <PageContent>
        <Container className="growfund-my-10">
          <CampaignTable />
        </Container>
      </PageContent>
    </Page>
  );
};

export default CampaignsPage;

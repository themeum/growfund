import { __ } from '@wordpress/i18n';
import { Plus } from 'lucide-react';
import { useNavigate } from 'react-router';

import { Container } from '@/components/layouts/container';
import { Page, PageContent, PageHeader } from '@/components/layouts/page';
import { Button } from '@/components/ui/button';
import { RouteConfig } from '@/config/route-config';
import PledgeList from '@/features/pledges/components/list/pledge-list';

const PledgesPage = () => {
  const navigate = useNavigate();

  return (
    <Page>
      <PageHeader
        name={__('Pledges', 'growfund')}
        action={
          <Button onClick={() => navigate(RouteConfig.CreatePledge.buildLink())}>
            <Plus />
            {__('New Pledge', 'growfund')}
          </Button>
        }
      />

      <PageContent>
        <Container className="growfund-my-10">
          <PledgeList />
        </Container>
      </PageContent>
    </Page>
  );
};

export default PledgesPage;

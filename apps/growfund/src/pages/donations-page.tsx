import { __ } from '@wordpress/i18n';
import { Plus } from 'lucide-react';
import { useNavigate } from 'react-router';

import { Container } from '@/components/layouts/container';
import { Page, PageContent, PageHeader } from '@/components/layouts/page';
import { Button } from '@/components/ui/button';
import { RouteConfig } from '@/config/route-config';
import DonationsList from '@/features/donations/list/donation-listing-page';

const DonationsPage = () => {
  const navigate = useNavigate();

  return (
    <Page>
      <PageHeader
        name={__('Donations', 'growfund')}
        action={
          <Button onClick={() => navigate(RouteConfig.CreateDonation.buildLink())}>
            <Plus />
            {__('New Donation', 'growfund')}
          </Button>
        }
      />

      <PageContent>
        <Container className="growfund-my-10">
          <DonationsList />
        </Container>
      </PageContent>
    </Page>
  );
};

export default DonationsPage;

import { __ } from '@wordpress/i18n';
import { Plus } from 'lucide-react';
import { useState } from 'react';

import { Container } from '@/components/layouts/container';
import { Page, PageContent, PageHeader } from '@/components/layouts/page';
import { Button } from '@/components/ui/button';
import ManageDonorDialog from '@/features/donors/components/dialogs/manage-donor-dialog';
import DonorsTable from '@/features/donors/components/donors-table/donors-table';

const DonorsListingPage = () => {
  const [open, setOpen] = useState(false);
  return (
    <Page>
      <PageHeader
        name={__('Donors', 'growfund')}
        action={
          <ManageDonorDialog isOpen={open} onOpenChange={setOpen}>
            <Button
              onClick={() => {
                setOpen(true);
              }}
            >
              <Plus />
              {__('New Donor', 'growfund')}
            </Button>
          </ManageDonorDialog>
        }
      />
      <PageContent>
        <Container className="growfund-mt-10">
          <DonorsTable />
        </Container>
      </PageContent>
    </Page>
  );
};

export default DonorsListingPage;

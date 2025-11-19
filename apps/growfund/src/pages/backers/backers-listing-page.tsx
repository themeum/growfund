import { __ } from '@wordpress/i18n';
import { Plus } from 'lucide-react';
import { useState } from 'react';

import { Container } from '@/components/layouts/container';
import { Page, PageContent, PageHeader } from '@/components/layouts/page';
import { Button } from '@/components/ui/button';
import BackersTable from '@/features/backers/components/backers-table';
import ManageBackerDialog from '@/features/pledges/components/dialogs/manage-backer-dialog';

const BackersListingPage = () => {
  const [open, setOpen] = useState(false);
  return (
    <Page>
      <PageHeader
        name={__('Backers', 'growfund')}
        action={
          <ManageBackerDialog open={open} onOpenChange={setOpen}>
            <Button
              onClick={() => {
                setOpen(true);
              }}
            >
              <Plus />
              {__('New Backer', 'growfund')}
            </Button>
          </ManageBackerDialog>
        }
      />
      <PageContent>
        <Container>
          <BackersTable />
        </Container>
      </PageContent>
    </Page>
  );
};

export default BackersListingPage;

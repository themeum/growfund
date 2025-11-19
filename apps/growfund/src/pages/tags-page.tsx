import { __ } from '@wordpress/i18n';
import { Plus } from 'lucide-react';
import { useState } from 'react';

import { Container } from '@/components/layouts/container';
import { Page, PageContent, PageHeader } from '@/components/layouts/page';
import { Button } from '@/components/ui/button';
import { TagFormDialog } from '@/features/tags/components/dialogs/tag-form-dialog';
import TagsTable from '@/features/tags/components/tags-table/tags-table';

const TagsPage = () => {
  const [open, setOpen] = useState(false);

  return (
    <Page>
      <PageHeader
        name={__('Tags', 'growfund')}
        action={
          <TagFormDialog open={open} onOpenChange={setOpen}>
            <Button>
              <Plus />
              {__('New Tag', 'growfund')}
            </Button>
          </TagFormDialog>
        }
      />

      <PageContent>
        <Container className="growfund-my-10">
          <TagsTable />
        </Container>
      </PageContent>
    </Page>
  );
};

export default TagsPage;

import { __ } from '@wordpress/i18n';
import { Plus } from 'lucide-react';
import { useState } from 'react';

import { Container } from '@/components/layouts/container';
import { Page, PageContent, PageHeader } from '@/components/layouts/page';
import { Button } from '@/components/ui/button';
import CategoriesTable from '@/features/categories/components/categories-table/categories-table';
import { CategoryFormDialog } from '@/features/categories/components/dialogs/category-form-dialog';

const CategoriesPage = () => {
  const [open, setOpen] = useState(false);
  return (
    <Page>
      <PageHeader
        name={__('Categories', 'growfund')}
        action={
          <CategoryFormDialog open={open} onOpenChange={setOpen}>
            <Button>
              <Plus />
              {__('New Category', 'growfund')}
            </Button>
          </CategoryFormDialog>
        }
      />

      <PageContent>
        <Container className="growfund-my-10">
          <CategoriesTable />
        </Container>
      </PageContent>
    </Page>
  );
};

export default CategoriesPage;

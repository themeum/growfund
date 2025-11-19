import { __ } from '@wordpress/i18n';

import { EmptyThemesIcon } from '@/app/icons';
import { EmptyState, EmptyStateDescription } from '@/components/empty-state';
import { Container } from '@/components/layouts/container';
import { Page, PageContent, PageHeader } from '@/components/layouts/page';

const ThemesPage = () => {
  return (
    <Page>
      <PageHeader name={__('Themes', 'growfund')} />

      <PageContent>
        <Container className="growfund-my-8">
          <div className="growfund-w-full">
            <EmptyState>
              <EmptyThemesIcon />
              <h4 className="growfund-typo-h4 growfund-font-semibold growfund-text-fg-primary">
                {__('Themes are coming soon!', 'growfund')}
              </h4>
              <EmptyStateDescription>
                {__('Stay tuned for exciting new themes, arriving soon!', 'growfund')}
              </EmptyStateDescription>
            </EmptyState>
          </div>
        </Container>
      </PageContent>
    </Page>
  );
};

export default ThemesPage;

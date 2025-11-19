import { __ } from '@wordpress/i18n';

import { ErrorState, ErrorStateDescription } from '@/components/error-state';
import { Page, PageContent, PageHeader } from '@/components/layouts/page';

const NotFoundPage = () => {
  return (
    <Page>
      <PageHeader variant="fluid" />
      <PageContent>
        <ErrorState className="growfund-mt-10">
          <ErrorStateDescription className="growfund-flex growfund-flex-col growfund-items-center">
            <div className="growfund-typo-h1">{__('404', 'growfund')}</div>
            <div>{__('Page not found.', 'growfund')}</div>
          </ErrorStateDescription>
        </ErrorState>
      </PageContent>
    </Page>
  );
};

export default NotFoundPage;

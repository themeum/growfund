import { __ } from '@wordpress/i18n';

import { Container } from '@/components/layouts/container';
import { Page, PageContent, PageHeader } from '@/components/layouts/page';

const FundraiserBookmarksPage = () => {
  return (
    <Page>
      <PageHeader name={__('Bookmarks', 'growfund')} />
      <PageContent>
        <Container className="growfund-my-10">
          <h1 className="growfund-typo-h1">{__('Bookmarks', 'growfund')}</h1>
        </Container>
      </PageContent>
    </Page>
  );
};

export default FundraiserBookmarksPage;

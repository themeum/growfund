import { __ } from '@wordpress/i18n';

import { Container } from '@/components/layouts/container';
import { Page, PageContent, PageHeader } from '@/components/layouts/page';

const SettingsPage = () => {
  return (
    <Page>
      <PageHeader title={__('Settings', 'growfund')} />
      <PageContent>
        <Container>
          <h1>Settings</h1>
        </Container>
      </PageContent>
    </Page>
  );
};

export default SettingsPage;

import { __ } from '@wordpress/i18n';

import { FundraiserEmptyStateIcon } from '@/app/icons';
import { Container } from '@/components/layouts/container';
import { Page, PageContent, PageHeader } from '@/components/layouts/page';
import { Card, CardContent } from '@/components/ui/card';
import { ProButton } from '@/components/ui/pro-button';

const FundraisersFallback = () => {
  return (
    <Page>
      <PageHeader name={__('Fundraisers', 'growfund')} />

      <PageContent>
        <Container size="xs" className="growfund-my-10">
          <Card className="growfund-bg-background-surface growfund-border-border">
            <CardContent className="growfund-flex growfund-flex-col growfund-items-center growfund-gap-6 growfund-justify-center growfund-py-8 growfund-px-6">
              <FundraiserEmptyStateIcon />
              <div className="growfund-flex growfund-flex-col growfund-gap-2">
                <h4 className="growfund-typo-h4 growfund-text-fg-primary growfund-text-center">
                  {__('Supercharge fundraising with collaboration', 'growfund')}
                </h4>
                <div className="growfund-typo-small growfund-text-fg-secondary growfund-text-center">
                  {__(
                    'Pro enables collaboration and the creation of multiple fundraisers.',
                    'growfund',
                  )}
                </div>
              </div>
              <ProButton />
            </CardContent>
          </Card>
        </Container>
      </PageContent>
    </Page>
  );
};

export default FundraisersFallback;

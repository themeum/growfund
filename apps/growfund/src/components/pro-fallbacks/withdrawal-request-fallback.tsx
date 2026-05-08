import { __ } from '@wordpress/i18n';

import { WithdrawalEmptyStateIcon } from '@/app/icons';
import { Container } from '@/components/layouts/container';
import { Page, PageContent, PageHeader } from '@/components/layouts/page';
import { Card, CardContent } from '@/components/ui/card';
import { ProButton } from '@/components/ui/pro-button';

const WithdrawalRequestFallback = () => {
  return (
    <Page>
      <PageHeader name={__('Withdrawal Requests', 'growfund')} />

      <PageContent>
        <Container size="xs" className="growfund-my-10">
          <Card className="growfund-bg-background-surface growfund-border-border">
            <CardContent className="growfund-flex growfund-flex-col growfund-items-center growfund-gap-6 growfund-justify-center growfund-py-8 growfund-pt-14 growfund-px-6">
              <WithdrawalEmptyStateIcon />
              <div className="growfund-flex growfund-flex-col growfund-gap-2">
                <h4 className="growfund-typo-h4 growfund-text-fg-primary growfund-text-center">
                  {__('Manage Withdrawal Requests', 'growfund')}
                </h4>
                <div className="growfund-typo-small growfund-text-fg-secondary growfund-text-center">
                  {__(
                    'Review, approve, and track withdrawal requests—all in one place with Pro.',
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

export default WithdrawalRequestFallback;

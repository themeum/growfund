import { __ } from '@wordpress/i18n';

import { CampaignEmptyStateIcon } from '@/app/icons';
import { EmptyState, EmptyStateDescription } from '@/components/empty-state';
import { Container } from '@/components/layouts/container';

const UnauthorizedPage = () => {
  return (
    <Container className="growfund-mt-10" size="lg">
      <EmptyState>
        <CampaignEmptyStateIcon />
        <EmptyStateDescription>
          {__('You are not authorized to access this page', 'growfund')}
        </EmptyStateDescription>
      </EmptyState>
    </Container>
  );
};

export default UnauthorizedPage;

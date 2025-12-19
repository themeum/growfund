import { __ } from '@wordpress/i18n';

import { Button } from '@/components/ui/button';
import { Image } from '@/components/ui/image';
import { ProBadge } from '@/components/ui/pro-badge';

const CollaboratorsFallback = () => {
  return (
    <div className="growfund-space-y-2">
      <div className="growfund-typo-small growfund-font-medium growfund-text-fg-primary">
        {__('Collaborators', 'growfund')} <ProBadge />
      </div>
      <div className="growfund-border growfund-border-border growfund-rounded-md">
        <div className="growfund-typo-small growfund-text-fg-disabled growfund-px-4 growfund-py-2 growfund-border-b growfund-border-b-border">
          {__('Search to add...', 'growfund')}
        </div>
        <div className="growfund-px-12 growfund-py-6 growfund-space-y-2 growfund-text-center">
          <Image
            src="/images/collaborators.webp"
            className="growfund-border-none growfund-bg-transparent growfund-h-8"
            fit="contain"
          />
          <p className="growfund-typo-tiny growfund-text-fg-secondary growfund-text-center">
            {__(
              'Invite teammates to co-manage your campaign. Plan, edit, and fundraise together.',
              'growfund',
            )}
          </p>
          <Button
            variant="link"
            className="growfund-text-fg-emphasis"
            onClick={() => {
              window.location.href = 'https://growfund.com/pricing';
            }}
          >
            {__('Get Pro', 'growfund')}
          </Button>
        </div>
      </div>
    </div>
  );
};

export default CollaboratorsFallback;

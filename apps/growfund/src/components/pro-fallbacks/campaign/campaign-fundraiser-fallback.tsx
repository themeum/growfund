import { __ } from '@wordpress/i18n';

import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { ProBadge } from '@/components/ui/pro-badge';
import { type UserBasic } from '@/dashboards/shared/schemas/user';
import useCurrentUser from '@/hooks/use-current-user';
import { isDefined } from '@/utils';

const CampaignFundraiserFallback = ({ fundraiser }: { fundraiser?: UserBasic | null }) => {
  const { isAdmin } = useCurrentUser();
  return (
    <div className="growfund-space-y-2">
      <div className="growfund-typo-small growfund-font-medium growfund-text-fg-primary">
        {__('Fundraiser', 'growfund')}
        {isAdmin && <ProBadge />}
      </div>
      <div className="growfund-border growfund-border-border growfund-rounded-md">
        <div className="growfund-typo-small growfund-text-fg-disabled growfund-px-4 growfund-py-2 growfund-border-b growfund-border-b-border">
          {__('Search to add...', 'growfund')}
        </div>
        <div className="growfund-flex growfund-flex-col growfund-p-3 growfund-gap-2 growfund-max-h-64 growfund-overflow-auto growfund-overflow-x-hidden">
          {isDefined(fundraiser) ? (
            <div className="growfund-group hover:growfund-bg-background-surface-secondary growfund-rounded-md growfund-flex growfund-justify-between growfund-items-center growfund-p-2">
              <div className="growfund-flex growfund-gap-3 growfund-items-center">
                <Avatar>
                  <AvatarImage src={fundraiser.image?.url ?? undefined} />
                  <AvatarFallback>{fundraiser.display_name.charAt(0).toUpperCase()}</AvatarFallback>
                </Avatar>

                <div className="growfund-typo-tiny growfund-flex growfund-flex-col growfund-gap-1">
                  <div className="growfund-text-fg-primary growfund-font-medium growfund-max-w-96">
                    {fundraiser.display_name}
                  </div>
                  <div
                    className="growfund-text-fg-secondary growfund-max-w-96 growfund-truncate"
                    title={fundraiser.email}
                  >
                    {fundraiser.email}
                  </div>
                </div>
              </div>
            </div>
          ) : (
            <span className="growfund-text-fg-secondary growfund-typo-small">
              {__('No Fundraiser Added', 'growfund')}
            </span>
          )}
        </div>
      </div>
    </div>
  );
};

export default CampaignFundraiserFallback;

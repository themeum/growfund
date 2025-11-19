import { _n, sprintf } from '@wordpress/i18n';
import { HeartHandshake, Users } from 'lucide-react';
import { useMemo } from 'react';

import { DotSeparator } from '@/components/ui/dot-separator';
import { Image } from '@/components/ui/image';
import { Progress } from '@/components/ui/progress';
import { getGoalInfo } from '@/config/goal-info';
import { useAppConfig } from '@/contexts/app-config';
import { type Campaign } from '@/features/campaigns/schemas/campaign';
import { useCurrency } from '@/hooks/use-currency';

interface CampaignCardMiniProps {
  campaign: Campaign;
}

const CampaignCardMini = ({ campaign }: CampaignCardMiniProps) => {
  const { isDonationMode } = useAppConfig();
  const { toCurrency } = useCurrency();

  const goalInfo = useMemo(() => {
    return getGoalInfo(campaign, isDonationMode, toCurrency);
  }, [campaign, toCurrency, isDonationMode]);

  const numberOfContributors = campaign.number_of_contributors ?? 0;
  const numberOfContributions = campaign.number_of_contributions ?? 0;

  return (
    <div
      className="growfund-grid growfund-grid-cols-[56px_auto] growfund-gap-3 growfund-min-h-[5rem] growfund-items-center"
      key={campaign.id}
    >
      <Image
        src={campaign.images?.[0]?.url ?? null}
        alt={campaign.title}
        fit="cover"
        aspectRatio="square"
      />
      <div className="growfund-space-y-2">
        <p className="growfund-typo-small growfund-font-medium growfund-text-fg-primary growfund-truncate-2-lines">
          {campaign.title}
        </p>
        {campaign.has_goal && <Progress value={goalInfo?.progress_percentage ?? 0} size="sm" />}
        <div className="growfund-flex growfund-items-center growfund-gap-2 growfund-typo-small growfund-font-medium growfund-text-fg-subdued">
          {campaign.has_goal ? (
            <div dangerouslySetInnerHTML={{ __html: goalInfo?.goal_label ?? '' }} />
          ) : (
            /* translators: %s: Raised amount. */
            <div>{sprintf('%s raised', toCurrency(campaign.fund_raised ?? 0))}</div>
          )}

          <DotSeparator />
          <div className="growfund-flex growfund-items-center growfund-gap-1">
            <Users className="growfund-size-3" />

            <span className="growfund-typo-tiny growfund-font-medium growfund-text-fg-primary">
              {sprintf(
                isDonationMode
                  ? /* translators: %d: Number of contributors (donor or donors). */
                    _n('%d Donor', '%d Donors', numberOfContributors, 'growfund')
                  : /* translators: %d: Number of contributors (backer or backers). */
                    _n('%d Backer', '%d Backers', numberOfContributors, 'growfund'),
                numberOfContributors,
              )}
            </span>
          </div>
          <DotSeparator />
          <div className="growfund-flex growfund-items-center growfund-gap-1">
            <HeartHandshake className="growfund-size-3" />

            <span className="growfund-typo-tiny growfund-font-medium growfund-text-fg-primary">
              {sprintf(
                isDonationMode
                  ? /* translators: %d: Number of contributions (donation or donations). */
                    _n('%d Donation', '%d Donations', numberOfContributions, 'growfund')
                  : /* translators: %d: Number of contributions (pledge or pledges). */
                    _n('%d Pledge', '%d Pledges', numberOfContributions, 'growfund'),
                numberOfContributions,
              )}
            </span>
          </div>
        </div>
      </div>
    </div>
  );
};

export default CampaignCardMini;

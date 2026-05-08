import { ArrowTopRightIcon, TimerIcon } from '@radix-ui/react-icons';
import { __, _n, sprintf } from '@wordpress/i18n';
import { format } from 'date-fns';
import { ArrowUpRight, BookmarkMinus, Trash2, Users } from 'lucide-react';
import React from 'react';

import { Box } from '@/components/ui/box';
import { Button } from '@/components/ui/button';
import { DotSeparator } from '@/components/ui/dot-separator';
import { Image } from '@/components/ui/image';
import { Progress } from '@/components/ui/progress';
import { getGoalInfo } from '@/config/goal-info';
import { useAppConfig } from '@/contexts/app-config';
import { type Campaign } from '@/features/campaigns/schemas/campaign';
import { useCurrency } from '@/hooks/use-currency';
import { DATE_FORMATS } from '@/lib/date';
import { cn } from '@/lib/utils';
import { isDefined } from '@/utils';

interface CampaignCardProps extends React.HTMLAttributes<HTMLDivElement> {
  campaign: Campaign;
  onRemove?: () => void;
  onRemoveBookmark?: () => void;
  mode?: 'view' | 'edit' | 'bookmark';
  showPreview?: boolean;
}

const CampaignCard = React.forwardRef<HTMLDivElement, CampaignCardProps>(
  (
    {
      campaign,
      onRemove,
      onRemoveBookmark,
      mode = 'edit',
      showPreview = false,
      className,
      ...props
    },
    ref,
  ) => {
    const { isDonationMode } = useAppConfig();
    const { toCurrency } = useCurrency();
    const goalInfo = getGoalInfo(campaign, isDonationMode, toCurrency);

    return (
      <Box
        className={cn(
          'growfund-flex growfund-gap-6 growfund-p-4 growfund-items-center growfund-relative growfund-group/campaign-card',
          className,
        )}
        {...props}
        ref={ref}
      >
        <Image
          src={campaign.images?.[0]?.url ?? null}
          alt={campaign.title}
          aspectRatio="square"
          className="growfund-w-[6.25rem]"
          fit="cover"
        />

        <div className="growfund-grid growfund-gap-1 growfund-flex-1">
          <div className="growfund-typo-paragraph growfund-font-medium growfund-text-fg-primary">
            {campaign.title}
          </div>
          <div className="growfund-flex growfund-items-center growfund-gap-2">
            {isDefined(campaign.author) && (
              <>
                <div className="growfund-text-fg-secondary growfund-flex growfund-items-center growfund-gap-1 growfund-flex-shrink-0">
                  <span>{__('by', 'growfund')}</span>
                  <span className="growfund-text-fg-success growfund-capitalize">
                    {campaign.author.display_name}
                  </span>
                </div>
                <DotSeparator />
              </>
            )}

            {campaign.start_date && (
              <div className="growfund-flex growfund-items-center growfund-gap-2 growfund-typo-small growfund-text-fg-secondary">
                <TimerIcon className="growfund-w-4 growfund-h-4 growfund-text-icon-primary" />
                <span>
                  {sprintf(
                    /* translators: %s: campaign start date */
                    __('Starts from %s', 'growfund'),
                    format(new Date(campaign.start_date), DATE_FORMATS.HUMAN_READABLE_V2),
                  )}
                </span>
              </div>
            )}
          </div>
          {campaign.has_goal && isDefined(goalInfo) ? (
            <>
              <div className="growfund-w-full growfund-max-w-[20rem]">
                <Progress value={goalInfo.progress_percentage} className="growfund-mt-1" />
              </div>
              <div className="growfund-typo-paragraph growfund-font-medium growfund-text-fg-secondary">
                <span
                  className="growfund-text-primary"
                  dangerouslySetInnerHTML={{ __html: goalInfo.goal_label }}
                />
              </div>
            </>
          ) : (
            <div className="growfund-typo-paragraph growfund-font-medium  growfund-text-fg-secondary growfund-flex growfund-items-center growfund-gap-2">
              <span className="growfund-text-primary">
                {/* translators: %s: Raised amount. */}
                {sprintf('%s raised', toCurrency(campaign.fund_raised ?? 0))}
              </span>
              <DotSeparator />
              <Users className="growfund-size-3" />
              <span className="growfund-typo-small">
                {sprintf(
                  isDonationMode
                    ? /* translators: %s: number of donors */
                      _n('%s donor', '%s donors', campaign.number_of_contributors ?? 0, 'growfund')
                    : /* translators: %s: number of backers */
                      _n(
                        '%s backer',
                        '%s backers',
                        campaign.number_of_contributors ?? 0,
                        'growfund',
                      ),
                  campaign.number_of_contributors ?? 0,
                )}
              </span>
            </div>
          )}
        </div>

        <div className="growfund-ms-auto growfund-opacity-0 growfund-transition-opacity group-hover/campaign-card:growfund-opacity-100">
          {mode === 'bookmark' && (
            <div className="growfund-flex growfund-flex-col growfund-gap-2">
              <Button variant="secondary" size="icon" className="" onClick={onRemoveBookmark}>
                <BookmarkMinus />
              </Button>
              <Button
                variant="secondary"
                size="icon"
                onClick={() => {
                  if (!campaign.preview_url) {
                    return;
                  }
                  window.open(campaign.preview_url, '_blank');
                }}
              >
                <ArrowUpRight />
              </Button>
            </div>
          )}

          {mode === 'edit' && (
            <Button
              variant="secondary"
              size="icon"
              className="hover:growfund-text-icon-critical"
              onClick={onRemove}
            >
              <Trash2 />
            </Button>
          )}

          {showPreview && (
            <Button
              variant="secondary"
              size="icon"
              className="growfund-top-4 growfund-right-4"
              onClick={() => {
                if (!campaign.preview_url) {
                  return;
                }
                window.open(campaign.preview_url, '_blank');
              }}
            >
              <ArrowTopRightIcon />
            </Button>
          )}
        </div>
      </Box>
    );
  },
);

export default CampaignCard;

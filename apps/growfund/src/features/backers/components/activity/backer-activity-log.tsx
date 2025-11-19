import { __, sprintf } from '@wordpress/i18n';
import { HeartHandshake, MessageSquare } from 'lucide-react';

import ActivityLog from '@/components/activity-log';
import { useCurrency } from '@/hooks/use-currency';
import { type Activity, ActivityType } from '@/types/activity';

const log = new Map<
  ActivityType,
  {
    icon: React.ReactNode;
    message: (
      activity: Activity,
      toCurrency: (amount: string | number) => string,
    ) => React.ReactNode;
  }
>([
  [
    ActivityType.PLEDGE_CREATION,
    {
      icon: <HeartHandshake className="growfund-size-6 growfund-text-icon-primary" />,
      message: (activity, toCurrency) => {
        return (
          <div
            className="growfund-typo-small growfund-text-fg-secondary"
            dangerouslySetInnerHTML={{
              __html: sprintf(
                /* translators: 1: pledge amount, 2: campaign title */
                __(
                  'Pledged <span class="growfund-font-semibold growfund-text-fg-primary">%1$s</span> to the campaign “%2$s”',
                  'growfund',
                ),
                toCurrency(activity.data?.pledge_amount ?? 0),
                activity.campaign_title,
              ),
            }}
          />
        );
      },
    },
  ],
  [
    ActivityType.PLEDGE_CANCELLED,
    {
      icon: <HeartHandshake className="growfund-size-6 growfund-text-icon-primary" />,
      message: (activity) => {
        return (
          <div className="growfund-typo-small growfund-text-fg-secondary">
            <span className="growfund-text-fg-critical">{__('Cancelled', 'growfund')}</span>
            {/* translators: %s: campaign title */}
            {sprintf(__(' pledge to the campaign “%s” ', 'growfund'), activity.campaign_title)}
          </div>
        );
      },
    },
  ],
  [
    ActivityType.PLEDGE_BACKED,
    {
      icon: <HeartHandshake className="growfund-size-6 growfund-text-icon-primary" />,
      message: (activity) => {
        return (
          <div className="growfund-typo-small growfund-text-fg-secondary">
            <span className="growfund-text-fg-emphasis">{__('Successfully Backed', 'growfund')}</span>
            {/* translators: %s: campaign title */}
            {sprintf(__(' to the campaign “%s”. ', 'growfund'), activity.campaign_title)}
          </div>
        );
      },
    },
  ],
  [
    ActivityType.PLEDGE_FAILED_TO_BACK,
    {
      icon: <HeartHandshake className="growfund-size-6 growfund-text-icon-primary" />,
      message: (activity) => {
        return (
          <div className="growfund-typo-small growfund-text-fg-secondary">
            <span className="growfund-text-fg-critical">{__('Failed to back', 'growfund')}</span>
            {/* translators: %s: campaign title */}
            {sprintf(__(' the campaign “%s”', 'growfund'), activity.campaign_title)}
          </div>
        );
      },
    },
  ],
  [
    ActivityType.PLEDGE_REFUND_REQUESTED,
    {
      icon: <HeartHandshake className="growfund-size-6 growfund-text-icon-primary" />,
      message: (activity) => {
        return (
          <div className="growfund-typo-small growfund-text-fg-secondary">
            {sprintf(
              /* translators: %s: campaign title */
              __('Refund requested from the campaign “%s”.', 'growfund'),
              activity.campaign_title,
            )}
          </div>
        );
      },
    },
  ],
  [
    ActivityType.PLEDGE_REFUND_RECEIVED,
    {
      icon: <HeartHandshake className="growfund-size-6 growfund-text-icon-primary" />,
      message: (activity) => {
        return (
          <div className="growfund-typo-small growfund-text-fg-secondary">
            {sprintf(
              /* translators: %s: campaign title */
              __('Received refund from the campaign “%s”.', 'growfund'),
              activity.campaign_title,
            )}
          </div>
        );
      },
    },
  ],
  [
    ActivityType.CAMPAIGN_COMMENT,
    {
      icon: <MessageSquare className="growfund-size-6 growfund-text-icon-primary" />,
      message: (activity) => {
        return (
          <div className="growfund-typo-small growfund-text-fg-secondary">
            {/* translators: %s: campaign title */}
            {sprintf(__('Commented on the campaign “%s”. ', 'growfund'), activity.campaign_title)}
          </div>
        );
      },
    },
  ],
]);

const BackerActivityLog = ({ activity }: { activity: Activity }) => {
  const { toCurrency } = useCurrency();
  if (!log.has(activity.type)) {
    return null;
  }

  const logContent = log.get(activity.type);
  return (
    <ActivityLog
      key={activity.id}
      icon={logContent?.icon}
      message={logContent?.message(activity, toCurrency)}
      created_at={activity.created_at}
    />
  );
};

export default BackerActivityLog;

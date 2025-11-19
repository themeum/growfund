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
    ActivityType.DONATION_CANCELLED,
    {
      icon: <HeartHandshake className="growfund-size-6 growfund-text-icon-primary" />,
      message: (activity) => {
        return (
          <div className="growfund-typo-paragraph growfund-text-fg-secondary">
            <span className="growfund-text-fg-critical">{__('Cancelled', 'growfund')}</span>
            {/* translators: %s: campaign title */}
            {sprintf(__(' donation to the campaign “%s” ', 'growfund'), activity.campaign_title)}
          </div>
        );
      },
    },
  ],
  [
    ActivityType.DONATION_FAILED,
    {
      icon: <HeartHandshake className="growfund-size-6 growfund-text-icon-primary" />,
      message: (activity) => {
        return (
          <div className="growfund-typo-paragraph growfund-text-fg-secondary">
            <span className="growfund-text-fg-critical">{__('Failed', 'growfund')}</span>
            {/* translators: %s: campaign title */}
            {sprintf(__(' donation to the campaign “%s”', 'growfund'), activity.campaign_title)}
          </div>
        );
      },
    },
  ],
  [
    ActivityType.DONATION_COMPLETED,
    {
      icon: <HeartHandshake className="growfund-size-6 growfund-text-icon-primary" />,
      message: (activity, toCurrency) => {
        return (
          <div className="growfund-typo-paragraph growfund-text-fg-secondary">
            <span className="growfund-text-fg-emphasis">{__('Donated', 'growfund')}</span>
            {sprintf(
              /* translators: 1: donation amount, 2: campaign title */
              __(' %1$s to the campaign “%2$s”. ', 'growfund'),
              toCurrency(activity.data?.donation_amount ?? 0),
              activity.campaign_title,
            )}
          </div>
        );
      },
    },
  ],
  [
    ActivityType.DONATION_REFUND_REQUESTED,
    {
      icon: <HeartHandshake className="growfund-size-6 growfund-text-icon-primary" />,
      message: (activity) => {
        return (
          <div className="growfund-typo-paragraph growfund-text-fg-secondary">
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
    ActivityType.DONATION_REFUND_RECEIVED,
    {
      icon: <HeartHandshake className="growfund-size-6 growfund-text-icon-primary" />,
      message: (activity) => {
        return (
          <div className="growfund-typo-paragraph growfund-text-fg-secondary">
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
          <div className="growfund-typo-paragraph growfund-text-fg-secondary">
            {/* translators: %s: campaign title */}
            {sprintf(__('Commented on the campaign “%s”. ', 'growfund'), activity.campaign_title)}
          </div>
        );
      },
    },
  ],
]);

const DonorActivityLog = ({ activity }: { activity: Activity }) => {
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

export default DonorActivityLog;

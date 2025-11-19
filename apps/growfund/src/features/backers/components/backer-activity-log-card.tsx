import { __ } from '@wordpress/i18n';
import { FileText } from 'lucide-react';

import { EmptySearchIcon2 } from '@/app/icons';
import { EmptyState, EmptyStateDescription } from '@/components/empty-state';
import { Box, BoxContent } from '@/components/ui/box';
import { Button } from '@/components/ui/button';
import BackerActivityLog from '@/features/backers/components/activity/backer-activity-log';
import BackerActivities from '@/features/backers/components/activity/sheet/backer-activity-log-sheet';
import { useBackerContext } from '@/features/backers/contexts/backer';
import { isDefined } from '@/utils';

const BackerActivityLogCard = () => {
  const { backer } = useBackerContext();
  const activities = backer.activity_logs;

  if (activities.length === 0) {
    return (
      <Box className="growfund-border-none growfund-group/activity-logs">
        <BoxContent className="growfund-p-5 growfund-h-full">
          <h6 className="growfund-typo-h6 growfund-font-semibold growfund-text-fg-primary">
            {__('Activity Logs', 'growfund')}
          </h6>
          <EmptyState className="growfund-shadow-none growfund-mt-0">
            <EmptySearchIcon2 />
            <EmptyStateDescription>
              {__('No activities created yet.', 'growfund')}
            </EmptyStateDescription>
          </EmptyState>
        </BoxContent>
      </Box>
    );
  }

  return (
    <Box className="growfund-border-none growfund-group/activity-logs">
      <BoxContent className="growfund-p-5">
        <div className="growfund-flex growfund-items-center growfund-justify-between">
          <h6 className="growfund-typo-h6 growfund-font-semibold growfund-text-fg-primary">
            {__('Activity Logs', 'growfund')}
          </h6>
          {isDefined(backer) && (
            <BackerActivities backerId={backer.backer_information.id}>
              <Button
                variant="ghost"
                size="sm"
                className="growfund-transition-opacity growfund-opacity-0 group-hover/activity-logs:growfund-opacity-100"
              >
                <FileText />
                {__('See All Logs', 'growfund')}
              </Button>
            </BackerActivities>
          )}
        </div>
        <div className="growfund-space-y-4 growfund-mt-5">
          {activities.map((activity) => {
            return <BackerActivityLog key={activity.id} activity={activity} />;
          })}
        </div>
      </BoxContent>
    </Box>
  );
};

export default BackerActivityLogCard;

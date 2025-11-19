import { __ } from '@wordpress/i18n';
import { FileSpreadsheet } from 'lucide-react';
import React, { useRef, useState } from 'react';

import InfiniteQueryScroll from '@/components/infinite-query-scroll';
import { Container } from '@/components/layouts/container';
import { Box, BoxContent } from '@/components/ui/box';
import { Sheet, SheetContent, SheetHeader, SheetTitle, SheetTrigger } from '@/components/ui/sheet';
import BackerActivityLog from '@/features/backers/components/activity/backer-activity-log';
import { useBackerActivitiesInfiniteQuery } from '@/features/backers/services/backer';

const BackerActivities = ({
  backerId,
  children,
}: React.PropsWithChildren<{ backerId: string }>) => {
  const [open, setOpen] = useState(false);
  const containerRef = useRef<HTMLDivElement | null>(null);

  const isEnabledQuery = open;
  const backerActivitiesQuery = useBackerActivitiesInfiniteQuery(backerId, isEnabledQuery);

  return (
    <Sheet onOpenChange={setOpen}>
      <SheetTrigger asChild>{children}</SheetTrigger>
      <SheetContent side="bottom">
        <SheetHeader>
          <SheetTitle>
            <FileSpreadsheet className="growfund-size-6" />
            {__('Activity Log', 'growfund')}
          </SheetTitle>
        </SheetHeader>

        <Container className="growfund-mt-7 growfund-flex growfund-justify-center growfund-min-h-[80svh]">
          {backerActivitiesQuery.data?.pages.some((page) => page.results.length > 0) && (
            <Box
              ref={containerRef}
              className="growfund-shadow-none growfund-w-[35rem] growfund-h-full growfund-max-h-[80svh] growfund-overflow-y-auto growfund-p-6 growfund-pb-0"
            >
              <BoxContent className="growfund-flex growfund-flex-col growfund-gap-4">
                {backerActivitiesQuery.data.pages.map((data, index) => {
                  const activities = data.results;
                  return (
                    <React.Fragment key={index}>
                      {activities.map((activity) => {
                        return <BackerActivityLog key={activity.id} activity={activity} />;
                      })}
                    </React.Fragment>
                  );
                })}
                <InfiniteQueryScroll
                  rootRef={containerRef}
                  query={backerActivitiesQuery}
                  showNoMore
                  noMoreText={__(
                    'That’s it—end of the list, unless you’re here for invisible items. ;)',
                    'growfund',
                  )}
                />
              </BoxContent>
            </Box>
          )}
        </Container>
      </SheetContent>
    </Sheet>
  );
};

export default BackerActivities;

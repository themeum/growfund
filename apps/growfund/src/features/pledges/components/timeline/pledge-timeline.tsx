import { zodResolver } from '@hookform/resolvers/zod';
import { __, sprintf } from '@wordpress/i18n';
import React from 'react';
import { useForm } from 'react-hook-form';

import InfiniteQueryScroll from '@/components/infinite-query-scroll';
import {
  Timeline,
  TimelineContentWrapper,
  TimelineForm,
  TimelineItem,
  TimelineItemWrapper,
} from '@/components/timeline/timeline';
import {
  useCreateTimelineMutation,
  useDeleteTimelineMutation,
  useTimelinesInfiniteQuery,
} from '@/features/pledges/services/timeline';
import { type User } from '@/features/settings/schemas/settings';
import useCurrentUser from '@/hooks/use-current-user';
import { getDefaults } from '@/lib/zod';
import { CommentSchema, type CommentSchemaType } from '@/schemas/timeline';
import { ActivityType, type Activity } from '@/types/activity';

interface TimelineProps {
  pledgeId: string;
}

const getActivityMessage = (activity: Activity, currentUser: User): string => {
  switch (activity.type) {
    case ActivityType.TIMELINE:
      return activity.data?.comment ?? '';
    case ActivityType.PLEDGE_CREATION:
      if (activity.created_by === currentUser.id) {
        return __('You created this pledge', 'growfund');
      }

      /* translators: %s: created by user name */
      return sprintf(__('%s created this pledge', 'growfund'), activity.created_by_name);
    case ActivityType.PLEDGE_CANCELLED:
      return __('Pledge “Cancelled”', 'growfund');
    case ActivityType.PLEDGE_BACKED:
      return __('Pledge marked as “Backed”', 'growfund');
    case ActivityType.PLEDGE_FAILED_TO_BACK:
      return __('Pledge “Failed”', 'growfund');
    case ActivityType.PLEDGE_COMPLETED:
      return __('Pledge “Completed” i.e. Rewards has been delivered', 'growfund');
    case ActivityType.PLEDGE_REFUND_RECEIVED:
      return __('Pledge amount “Refunded”', 'growfund');
    default:
      return '';
  }
};

const PledgeTimeline = ({ pledgeId }: TimelineProps) => {
  const { currentUser } = useCurrentUser();
  const form = useForm<CommentSchemaType>({
    resolver: zodResolver(CommentSchema),
    defaultValues: getDefaults(CommentSchema),
  });

  const createTimelineMutation = useCreateTimelineMutation();
  const deleteTimelineMutation = useDeleteTimelineMutation();

  const timelinesQuery = useTimelinesInfiniteQuery(pledgeId);

  const onRemove = (timelineId: string) => {
    deleteTimelineMutation.mutate({
      pledgeId: pledgeId,
      timelineId: timelineId,
    });
  };

  const onSubmit = (values: CommentSchemaType) => {
    const payload = {
      comment: values.comment,
      pledge_id: pledgeId,
    };

    createTimelineMutation.mutate(payload, {
      onSuccess: () => {
        form.reset();
      },
    });
  };

  const hasActivities = timelinesQuery.data?.pages.some((page) => page.results.length > 0) ?? false;

  return (
    <Timeline>
      <TimelineContentWrapper>
        <TimelineForm onSubmit={onSubmit} />
        <TimelineItemWrapper hasItems={hasActivities}>
          {timelinesQuery.data?.pages.map((data, index) => {
            const activities = data.results;
            return (
              <React.Fragment key={index}>
                {activities.map((activity) => {
                  const timeline = {
                    id: activity.id,
                    type: activity.type,
                    user: {
                      id: activity.created_by,
                      name: activity.created_by_name,
                      image: activity.created_by_image,
                    },
                    created_at: activity.created_at,
                    comment: getActivityMessage(activity, currentUser),
                  };
                  return <TimelineItem key={activity.id} timeline={timeline} onRemove={onRemove} />;
                })}
              </React.Fragment>
            );
          })}
        </TimelineItemWrapper>

        <InfiniteQueryScroll query={timelinesQuery} />
      </TimelineContentWrapper>
    </Timeline>
  );
};

export default PledgeTimeline;

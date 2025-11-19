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
  useCreateDonationTimelineMutation,
  useDeleteDonationTimelineMutation,
  useDonationTimelinesInfiniteQuery,
} from '@/features/donations/services/timeline';
import { type User } from '@/features/settings/schemas/settings';
import useCurrentUser from '@/hooks/use-current-user';
import { getDefaults } from '@/lib/zod';
import { CommentSchema, type CommentSchemaType } from '@/schemas/timeline';
import { ActivityType, type Activity } from '@/types/activity';

interface TimelineProps {
  donationId: string;
}

const getActivityMessage = (activity: Activity, currentUser: User): string => {
  switch (activity.type) {
    case ActivityType.TIMELINE:
      return activity.data?.comment ?? '';
    case ActivityType.DONATION_CREATION:
      if (activity.created_by === currentUser.id) {
        return __('You created this donation', 'growfund');
      }

      /* translators: %s: created by user name */
      return sprintf(__('%s created this donation', 'growfund'), activity.created_by_name);
    case ActivityType.DONATION_CANCELLED:
      return __('Donation “Cancelled”', 'growfund');
    case ActivityType.DONATION_FAILED:
      return __('Donation “Failed”', 'growfund');
    case ActivityType.DONATION_COMPLETED:
      return __('Donation “Completed”', 'growfund');
    case ActivityType.DONATION_REFUND_RECEIVED:
      return __('Donation amount “Refunded”', 'growfund');
    default:
      return '';
  }
};

const DonationTimeline = ({ donationId }: TimelineProps) => {
  const { currentUser } = useCurrentUser();
  const form = useForm<CommentSchemaType>({
    resolver: zodResolver(CommentSchema),
    defaultValues: getDefaults(CommentSchema),
  });

  const createTimelineMutation = useCreateDonationTimelineMutation();
  const deleteTimelineMutation = useDeleteDonationTimelineMutation();

  const timelinesQuery = useDonationTimelinesInfiniteQuery(donationId);

  const onRemove = (timelineId: string) => {
    deleteTimelineMutation.mutate({
      donationId: donationId,
      timelineId: timelineId,
    });
  };

  const onSubmit = (values: CommentSchemaType) => {
    const payload = {
      comment: values.comment,
      donation_id: donationId,
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

export default DonationTimeline;

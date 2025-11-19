import { zodResolver } from '@hookform/resolvers/zod';
import { __ } from '@wordpress/i18n';
import { Trash2 } from 'lucide-react';
import React from 'react';
import { useForm } from 'react-hook-form';

import { TextField } from '@/components/form/text-field';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import { Form } from '@/components/ui/form';
import useCurrentUser from '@/hooks/use-current-user';
import { cn } from '@/lib/utils';
import { getDefaults } from '@/lib/zod';
import { type MediaAttachment } from '@/schemas/media';
import { CommentSchema, type CommentSchemaType } from '@/schemas/timeline';
import { ActivityType } from '@/types/activity';
import { createAcronym } from '@/utils';

interface TimelineFormProps {
  onSubmit: (data: CommentSchemaType) => void;
}

const TimelineForm = ({ onSubmit }: TimelineFormProps) => {
  const { currentUser } = useCurrentUser();
  const myAcronym =
    createAcronym({
      first_name: currentUser.first_name,
      last_name: currentUser.last_name,
    }) || 'A';

  const form = useForm<CommentSchemaType>({
    resolver: zodResolver(CommentSchema),
    defaultValues: getDefaults(CommentSchema),
  });

  const handleFormSubmit = (values: CommentSchemaType) => {
    onSubmit(values);
    form.reset();
  };

  return (
    <Form {...form}>
      <form
        className="growfund-rounded-lg growfund-bg-background-surface growfund-shadow-md growfund-overflow-hidden growfund-mt-3"
        onSubmit={form.handleSubmit(handleFormSubmit, (errors) => {
          console.error(errors);
        })}
      >
        <div className="growfund-flex growfund-items-center growfund-gap-2 growfund-p-3">
          <Avatar>
            <AvatarImage src={currentUser.image?.url} />
            <AvatarFallback>{myAcronym}</AvatarFallback>
          </Avatar>
          <TextField
            control={form.control}
            name="comment"
            placeholder={__('Leave a comment...', 'growfund')}
            className="growfund-border-none"
            noErrorMessage
          />
        </div>
        <div className="growfund-p-2 growfund-bg-background-surface-secondary growfund-w-full growfund-flex growfund-justify-end">
          <Button variant="outline" type="submit">
            {__('Post', 'growfund')}
          </Button>
        </div>
      </form>
    </Form>
  );
};

interface TimelineItemProps {
  onRemove: (timelineId: string) => void;
  timeline: {
    id: string;
    type: ActivityType;
    user: {
      id: string;
      name: string;
      image?: MediaAttachment | null;
    };
    created_at: string;
    comment?: string;
  };
}

const TimelineItem = ({ timeline, onRemove }: TimelineItemProps) => {
  const { currentUser } = useCurrentUser();
  const isSystemGenerated = timeline.type !== ActivityType.TIMELINE;
  const acronym = createAcronym({ first_name: timeline.user.name });
  return (
    <div
      className={cn(
        'growfund-px-5 growfund-py-3 growfund-pr-0 growfund-flex growfund-items-center growfund-justify-between growfund-rounded-lg growfund-group/timeline growfund-min-h-16 last:growfund-pb-0 last:growfund-min-h-0',
        !isSystemGenerated && 'hover:growfund-bg-background-fill hover:growfund-shadow-md',
      )}
    >
      <div className="growfund-flex growfund-items-center growfund-gap-2">
        {isSystemGenerated ? (
          <div className="growfund-size-6 growfund-rounded growfund-bg-background-fill growfund-flex growfund-items-center growfund-justify-center">
            <span className="growfund-size-2 growfund-rounded-full growfund-bg-icon-primary" />
          </div>
        ) : (
          <div>
            <Avatar className="growfund-size-10 growfund-relative growfund-left-[-0.5rem]">
              <AvatarImage
                src={timeline.user.image?.url}
                className="growfund-bg-background-fill-special-2 growfund-border growfund-border-border"
              />
              <AvatarFallback className="growfund-bg-background-surface">
                {currentUser.active_role === 'growfund_admin' ? 'A' : acronym}
              </AvatarFallback>
            </Avatar>
          </div>
        )}
        <div className="growfund-space-y-1 growfund-typo-small growfund-text-fg-primary growfund-font-medium">
          {!isSystemGenerated && <div>{timeline.user.name}</div>}
          <div className={cn('growfund-font-regular', !isSystemGenerated && 'growfund-text-fg-secondary')}>
            {timeline.comment}
          </div>
        </div>
      </div>

      <div>
        <div
          className={cn(
            'growfund-typo-tiny growfund-text-fg-subdued',
            !isSystemGenerated && 'group-hover/timeline:growfund-hidden',
          )}
        >
          {timeline.created_at}
        </div>
        {!isSystemGenerated && (
          <Button
            variant="ghost"
            size="icon"
            className="hover:growfund-text-icon-critical growfund-mr-2 growfund-hidden group-hover/timeline:growfund-flex"
            onClick={() => {
              onRemove(timeline.id);
            }}
          >
            <Trash2 />
          </Button>
        )}
      </div>
    </div>
  );
};

const TimelineItemWrapper = ({
  children,
  hasItems,
}: React.PropsWithChildren<{ hasItems: boolean }>) => {
  return (
    <div className="growfund-relative">
      <div className="growfund-pt-6">{children}</div>
      {hasItems && (
        <div className="growfund-absolute growfund-bottom-0 growfund-left-8 growfund-w-[2px] growfund-h-full growfund-bg-border growfund-z-negative" />
      )}
    </div>
  );
};

const TimelineContentWrapper = ({ children }: { children: React.ReactNode }) => {
  return <div className="growfund-pt-3">{children}</div>;
};

const Timeline = ({ children }: { children: React.ReactNode }) => {
  return (
    <div className="growfund-mt-10">
      <h6 className="growfund-typo-h6 growfund-font-semibold growfund-text-fg-primary">
        {__('Timeline', 'growfund')}
      </h6>
      {children}
    </div>
  );
};

export { Timeline, TimelineContentWrapper, TimelineForm, TimelineItem, TimelineItemWrapper };

import z from 'zod';

import { MediaSchema } from '@/schemas/media';

enum ActivityType {
  TIMELINE = 'timeline',

  CAMPAIGN_SUBMITTED_FOR_REVIEW = 'campaign-submitted-for-review',
  CAMPAIGN_RE_SUBMITTED_FOR_REVIEW = 'campaign-re-submitted-for-review',
  CAMPAIGN_DECLINED = 'campaign-declined',
  CAMPAIGN_APPROVED_AND_PUBLISHED = 'campaign-approved-and-published',
  CAMPAIGN_SET_DEADLINE = 'campaign-set-deadline',
  CAMPAIGN_REMOVED_DEADLINE = 'campaign-removed-deadline',
  CAMPAIGN_EXTENDED_DEADLINE = 'campaign-extended-deadline',
  CAMPAIGN_MARKED_AS_COMPLETED = 'campaign-marked-as-completed',

  CAMPAIGN_POST_UPDATE = 'campaign-post-update',
  CAMPAIGN_GOAL_REACHED = 'campaign-goal-reached',
  CAMPAIGN_COMMENT = 'commented-on-the-campaign',

  PLEDGE_CREATION = 'pledge-created',
  PLEDGE_CANCELLED = 'pledge-cancelled',
  PLEDGE_BACKED = 'pledge-backed',
  PLEDGE_FAILED_TO_BACK = 'pledge-failed-to-back',
  PLEDGE_COMPLETED = 'pledge-completed',

  PLEDGE_REFUND_REQUESTED = 'pledge-refund-requested', //@todo: need to implement
  PLEDGE_REFUND_RECEIVED = 'pledge-refund-received', //@todo: need to implement

  DONATION_CREATION = 'donation-created',
  DONATION_CANCELLED = 'donation-cancelled',
  DONATION_FAILED = 'donation-failed',
  DONATION_COMPLETED = 'donation-completed',

  DONATION_REFUND_REQUESTED = 'donation-refund-requested', //@todo: need to implement
  DONATION_REFUND_RECEIVED = 'donation-refund-received', //@todo: need to implement
}

const ActivitySchema = z.object({
  id: z.coerce.string(),
  type: z.nativeEnum(ActivityType),
  campaign_id: z.string().nullish(),
  campaign_title: z.string().nullish(),
  campaign_images: MediaSchema.array().nullish(),
  pledge_id: z.string().nullish(),
  donation_id: z.string().nullish(),
  created_by: z.string(),
  created_by_image: MediaSchema.nullish(),
  created_by_name: z.string(),
  created_at: z.string(),
  data: z
    .object({
      comment: z.string().nullish(),
      post_update_id: z.string().nullish(),
      old_end_date: z.string().nullish(),
      new_end_date: z.string().nullish(),
      no_of_extended_days: z.number().nullish(),
      donation_amount: z.number().nullish(),
      pledge_amount: z.number().nullish(),
    })
    .nullish(),
});

type Activity = z.infer<typeof ActivitySchema>;

export { ActivitySchema, ActivityType, type Activity };

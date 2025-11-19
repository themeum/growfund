import { __ } from '@wordpress/i18n';
import { z } from 'zod';

const DeclineCampaignSchema = z.object({
  reason: z.string({
    message: __('Please provide a reason for declining this campaign', 'growfund'),
  }),
});

type DeclineCampaignForm = z.infer<typeof DeclineCampaignSchema>;

export { DeclineCampaignSchema, type DeclineCampaignForm };

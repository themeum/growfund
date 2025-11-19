import { __, _n, sprintf } from '@wordpress/i18n';

import { type Campaign } from '@/features/campaigns/schemas/campaign';

export const getGoalInfo = (
  campaign: Campaign,
  isDonationMode: boolean,
  toCurrency: (amount: string | number) => string,
) => {
  if (!campaign.has_goal) {
    return null;
  }

  switch (campaign.goal_type) {
    case 'raised-amount':
      return {
        progress_percentage:
          campaign.fund_raised && campaign.goal_amount
            ? Number(((campaign.fund_raised / campaign.goal_amount) * 100).toFixed(2))
            : 0,
        gained: toCurrency(campaign.fund_raised ?? 0),
        goal_label: sprintf(
          /* translators: 1: raised amount, 2: goal amount */
          __('<span class="growfund-text-fg-primary">%1$s</span> of %2$s', 'growfund'),
          toCurrency(campaign.fund_raised ?? 0),
          toCurrency(campaign.goal_amount ?? 0),
        ),
        goal_amount: toCurrency(campaign.goal_amount ?? 0),
      };
    case 'number-of-contributions':
      return {
        progress_percentage:
          campaign.number_of_contributions && campaign.goal_amount
            ? Number(((campaign.number_of_contributions / campaign.goal_amount) * 100).toFixed(2))
            : 0,
        gained: campaign.number_of_contributions,
        goal_label: sprintf(
          isDonationMode
            ? /* translators: 1: number of donations, 2: goal amount */
              _n(
                '%1$s donation of %2$s',
                '%1$s donations of %2$s',
                campaign.number_of_contributions ?? 0,
                'growfund',
              )
            : /* translators: 1: number of pledges, 2: goal amount */
              _n(
                '%1$s pledge of %2$s',
                '%1$s pledges of %2$s',
                campaign.number_of_contributions ?? 0,
                'growfund',
              ),
          campaign.number_of_contributions ?? 0,
          campaign.goal_amount ?? 0,
        ),
        goal_amount: campaign.goal_amount,
      };
    case 'number-of-contributors':
      return {
        progress_percentage:
          campaign.number_of_contributors && campaign.goal_amount
            ? Number(((campaign.number_of_contributors / campaign.goal_amount) * 100).toFixed(2))
            : 0,
        gained: campaign.number_of_contributors,
        goal_label: sprintf(
          isDonationMode
            ? /* translators: 1: number of donors, 2: goal amount */
              _n(
                '%1$s donor of %2$s',
                '%1$s donors of %2$s',
                campaign.number_of_contributors ?? 0,
                'growfund',
              )
            : /* translators: 1: number of backers, 2: goal amount */
              _n(
                '%1$s backer of %2$s',
                '%1$s backers of %2$s',
                campaign.number_of_contributors ?? 0,
                'growfund',
              ),
          campaign.number_of_contributors ?? 0,
          campaign.goal_amount,
        ),
        goal_amount: campaign.goal_amount,
      };
    default:
      return null;
  }
};

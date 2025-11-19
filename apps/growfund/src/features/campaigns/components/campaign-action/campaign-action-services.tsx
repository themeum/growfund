import { Cross2Icon } from '@radix-ui/react-icons';
import { __, _n, sprintf } from '@wordpress/i18n';
import {
  CheckCircle,
  CircleDollarSign,
  Eye,
  EyeOff,
  Pause,
  Play,
  Receipt,
  Trash2,
} from 'lucide-react';

import {
  type Action,
  type ActionVariant,
  type MessageKey,
} from '@/features/campaigns/components/campaign-action/campaign-action-types';
import { type Campaign } from '@/features/campaigns/schemas/campaign';

function prepareActionOptions(campaign: Campaign, isDonationMode: boolean) {
  return [
    {
      label: sprintf(
        /* translators: %d: pledge count */
        _n(
          'Charge Backers (%d pledge)',
          'Charge Backers (%d pledges)',
          campaign.uncharged_pledge_count,
          'growfund',
        ),
        campaign.uncharged_pledge_count,
      ),
      value: 'charge-backers',
      icon: <Receipt />,
      proceed: () => {
        if (
          isDonationMode ||
          campaign.status !== 'funded' ||
          campaign.uncharged_pledge_count === 0
        ) {
          return false;
        }
        return true;
      },
    },
    {
      label: isDonationMode
        ? __('Pause Receiving Donation', 'growfund')
        : __('Pause Pledging', 'growfund'),
      value: 'pause',
      icon: <Pause />,
      proceed: () => {
        if (
          campaign.is_paused ||
          campaign.status === 'completed' ||
          !campaign.is_launched ||
          campaign.is_ended ||
          (campaign.status === 'funded' && campaign.reaching_action === 'close')
        ) {
          return false;
        }
        return true;
      },
    },
    {
      label: isDonationMode
        ? __('Resume Receiving Donation', 'growfund')
        : __('Resume Pledging', 'growfund'),
      value: 'resume',
      icon: <Play />,
      proceed: () => {
        if (
          !campaign.is_paused ||
          campaign.status === 'completed' ||
          !campaign.is_launched ||
          campaign.is_ended ||
          (campaign.status === 'funded' && campaign.reaching_action === 'close')
        ) {
          return false;
        }
        return true;
      },
    },
    {
      label: '---',
      value: 'separator',
      proceed: () => {
        if (
          campaign.status === 'completed' ||
          campaign.is_ended ||
          !campaign.is_launched ||
          (campaign.status === 'funded' && campaign.reaching_action === 'close')
        ) {
          return false;
        }
        return true;
      },
    },
    {
      label: __('Mark as Funded', 'growfund'),
      value: 'funded',
      icon: <CircleDollarSign />,
      proceed: () => {
        if (['funded', 'completed'].includes(campaign.status) || !campaign.is_launched) {
          return false;
        }
        return true;
      },
    },
    {
      label: __('End the Campaign', 'growfund'),
      value: 'end',
      icon: <Cross2Icon />,
      proceed: () => {
        if (campaign.is_ended || campaign.status === 'completed') {
          return false;
        }
        return true;
      },
    },
    {
      label: __('Mark as Completed', 'growfund'),
      value: 'completed',
      icon: <CheckCircle />,
      proceed: () => {
        if (campaign.status === 'completed' || campaign.status !== 'funded') {
          return false;
        }
        return true;
      },
    },
    {
      label: __('Unhide Campaign', 'growfund'),
      value: 'visible',
      icon: <Eye />,
      proceed: () => {
        if (!campaign.is_hidden) {
          return false;
        }
        return true;
      },
    },
    {
      label: __('Hide Campaign', 'growfund'),
      value: 'hide',
      icon: <EyeOff />,
      proceed: () => {
        if (campaign.is_hidden) {
          return false;
        }
        return true;
      },
    },
    {
      label: '---',
      value: 'separator',
      proceed: () => {
        return true;
      },
    },
    {
      label: __('Delete the Campaign', 'growfund'),
      value: 'delete',
      icon: <Trash2 />,
      is_critical: true,
      proceed: () => {
        return true;
      },
    },
  ]
    .filter((option) => option.proceed())
    .map(({ proceed: _, ...option }) => option);
}

function getActionHeaderText(isDonationMode: boolean) {
  return new Map<MessageKey, { label: string; variant: ActionVariant }>([
    ['published', { label: __('Campaign is published', 'growfund'), variant: 'success' }],
    ['ended', { label: __('Campaign ended', 'growfund'), variant: 'ended' }],
    ['completed', { label: __('Campaign is completed', 'growfund'), variant: 'completed' }],
    ['hidden', { label: __('Campaign is hidden from public', 'growfund'), variant: 'hidden' }],
    [
      'receiving-contributions',
      {
        label: isDonationMode
          ? __('Actively receiving donations', 'growfund')
          : __('Actively receiving pledges', 'growfund'),
        variant: 'success',
      },
    ],
    [
      'paused',
      {
        label: isDonationMode
          ? __('Donation paused', 'growfund')
          : __('Pledging paused', 'growfund'),
        variant: 'secondary',
      },
    ],
    ['paused-and-hidden', { label: __('Hidden and paused', 'growfund'), variant: 'secondary' }],
    [
      'not-launched',
      { label: __('Campaign is not launched yet', 'growfund'), variant: 'secondary' },
    ],
  ]);
}

function prepareMessageKey(campaign: Campaign): MessageKey | null {
  if (campaign.status === 'completed') {
    return 'completed';
  }

  if (campaign.is_paused && campaign.is_hidden) {
    return 'paused-and-hidden';
  }

  if (campaign.is_hidden) {
    return 'hidden';
  }

  if (campaign.is_paused) {
    return 'paused';
  }

  if (campaign.is_ended) {
    return 'ended';
  }

  if (!campaign.is_launched && campaign.status === 'published') {
    return 'published';
  }

  if (!campaign.is_launched) {
    return 'not-launched';
  }

  if (campaign.status === 'funded' && campaign.reaching_action === 'close') {
    return 'ended';
  }

  return 'receiving-contributions';
}

function getActionAlerts(isDonationMode: boolean) {
  return new Map<Action, string>([
    [
      'charge-backers',
      __(
        'This action will charge all associated backers for their pledges. The pledged amounts will be collected and recorded.',
        'growfund',
      ),
    ],
    [
      'pause',
      isDonationMode
        ? __(
            'This action will temporarily stop new donations to this campaign. You can resume receiving donations at any time.',
            'growfund',
          )
        : __(
            'This action will temporarily disable new pledges for this campaign. You can resume pledges at any time.',
            'growfund',
          ),
    ],
    [
      'resume',
      isDonationMode
        ? __('This action will allow receiving new donations to this campaign again.', 'growfund')
        : __('This action will allow new pledges for this campaign again.', 'growfund'),
    ],
    [
      'end',
      isDonationMode
        ? __(
            'This action will permanently close the campaign. Supporters will no longer be able to donate. This cannot be undone.',
            'growfund',
          )
        : __(
            'This action will permanently close the campaign. Backers will no longer be able to pledge, and this cannot be undone.',
            'growfund',
          ),
    ],
    [
      'funded',
      isDonationMode
        ? __(
            'This action will mark the campaign as funded, indicating the fundraising goal has been reached.',
            'growfund',
          )
        : __(
            'This action will mark the campaign as funded, meaning it has reached its goal.',
            'growfund',
          ),
    ],
    [
      'completed',
      isDonationMode
        ? __(
            'This action confirms the campaign is finished and all obligations are complete (e.g., thank-you messages, receipts).',
            'growfund',
          )
        : __(
            'This action confirms the campaign is finished, all rewards are delivered, and fulfillment is complete.',
            'growfund',
          ),
    ],
    [
      'visible',
      isDonationMode
        ? __('This action will make the campaign visible to supporters again.', 'growfund')
        : __('This action will make the campaign visible to backers again.', 'growfund'),
    ],
    [
      'hide',
      isDonationMode
        ? __('This action will make the campaign invisible to supporters.', 'growfund')
        : __('This action will make the campaign invisible to backers.', 'growfund'),
    ],
    [
      'delete',
      __(
        'This action will permanently remove the campaign and all associated data. This cannot be undone.',
        'growfund',
      ),
    ],
  ]);
}

export { getActionAlerts, getActionHeaderText, prepareActionOptions, prepareMessageKey };

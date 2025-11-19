import { __ } from '@wordpress/i18n';
import { CheckCircle, Trash2, X } from 'lucide-react';

import { type Donation } from '@/features/donations/schemas/donation';
import {
  type Action,
  type ActionVariant,
  type MessageKey,
} from '@/features/pledges/components/pledge-action/pledge-action-types';

function prepareActionOptions(donation: Donation) {
  return [
    {
      label: __('Mark as Completed', 'growfund'),
      value: 'complete',
      icon: <CheckCircle />,
      proceed: () => {
        if (donation.status === 'pending') {
          return true;
        }
        return false;
      },
    },
    {
      label: __('Cancel Pledge', 'growfund'),
      value: 'cancel',
      icon: <X />,
      proceed: () => {
        if (['completed', 'cancelled'].includes(donation.status)) {
          return false;
        }
        return true;
      },
    },
    {
      label: '---',
      value: 'separator',
      proceed: () => {
        if (['completed', 'cancelled'].includes(donation.status)) {
          return false;
        }
        return true;
      },
    },
    {
      label: __('Delete Donation', 'growfund'),
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

function getActionHeaderText() {
  return new Map<MessageKey, { label: string; variant: ActionVariant }>([
    ['created', { label: __('Donation Created', 'growfund'), variant: 'success' }],
    ['completed', { label: __('Donation Successful', 'growfund'), variant: 'completed' }],
    ['in-progress', { label: __('In Progress', 'growfund'), variant: 'in-progress' }],
    ['cancelled', { label: __('Donation Cancelled', 'growfund'), variant: 'critical' }],
    ['failed', { label: __('Donation Failed', 'growfund'), variant: 'critical' }],
  ]);
}

function prepareMessageKey(donation: Donation): MessageKey | null {
  const allowed: Donation['status'][] = ['pending', 'completed', 'failed', 'cancelled'];
  if (!allowed.includes(donation.status)) {
    return null;
  }
  if (donation.status === 'pending') {
    return 'created';
  }

  return donation.status as MessageKey;
}

function getActionAlerts() {
  return new Map<Action, string>([
    ['cancel', __('This action will cancel the pledge. This action cannot be undone.', 'growfund')],
    [
      'complete',
      __(
        'This action will mark the pledge as completed, indicating fulfillment is finished.',
        'growfund',
      ),
    ],
    [
      'delete',
      __(
        'This action will permanently remove the pledge and all associated data. This cannot be undone.',
        'growfund',
      ),
    ],
  ]);
}

export { getActionAlerts, getActionHeaderText, prepareActionOptions, prepareMessageKey };

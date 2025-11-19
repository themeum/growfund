import { __ } from '@wordpress/i18n';
import { CheckCircle, Receipt, RotateCcw, Trash2, X } from 'lucide-react';

import {
  type Action,
  type ActionVariant,
  type MessageKey,
} from '@/features/pledges/components/pledge-action/pledge-action-types';
import { type Pledge } from '@/features/pledges/schemas/pledge';

function prepareActionOptions(pledge: Pledge) {
  return [
    {
      label: __('Charge Backer', 'growfund'),
      value: 'charge-backer',
      icon: <Receipt />,
      proceed: () => {
        if (
          pledge.status === 'pending' &&
          ['funded', 'completed'].includes(pledge.campaign.status) &&
          pledge.payment.payment_method.type === 'online-payment'
        ) {
          return true;
        }
        return false;
      },
    },
    {
      label: __('Mark as Backed', 'growfund'),
      value: 'mark-as-backed',
      icon: <Receipt />,
      proceed: () => {
        if (
          pledge.status === 'pending' &&
          ['funded', 'completed'].includes(pledge.campaign.status) &&
          pledge.payment.payment_method.type === 'manual-payment'
        ) {
          return true;
        }
        return false;
      },
    },
    {
      label: __('Mark as Completed', 'growfund'),
      value: 'complete',
      icon: <CheckCircle />,
      proceed: () => {
        if (pledge.status === 'backed') {
          return true;
        }
        return false;
      },
    },
    {
      label: __('Retry Payment', 'growfund'),
      value: 'retry',
      icon: <RotateCcw />,
      proceed: () => {
        if (pledge.status === 'failed' && pledge.payment.payment_method.type === 'online-payment') {
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
        if (['completed', 'cancelled', 'backed'].includes(pledge.status)) {
          return false;
        }
        return true;
      },
    },
    {
      label: '---',
      value: 'separator',
      proceed: () => {
        if (pledge.status !== 'completed' && pledge.status !== 'cancelled') {
          return true;
        }
        return false;
      },
    },
    {
      label: __('Delete Pledge', 'growfund'),
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
    ['created', { label: __('Pledge Created', 'growfund'), variant: 'success' }],
    ['completed', { label: __('Commitment Fulfilled ', 'growfund'), variant: 'completed' }],
    ['in-progress', { label: __('In Progress', 'growfund'), variant: 'in-progress' }],
    ['backed', { label: __('Pledge Backed', 'growfund'), variant: 'backed' }],
    ['cancelled', { label: __('Pledge Cancelled', 'growfund'), variant: 'critical' }],
    ['failed', { label: __('Pledge Failed', 'growfund'), variant: 'critical' }],
  ]);
}

function prepareMessageKey(pledge: Pledge): MessageKey | null {
  const allowed: Pledge['status'][] = [
    'pending',
    'completed',
    'backed',
    'failed',
    'cancelled',
    'in-progress',
  ];
  if (!allowed.includes(pledge.status)) {
    return null;
  }
  if (pledge.status === 'pending') {
    return 'created';
  }

  return pledge.status as MessageKey;
}

function getActionAlerts() {
  return new Map<Action, string>([
    [
      'charge-backer',
      __(
        'This action will charge the associated backer for the pledge. The pledged amount will be collected and recorded.',
        'growfund',
      ),
    ],
    [
      'mark-as-backed',
      __(
        'This action will mark the pledge as backed, indicating that the payment has been successfully collected from the backer.',
        'growfund',
      ),
    ],
    [
      'retry',
      __(
        'Retrying the failed pledge will rescheduled the pledge to charge the backer again.',
        'growfund',
      ),
    ],
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
    [
      'in-progress',
      __(
        'The payment is in progress. Once the payment is successful, the pledge will be backed automatically.',
        'growfund',
      ),
    ],
  ]);
}

export { getActionAlerts, getActionHeaderText, prepareActionOptions, prepareMessageKey };

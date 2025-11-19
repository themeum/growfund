type Action =
  | 'charge-backer'
  | 'complete'
  | 'cancel'
  | 'delete'
  | 'separator'
  | 'in-progress'
  | 'mark-as-backed'
  | 'retry';

type MessageKey =
  | 'created'
  | 'in-progress'
  | 'completed'
  | 'cancelled'
  | 'backed'
  | 'failed'
  | 'mark-as-backed';

type ActionVariant = 'success' | 'in-progress' | 'critical' | 'backed' | 'completed';

export type { Action, ActionVariant, MessageKey };

type Action =
  | 'charge-backers'
  | 'pause'
  | 'resume'
  | 'end'
  | 'funded'
  | 'completed'
  | 'visible'
  | 'hide'
  | 'delete'
  | 'separator';
type MessageKey =
  | 'published'
  | 'funded'
  | 'completed'
  | 'hidden'
  | 'receiving-contributions'
  | 'paused'
  | 'ended'
  | 'paused-and-hidden'
  | 'not-launched';
type ActionVariant = 'success' | 'secondary' | 'ended' | 'hidden' | 'completed' | 'funded';

export type { Action, ActionVariant, MessageKey };

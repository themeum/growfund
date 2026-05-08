import { growfundConfig } from '@/config/growfund';

export type UserRole =
  | 'administrator'
  | 'growfund_fundraiser'
  | 'growfund_donor'
  | 'growfund_backer'
  | 'growfund_collaborator';

const isMatchedSpecificRole = (role: UserRole) => {
  return growfundConfig.user_role === role;
};

export const User = {
  isFundraiser: () => isMatchedSpecificRole('growfund_fundraiser'),
  isAdmin: () => isMatchedSpecificRole('administrator'),
  isDonor: () => isMatchedSpecificRole('growfund_donor'),
  isBacker: () => isMatchedSpecificRole('growfund_backer'),
  isCollaborator: () => isMatchedSpecificRole('growfund_collaborator'),
  role: growfundConfig.user_role as UserRole,
} as const;

import { __ } from '@wordpress/i18n';
import React, { createContext } from 'react';

import { ErrorIcon } from '@/app/icons';
import { EmptyState, EmptyStateDescription } from '@/components/empty-state';
import { ErrorState, ErrorStateDescription } from '@/components/error-state';
import { LoadingSpinnerOverlay } from '@/components/layouts/loading-spinner';
import { type User } from '@/features/settings/schemas/settings';
import { useCurrentUserQuery } from '@/services/user';
import { matchQueryStatus } from '@/utils/match-query-status';
import { User as CurrentUser, type UserRole } from '@/utils/user';

interface CurrentUserContextType {
  currentUser: User;
  isFundraiser: boolean;
  isCollaborator: boolean;
  isAdmin: boolean;
  isDonor: boolean;
  isBacker: boolean;
  role: UserRole;
}

const CurrentUserContext = createContext<CurrentUserContextType | null>(null);

const CurrentUserProvider = ({ children }: React.PropsWithChildren) => {
  const currentUserQuery = useCurrentUserQuery();

  return matchQueryStatus(currentUserQuery, {
    Loading: <LoadingSpinnerOverlay />,
    Error: (
      <ErrorState className="growfund-mt-10">
        <ErrorIcon />
        <ErrorStateDescription>
          {__('Error loading current user', 'growfund')}
        </ErrorStateDescription>
      </ErrorState>
    ),
    Empty: (
      <EmptyState className="growfund-mt-10">
        <EmptyStateDescription className="growfund-flex growfund-flex-col growfund-items-center">
          {__('No current user', 'growfund')}
        </EmptyStateDescription>
      </EmptyState>
    ),
    Success: ({ data }) => {
      return (
        <CurrentUserContext.Provider
          value={{
            currentUser: data,
            isFundraiser: CurrentUser.isFundraiser(),
            isCollaborator: CurrentUser.isCollaborator(),
            isAdmin: CurrentUser.isAdmin(),
            isDonor: CurrentUser.isDonor(),
            isBacker: CurrentUser.isBacker(),
            role: CurrentUser.role,
          }}
        >
          {children}
        </CurrentUserContext.Provider>
      );
    },
  });
};

export { CurrentUserContext, CurrentUserProvider };

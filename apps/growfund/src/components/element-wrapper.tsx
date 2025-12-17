import React from 'react';

import useCurrentUser from '@/hooks/use-current-user';

interface ElementWrapperProps {
  children: React.ReactNode;
  fallback?: React.ReactNode;
}

const ElementWrapper = ({ children, fallback }: ElementWrapperProps) => {
  const { isAdmin } = useCurrentUser();

  if (!children) {
    if (!isAdmin) {
      return null;
    }

    return fallback ?? null;
  }

  return <>{children}</>;
};

export default ElementWrapper;

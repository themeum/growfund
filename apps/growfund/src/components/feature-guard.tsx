import { __ } from '@wordpress/i18n';
import React from 'react';

import useCurrentUser from '@/hooks/use-current-user';
import { useIsFeatureAvailable } from '@/hooks/use-is-feature-available';

interface FeatureGuardProps {
  feature: string;
  consumedLimit?: number;
  children: React.ReactNode;
  fallback?: React.ReactNode;
}

const FeatureGuard = ({ feature, consumedLimit, children, fallback }: FeatureGuardProps) => {
  const { isAdmin } = useCurrentUser();
  const isFeatureAvailable = useIsFeatureAvailable(feature, consumedLimit);

  if (!isFeatureAvailable) {
    if (!isAdmin) {
      return null;
    }

    return fallback ?? <div>{__('Feature is not available', 'growfund')}</div>;
  }

  return <>{children}</>;
};

export default FeatureGuard;

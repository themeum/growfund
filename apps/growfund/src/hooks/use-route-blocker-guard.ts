import { __ } from '@wordpress/i18n';
import { useEffect } from 'react';
import { useBlocker } from 'react-router';

import { useConsentDialog } from '@/features/campaigns/contexts/consent-dialog-context';

const useRouteBlockerGuard = ({ isDirty }: { isDirty: boolean }) => {
  const { openDialog } = useConsentDialog();
  const blocker = useBlocker(({ currentLocation, nextLocation }) => {
    if (!isDirty) {
      return false;
    }

    return currentLocation.pathname !== nextLocation.pathname;
  });

  useEffect(() => {
    if (blocker.state === 'blocked') {
      openDialog({
        title: __('Unsaved Changes', 'growfund'),
        content: __(
          'You have unsaved changes. Are you sure you want to leave without saving?',
          'growfund',
        ),
        confirmText: __('Leave anyway', 'growfund'),
        confirmButtonVariant: 'destructive',
        declineText: __('Stay', 'growfund'),
        onConfirm: (closeDialog) => {
          blocker.proceed();
          closeDialog();
        },
        onDecline: () => {
          blocker.reset();
        },
      });
    }
  }, [blocker, openDialog]);

  useEffect(() => {
    const handleBeforeUnload = (event: BeforeUnloadEvent) => {
      if (!isDirty) {
        return;
      }
      event.preventDefault();
      return __('You have unsaved changes. Are you sure you want to leave?', 'growfund');
    };
    window.addEventListener('beforeunload', handleBeforeUnload);
    return () => {
      window.removeEventListener('beforeunload', handleBeforeUnload);
    };
  }, [isDirty]);
};

export { useRouteBlockerGuard };

import * as React from 'react';

import {
  DESKTOP_BREAKPOINT,
  LARGE_DESKTOP_BREAKPOINT,
  MOBILE_BREAKPOINT,
  TABLET_BREAKPOINT,
} from '@/constants/screen-breakpoints';

function useBreakpoint() {
  const [breakpoint, setBreakpoint] = React.useState<'sm' | 'md' | 'lg' | 'xl'>('lg');
  React.useEffect(() => {
    const mobileQuery = window.matchMedia(`(max-width: ${MOBILE_BREAKPOINT - 1}px)`);
    const tabletQuery = window.matchMedia(`(max-width: ${TABLET_BREAKPOINT - 1}px)`);
    const desktopQuery = window.matchMedia(`(max-width: ${DESKTOP_BREAKPOINT - 1}px)`);
    const largeDesktopQuery = window.matchMedia(`(max-width: ${LARGE_DESKTOP_BREAKPOINT - 1}px)`);

    const handleBreakpoint = (width: number) => {
      if (width < MOBILE_BREAKPOINT) {
        setBreakpoint('sm');
        return;
      }

      if (width < TABLET_BREAKPOINT) {
        setBreakpoint('md');
        return;
      }

      if (width < DESKTOP_BREAKPOINT) {
        setBreakpoint('lg');
        return;
      }

      if (width < LARGE_DESKTOP_BREAKPOINT) {
        setBreakpoint('xl');
        return;
      }
    };
    const onChange = () => {
      handleBreakpoint(window.innerWidth);
    };
    mobileQuery.addEventListener('change', onChange);
    tabletQuery.addEventListener('change', onChange);
    desktopQuery.addEventListener('change', onChange);
    largeDesktopQuery.addEventListener('change', onChange);
    handleBreakpoint(window.innerWidth);

    return () => {
      mobileQuery.removeEventListener('change', onChange);
      tabletQuery.removeEventListener('change', onChange);
      desktopQuery.removeEventListener('change', onChange);
      largeDesktopQuery.removeEventListener('change', onChange);
    };
  }, []);

  return breakpoint;
}

export { useBreakpoint };

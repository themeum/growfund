import { useEffect } from 'react';

import { RouteConfig } from '@/config/route-config';
import SettingsLayoutContent from '@/features/settings/components/settings-layout-contents';
import { SettingsProvider } from '@/features/settings/context/settings-context';
import { useCurrentPath } from '@/hooks/use-current-path';

const SettingsLayout = () => {
  const currentPath = useCurrentPath();
  useEffect(() => {
    const elements = document.getElementsByClassName('growfund-license-notice');

    Array.from(elements).forEach((el) => {
      const element = el as HTMLElement;
      if (currentPath === RouteConfig.LicenseSettings.template) {
        element.style.setProperty('display', 'none');
      } else {
        element.style.setProperty('display', 'block');
      }
    });
  }, [currentPath]);
  return (
    <SettingsProvider>
      <SettingsLayoutContent />
    </SettingsProvider>
  );
};

export default SettingsLayout;

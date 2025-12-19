import React from 'react';

import MissingPage from '@/components/missing-page';
import { getComponent } from '@/lib/registry';

export const loadComponentLazily = (componentName: string) => {
  return React.lazy(() => {
    const Page = getComponent(componentName);
    return Promise.resolve({ default: Page ? Page : MissingPage });
  });
};

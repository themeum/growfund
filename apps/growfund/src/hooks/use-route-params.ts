import { useParams } from 'react-router';

import { type RouteConfig } from '@/config/route-config';

const useRouteParams = <T extends (typeof RouteConfig)[keyof typeof RouteConfig]>(
  // eslint-disable-next-line @typescript-eslint/no-unused-vars
  routeConfig: T,
) => {
  type UrlParams = Parameters<(typeof routeConfig)['buildLink']>[0];
  return useParams() as unknown as UrlParams extends Record<string, string>
    ? UrlParams
    : Record<string, never>;
};

export { useRouteParams };

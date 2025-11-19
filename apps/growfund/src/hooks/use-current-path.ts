import { matchRoutes, useLocation } from 'react-router';

import { routes } from '@/app/routes';
import { isDefined } from '@/utils';

export const useCurrentPath = () => {
  const location = useLocation();
  const routeMatches = matchRoutes(routes.routes, location);

  if (!isDefined(routeMatches)) {
    return location.pathname;
  }

  const route = routeMatches.find((item) => item.pathname === location.pathname);
  return route?.route.path;
};

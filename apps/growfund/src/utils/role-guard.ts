// import { redirect } from 'react-router';

// import { RouteConfig } from '@/config/route-config';
// import { User as CurrentUser, type UserRole } from '@/utils/user';

// const roleGuard = (roles: UserRole[], redirectTo = RouteConfig.Unauthorized) => {
//   return () => {
//     const isAllowed = roles.includes(CurrentUser.role);

//     if (!isAllowed) {
//       return redirect(redirectTo.buildLink());
//     }

//     return null;
//   };
// };

// export { roleGuard };

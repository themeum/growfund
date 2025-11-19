import { defineRoute } from '@/lib/route';

const UserRouteConfig = {
  Home: defineRoute('/'),
  Donations: defineRoute('/donations'),
  Pledges: defineRoute('/pledges'),
  AnnualReceipts: defineRoute('/annual-receipts'),
  Bookmarks: defineRoute('/bookmarks'),
  Profile: defineRoute('/profile'),
  BackedCampaigns: defineRoute('/backed-campaigns'),
  Settings: defineRoute('/settings'),
  AccountSettings: defineRoute('account'),
  NotificationsSettings: defineRoute('notifications'),
};

export { UserRouteConfig };

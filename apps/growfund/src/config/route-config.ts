import { defineRoute } from '@/lib/route';

export const RouteConfig = {
  Onboarding: defineRoute('/onboarding'),

  MigrateFromCrowdfunding: defineRoute('/migrate-from-crowdfunding'),

  Home: defineRoute('/'),
  About: defineRoute('/about'),
  DesignSystem: defineRoute('/design-system'),
  Campaigns: defineRoute('/campaigns'),
  Pledges: defineRoute('/pledges'),
  CreatePledge: defineRoute('/pledges/create'),
  EditPledge: defineRoute('/pledges/:id'),

  Analytics: defineRoute('/analytics'),
  Themes: defineRoute('/themes'),
  Settings: defineRoute('/settings'),

  FundraiserProfile: defineRoute('/profile'),
  FundraiserSettings: defineRoute('/profile-settings'),
  FundraiserAccount: defineRoute('account'),
  FundraiserNotifications: defineRoute('notifications'),

  Tools: defineRoute('/tools'),
  License: defineRoute('/license'),
  CampaignBuilder: defineRoute('/campaigns/:id'),
  CampaignStepBasic: defineRoute('basic'),
  CampaignStepGoal: defineRoute('goal'),
  CampaignStepRewards: defineRoute('rewards'),
  CampaignStepSettings: defineRoute('settings'),
  CampaignStepAdditional: defineRoute('additional'),
  GeneralSettings: defineRoute('general'),
  ApiDocs: defineRoute('/api-docs'),

  //Donations routes
  Donations: defineRoute('/donations'),
  CreateDonation: defineRoute('/donations/create'),
  EditDonation: defineRoute('/donations/:id'),

  // Setting routes
  PaymentSettings: defineRoute('payment'),
  CampaignSettings: defineRoute('campaign'),
  UserAndPermissionsSettings: defineRoute('user-and-permissions'),
  paymentSettings: defineRoute('payment'),
  PdfReceiptSettings: defineRoute('pdf-receipt'),
  EmailAndNotificationsSettings: defineRoute('email-and-notifications'),
  SecuritySettings: defineRoute('security'),
  IntegrationsSettings: defineRoute('integrations'),
  BrandingSettings: defineRoute('branding'),
  AdvancedSettings: defineRoute('advanced'),
  LicenseSettings: defineRoute('license'),

  // settings templates
  EcardTemplate: defineRoute('/tribute/ecard-template'),
  PdfReceiptTemplate: defineRoute('/pdf-receipt/template'),
  PdfAnnualReceiptTemplate: defineRoute('/pdf-annual-receipt/template'),
  EmailNotificationTemplate: defineRoute('/email-notification/template'),
  EmailContents: defineRoute('/email-contents/:userType/:emailType'),

  // Parallel routes
  CampaignDetails: defineRoute('/campaigns/:id/details'),

  // Backer routes
  Backers: defineRoute('/backers'),
  BackerDetails: defineRoute('/backers/:id'),
  BackerOverview: defineRoute('overview'),
  BackerPledges: defineRoute('pledges'),

  // Categories routes
  Categories: defineRoute('/categories'),

  // Tags routes
  Tags: defineRoute('/tags'),

  // Donors routes
  Donors: defineRoute('/donors'),
  DonorDetails: defineRoute('/donors/:id'),
  DonorOverview: defineRoute('overview'),
  DonorDonations: defineRoute('donations'),
  DonorsOverview: defineRoute('/donors/:id/overview'),

  // Funds routes
  Funds: defineRoute('/funds'),

  // Fundraiser routes
  Fundraisers: defineRoute('/fundraisers'),
  FundraiserDetails: defineRoute('/fundraisers/:id'),
  FundraiserDetailsOverview: defineRoute('overview'),
  FundraiserDetailsCampaigns: defineRoute('campaigns'),
  FundraisersOverview: defineRoute('/fundraisers/:id/overview'),

  FundraiserBookmarks: defineRoute('/bookmarks'),
  FundraiserMyDonations: defineRoute('/my-donations'),
  FundraiserMyPledges: defineRoute('/my-pledges'),
  FundraiserAnnualReceipts: defineRoute('/annual-receipts'),

  // Common routes
  NotFound: defineRoute('/404-not-found'),

  //public routes
  DonationReceipt: defineRoute('/donations/:uid/receipt'),
  ECard: defineRoute('/donations/:uid/ecard'),
  PledgeReceipt: defineRoute('/pledges/:uid/receipt'),
};

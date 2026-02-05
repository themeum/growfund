import React from 'react';
import { createHashRouter, Navigate } from 'react-router';

import { loadComponentLazily } from '@/config';
import { growfundConfig } from '@/config/growfund';
import { RouteConfig } from '@/config/route-config';
import { UserRouteConfig } from '@/dashboards/shared/config/user-route-config';
import UserSettingsLayout from '@/dashboards/shared/layouts/user-settings-layout';
import UserRootLayout from '@/dashboards/shared/root-layout';
import { ErrorBoundary } from '@/error-boundary';
import BackerDetailsLayout from '@/layouts/backer-details-layout';
import DonorDetailsLayout from '@/layouts/donor-details-layout';
import FundraiserDetailsLayout from '@/layouts/fundraiser-details-layout';
import PublicRootLayout from '@/layouts/public-root-layout';
import RootLayout from '@/layouts/root-layout';
import { lazyImportDevOnly } from '@/lib/route';
import { isDefined } from '@/utils';
import { User as CurrentUser, type UserRole } from '@/utils/user';

const AdditionalPage = React.lazy(() => import('@/features/campaigns/pages/additional-page'));
const BasicStep = React.lazy(() => import('@/features/campaigns/pages/basic-page'));
const GoalStep = React.lazy(() => import('@/features/campaigns/pages/goal-page'));
const RewardsStep = React.lazy(() => import('@/features/campaigns/pages/rewards-page'));
const SettingsStep = React.lazy(() => import('@/features/campaigns/pages/settings-page'));
const AdvancedSettingsPage = React.lazy(
  () => import('@/features/settings/pages/advanced-settings-page'),
);
const BrandingSettingsPage = React.lazy(
  () => import('@/features/settings/pages/branding-settings-page'),
);
const CampaignSettingsPage = React.lazy(
  () => import('@/features/settings/pages/campaign-settings-page'),
);
const EmailNotificationSettingsPage = React.lazy(
  () => import('@/features/settings/pages/email-notification-settings-page'),
);
const GeneralSettingsPage = React.lazy(
  () => import('@/features/settings/pages/general-settings-page'),
);
const PaymentSettingsPage = React.lazy(
  () => import('@/features/settings/pages/payment-settings-page'),
);
const PDFReceiptSettingsPage = React.lazy(
  () => import('@/features/settings/pages/pdf-receipt-settings-page'),
);
const SecuritySettingsPage = React.lazy(
  () => import('@/features/settings/pages/security-settings-page'),
);
const UserPermissionsSettingsPage = React.lazy(
  () => import('@/features/settings/pages/user-permissions-settings-page'),
);

// settings templates
const TemplateBuilderLayout = React.lazy(() => import('@/layouts/template-builder-layout'));
const EcardTemplatePage = React.lazy(
  () => import('@/features/settings/pages/templates/ecard-template-page'),
);
const PdfReceiptTemplate = React.lazy(
  () => import('@/features/settings/pages/templates/pdf-receipt-template-page'),
);
const PdfAnnualReceiptTemplate = React.lazy(
  () => import('@/features/settings/pages/templates/pdf-annual-receipt-template-page'),
);
const EmailNotificationTemplate = React.lazy(
  () => import('@/features/settings/pages/templates/email-default-template-page'),
);

// email contents
const EmailContentPage = React.lazy(
  () => import('@/features/settings/pages/templates/email-content-page'),
);

const HomePage = React.lazy(() => import('@/pages/home-page'));
const DesignSystemPage = React.lazy(() => import('@/pages/design-system-page'));
const CampaignsPage = React.lazy(() => import('@/pages/campaigns-page'));
const PledgesPage = React.lazy(() => import('@/pages/pledges/pledges-page'));
const FundraisersPage = React.lazy(() => import('@/pages/fundraisers-page'));
const AnalyticsPage = React.lazy(() => import('@/pages/analytics-page'));
const LicensePage = React.lazy(() => import('@/pages/license-page'));
const CampaignBuilderLayout = React.lazy(() => import('@/layouts/campaign-builder-layout'));
const SettingsLayout = React.lazy(() => import('@/layouts/settings-layout'));
const CampaignDetailsPage = React.lazy(
  () => import('@/features/campaigns/pages/campaign-details.page'),
);
const ApiDocsPage = lazyImportDevOnly(() => import('@/pages/api-docs'));

// Pledge pages
const CreatePledgePage = React.lazy(() => import('@/pages/pledges/create-pledge-page'));
const PledgeDetailsPage = React.lazy(() => import('@/pages/pledges/pledge-details-page'));

//Donation page
const CreateDonationPage = React.lazy(() => import('@/pages/donations/create-donation-page'));
const DonationsPage = React.lazy(() => import('@/pages/donations-page'));
const EditDonationPage = React.lazy(() => import('@/pages/donations/edit-donation-page'));

// Backer pages
const BackersListingPage = React.lazy(() => import('@/pages/backers/backers-listing-page'));
const BackerDetailsOverviewPage = React.lazy(
  () => import('@/pages/backers/backer-details-overview-page'),
);
const BackerDetailsPledgesPage = React.lazy(
  () => import('@/pages/backers/backer-details-pledges-page'),
);

// Donor pages
const DonorsListingPage = React.lazy(() => import('@/pages/donors/donors-listing-page'));
const DonorDetailsOverviewPage = React.lazy(
  () => import('@/pages/donors/donor-details-overview-page'),
);
const DonorDetailsDonationsPage = React.lazy(
  () => import('@/pages/donors/donor-details-donations-page'),
);

// Common pages
const NotFoundPage = React.lazy(() => import('@/pages/not-found-page'));

const CategoriesPage = React.lazy(() => import('@/pages/categories-page'));
const TagsPage = React.lazy(() => import('@/pages/tags-page'));

// Theme pages
const ThemesPage = React.lazy(() => import('@/pages/themes-page'));

// Fundraiser pages
const FundraiserDetailsOverview = React.lazy(
  () => import('@/pages/fundraisers/fundraiser-details-overview'),
);
const FundraiserDetailsCampaigns = React.lazy(
  () => import('@/pages/fundraisers/fundraiser-details-campaigns'),
);

// user
const UserHomePage = React.lazy(() => import('@/dashboards/shared/pages/user-home-page'));
const UserDonationsPage = React.lazy(() => import('@/dashboards/shared/pages/user-donations-page'));
const UserPledgesPage = React.lazy(() => import('@/dashboards/shared/pages/user-pledges-page'));
const UserProfilePage = React.lazy(() => import('@/dashboards/shared/pages/user-profile-page'));
const UserAccountSettingsPage = React.lazy(
  () => import('@/dashboards/shared/pages/settings/user-account-settings-page'),
);
const UserNotificationsPage = React.lazy(
  () => import('@/dashboards/shared/pages/settings/user-notifications-page'),
);
const UserBookmarkPage = React.lazy(() => import('@/dashboards/shared/pages/user-bookmarks-page'));
const UserAnnualReceiptPage = React.lazy(
  () => import('@/dashboards/shared/pages/user-annual-receipt-page'),
);
const UserBackedCampaignsPage = React.lazy(
  () => import('@/dashboards/shared/pages/user-backed-campaigns-page'),
);

const OnboardingPage = React.lazy(() => import('@/pages/onboarding-page'));
const MigrationPage = React.lazy(() => import('@/pages/migration-page'));

const DonationReceiptPage = React.lazy(() => import('@/public/pages/donation-receipt-page'));
const ECardPage = React.lazy(() => import('@/public/pages/ecard-page'));
const PledgeReceiptPage = React.lazy(() => import('@/public/pages/pledge-receipt-page'));

const FundPage = loadComponentLazily('FundPage');
const LicenseSettingsPage = loadComponentLazily('LicenseSettingsPage');
const FundraiserBookmarkPage = loadComponentLazily('FundraiserBookmarkPage');
const FundraiserProfilePage = loadComponentLazily('FundraiserProfilePage');
const FundraiserMyDonationsPage = loadComponentLazily('FundraiserMyDonationsPage');
const FundraiserAnnualReceiptsPage = loadComponentLazily('FundraiserAnnualReceiptsPage');
const FundraiserMyPledgesPage = loadComponentLazily('FundraiserMyPledgesPage');

const allRoutes = [
  {
    path: RouteConfig.MigrateFromCrowdfunding.template,
    roles: ['administrator'],
    Component: MigrationPage,
    mode: 'all',
  },
  {
    path: RouteConfig.Home.template,
    Component: RootLayout,
    ErrorBoundary: ErrorBoundary,
    roles: ['administrator', 'growfund_fundraiser'],
    children: [
      {
        path: RouteConfig.Home.template,
        Component: HomePage,
        roles: ['administrator', 'growfund_fundraiser'],
        mode: 'all',
      },
      {
        path: RouteConfig.DesignSystem.template,
        Component: DesignSystemPage,
        roles: ['administrator'],
        mode: 'all',
      },
      {
        path: RouteConfig.Campaigns.template,
        Component: CampaignsPage,
        roles: ['administrator', 'growfund_fundraiser'],
        mode: 'all',
      },
      {
        path: RouteConfig.CampaignDetails.template,
        Component: CampaignDetailsPage,
        roles: ['administrator', 'growfund_fundraiser'],
        mode: 'all',
      },
      {
        path: RouteConfig.CampaignBuilder.template,
        Component: CampaignBuilderLayout,
        roles: [],
        mode: 'all',
        children: [
          {
            index: true,
            element: <Navigate to={RouteConfig.CampaignStepBasic.buildLink()} replace />,
          },
          {
            path: RouteConfig.CampaignStepBasic.template,
            Component: BasicStep,
            roles: ['administrator', 'growfund_fundraiser'],
            mode: 'all',
          },
          {
            path: RouteConfig.CampaignStepGoal.template,
            Component: GoalStep,
            roles: ['administrator', 'growfund_fundraiser'],
            mode: 'all',
          },
          {
            path: RouteConfig.CampaignStepRewards.template,
            Component: RewardsStep,
            roles: ['administrator', 'growfund_fundraiser'],
            mode: 'reward',
          },
          {
            path: RouteConfig.CampaignStepSettings.template,
            Component: SettingsStep,
            roles: ['administrator', 'growfund_fundraiser'],
            mode: 'all',
          },
          {
            path: RouteConfig.CampaignStepAdditional.template,
            Component: AdditionalPage,
            roles: ['administrator', 'growfund_fundraiser'],
            mode: 'all',
          },
        ],
      },
      {
        path: RouteConfig.Pledges.template,
        Component: PledgesPage,
        roles: ['administrator', 'growfund_fundraiser'],
        mode: 'reward',
      },
      {
        path: RouteConfig.CreatePledge.template,
        Component: CreatePledgePage,
        roles: ['administrator', 'growfund_fundraiser'],
        mode: 'reward',
      },
      {
        path: RouteConfig.EditPledge.template,
        Component: PledgeDetailsPage,
        roles: ['administrator', 'growfund_fundraiser'],
        mode: 'reward',
      },
      {
        path: RouteConfig.Backers.template,
        Component: BackersListingPage,
        roles: ['administrator', 'growfund_fundraiser'],
        mode: 'reward',
      },
      {
        path: RouteConfig.BackerDetails.template,
        Component: BackerDetailsLayout,
        roles: [],
        children: [
          {
            index: true,
            element: <Navigate to={RouteConfig.BackerOverview.buildLink()} replace />,
          },
          {
            path: RouteConfig.BackerOverview.template,
            Component: BackerDetailsOverviewPage,
            roles: ['administrator', 'growfund_fundraiser'],
            mode: 'reward',
          },
          {
            path: RouteConfig.BackerPledges.template,
            Component: BackerDetailsPledgesPage,
            roles: ['administrator', 'growfund_fundraiser'],
            mode: 'reward',
          },
        ],
      },
      {
        path: RouteConfig.Donors.template,
        Component: DonorsListingPage,
        roles: ['administrator', 'growfund_fundraiser'],
        mode: 'donation',
      },
      {
        path: RouteConfig.DonorDetails.template,
        Component: DonorDetailsLayout,
        roles: [],
        children: [
          {
            index: true,
            element: <Navigate to={RouteConfig.DonorOverview.buildLink()} replace />,
          },
          {
            path: RouteConfig.DonorOverview.template,
            Component: DonorDetailsOverviewPage,
            roles: ['administrator', 'growfund_fundraiser'],
            mode: 'donation',
          },
          {
            path: RouteConfig.DonorDonations.template,
            Component: DonorDetailsDonationsPage,
            roles: ['administrator', 'growfund_fundraiser'],
            mode: 'donation',
          },
        ],
      },
      {
        path: RouteConfig.Fundraisers.template,
        Component: FundraisersPage,
        roles: ['administrator'],
        mode: 'all',
      },
      {
        path: RouteConfig.FundraiserDetails.template,
        Component: FundraiserDetailsLayout,
        roles: [],
        children: [
          {
            index: true,
            element: <Navigate to={RouteConfig.FundraiserDetailsOverview.buildLink()} replace />,
          },
          {
            path: RouteConfig.FundraiserDetailsOverview.template,
            Component: FundraiserDetailsOverview,
            roles: ['administrator'],
            mode: 'all',
          },
          {
            path: RouteConfig.FundraiserDetailsCampaigns.template,
            Component: FundraiserDetailsCampaigns,
            roles: ['administrator'],
            mode: 'all',
          },
        ],
      },
      {
        path: RouteConfig.Donations.template,
        Component: DonationsPage,
        roles: ['administrator', 'growfund_fundraiser'],
        mode: 'donation',
      },
      {
        path: RouteConfig.CreateDonation.template,
        Component: CreateDonationPage,
        roles: ['administrator'],
        mode: 'donation',
      },
      {
        path: RouteConfig.EditDonation.template,
        Component: EditDonationPage,
        roles: ['administrator'],
        mode: 'donation',
      },
      {
        path: RouteConfig.Analytics.template,
        Component: AnalyticsPage,
        roles: ['administrator', 'growfund_fundraiser'],
        mode: 'all',
      },
      {
        path: RouteConfig.Categories.template,
        Component: CategoriesPage,
        roles: ['administrator'],
        mode: 'all',
      },
      {
        path: RouteConfig.Tags.template,
        Component: TagsPage,
        roles: ['administrator'],
        mode: 'all',
      },
      {
        path: RouteConfig.Themes.template,
        Component: ThemesPage,
        roles: ['administrator'],
        mode: 'all',
      },
      {
        path: RouteConfig.Settings.template,
        Component: SettingsLayout,
        roles: ['administrator'],
        mode: 'all',
        children: [
          {
            index: true,
            element: <Navigate to={RouteConfig.GeneralSettings.buildLink()} replace />,
            roles: ['administrator'],
            mode: 'all',
          },
          {
            path: RouteConfig.GeneralSettings.template,
            Component: GeneralSettingsPage,
            roles: ['administrator'],
            mode: 'all',
          },
          {
            path: RouteConfig.CampaignSettings.template,
            Component: CampaignSettingsPage,
            roles: ['administrator'],
            mode: 'all',
          },
          {
            path: RouteConfig.PaymentSettings.template,
            Component: PaymentSettingsPage,
            roles: ['administrator'],
            mode: 'all',
          },
          {
            path: RouteConfig.UserAndPermissionsSettings.template,
            Component: UserPermissionsSettingsPage,
            roles: ['administrator'],
            mode: 'all',
          },
          {
            path: RouteConfig.PdfReceiptSettings.template,
            Component: PDFReceiptSettingsPage,
            roles: ['administrator'],
            mode: 'all',
          },
          {
            path: RouteConfig.EmailAndNotificationsSettings.template,
            Component: EmailNotificationSettingsPage,
            roles: ['administrator'],
            mode: 'all',
          },
          {
            path: RouteConfig.SecuritySettings.template,
            Component: SecuritySettingsPage,
            roles: ['administrator'],
            mode: 'all',
          },
          {
            path: RouteConfig.BrandingSettings.template,
            Component: BrandingSettingsPage,
            roles: ['administrator'],
            mode: 'all',
          },
          {
            path: RouteConfig.AdvancedSettings.template,
            Component: AdvancedSettingsPage,
            roles: ['administrator'],
            mode: 'all',
          },
          {
            path: RouteConfig.LicenseSettings.template,
            Component: LicenseSettingsPage,
            roles: ['administrator'],
            mode: 'all',
            ignore: !growfundConfig.has_growfund_pro,
          },
        ],
      },
      {
        Component: TemplateBuilderLayout,
        children: [
          {
            path: RouteConfig.EcardTemplate.template,
            Component: EcardTemplatePage,
            roles: ['administrator'],
            mode: 'donation',
          },
          {
            path: RouteConfig.PdfReceiptTemplate.template,
            Component: PdfReceiptTemplate,
            roles: ['administrator'],
            mode: 'all',
          },
          {
            path: RouteConfig.PdfAnnualReceiptTemplate.template,
            Component: PdfAnnualReceiptTemplate,
            roles: ['administrator'],
            mode: 'donation',
          },
          {
            path: RouteConfig.EmailNotificationTemplate.template,
            Component: EmailNotificationTemplate,
            roles: ['administrator'],
            mode: 'all',
          },
        ],
      },
      {
        Component: EmailContentPage,
        path: RouteConfig.EmailContents.template,
        roles: ['administrator'],
        mode: 'all',
      },
      {
        path: RouteConfig.License.template,
        Component: LicensePage,
        roles: ['administrator'],
        mode: 'all',
      },
      {
        path: RouteConfig.ApiDocs.template,
        Component: ApiDocsPage,
        roles: ['administrator'],
        mode: 'all',
      },
      {
        path: RouteConfig.Funds.template,
        Component: FundPage,
        roles: ['administrator'],
        mode: 'donation',
        ignore: !growfundConfig.has_growfund_pro,
      },

      // Fundraiser routes.
      {
        path: RouteConfig.FundraiserBookmarks.template,
        Component: FundraiserBookmarkPage,
        roles: ['growfund_fundraiser'],
        mode: 'all',
        ignore: !growfundConfig.has_growfund_pro,
      },
      {
        path: RouteConfig.FundraiserProfile.template,
        Component: FundraiserProfilePage,
        roles: ['growfund_fundraiser'],
        mode: 'all',
        ignore: !growfundConfig.has_growfund_pro,
      },
      {
        path: RouteConfig.FundraiserMyDonations.template,
        Component: FundraiserMyDonationsPage,
        roles: ['growfund_fundraiser'],
        mode: 'donation',
        ignore: !growfundConfig.has_growfund_pro,
      },
      {
        path: RouteConfig.FundraiserAnnualReceipts.template,
        Component: FundraiserAnnualReceiptsPage,
        roles: ['growfund_fundraiser'],
        mode: 'donation',
        ignore: !growfundConfig.has_growfund_pro,
      },
      {
        path: RouteConfig.FundraiserMyPledges.template,
        Component: FundraiserMyPledgesPage,
        roles: ['growfund_fundraiser'],
        mode: 'reward',
        ignore: !growfundConfig.has_growfund_pro,
      },
      {
        path: RouteConfig.FundraiserSettings.template,
        Component: UserSettingsLayout,
        roles: ['growfund_fundraiser'],
        mode: 'all',
        children: [
          {
            index: true,
            element: <Navigate to={RouteConfig.FundraiserAccount.buildLink()} replace />,
            roles: ['growfund_fundraiser'],
            mode: 'all',
          },
          {
            path: RouteConfig.FundraiserAccount.template,
            Component: UserAccountSettingsPage,
            roles: ['growfund_fundraiser'],
            mode: 'all',
          },
          {
            path: RouteConfig.FundraiserNotifications.template,
            Component: UserNotificationsPage,
            roles: ['growfund_fundraiser'],
            mode: 'all',
          },
        ],
      },
    ],
  },

  // User routes
  {
    path: UserRouteConfig.Home.template,
    Component: UserRootLayout,
    roles: ['growfund_backer', 'growfund_donor'],
    children: [
      {
        path: UserRouteConfig.Home.template,
        Component: UserHomePage,
        roles: ['growfund_backer', 'growfund_donor'],
        mode: 'all',
      },
      {
        path: UserRouteConfig.Donations.template,
        Component: UserDonationsPage,
        roles: ['growfund_donor'],
        mode: 'donation',
      },
      {
        path: UserRouteConfig.Pledges.template,
        Component: UserPledgesPage,
        roles: ['growfund_backer'],
        mode: 'reward',
      },
      {
        path: UserRouteConfig.Profile.template,
        Component: UserProfilePage,
        roles: ['growfund_backer', 'growfund_donor'],
        mode: 'all',
      },
      {
        path: UserRouteConfig.Bookmarks.template,
        Component: UserBookmarkPage,
        roles: ['growfund_backer', 'growfund_donor'],
        mode: 'all',
      },
      {
        path: UserRouteConfig.AnnualReceipts.template,
        Component: UserAnnualReceiptPage,
        roles: ['growfund_donor'],
        mode: 'donation',
      },
      {
        path: UserRouteConfig.BackedCampaigns.template,
        Component: UserBackedCampaignsPage,
        roles: ['growfund_backer'],
        mode: 'reward',
      },
    ],
  },
  {
    path: UserRouteConfig.Settings.template,
    Component: UserSettingsLayout,
    roles: ['growfund_backer', 'growfund_donor'],
    children: [
      {
        index: true,
        element: <Navigate to={UserRouteConfig.AccountSettings.buildLink()} replace />,
        roles: ['growfund_backer', 'growfund_donor'],
        mode: 'all',
      },
      {
        path: UserRouteConfig.AccountSettings.template,
        Component: UserAccountSettingsPage,
        roles: ['growfund_backer', 'growfund_donor'],
        mode: 'all',
      },
      {
        path: UserRouteConfig.NotificationsSettings.template,
        Component: UserNotificationsPage,
        roles: ['growfund_backer', 'growfund_donor'],
        mode: 'all',
      },
    ],
  },
  {
    path: '*',
    Component: NotFoundPage,
  },
] as Route[];

const onboardingOnlyRoutes: Route[] = [
  {
    path: RouteConfig.Onboarding.template,
    roles: ['administrator'],
    Component: OnboardingPage,
    mode: 'all',
  },
  {
    path: '*',
    Component: () => <Navigate to={RouteConfig.Onboarding.template} replace />,
  },
];

const publicRoutes = [
  {
    Component: PublicRootLayout,
    ErrorBoundary: ErrorBoundary,
    children: [
      {
        path: RouteConfig.DonationReceipt.template,
        Component: DonationReceiptPage,
      },
      {
        path: RouteConfig.PledgeReceipt.template,
        Component: PledgeReceiptPage,
      },
      {
        path: RouteConfig.ECard.template,
        Component: ECardPage,
      },
    ],
  },
  {
    path: '*',
    Component: NotFoundPage,
  },
] as Route[];

interface Route {
  path: string;
  Component: React.ComponentType;
  roles?: UserRole[];
  children?: Route[];
  mode?: 'all' | 'reward' | 'donation';
  ignore?: boolean;
}

const excludeProduction: string[] =
  __ENV_MODE__ === 'production' ? [RouteConfig.ApiDocs.template] : [];

function filterRoutesByRole(routes: Route[], role: UserRole): Route[] {
  return routes
    .filter(
      // Keep the route if there is no roles defined for the route or defined but no value or
      // the current user role includes the allowed route roles.
      (route) => {
        if (isDefined(route.ignore) && route.ignore) {
          return false;
        }

        if (!isDefined(route.roles) || route.roles.length === 0) {
          return true;
        }

        return route.roles.includes(role);
      },
    )
    .filter((route) => {
      // Keep the route if there is no route mode defined for the route or defined and is 'all'
      if (!isDefined(route.mode) || route.mode === 'all') {
        return true;
      }

      // Keep the route if the route mode is 'donation' when donation mode is activated
      if (growfundConfig.is_donation_mode) {
        return route.mode === 'donation';
      }

      // Keep the route if the route mode is 'reward' when donation mode is deactivated
      return route.mode === 'reward';
    })
    .filter((route) => !excludeProduction.includes(route.path))
    .map((route) => {
      const newRoute = { ...route };
      if (newRoute.children) {
        newRoute.children = filterRoutesByRole(newRoute.children, role);
      }
      const { roles: _, mode: __, ...rest } = newRoute;
      return rest;
    });
}

const getRoutes = () => {
  if (growfundConfig.as_guest) {
    return publicRoutes;
  }

  return growfundConfig.is_onboarding_completed
    ? filterRoutesByRole(allRoutes, CurrentUser.role)
    : filterRoutesByRole(onboardingOnlyRoutes, CurrentUser.role);
};

const routes = createHashRouter(getRoutes());

export { routes };

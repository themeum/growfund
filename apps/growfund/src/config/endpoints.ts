import { type AnalyticType } from '@/features/analytics/schemas/analytics';

const endpoints = {
  // App Config endpoints
  APP_CONFIG: '/app-config',
  WORDPRESS_PAGES: '/wp-pages',
  OPTIONS: '/options',
  ONBOARDING: '/app-config/onboarding',
  WOOCOMMERCE_CONFIG: '/app-config/woocommerce',

  // migration
  MIGRATION: '/migration',

  // Media
  UPLOAD_MEDIA: '/upload-media',

  //Email
  EMAIL_CONTENT: '/email-content',
  EMAIL_CONTENT_RESTORE: '/email-content/restore',

  // Payment Gateways
  PAYMENT_GATEWAYS: '/payment-gateways',
  INSTALL_PAYMENT_GATEWAY: '/payment-gateways/install',
  PAYMENT_GATEWAY: (name: string) => `/payment-gateways/${name}`,

  PAYMENT_METHODS: (type: string) => `/payment-methods/${type}`,

  // Users endpoints
  VALIDATE_EMAIL: '/validate-email',
  VALIDATE_USERNAME: '/validate-username',
  CURRENT_USER: '/current-user',
  USERS: '/users',
  USER_RESET_PASSWORD: (id: string) => `/users/${id}/update-password`,
  USER_NOTIFICATIONS: (id: string) => `/users/${id}/notifications`,
  USER_LOGOUT: '/logout',
  DELETE_MY_ACCOUNT: '/delete-my-account',

  // Categories endpoints
  CATEGORIES: '/categories',
  TOP_LEVEL_CATEGORIES: '/categories/top-level',
  SUBCATEGORIES: (parentId: string) => `/categories/${parentId}/subcategories`,
  CATEGORIES_WITH_ID: (id: string) => `/categories/${id}`,
  CATEGORIES_BULK_ACTIONS: '/categories/bulk-actions',
  EMPTY_CATEGORIES_TRASH: '/categories/empty-trash',

  // Tags endpoints
  TAGS: '/tags',
  TAGS_WITH_ID: (id: string) => `/tags/${id}`,
  TAGS_BULK_ACTIONS: '/tags/bulk-actions',

  // Campaigns endpoints
  CAMPAIGNS: '/campaigns',
  CAMPAIGNS_WITH_ID: (id: string) => `/campaigns/${id}`,
  CAMPAIGN_REWARD_ITEMS: (campaign_id: string) => `/campaigns/${campaign_id}/reward-items`,
  CAMPAIGN_REWARD_ITEMS_WITH_ID: (campaign_id: string, id: string) =>
    `/campaigns/${campaign_id}/reward-items/${id}`,
  CAMPAIGN_REWARDS: (campaign_id: string) => `/campaigns/${campaign_id}/rewards`,
  CAMPAIGN_REWARD_WITH_ID: (campaign_id: string, reward_id: string) =>
    `/campaigns/${campaign_id}/rewards/${reward_id}`,
  CAMPAIGN_POST_UPDATE: (campaign_id: string) => `/campaigns/${campaign_id}/post-update`,
  CAMPAIGNS_BULK_ACTIONS: '/campaigns/bulk-actions',
  EMPTY_CAMPAIGNS_TRASH: '/campaigns/empty-trash',
  CAMPAIGN_UPDATE_SECONDARY_STATUS: (id: string) => `/campaigns/${id}/update-secondary-status`,
  CHARGE_BACKERS: (id: string) => `/campaigns/${id}/charge-backers`,
  CAMPAIGN_DUPLICATE: (id: string) => `/campaigns/${id}/copy`,
  CAMPAIGN_MARK_AS_READY_FOR_PICKUP: (id: string) => `/campaigns/${id}/mark-as-ready-for-pickup`,

  // Backers endpoints
  BACKERS: '/backers',
  BACKERS_WITH_ID: (id: string) => `/backers/${id}`,
  BACKERS_OVERVIEW: (id: string) => `/backers/${id}/overview`,
  BACKERS_BULK_ACTIONS: '/backers/bulk-actions',
  EMPTY_BACKERS_TRASH: '/backers/empty-trash',
  BACKERS_NOTIFICATION_SETTINGS: (id: string) => `/backers/${id}/notification-settings`,
  BACKERS_CAMPAIGNS_BY_A_BACKER: (backerId: string) => `/backers/${backerId}/campaigns`,
  BACKER_GIVING_STATS: (id: string) => `/backers/${id}/giving-stats`,
  BACKER_ACTIVITIES: (id: string) => `/backers/${id}/activities`,

  //Pledges endpoints
  PLEDGES: '/pledges',
  PLEDGES_WITH_ID: (id: string) => `/pledges/${id}`,
  PLEDGES_BULK_ACTIONS: '/pledges/bulk-actions',
  EMPTY_PLEDGES_TRASH: '/pledges/empty-trash',
  CHARGE_BACKER: (pledgeId: string) => `/pledges/${pledgeId}/charge-backer`,
  RETRY_FAILED_PAYMENT: (pledgeId: string) => `/pledges/${pledgeId}/retry-failed-payment`,
  MARK_AS_READY_FOR_PICKUP: (pledgeId: string) => `/pledges/${pledgeId}/mark-as-ready-for-pickup`,
  REWARD_ITEM_DOWNLOAD: (uid: string, id: string | number) =>
    `/pledges/${uid}/reward-items/${id}/download`,

  // Pledge Timelines endpoints
  PLEDGE_TIMELINES: (pledgeId: string) => `/pledges/${pledgeId}/timelines`,
  PLEDGE_TIMELINES_WITH_ID: (pledgeId: string, timelineId: string) =>
    `/pledges/${pledgeId}/timelines/${timelineId}`,
  PLEDGE_ACTIVITIES: (id: string) => `/pledges/${id}/activities`,

  //Fundraiser endpoints
  FUNDRAISERS: '/fundraisers',
  FUNDRAISERS_WITH_ID: (id: string) => `/fundraisers/${id}`,
  FUNDRAISERS_BULK_ACTIONS: '/fundraisers/bulk-actions',
  EMPTY_FUNDRAISERS_TRASH: '/fundraisers/empty-trash',
  FUNDRAISER_OVERVIEW: (id: string) => `/fundraisers/${id}/overview`,
  FUNDRAISER_ACTIVITIES: (id: string) => `/fundraisers/${id}/activities`,
  FUNDRAISER_WITHDRAWALS: '/fundraisers-withdrawals',
  FUNDRAISER_PAYOUT_SETTINGS: (id: string) => `/fundraisers/${id}/payout-method`,
  WITHDRAWALS: '/withdrawals',
  WITHDRAWALS_WITH_ID: (id: string) => `/withdrawals/${id}`,
  WALLET: '/wallet',

  // Collaborators endpoints
  COLLABORATORS: '/collaborators',
  CAMPAIGN_COLLABORATORS: (id: string) => `/campaigns/${id}/collaborators`,

  // Donations endpoints
  DONATIONS: '/donations',
  DONATIONS_WITH_ID: (id: string) => `/donations/${id}`,
  DONATIONS_BULK_ACTIONS: '/donations/bulk-actions',
  EMPTY_DONATIONS_TRASH: '/donations/empty-trash',

  // Donations Timelines endpoints
  DONATION_TIMELINES: (donationId: string) => `/donations/${donationId}/timelines`,
  DONATION_TIMELINES_WITH_ID: (donationId: string, timelineId: string) =>
    `/donations/${donationId}/timelines/${timelineId}`,
  DONATION_ACTIVITIES: (id: string) => `/donations/${id}/activities`,

  // Donors endpoints
  DONORS: '/donors',
  DONORS_WITH_ID: (id: string) => `/donors/${id}`,
  DONORS_OVERVIEW: (id: string) => `/donors/${id}/overview`,
  DONORS_BULK_ACTIONS: '/donors/bulk-actions',
  EMPTY_DONORS_TRASH: '/donors/empty-trash',
  DONOR_STATS: `/donor-stats`,
  DONOR_ACTIVITIES: (id: string) => `/donors/${id}/activities`,
  DONOR_ANNUAL_RECEIPTS: `/annual-receipts`,
  DONOR_ANNUAL_RECEIPT_DONATIONS_BY_YEAR: (year: string) => `/annual-receipts/${year}`,
  MAKE_FUNDRAISER: `/make-fundraiser`,

  //Fund endpoints
  FUNDS: '/funds',
  FUNDS_WITH_ID: (id: string) => `/funds/${id}`,
  FUNDS_DETAILS: (id: string) => `/funds/${id}/details`,
  FUNDS_BULK_ACTIONS: '/funds/bulk-actions',
  EMPTY_FUNDS_TRASH: '/funds/empty-trash',
  ALL_FUNDS: '/funds/all',

  // analytics endpoints
  ANALYTICS: (type: AnalyticType) => `/analytics/${type}`,

  // bookmarks endpoints
  BOOKMARKS: '/bookmarks',

  // guest endpoints
  DONATION_RECEIPT: (uid: string) => `/donations/${uid}/receipt`,
  ECard: (uid: string) => `/donations/${uid}/ecard`,
  PLEDGE_RECEIPT: (uid: string) => `/pledges/${uid}/receipt`,

  // manual pages generation endpoints
  MANUAL_PAGES: '/growfund-pages',
  REGENERATE_PAGES: '/growfund-pages',

  // withdrawal requests endpoints
  WITHDRAWAL_REQUESTS: '/withdrawal-requests',
} as const;

export { endpoints };

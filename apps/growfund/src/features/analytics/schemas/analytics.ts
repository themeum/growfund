import { z } from 'zod';

import { DateRangeSchema } from '@/schemas/filter';

export enum AnalyticType {
  Metrics = 'metrics',
  RevenueChart = 'revenue-chart',
  TopCampaigns = 'top-campaigns',
  TopBackers = 'top-backers',
  TopDonors = 'top-donors',
  DonorOverTime = 'donor-over-time',
  BackerOverTime = 'backer-over-time',
  RevenueBreakdown = 'revenue-breakdown',
  TopFunds = 'top-funds',
  RecentContributions = 'recent-contributions',
}

const AnalyticsFilterSchema = z.object({
  date_range: DateRangeSchema.nullish(),
});

const AnalyticsMetricSchema = z.object({
  amount: z.number(),
  growth: z.number(),
});

const CampaignInformationMetricsSchema = z.object({
  pledged_amount: AnalyticsMetricSchema,
  total_backed_amount: AnalyticsMetricSchema,
  net_backed_amount: AnalyticsMetricSchema,
  total_backers: AnalyticsMetricSchema,
  average_backed_amount: AnalyticsMetricSchema,
  successful_campaigns: AnalyticsMetricSchema,
  failed_campaigns: AnalyticsMetricSchema,
  campaign_success_rate: AnalyticsMetricSchema,
});

const DonationInformationMetricsSchema = z.object({
  total_donation: AnalyticsMetricSchema,
  net_donation: AnalyticsMetricSchema,
  average_donation: AnalyticsMetricSchema,
  total_donors: AnalyticsMetricSchema,
});

const TopFundsSchema = z.object({
  fund_id: z.string(),
  fund_title: z.string(),
  fund_raised: z.number(),
});

const RevenueChartSchema = z.object({ date: z.string(), revenue: z.number() });

type TopFunds = z.infer<typeof TopFundsSchema>;

type CampaignInformationMetrics = z.infer<typeof CampaignInformationMetricsSchema>;
type DonationInformationMetrics = z.infer<typeof DonationInformationMetricsSchema>;

type AnalyticsFilter = z.infer<typeof AnalyticsFilterSchema>;
type AnalyticsMetric = z.infer<typeof AnalyticsMetricSchema>;

type RevenueChart = z.infer<typeof RevenueChartSchema>;

export {
  AnalyticsFilterSchema,
  AnalyticsMetricSchema,
  CampaignInformationMetricsSchema,
  DonationInformationMetricsSchema,
  TopFundsSchema,
  RevenueChartSchema,
};

export type {
  AnalyticsFilter,
  AnalyticsMetric,
  CampaignInformationMetrics,
  DonationInformationMetrics,
  TopFunds,
  RevenueChart,
};

interface RevenueBreakdown {
  date: string;
  number_of_contributors: number;
  pledged_amount?: number;
  total_contributions: number;
  net_payout: number;
}

export type { RevenueBreakdown };

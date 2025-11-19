import { __, sprintf } from '@wordpress/i18n';
import { FileText, HeartHandshake } from 'lucide-react';
import { useNavigate } from 'react-router';

import { EmptySearchIcon2 } from '@/app/icons';
import { EmptyState, EmptyStateDescription } from '@/components/empty-state';
import { LoadingSkeletonJustifyBetween } from '@/components/layouts/loading-skeleton';
import { Badge, type BadgeVariant } from '@/components/ui/badge';
import { Box, BoxContent, BoxTitle } from '@/components/ui/box';
import { Button } from '@/components/ui/button';
import { DotSeparator } from '@/components/ui/dot-separator';
import { Image } from '@/components/ui/image';
import { InfoTooltip } from '@/components/ui/tooltip';
import { RouteConfig } from '@/config/route-config';
import { useAppConfig } from '@/contexts/app-config';
import { useCurrency } from '@/hooks/use-currency';
import { type MediaAttachment } from '@/schemas/media';

interface TopContributorsProps {
  users: {
    id: string;
    first_name: string;
    last_name: string;
    image?: MediaAttachment | null;
    total_contributions: number;
    number_of_contributions: number;
  }[];
  loading?: boolean;
}

const badges: BadgeVariant[] = ['primary', 'secondary', 'warning', 'info', 'special'];

const TopContributors = ({ users, loading }: TopContributorsProps) => {
  const navigate = useNavigate();
  const { isDonationMode } = useAppConfig();
  const { toCurrency } = useCurrency();

  if (users.length === 0) {
    return (
      <Box className="growfund-rounded-3xl">
        <BoxContent className="growfund-py-4 growfund-px-6 growfund-h-full growfund-overflow-hidden">
          <BoxTitle>
            {isDonationMode ? __('Top Donors', 'growfund') : __('Top Backers', 'growfund')}
          </BoxTitle>
          <EmptyState className="growfund-h-full growfund-shadow-none growfund-pt-0">
            <EmptySearchIcon2 />
            <EmptyStateDescription>{__('No data found.', 'growfund')}</EmptyStateDescription>
          </EmptyState>
        </BoxContent>
      </Box>
    );
  }
  return (
    <Box className="growfund-rounded-3xl">
      <BoxContent className="growfund-py-4 growfund-px-6">
        <BoxTitle className="growfund-justify-between [&>span>[data-type=tooltip]]:growfund-opacity-0 group-hover/box:[&>span>[data-type=tooltip]]:growfund-opacity-100">
          <span>
            {isDonationMode ? __('Top Donors', 'growfund') : __('Top Backers', 'growfund')}
            <InfoTooltip>
              {isDonationMode
                ? __(
                    'The Donors who have made the highest total donations across all campaigns.',
                    'growfund',
                  )
                : __(
                    'The Backers who have made the highest total contributions across all campaigns.',
                    'growfund',
                  )}
            </InfoTooltip>
          </span>
          <Button
            variant="ghost"
            size="sm"
            className="growfund-opacity-0 group-hover/box:growfund-opacity-100"
            onClick={() => {
              if (isDonationMode) {
                void navigate(RouteConfig.Donors.buildLink());

                return;
              }

              void navigate(RouteConfig.Backers.buildLink());
            }}
          >
            <FileText className="growfund-size-4" />
            {isDonationMode ? __('See All Donors', 'growfund') : __('See All Backers', 'growfund')}
          </Button>
        </BoxTitle>
        <div className="growfund-space-y-5 growfund-mt-4">
          {users.map((user) => {
            return (
              <LoadingSkeletonJustifyBetween key={user.id} loading={loading} showAvatarSkeleton>
                <div className="growfund-flex growfund-items-center growfund-justify-between">
                  <div className="growfund-flex growfund-items-center growfund-gap-2">
                    <Image
                      src={user.image?.url ?? null}
                      alt={user.first_name}
                      className="growfund-size-5 growfund-rounded-full"
                      aspectRatio="square"
                    />
                    <p
                      className="growfund-typo-small growfund-font-medium growfund-text-fg-primary growfund-max-w-32 growfund-truncate"
                      title={sprintf('%s %s', user.first_name, user.last_name)}
                    >
                      {sprintf('%s %s', user.first_name, user.last_name)}
                    </p>
                    <DotSeparator />
                    <div className="growfund-flex growfund-items-center growfund-gap-1">
                      <HeartHandshake className="growfund-size-3 growfund-text-icon-primary" />
                      <span className="growfund-typo-tiny growfund-font-medium growfund-text-fg-secondary">
                        {isDonationMode
                          ? /* translators: %s: number of donations */
                            sprintf(__('%s donations', 'growfund'), user.number_of_contributions)
                          : /* translators: %s: number of pledges */
                            sprintf(__('%s pledges', 'growfund'), user.number_of_contributions)}
                      </span>
                    </div>
                  </div>
                  <Badge variant={badges[Math.floor(Math.random() * badges.length + 1)]}>
                    {toCurrency(user.total_contributions)}
                  </Badge>
                </div>
              </LoadingSkeletonJustifyBetween>
            );
          })}
        </div>
      </BoxContent>
    </Box>
  );
};

export default TopContributors;

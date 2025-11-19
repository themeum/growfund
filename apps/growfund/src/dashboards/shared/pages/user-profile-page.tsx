import { __, sprintf } from '@wordpress/i18n';
import { format } from 'date-fns';
import { BadgeCheck, Calendar, Clock, Edit, User } from 'lucide-react';
import { useEffect } from 'react';
import { useNavigate } from 'react-router';

import { Container } from '@/components/layouts/container';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Box, BoxContent } from '@/components/ui/box';
import { Button } from '@/components/ui/button';
import { DotSeparator } from '@/components/ui/dot-separator';
import { UserRouteConfig } from '@/dashboards/shared/config/user-route-config';
import { useDashboardLayoutContext } from '@/dashboards/shared/contexts/root-layout-context';
import useCurrentUser from '@/hooks/use-current-user';
import { DATE_FORMATS } from '@/lib/date';
import { createAcronym, displayAddress } from '@/utils';
import { User as CurrentUser } from '@/utils/user';

const UserProfilePage = () => {
  const { setTopbar } = useDashboardLayoutContext();
  const { currentUser: user } = useCurrentUser();
  const navigate = useNavigate();

  useEffect(() => {
    setTopbar({
      title: __('Profile', 'growfund'),
      icon: User,
    });
  }, [setTopbar]);

  const isBacker = CurrentUser.isBacker();
  const isDonor = CurrentUser.isDonor();

  if (!isBacker && !isDonor) {
    return <div>{__('You are not allowed to access this page.', 'growfund')}</div>;
  }

  return (
    <Container className="growfund-mt-10 growfund-space-y-3" size="sm">
      <Box className="growfund-border-none growfund-shadow-none">
        <BoxContent className="growfund-flex growfund-items-center growfund-gap-3 growfund-relative">
          <Avatar className="growfund-shrink-0 growfund-size-[5.5rem]">
            <AvatarImage src={user.image?.url ?? undefined} />
            <AvatarFallback>
              {createAcronym({ first_name: user.first_name, last_name: user.last_name })}
            </AvatarFallback>
          </Avatar>
          <div className="growfund-space-y-3">
            <h4 className="growfund-typo-h4 growfund-font-semibold growfund-text-fg-primary growfund-flex growfund-items-center growfund-gap-2">
              <span className="growfund-break-all growfund-line-clamp-2 growfund-max-w-96">
                {sprintf('%s %s', user.first_name, user.last_name)}
              </span>
              {user.is_verified ? (
                <Badge variant="primary">
                  <BadgeCheck className="growfund-size-3 growfund-text-icon-success" />
                  {__('Verified', 'growfund')}
                </Badge>
              ) : (
                <Badge variant="secondary">{__('Awaiting Verification', 'growfund')}</Badge>
              )}
            </h4>
            <div className="growfund-flex growfund-items-center growfund-gap-2 growfund-typo-tiny">
              {user.last_contribution_at && (
                <>
                  <div className="growfund-flex growfund-items-center growfund-gap-1">
                    <Clock className="growfund-size-4 growfund-text-icon-primary" />
                    <span className="growfund-typo-body-sm growfund-text-fg-primary">
                      {
                        (isBacker
                          ? sprintf(
                              /* translators: %s: Last pledged date */
                              __('Last Pledged %s', 'growfund'),
                              format(
                                new Date(user.last_contribution_at),
                                DATE_FORMATS.HUMAN_READABLE_DATE_WITH_TIME,
                              ),
                            )
                          : /* translators: %s: Last donated date */
                            __('Last Donated %s', 'growfund'),
                        format(
                          new Date(user.last_contribution_at),
                          DATE_FORMATS.HUMAN_READABLE_DATE_WITH_TIME,
                        ))
                      }
                    </span>
                  </div>
                  <DotSeparator />
                </>
              )}

              <div className="growfund-flex growfund-items-center growfund-gap-1">
                <Calendar className="growfund-size-4 growfund-text-icon-primary" />
                <span className="growfund-typo-body-sm growfund-text-fg-primary">
                  {sprintf(
                    /* translators: %s: Joined date */
                    __('Joined %s', 'growfund'),
                    format(new Date(user.joined_at), DATE_FORMATS.HUMAN_READABLE),
                  )}
                </span>
              </div>
            </div>
            <Badge variant="info">
              {sprintf(
                isBacker
                  ? /* translators: %s: Number of pledged campaigns */
                    __('Pledged to %s campaigns', 'growfund')
                  : /* translators: %s: Number of donated campaigns */
                    __('Donated to %s campaigns', 'growfund'),
                user.total_number_of_contributions,
              )}
            </Badge>
          </div>

          <Button
            variant="secondary"
            size="sm"
            className="growfund-absolute growfund-top-1/2 growfund-right-6 growfund-translate-y-[-50%]"
            onClick={() => {
              void navigate(UserRouteConfig.Settings.buildLink());
            }}
          >
            <Edit />
            {__('Edit Profile', 'growfund')}
          </Button>
        </BoxContent>
      </Box>

      <Box className="growfund-border-none growfund-shadow-none">
        <BoxContent>
          <h6 className="growfund-typo-h6 growfund-font-semibold growfund-text-fg-primary">
            {__('Details', 'growfund')}
          </h6>
          <div className="growfund-space-y-4 growfund-mt-3">
            {/* First & Last Name */}
            <div className="growfund-grid growfund-grid-cols-2 growfund-gap-3">
              <div className="growfund-flex growfund-flex-col growfund-gap-1">
                <span className="growfund-typo-small growfund-text-fg-muted-foreground">
                  {__('First Name', 'growfund')}
                </span>
                <span className="growfund-typo-small growfund-text-fg-primary growfund-break-all growfund-line-clamp-2">
                  {user.first_name}
                </span>
              </div>
              <div className="growfund-flex growfund-flex-col growfund-gap-1">
                <span className="growfund-typo-small growfund-text-fg-muted-foreground">
                  {__('Last Name', 'growfund')}
                </span>
                <span className="growfund-typo-small growfund-text-fg-primary growfund-break-all growfund-line-clamp-2">
                  {user.last_name}
                </span>
              </div>
            </div>

            {/* Email & Phone */}
            <div className="growfund-grid growfund-grid-cols-2 growfund-gap-3">
              <div className="growfund-flex growfund-flex-col growfund-gap-1">
                <span className="growfund-typo-small growfund-text-fg-muted-foreground growfund-flex growfund-items-center growfund-gap-2">
                  {__('Email', 'growfund')}
                </span>
                <span className="growfund-typo-small growfund-text-fg-primary">{user.email}</span>
              </div>
              <div className="growfund-flex growfund-flex-col growfund-gap-1">
                <span className="growfund-typo-small growfund-text-fg-muted-foreground">
                  {__('Phone Number', 'growfund')}
                </span>
                <span className="growfund-typo-small growfund-text-fg-primary">{user.phone}</span>
              </div>
            </div>

            {/* Shipping Address & Billing Address */}
            <div className="growfund-grid growfund-grid-cols-2 growfund-gap-3">
              {isBacker && (
                <>
                  <div className="growfund-flex growfund-flex-col growfund-gap-1">
                    <span className="growfund-typo-small growfund-text-fg-muted-foreground">
                      {__('Shipping Address', 'growfund')}
                    </span>
                    <span className="growfund-typo-small growfund-text-fg-primary">
                      {displayAddress(user.shipping_address)}
                    </span>
                  </div>
                  {user.is_billing_address_same ? (
                    <div className="growfund-flex growfund-flex-col growfund-gap-1">
                      <span className="growfund-typo-small growfund-text-fg-muted-foreground">
                        {__('Billing Address', 'growfund')}
                      </span>
                      <span className="growfund-typo-small growfund-text-fg-primary">
                        {displayAddress(user.shipping_address)}
                      </span>
                    </div>
                  ) : (
                    <div className="growfund-flex growfund-flex-col growfund-gap-1">
                      <span className="growfund-typo-small growfund-text-fg-muted-foreground">
                        {__('Billing Address', 'growfund')}
                      </span>
                      <span className="growfund-typo-small growfund-text-fg-primary">
                        {displayAddress(user.billing_address)}
                      </span>
                    </div>
                  )}
                </>
              )}

              {CurrentUser.isDonor() && (
                <div className="growfund-flex growfund-flex-col growfund-gap-1">
                  <span className="growfund-typo-small growfund-text-fg-muted-foreground">
                    {__('Billing Address', 'growfund')}
                  </span>
                  <span className="growfund-typo-small growfund-text-fg-primary">
                    {displayAddress(user.billing_address)}
                  </span>
                </div>
              )}
            </div>
          </div>
        </BoxContent>
      </Box>
    </Container>
  );
};

export default UserProfilePage;

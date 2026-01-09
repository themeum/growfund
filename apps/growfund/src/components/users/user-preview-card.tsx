import { __, sprintf } from '@wordpress/i18n';
import { format } from 'date-fns';
import { Baby, BadgeCheck, Edit3, Mail, MapPin, Phone, Trash2 } from 'lucide-react';

import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Box, BoxContent } from '@/components/ui/box';
import { Button } from '@/components/ui/button';
import { DotSeparator } from '@/components/ui/dot-separator';
import { DATE_FORMATS } from '@/lib/date';
import { type Address } from '@/schemas/address';
import { type MediaAttachment } from '@/schemas/media';
import { createAcronym, displayAddress, isDefined } from '@/utils';

interface UserCardProps {
  user?: {
    id: string;
    first_name: string;
    last_name: string;
    email: string;
    username: string;
    joined_at?: string | null;
    phone?: string | null;
    image?: MediaAttachment | null;
    shipping_address?: Address | null;
    billing_address?: {
      address?: string | null;
      address_2?: string | null;
      city?: string | null;
      state?: string | null;
      zip_code?: string | null;
      country?: string | null;
    } | null;
    is_verified: boolean;
  };
  title?: string;
  onRemove?: () => void;
  onEdit?: () => void;
  hideHeader?: boolean;
}

const UserPreviewCard = ({ user, title, onRemove, onEdit, hideHeader = false }: UserCardProps) => {
  if (!user) {
    return null;
  }

  const acronym = createAcronym(user);

  return (
    <Box className="growfund-group/user-card growfund-min-h-80 growfund-h-fit growfund-relative">
      <BoxContent className="growfund-space-y-3">
        {!hideHeader && (
          <div className="growfund-flex growfund-items-center growfund-justify-between">
            <h6 className="growfund-typo-h6 growfund-font-medium growfund-text-fg-primary">
              {title ?? __('User', 'growfund')}
            </h6>
            <div className="growfund-opacity-0 group-hover/user-card:growfund-opacity-100 growfund-transition-opacity">
              {onRemove && (
                <Button
                  variant="ghost"
                  size="icon"
                  className="hover:growfund-text-icon-critical"
                  onClick={onRemove}
                >
                  <Trash2 />
                </Button>
              )}

              {onEdit && (
                <Button
                  variant="ghost"
                  size="icon"
                  className="growfund-size-8 growfund-text-icon-primary"
                  onClick={onEdit}
                >
                  <Edit3 />
                </Button>
              )}
            </div>
          </div>
        )}

        <div className="growfund-space-y-5">
          <div className="growfund-flex growfund-gap-3 growfund-items-center">
            <Avatar className="growfund-size-10">
              <AvatarImage src={user.image?.url ?? undefined} />
              <AvatarFallback>{acronym}</AvatarFallback>
            </Avatar>

            <div className="growfund-flex growfund-flex-col growfund-gap-1 growfund-w-full">
              <div className="growfund-text-fg-secondary growfund-typo-small growfund-font-medium growfund-break-all growfund-line-clamp-2">
                {sprintf('%s %s', user.first_name, user.last_name)}
              </div>
              <div className="growfund-flex growfund-items-center growfund-gap-2">
                <div className="growfund-typo-tiny growfund-text-fg-subdued">{user.username}</div>
                <DotSeparator />
                {Number(user.id) > 0 && (
                  <div className="growfund-typo-tiny growfund-text-fg-subdued">
                    {/* translator: %s: user ID */}
                    {sprintf(__('ID #%s', 'growfund-pro'), user.id)}
                  </div>
                )}
              </div>
            </div>
          </div>
          <div className="growfund-space-y-4">
            <div className="growfund-grid growfund-grid-cols-[1rem_auto] growfund-gap-2">
              <Mail className="growfund-size-4 growfund-text-icon-secondary growfund-flex-shrink-0 growfund-mt-[0.125rem]" />
              <div className="growfund-space-y-2">
                <div className="growfund-text-fg-secondary growfund-typo-small growfund-font-medium">
                  {user.email}
                </div>
                {user.id && (
                  <>
                    {user.is_verified ? (
                      <Badge variant="primary">
                        <BadgeCheck className="growfund-size-3 growfund-text-icon-brand" />
                        {__('Verified', 'growfund')}
                      </Badge>
                    ) : (
                      <Badge variant="secondary">{__('Awaiting Verification', 'growfund')}</Badge>
                    )}
                  </>
                )}
              </div>
            </div>

            {isDefined(user.phone) && (
              <div className="growfund-flex growfund-gap-2 growfund-items-start">
                <Phone className="growfund-size-4 growfund-text-icon-secondary growfund-flex-shrink-0" />
                <div className="growfund-text-fg-secondary growfund-font-medium growfund-typo-small">
                  {user.phone}
                </div>
              </div>
            )}

            {(('shipping_address' in user && isDefined(user.shipping_address)) ||
              ('billing_address' in user && isDefined(user.billing_address))) && (
              <div className="growfund-grid growfund-grid-cols-[1rem_auto] growfund-gap-2">
                <MapPin className="growfund-size-4 growfund-text-icon-secondary growfund-flex-shrink-0 growfund-mt-1" />
                <div className="growfund-space-y-3">
                  {'billing_address' in user && isDefined(user.billing_address) && (
                    <div className="growfund-space-y-1">
                      <span className="growfund-typo-tiny growfund-text-fg-secondary">
                        {__('Billing Address', 'growfund')}
                      </span>
                      <div className="growfund-text-fg-primary growfund-font-medium growfund-typo-small">
                        {displayAddress(user.billing_address as Address)}
                      </div>
                    </div>
                  )}
                  {'shipping_address' in user && user.shipping_address && (
                    <div className="growfund-space-y-1">
                      <span className="growfund-typo-tiny growfund-text-fg-secondary">
                        {__('Shipping Address', 'growfund')}
                      </span>
                      <div className="growfund-text-fg-primary growfund-font-medium growfund-typo-small">
                        {displayAddress(user.shipping_address)}
                      </div>
                    </div>
                  )}
                </div>
              </div>
            )}
          </div>
        </div>
        {isDefined(user.joined_at) && (
          <div className="growfund-flex growfund-gap-2 growfund-items-start growfund-absolute growfund-bottom-4 growfund-left-4">
            <Baby className="growfund-size-4 growfund-text-icon-secondary growfund-flex-shrink-0" />
            <div className="growfund-text-fg-secondary growfund-font-medium growfund-typo-tiny">
              {sprintf(
                /* translators: %s: joined date */
                __('Joined %s', 'growfund'),
                format(new Date(user.joined_at), DATE_FORMATS.HUMAN_READABLE),
              )}
            </div>
          </div>
        )}
      </BoxContent>
    </Box>
  );
};

export default UserPreviewCard;

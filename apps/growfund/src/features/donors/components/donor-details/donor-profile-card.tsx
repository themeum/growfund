import { __, sprintf } from '@wordpress/i18n';
import { format } from 'date-fns';
import { Baby, BadgeCheck, Mail, MapPin, Phone } from 'lucide-react';

import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Box, BoxContent } from '@/components/ui/box';
import { type Donor } from '@/features/donors/schemas/donor';
import { DATE_FORMATS } from '@/lib/date';
import { createAcronym, displayAddress, isDefined } from '@/utils';

interface DonorProfileCardProps {
  donor: Donor;
}

const DonorProfileCard = ({ donor }: DonorProfileCardProps) => {
  const acronym = createAcronym({
    first_name: donor.first_name,
    last_name: donor.last_name,
  });
  return (
    <Box className="growfund-border-none">
      <BoxContent className="growfund-p-5 growfund-h-full growfund-flex growfund-flex-col">
        <div className="growfund-flex growfund-items-center growfund-gap-2">
          <Avatar className="growfund-size-10 growfund-rounded-full">
            <AvatarImage
              src={donor.image?.url}
              alt={sprintf('%s %s', donor.first_name, donor.last_name)}
            />
            <AvatarFallback>{acronym}</AvatarFallback>
          </Avatar>
          <div>
            <p className="growfund-typo-tiny growfund-text-fg-subdued">
              {/* translator: %s: Donor ID */}
              {sprintf(__('ID #%s', 'growfund'), donor.id)}
            </p>
            <p className="growfund-typo-small growfund-font-medium growfund-text-fg-primary growfund-break-all">
              {sprintf('%s %s', donor.first_name, donor.last_name)}
            </p>
          </div>
        </div>

        <div className="growfund-mt-5 growfund-space-y-4">
          <div className="growfund-flex growfund-items-start growfund-gap-2">
            <Mail className="growfund-size-4 growfund-text-icon-secondary growfund-flex-shrink-0" />
            <div className="growfund-grid growfund-gap-2">
              <span className="growfund-typo-small growfund-font-medium growfund-text-fg-primary">{donor.email}</span>
              {donor.is_verified ? (
                <Badge variant="primary">
                  <BadgeCheck className="growfund-size-3 growfund-text-icon-success" />
                  {__('Verified', 'growfund')}
                </Badge>
              ) : (
                <Badge variant="secondary">{__('Awaiting Verification', 'growfund')}</Badge>
              )}
            </div>
          </div>

          {isDefined(donor.phone) && (
            <div className="growfund-flex growfund-items-start growfund-gap-2">
              <Phone className="growfund-size-4 growfund-text-icon-secondary growfund-flex-shrink-0" />
              <span className="growfund-typo-small growfund-font-medium growfund-text-fg-primary">{donor.phone}</span>
            </div>
          )}

          {isDefined(donor.billing_address) && (
            <div className="growfund-flex growfund-items-start growfund-gap-2">
              <MapPin className="growfund-size-4 growfund-text-icon-secondary growfund-flex-shrink-0" />
              <div className="growfund-typo-small growfund-font-medium growfund-text-fg-primary growfund-space-y-2">
                <p className="growfund-text-fg-primary growfund-font-medium">
                  {displayAddress(donor.billing_address)}
                </p>
              </div>
            </div>
          )}
        </div>
        <div className="growfund-flex growfund-items-start growfund-gap-2 growfund-mt-auto">
          <Baby className="growfund-size-4 growfund-text-icon-secondary growfund-flex-shrink-0" />
          <p className="growfund-text-fg-subdued growfund-typo-tiny">
            {`${__('Joined', 'growfund')} ${format(donor.joined_at, DATE_FORMATS.HUMAN_READABLE)}`}
          </p>
        </div>
      </BoxContent>
    </Box>
  );
};

export default DonorProfileCard;

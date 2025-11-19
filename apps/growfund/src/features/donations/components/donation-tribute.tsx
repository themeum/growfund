import { __, sprintf } from '@wordpress/i18n';
import { Flower2, Mail, Mailbox, MapPin, Phone } from 'lucide-react';

import { Badge } from '@/components/ui/badge';
import { isDefined } from '@/utils';

interface Address {
  address?: string | null;
  address_2?: string | null;
  city?: string | null;
  state?: string | null;
  zip_code?: string | null;
  country?: string | null;
}

interface Donation {
  tribute_type?: string | null;
  tribute_salutation?: string | null;
  tribute_to?: string | null;
  tribute_notification_type?: 'send-post-mail' | 'send-ecard' | 'send-ecard-and-post-mail' | null;
  tribute_notification_recipient_name?: string | null;
  tribute_notification_recipient_email?: string | null;
  tribute_notification_recipient_phone?: string | null;
  tribute_notification_recipient_address?: Address | null;
}

const DonationTributeCard = ({ donation }: { donation: Donation }) => {
  const badges = donation.tribute_notification_type
    ? {
        'send-post-mail': [{ icon: Mailbox, label: __('Post Mail', 'growfund') }],
        'send-ecard': [{ icon: Mail, label: __('eCard', 'growfund') }],
        'send-ecard-and-post-mail': [
          { icon: Mailbox, label: __('Post Mail', 'growfund') },
          { icon: Mail, label: __('eCard', 'growfund') },
        ],
      }[donation.tribute_notification_type]
    : null;

  const fullAddress = [
    donation.tribute_notification_recipient_address?.address,
    donation.tribute_notification_recipient_address?.address_2,
    donation.tribute_notification_recipient_address?.city,
    donation.tribute_notification_recipient_address?.state,
    donation.tribute_notification_recipient_address?.zip_code,
    donation.tribute_notification_recipient_address?.country,
  ]
    .filter(isDefined)
    .join(', ');

  return (
    isDefined(donation.tribute_to) && (
      <div className="growfund-group/tribute">
        <div className="growfund-flex growfund-items-center growfund-gap-2 growfund-mb-3">
          <Flower2 className="growfund-size-5 growfund-text-icon-primary" />
          <h6 className="growfund-typo-h6 growfund-font-medium growfund-text-fg-primary">
            {__('Tribute', 'growfund')}
          </h6>
        </div>

        <div className="growfund-flex growfund-flex-col growfund-mb-2">
          <span className="growfund-typo-tiny growfund-text-fg-secondary">{donation.tribute_type}</span>
          <span className="growfund-font-medium growfund-text-fg-special-3 growfund-text-base">
            {sprintf('%s %s', donation.tribute_salutation, donation.tribute_to)}
          </span>
        </div>

        <div className="growfund-mb-2">
          <span className="growfund-typo-tiny growfund-text-fg-secondary">
            {__('Notification Receives', 'growfund')}
          </span>
          <p className="growfund-font-medium growfund-text-base growfund-text-fg-primary growfund-flex growfund-items-center growfund-gap-1">
            {donation.tribute_notification_recipient_name}
            <span className="growfund-typo-tiny growfund-text-fg-subdued growfund-pt-[6px]">
              {__('via', 'growfund')}
            </span>
          </p>
        </div>

        <div className="growfund-flex growfund-gap-2">
          {badges?.map(({ icon: Icon, label }) => (
            <Badge
              key={label}
              variant="secondary"
              className="growfund-w-full growfund-h-8 growfund-flex growfund-justify-center"
            >
              <Icon className="growfund-size-4 growfund-text-icon-primary growfund-flex-shrink-0" />
              {label}
            </Badge>
          ))}
        </div>

        <div className="growfund-flex growfund-flex-col growfund-gap-3 growfund-mt-5">
          {donation.tribute_notification_recipient_email && (
            <div className="growfund-flex growfund-gap-2">
              <Mail className="growfund-size-4 growfund-text-icon-secondary growfund-flex-shrink-0" />
              <div className="growfund-flex growfund-gap-2 growfund-flex-col">
                <span className="growfund-text-fg-secondary growfund-font-medium growfund-typo-small">
                  {donation.tribute_notification_recipient_email}
                </span>
              </div>
            </div>
          )}

          {donation.tribute_notification_recipient_phone && (
            <div className="growfund-flex growfund-gap-2 growfund-items-center">
              <Phone className="growfund-size-4 growfund-text-icon-secondary growfund-flex-shrink-0" />
              <span className="growfund-text-fg-secondary growfund-font-medium growfund-typo-small">
                {donation.tribute_notification_recipient_phone}
              </span>
            </div>
          )}

          {fullAddress && (
            <div className="growfund-flex growfund-gap-2">
              <MapPin className="growfund-size-4 growfund-text-icon-secondary growfund-flex-shrink-0" />
              <p className="growfund-text-fg-secondary growfund-font-medium growfund-typo-small">{fullAddress}</p>
            </div>
          )}
        </div>
      </div>
    )
  );
};

export default DonationTributeCard;

import { __, sprintf } from '@wordpress/i18n';
import { Edit3, Flower2, Mail, Mailbox, MapPin, Phone, Trash2 } from 'lucide-react';
import { useFormContext } from 'react-hook-form';

import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { type TributeFields } from '@/features/donations/schemas/donation';
import { type DonationForm } from '@/features/donations/schemas/donation-form';
import { displayAddress, isDefined } from '@/utils';
interface TributeCardProps {
  tribute?: TributeFields;
  onRemove: () => void;
  onEdit: () => void;
}

const TributeCard = ({ onRemove, onEdit }: TributeCardProps) => {
  const form = useFormContext<DonationForm>();
  const tribute = form.getValues();
  const badges = tribute.tribute_notification_type
    ? {
        'send-post-mail': [
          {
            icon: Mailbox,
            label: __('Post Mail', 'growfund'),
          },
        ],
        'send-ecard': [
          {
            icon: Mail,
            label: __('eCard', 'growfund'),
          },
        ],
        'send-ecard-and-post-mail': [
          {
            icon: Mailbox,
            label: __('Post Mail', 'growfund'),
          },
          {
            icon: Mail,
            label: __('eCard', 'growfund'),
          },
        ],
      }[tribute.tribute_notification_type]
    : null;

  const fullAddress = displayAddress(tribute.tribute_notification_recipient_address);

  return (
    <>
      {isDefined(tribute) && (
        <div className="growfund-group/tribute">
          <div className="growfund-flex growfund-items-center growfund-justify-between growfund-gap-2">
            <div className="growfund-flex growfund-items-center growfund-gap-2">
              <Flower2 className="growfund-size-5 growfund-text-icon-primary" />
              <h6 className="growfund-typo-h6 growfund-font-medium growfund-text-fg-primary">
                {__('Tribute', 'growfund')}
              </h6>
            </div>
            <div className="growfund-opacity-0 group-hover/tribute:growfund-opacity-100 growfund-transition-opacity">
              <Button
                variant="ghost"
                size="icon"
                onClick={() => {
                  onRemove();
                }}
                className="hover:growfund-text-icon-critical"
              >
                <Trash2 />
              </Button>
              <Button variant="ghost" size="icon" onClick={onEdit}>
                <Edit3 />
              </Button>
            </div>
          </div>
          <>
            <div className="growfund-flex growfund-flex-col growfund-mb-2">
              <span className="growfund-typo-tiny growfund-text-fg-secondary">{tribute.tribute_type}</span>
              <span className="growfund-font-medium growfund-text-fg-special-3 growfund-text-base">
                {sprintf('%s %s', tribute.tribute_salutation, tribute.tribute_to)}
              </span>
            </div>

            <div className="growfund-mb-2">
              <span className="growfund-typo-tiny growfund-text-fg-secondary">
                {__('Notification Receives', 'growfund')}
              </span>
              <p className="growfund-font-medium growfund-text-base growfund-text-fg-primary growfund-flex growfund-items-center growfund-gap-1">
                {tribute.tribute_notification_recipient_name}
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
              <div className="growfund-flex growfund-gap-2">
                <Mail className="growfund-size-4 growfund-text-icon-secondary growfund-flex-shrink-0" />
                <div className="growfund-flex growfund-gap-2 growfund-flex-col">
                  <span className="growfund-text-fg-secondary growfund-font-medium growfund-typo-small">
                    {tribute.tribute_notification_recipient_email}
                  </span>
                </div>
              </div>
              <div className="growfund-flex growfund-gap-2 growfund-items-center">
                <Phone className="growfund-size-4 growfund-text-icon-secondary growfund-flex-shrink-0" />
                <span className="growfund-text-fg-secondary growfund-font-medium growfund-typo-small">
                  {tribute.tribute_notification_recipient_phone}
                </span>
              </div>
              {fullAddress && (
                <div className="growfund-flex growfund-gap-2">
                  <MapPin className="growfund-size-4 growfund-text-icon-secondary growfund-flex-shrink-0" />
                  <p className="growfund-text-fg-secondary growfund-font-medium growfund-typo-small">{fullAddress}</p>
                </div>
              )}
            </div>
          </>
        </div>
      )}
    </>
  );
};

export default TributeCard;

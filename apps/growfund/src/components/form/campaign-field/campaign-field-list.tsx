import { __ } from '@wordpress/i18n';
import { CheckIcon } from 'lucide-react';

import { useCampaignFieldContext } from '@/components/form/campaign-field/campaign-field-context';
import { Image } from '@/components/ui/image';
import { cn } from '@/lib/utils';
import { noop } from '@/utils';

const CampaignFieldList = () => {
  const { campaigns, field, onOpenChange } = useCampaignFieldContext();

  if (campaigns.length === 0) {
    return (
      <div className="growfund-flex growfund-items-center growfund-justify-center growfund-h-24 growfund-text-fg-secondary growfund-typo-small">
        <p>{__('No campaigns found', 'growfund')}</p>
      </div>
    );
  }

  return (
    <div className="growfund-flex growfund-flex-col growfund-mt-3">
      {campaigns.map((campaign) => {
        return (
          <div
            key={campaign.id}
            role="button"
            tabIndex={0}
            onKeyDown={noop}
            onClick={() => {
              field.onChange(field.value === campaign.id ? null : campaign.id);
              onOpenChange(false);
            }}
            className={cn(
              'growfund-flex growfund-items-center growfund-cursor-pointer growfund-px-4 growfund-py-2 hover:growfund-bg-background-surface-secondary',
              field.value === campaign.id && 'growfund-text-fg-primary growfund-font-medium growfund-typo-small',
            )}
          >
            <div className="growfund-flex growfund-items-center growfund-gap-2 growfund-min-w-0">
              <Image
                src={campaign.images?.[0]?.url ?? null}
                alt={campaign.title}
                className="growfund-size-8 growfund-rounded-sm growfund-flex-shrink-0"
              />
              <span className="growfund-truncate growfund-typo-small growfund-w-full" title={campaign.title}>
                {campaign.title}
              </span>
            </div>

            <CheckIcon
              className={cn(
                'growfund-size-4 growfund-ml-auto growfund-shrink-0 growfund-transition-all',
                field.value === campaign.id ? 'growfund-opacity-100' : 'growfund-opacity-0',
              )}
            />
          </div>
        );
      })}
    </div>
  );
};

export default CampaignFieldList;

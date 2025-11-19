import { __ } from '@wordpress/i18n';
import { format } from 'date-fns';
import { CalendarIcon } from 'lucide-react';

import { Button } from '@/components/ui/button';
import { ProBadge } from '@/components/ui/pro-badge';
import { DATE_FORMATS } from '@/lib/date';
import { isDefined } from '@/utils';

const CampaignLaunchDateFallback = ({ defaultValue }: { defaultValue?: Date | null }) => {
  return (
    <div className="growfund-space-y-1 growfund-w-full">
      <div className="growfund-typo-small growfund-font-medium growfund-text-fg-primary growfund-flex growfund-items-center growfund-gap-1">
        {__('Launch Date', 'growfund')} <ProBadge />
      </div>
      <Button
        variant="outline"
        className="growfund-w-full growfund-justify-start growfund-text-left growfund-font-normal growfund-px-3 hover:growfund-bg-background-surface growfund-bg-background-white growfund-opacity-50"
      >
        <CalendarIcon />
        {isDefined(defaultValue)
          ? format(defaultValue, DATE_FORMATS.DATE_FIELD)
          : __('Pick a date', 'growfund')}
      </Button>
    </div>
  );
};

export default CampaignLaunchDateFallback;

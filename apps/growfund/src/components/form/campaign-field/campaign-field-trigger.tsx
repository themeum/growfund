import { Cross2Icon } from '@radix-ui/react-icons';
import { ChevronDownIcon } from 'lucide-react';

import { useCampaignFieldContext } from '@/components/form/campaign-field/campaign-field-context';
import { Button } from '@/components/ui/button';
import { PopoverTrigger } from '@/components/ui/popover';
import { cn } from '@/lib/utils';
import { isDefined } from '@/utils';

const CampaignFieldTrigger = ({ placeholder }: { placeholder?: string }) => {
  const { field, campaigns } = useCampaignFieldContext();
  const selectedCampaign = campaigns.find((campaign) => campaign.id === field.value);
  const label = isDefined(selectedCampaign) ? selectedCampaign.title : placeholder;

  return (
    <div className="growfund-flex growfund-items-center growfund-rounded-md focus-within:growfund-ring-2 focus-within:growfund-ring-ring focus-within:growfund-ring-offset-2">
      <PopoverTrigger asChild>
        <Button
          variant="outline"
          title={label}
          className={cn(
            'growfund-max-w-40 growfund-min-w-[12.5rem] growfund-justify-between growfund-pe-3 focus-visible:growfund-ring-0',
            isDefined(selectedCampaign) && 'growfund-rounded-none growfund-rounded-tl-lg growfund-rounded-bl-lg',
          )}
        >
          <span className="growfund-typo-small growfund-truncate">{label}</span>
          <ChevronDownIcon />
        </Button>
      </PopoverTrigger>

      {isDefined(selectedCampaign) && (
        <Button
          onClick={() => {
            field.onChange(null);
          }}
          variant="outline"
          className="growfund-rounded-none growfund-rounded-tr-lg growfund-rounded-br-lg growfund-border-l-0 focus-visible:growfund-ring-0"
          size="icon"
        >
          <Cross2Icon />
        </Button>
      )}
    </div>
  );
};

export default CampaignFieldTrigger;

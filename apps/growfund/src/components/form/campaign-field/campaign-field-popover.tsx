import { useCampaignFieldContext } from '@/components/form/campaign-field/campaign-field-context';
import CampaignFieldList from '@/components/form/campaign-field/campaign-field-list';
import CampaignFieldSearch from '@/components/form/campaign-field/campaign-field-search';
import CampaignFieldTrigger from '@/components/form/campaign-field/campaign-field-trigger';
import { Popover, PopoverContent } from '@/components/ui/popover';

const CampaignFieldPopover = ({ placeholder }: { placeholder?: string }) => {
  const { open, onOpenChange } = useCampaignFieldContext();
  return (
    <Popover open={open} onOpenChange={onOpenChange}>
      <CampaignFieldTrigger placeholder={placeholder} />
      <PopoverContent className="growfund-p-0 growfund-w-96" align="start">
        <div className="growfund-space-y-3">
          <CampaignFieldSearch />
          <CampaignFieldList />
        </div>
      </PopoverContent>
    </Popover>
  );
};

export default CampaignFieldPopover;

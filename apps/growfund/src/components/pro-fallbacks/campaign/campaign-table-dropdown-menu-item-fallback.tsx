import { ProBadge } from '@/components/ui/pro-badge';

const CampaignTableDropdownMenuItemFallback = ({ label }: { label: string }) => {
  return (
    <div className="growfund-relative growfund-flex growfund-cursor-default growfund-pointer-events-none growfund-select-none growfund-items-center growfund-gap-2 growfund-rounded-sm growfund-px-2 growfund-py-1.5 growfund-typo-small growfund-outline-none growfund-transition-colors">
      <span className="growfund-text-fg-disabled">{label}</span> <ProBadge />
    </div>
  );
};

export default CampaignTableDropdownMenuItemFallback;

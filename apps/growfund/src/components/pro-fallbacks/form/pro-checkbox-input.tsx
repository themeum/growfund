import { Checkbox } from '@/components/ui/checkbox';
import { ProBadge } from '@/components/ui/pro-badge';

const ProCheckboxInput = ({ label, showProBadge }: { label: string; showProBadge?: boolean }) => {
  return (
    <div className="growfund-w-full growfund-flex growfund-gap-2 growfund-items-center">
      <Checkbox disabled={true} checked={false} aria-readonly />
      <div className="growfund-flex growfund-items-center growfund-gap-2 ">
        <span className="growfund-text-fg-subdued growfund-typo-small growfund-font-medium growfund-min-h-4 growfund-flex-shrink-0">
          {label}
        </span>
        {showProBadge && <ProBadge />}
      </div>
    </div>
  );
};

export default ProCheckboxInput;

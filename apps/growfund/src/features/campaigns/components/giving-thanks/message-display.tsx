import { sprintf } from '@wordpress/i18n';
import { Edit, Trash2 } from 'lucide-react';

import { Button } from '@/components/ui/button';
import { type AppreciationMessageSchemaType } from '@/features/campaigns/schemas/campaign';
import { useCurrency } from '@/hooks/use-currency';

interface MessageDisplayProps {
  formatData: AppreciationMessageSchemaType;
  onEdit: () => void;
  onRemove?: () => void;
  disabled?: boolean;
}

export const MessageDisplay = ({
  formatData,
  onEdit,
  onRemove,
  disabled = false,
}: MessageDisplayProps) => {
  const { toCurrency } = useCurrency();
  const { pledge_from, pledge_to, appreciation_message } = formatData;
  return (
    <div className="growfund-relative growfund-flex growfund-gap-2 growfund-group">
      <div className="growfund-flex growfund-flex-col growfund-gap-2">
        <div className="growfund-flex growfund-justify-between growfund-items-center">
          <h6 className="growfund-typo-h6 growfund-text-fg-primary">
            {sprintf('%s-%s ', toCurrency(pledge_from), toCurrency(pledge_to))}
          </h6>
        </div>
        <p className="growfund-typo-tiny growfund-text-fg-secondary">{appreciation_message}</p>
      </div>

      <div className="growfund-absolute growfund-right-0 growfund-h-9 growfund-w-[64px] growfund-items-center growfund-justify-center growfund-border growfund-rounded-md growfund-hidden group-hover:growfund-flex">
        <Button
          variant="ghost"
          size="icon"
          className="growfund-size-8 growfund-cursor-pointer"
          onClick={onEdit}
          disabled={disabled}
        >
          <Edit />
        </Button>
        <Button
          variant="ghost"
          size="icon"
          className="growfund-size-8 growfund-cursor-pointer hover:growfund-text-icon-critical"
          onClick={onRemove}
          disabled={disabled}
        >
          <Trash2 />
        </Button>
      </div>
    </div>
  );
};

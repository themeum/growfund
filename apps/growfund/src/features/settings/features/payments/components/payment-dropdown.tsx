import { __ } from '@wordpress/i18n';
import { Plus } from 'lucide-react';

import { Button } from '@/components/ui/button';
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { ProBadge } from '@/components/ui/pro-badge';
import { growfundConfig } from '@/config/growfund';

type DropdownAction = 'new-gateway' | 'manual-payment';

interface DropDownProps {
  onActionChange: (action: DropdownAction) => void;
  disabled?: boolean;
  open: boolean;
  onOpenChange: (open: boolean) => void;
}

const PaymentOptions = ({ onActionChange, disabled, open, onOpenChange }: DropDownProps) => {
  const handleAction = (action: DropdownAction, event: Event) => {
    event.preventDefault();
    onActionChange(action);
  };
  return (
    <DropdownMenu open={open} onOpenChange={onOpenChange}>
      <DropdownMenuTrigger asChild>
        <Button size="icon" variant="secondary" disabled={disabled}>
          <Plus />
        </Button>
      </DropdownMenuTrigger>
      <DropdownMenuContent align="end">
        <DropdownMenuItem
          disabled={!growfundConfig.is_pro}
          onSelect={(event) => {
            if (growfundConfig.is_pro) {
              handleAction('new-gateway', event);
            }
          }}
        >
          {__('New Gateway', 'growfund')} {!growfundConfig.is_pro && <ProBadge />}
        </DropdownMenuItem>
        <DropdownMenuItem
          onSelect={(event) => {
            handleAction('manual-payment', event);
          }}
        >
          {__('Manual Payment', 'growfund')}
        </DropdownMenuItem>
      </DropdownMenuContent>
    </DropdownMenu>
  );
};

export default PaymentOptions;

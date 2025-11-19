import { __ } from '@wordpress/i18n';
import { Crown } from 'lucide-react';

import { Button } from '@/components/ui/button';

const ProButton = () => {
  return (
    <Button
      className="growfund-rounded-full growfund-bg-gradient-to-r growfund-from-[#FFF9BF] growfund-to-[#FFB413] growfund-border growfund-border-[#FFBC07] growfund-bg-black growfund-text-[#FFC105] hover:growfund-bg-opacity-75"
      onClick={() => {
        window.location.href = 'https://growfund.com/pricing';
      }}
    >
      <Crown size={16} />
      {__('Upgrade to Pro', 'growfund')}
    </Button>
  );
};

export { ProButton };

import { Avatar, AvatarImage } from '@/components/ui/avatar';
import { cn } from '@/lib/utils';

interface DonorInfoProps {
  currentDonor: {
    id: string;
    name: string;
    email: string;
    phone?: string;
    billing_address?: string;
    payment_method?: string;
    account_number?: string;
    verification_status?: string;
    image?: string;
  };
  wrapperClass?: string;
}

const DonorInfo = ({ currentDonor, wrapperClass }: DonorInfoProps) => {
  return (
    <div className={cn('growfund-flex growfund-gap-3 growfund-items-center', wrapperClass)}>
      <Avatar>
        <AvatarImage src={currentDonor.image} />
      </Avatar>
      <div className="growfund-typo-tiny growfund-flex growfund-flex-col growfund-gap-1">
        <div className="growfund-text-fg-primary growfund-font-medium growfund-max-w-96">{currentDonor.name}</div>
        <div className="growfund-text-fg-secondary growfund-max-w-96 growfund-truncate" title={currentDonor.email}>
          {currentDonor.email}
        </div>
      </div>
    </div>
  );
};

export default DonorInfo;

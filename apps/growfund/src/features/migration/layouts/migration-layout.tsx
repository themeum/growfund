import { BrandWhiteIcon, BridgeIcon } from '@/app/icons';
import { Image } from '@/components/ui/image';
import StepNavigator from '@/features/migration/components/step-navigator';
import { useMigration } from '@/features/migration/contexts/migration-context';

const MigrationLayout = () => {
  const { step } = useMigration();
  return (
    <div className="growfund-w-[100vw] growfund-h-[100svh] growfund-flex growfund-items-center growfund-justify-center growfund-bg-[#406B52] growfund-relative">
      <div className="growfund-flex growfund-flex-col growfund-gap-5 growfund-items-start">
        <BrandWhiteIcon className="growfund-h-6 growfund-ms-10" />
        <div className="growfund-relative growfund-h-full growfund-flex growfund-items-center growfund-justify-center growfund-w-[min(100vw,58.75rem)] growfund-mx-10">
          <div className="growfund-grid growfund-grid-cols-[42.5532%_1fr] growfund-gap-[2.625rem] growfund-min-h-[min(28.25rem,90svh)] growfund-w-full">
            <StepNavigator />
          </div>
          <div className="growfund-absolute growfund-top-[50%] growfund-left-[42.5532%] growfund-translate-y-[-50%]">
            <BridgeIcon />
          </div>
        </div>
      </div>
      {step === 'successful' && (
        <div className="growfund-absolute growfund-left-44 growfund-bottom-0">
          <Image src="/images/party.webp" className="growfund-size-96 growfund-border-none growfund-bg-transparent" />
        </div>
      )}
    </div>
  );
};

export default MigrationLayout;

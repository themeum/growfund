import { ProButton } from '@/components/ui/pro-button';
import { cn } from '@/lib/utils';

const ProBanner = ({ title, description, className }: { title: string; description?: string, className?: string }) => {
  return (
    <div
      className={cn(
        'growfund-bg-background-surface-alt growfund-w-full growfund-max-w-[34rem] growfund-flex growfund-flex-col growfund-items-center growfund-justify-center growfund-gap-6 growfund-rounded-lg growfund-p-8 growfund-shadow-sm',
        className,
      )}
    >
      <div className="growfund-flex growfund-flex-col growfund-gap-2">
        <h4 className="growfund-typo-h4 growfund-text-fg-primary growfund-text-center">{title}</h4>
        {description && (
          <div className="growfund-typo-small growfund-text-fg-secondary growfund-text-center">{description}</div>
        )}
      </div>
      <ProButton />
    </div>
  );
};

export default ProBanner;

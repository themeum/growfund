import { Box, BoxContent } from '@/components/ui/box';
import { Skeleton } from '@/components/ui/skeleton';

const CampaignCardSkeleton = () => {
  return (
    <Box className="growfund-shadow-none growfund-border-none">
      <BoxContent className="growfund-grid growfund-grid-cols-[5.5rem_auto] growfund-gap-6">
        <Skeleton className="growfund-size-[6.25rem] growfund-rounded-sm" animate />
        <div className="growfund-space-y-3 growfund-mt-3">
          <Skeleton className="growfund-h-3 growfund-w-[60%]" animate />
          <div className="growfund-flex growfund-gap-2 growfund-items-center">
            <Skeleton className="growfund-h-3 growfund-w-[20%]" animate />
            <Skeleton className="growfund-h-3 growfund-w-[30%]" animate />
          </div>
          <Skeleton className="growfund-h-3 growfund-w-[80%]" animate />
          <Skeleton className="growfund-h-3 growfund-w-[70%]" animate />
        </div>
      </BoxContent>
    </Box>
  );
};

export default CampaignCardSkeleton;

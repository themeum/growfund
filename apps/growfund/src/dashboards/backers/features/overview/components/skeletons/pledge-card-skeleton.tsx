import { Box, BoxContent } from '@/components/ui/box';
import { Skeleton } from '@/components/ui/skeleton';

const PledgeCardSkeleton = () => {
  return (
    <Box className="growfund-shadow-none growfund-border-none">
      <BoxContent className="growfund-grid growfund-grid-cols-[5.5rem_auto_4rem] growfund-gap-4">
        <Skeleton className="growfund-h-[5.5rem] growfund-w-[5.5rem] growfund-rounded-sm" animate />
        <div className="growfund-space-y-3 growfund-mt-3">
          <div className="growfund-flex growfund-gap-2 growfund-items-center">
            <Skeleton className="growfund-h-3 growfund-w-[4rem]" animate />
            <Skeleton className="growfund-h-3 growfund-w-[4rem]" animate />
          </div>
          <Skeleton className="growfund-h-3 growfund-w-[7rem]" animate />
          <div className="growfund-flex growfund-gap-2 growfund-items-center">
            <Skeleton className="growfund-h-3 growfund-w-[12rem]" animate />
            <Skeleton className="growfund-h-3 growfund-w-[16rem]" animate />
          </div>
        </div>
        <div className="growfund-flex growfund-flex-col growfund-justify-between">
          <Skeleton className="growfund-size-5 growfund-rounded-sm growfund-self-end" animate />
          <Skeleton className="growfund-h-2 growfund-w-10 growfund-self-end" animate />
        </div>
      </BoxContent>
    </Box>
  );
};

export default PledgeCardSkeleton;

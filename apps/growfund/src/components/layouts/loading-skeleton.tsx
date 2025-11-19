import React from 'react';

import { Skeleton } from '@/components/ui/skeleton';
import { cn } from '@/lib/utils';

interface LoadingSkeletonProps extends React.HTMLAttributes<HTMLDivElement> {
  loading?: boolean;
  showAvatarSkeleton?: boolean;
  avatarClassName?: string;
  skeleton?: React.ReactNode;
  skeletonClassName?: string;
}

const LoadingSkeletonCard = React.forwardRef<
  HTMLDivElement,
  Omit<LoadingSkeletonProps, 'skeleton'>
>(
  (
    {
      className,
      children,
      loading,
      showAvatarSkeleton,
      skeletonClassName,
      avatarClassName,
      ...props
    },
    ref,
  ) => {
    if (!loading) {
      return children;
    }

    return (
      <div
        ref={ref}
        className={cn('growfund-flex growfund-items-center growfund-gap-3 growfund-p-3', className)}
        {...props}
      >
        {showAvatarSkeleton && (
          <Skeleton animate className={cn('growfund-h-14 growfund-w-14 growfund-rounded-xl', avatarClassName)} />
        )}
        <div className="growfund-space-y-2">
          <Skeleton animate className={cn('growfund-h-2 growfund-w-72', skeletonClassName)} />
          <Skeleton animate className={cn('growfund-h-2 growfund-w-72', skeletonClassName)} />
          <div
            className={cn('growfund-flex growfund-items-center growfund-justify-center growfund-gap-2', skeletonClassName)}
          >
            <Skeleton animate className="growfund-h-2 growfund-w-[8.2rem]" />
            <Skeleton animate className="growfund-h-2 growfund-w-2 growfund-rounded-full" />
            <Skeleton animate className="growfund-h-2 growfund-w-[8.2rem]" />
          </div>
        </div>
      </div>
    );
  },
);

const LoadingSkeletonJustifyBetween = React.forwardRef<
  HTMLDivElement,
  Omit<LoadingSkeletonProps, 'skeleton'>
>(
  (
    {
      className,
      children,
      loading,
      showAvatarSkeleton,
      avatarClassName,
      skeletonClassName,
      ...props
    },
    ref,
  ) => {
    if (!loading) {
      return children;
    }

    return (
      <div
        ref={ref}
        className={cn('growfund-flex growfund-items-center growfund-gap-3 growfund-p-3', className)}
        {...props}
      >
        {showAvatarSkeleton && (
          <Skeleton animate className={cn('growfund-h-5 growfund-w-5 growfund-rounded-full', avatarClassName)} />
        )}
        <div className="growfund-w-full growfund-gap-8 growfund-flex growfund-items-center growfund-justify-between">
          <Skeleton animate className={cn('growfund-h-2 growfund-w-[4.25rem]', skeletonClassName)} />
          <Skeleton animate className={cn('growfund-h-2 growfund-w-[4.25rem]', skeletonClassName)} />
        </div>
      </div>
    );
  },
);

const LoadingSkeleton = React.forwardRef<HTMLDivElement, LoadingSkeletonProps>(
  (
    {
      className,
      children,
      loading,
      showAvatarSkeleton,
      avatarClassName,
      skeleton,
      skeletonClassName,
      ...props
    },
    ref,
  ) => {
    if (!loading) {
      return children;
    }

    skeleton = skeleton ?? (
      <Skeleton animate className={cn('growfund-h-2 growfund-w-[4.25rem]', skeletonClassName)} />
    );

    return (
      <div
        ref={ref}
        className={cn('growfund-flex growfund-items-start growfund-gap-[0.375rem] growfund-p-3', className)}
        {...props}
      >
        {showAvatarSkeleton && (
          <Skeleton animate className={cn('growfund-h-5 growfund-w-5 growfund-rounded-full', avatarClassName)} />
        )}
        <div className="growfund-space-y-2 growfund-w-full">{skeleton}</div>
      </div>
    );
  },
);

LoadingSkeleton.displayName = 'LoadingSkeleton';
LoadingSkeletonCard.displayName = 'LoadingSkeletonCard';
LoadingSkeletonJustifyBetween.displayName = 'LoadingSkeletonJustifyBetween';

export { LoadingSkeleton, LoadingSkeletonCard, LoadingSkeletonJustifyBetween };

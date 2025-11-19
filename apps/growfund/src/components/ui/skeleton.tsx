import { cn } from '@/lib/utils';

function Skeleton({
  className,
  animate = false,
  ...props
}: React.HTMLAttributes<HTMLDivElement> & {
  animate?: boolean;
}) {
  return (
    <div
      className={cn(
        ' growfund-rounded-full growfund-bg-background-surface-subdued',
        animate && 'growfund-animate-pulse',
        className,
      )}
      {...props}
    />
  );
}

export { Skeleton };

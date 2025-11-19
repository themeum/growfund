import { cn } from '@/lib/utils';

interface CircularProgressProps {
  value: number;
  size?: number;
  strokeWidth?: number;
  showLabel?: boolean;
  backgroundStrokeClass?: string;
  progressStrokeClass?: string;
}

export function CircularProgress({
  value,
  size = 20,
  strokeWidth = 1.5,
  showLabel = false,
  backgroundStrokeClass,
  progressStrokeClass,
}: CircularProgressProps) {
  const radius = (size - strokeWidth) / 2;
  const circumference = 2 * Math.PI * radius;
  const offset = circumference - (value / 100) * circumference;

  return (
    <div
      className="growfund-relative growfund-flex growfund-items-center growfund-justify-center"
      style={{ width: size, height: size }}
    >
      <svg width={size} height={size} className="growfund-absolute growfund-rotate-[-90deg]">
        <circle
          className={cn('growfund-stroke-fg-disabled', backgroundStrokeClass)}
          fill="transparent"
          strokeWidth={strokeWidth}
          r={radius}
          cx={size / 2}
          cy={size / 2}
        />
        <circle
          className={cn(
            'growfund-transition-[stroke-dashoffset] growfund-duration-500 growfund-ease-in-out growfund-stroke-fg-success',
            progressStrokeClass,
          )}
          fill="transparent"
          strokeWidth={strokeWidth}
          strokeDasharray={circumference}
          strokeDashoffset={offset}
          strokeLinecap="round"
          r={radius}
          cx={size / 2}
          cy={size / 2}
        />
      </svg>

      {showLabel && <span className="growfund-absolute growfund-typo-tiny">{Math.round(value)}%</span>}
    </div>
  );
}

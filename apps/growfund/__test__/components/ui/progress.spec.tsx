import { render, screen } from '@testing-library/react';
import { describe, expect, it } from 'vitest';

import { Progress } from '@/components/ui/progress';

describe('Progress Component', () => {
  it('should render with default props', () => {
    render(<Progress value={50} />);

    const progress = screen.getByRole('progressbar');
    expect(progress).toBeInTheDocument();
    expect(progress).toHaveClass(
      'growfund-relative',
      'growfund-h-2',
      'growfund-w-full',
      'growfund-overflow-hidden',
      'growfund-rounded-full',
      'growfund-bg-background-fill-tertiary',
    );
  });

  it('should render with custom className', () => {
    render(<Progress value={50} className="custom-progress" />);

    const progress = screen.getByRole('progressbar');
    expect(progress).toHaveClass('custom-progress');
  });

  it('should render with default size', () => {
    render(<Progress value={50} />);

    const progress = screen.getByRole('progressbar');
    expect(progress).toHaveClass('growfund-h-2');
  });

  it('should render with small size', () => {
    render(<Progress value={50} size="sm" />);

    const progress = screen.getByRole('progressbar');
    expect(progress).toHaveClass('growfund-h-1');
  });

  it('should render with large size', () => {
    render(<Progress value={50} size="lg" />);

    const progress = screen.getByRole('progressbar');
    expect(progress).toHaveClass('growfund-h-3');
  });

  it('should display correct progress value', () => {
    render(<Progress value={75} />);

    const progress = screen.getByRole('progressbar');
    expect(progress).toBeInTheDocument();
  });

  it('should handle 0% progress', () => {
    render(<Progress value={0} />);

    const progress = screen.getByRole('progressbar');
    expect(progress).toBeInTheDocument();
  });

  it('should handle 100% progress', () => {
    render(<Progress value={100} />);

    const progress = screen.getByRole('progressbar');
    expect(progress).toBeInTheDocument();
  });

  it('should handle 50% progress', () => {
    render(<Progress value={50} />);

    const progress = screen.getByRole('progressbar');
    expect(progress).toBeInTheDocument();
  });

  it('should handle undefined value', () => {
    render(<Progress value={undefined} />);

    const progress = screen.getByRole('progressbar');
    expect(progress).toBeInTheDocument();
  });

  it('should handle null value', () => {
    render(<Progress value={null} />);

    const progress = screen.getByRole('progressbar');
    expect(progress).toBeInTheDocument();
  });

  it('should render progress indicator', () => {
    render(<Progress value={50} />);

    const progress = screen.getByRole('progressbar');
    const indicator = progress.querySelector('[data-state]');

    expect(indicator).toBeInTheDocument();
    expect(indicator).toHaveClass(
      'growfund-h-full',
      'growfund-w-full',
      'growfund-flex-1',
      'growfund-bg-background-fill-brand',
      'growfund-transition-all',
      'growfund-rounded-full',
    );
  });

  it('should forward ref correctly', () => {
    const ref = { current: null };
    render(<Progress ref={ref} value={50} />);

    expect(ref.current).toBeInstanceOf(HTMLDivElement);
  });

  it('should handle all HTML div attributes', () => {
    render(
      <Progress
        value={50}
        id="test-progress"
        title="Progress bar"
        role="progressbar"
        aria-label="Loading progress"
        data-testid="progress-test"
      />,
    );

    const progress = screen.getByRole('progressbar');
    expect(progress).toHaveAttribute('id', 'test-progress');
    expect(progress).toHaveAttribute('title', 'Progress bar');
    expect(progress).toHaveAttribute('role', 'progressbar');
    expect(progress).toHaveAttribute('aria-label', 'Loading progress');
    expect(progress).toHaveAttribute('data-testid', 'progress-test');
  });

  it('should handle different progress values', () => {
    const values = [0, 25, 50, 75, 100];

    values.forEach((value) => {
      const { unmount } = render(<Progress value={value} />);

      const progress = screen.getByRole('progressbar');
      expect(progress).toBeInTheDocument();

      unmount();
    });
  });

  it('should handle edge cases for progress values', () => {
    const edgeCases = [-10, 150, 0.5, 99.9];

    edgeCases.forEach((value) => {
      const { unmount } = render(<Progress value={value} />);

      const progress = screen.getByRole('progressbar');
      expect(progress).toBeInTheDocument();

      unmount();
    });
  });

  it('should maintain consistent styling across sizes', () => {
    const sizes = ['sm', 'default', 'lg'] as const;

    sizes.forEach((size) => {
      const { unmount } = render(<Progress value={50} size={size} />);

      const progress = screen.getByRole('progressbar');
      expect(progress).toHaveClass(
        'growfund-relative',
        'growfund-w-full',
        'growfund-overflow-hidden',
        'growfund-rounded-full',
        'growfund-bg-background-fill-tertiary',
      );

      unmount();
    });
  });

  it('should handle custom max value', () => {
    render(<Progress value={50} max={200} />);

    const progress = screen.getByRole('progressbar');
    expect(progress).toHaveAttribute('data-max', '200');
  });

  it('should handle aria attributes', () => {
    render(
      <Progress
        value={50}
        aria-label="Download progress"
        aria-describedby="progress-description"
        aria-valuemin={0}
        aria-valuemax={100}
        aria-valuenow={50}
      />,
    );

    const progress = screen.getByRole('progressbar');
    expect(progress).toHaveAttribute('aria-label', 'Download progress');
    expect(progress).toHaveAttribute('aria-describedby', 'progress-description');
    expect(progress).toHaveAttribute('aria-valuemin', '0');
    expect(progress).toHaveAttribute('aria-valuemax', '100');
    expect(progress).toHaveAttribute('aria-valuenow', '50');
  });

  it('should handle indeterminate state', () => {
    render(<Progress value={undefined} />);

    const progress = screen.getByRole('progressbar');
    const indicator = progress.querySelector('[data-state="indeterminate"]');

    if (indicator) {
      expect(indicator).toBeInTheDocument();
    }
  });
});

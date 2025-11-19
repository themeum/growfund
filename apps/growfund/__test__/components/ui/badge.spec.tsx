import { fireEvent, render, screen } from '@testing-library/react';
import { describe, expect, it, vi } from 'vitest';

import { Badge } from '@/components/ui/badge';

describe('Badge Component', () => {
  it('should render with default variant (primary)', () => {
    render(<Badge>Default Badge</Badge>);

    const badge = screen.getByText('Default Badge');
    expect(badge).toBeInTheDocument();
    expect(badge).toHaveClass(
      'growfund-inline-flex',
      'growfund-gap-1',
      'growfund-items-center',
      'growfund-rounded-md',
      'growfund-border',
      'growfund-px-2',
      'growfund-py-1',
      'growfund-typo-tiny',
      'growfund-transition-colors',
    );
    expect(badge).toHaveClass(
      'growfund-border-transparent',
      'growfund-bg-background-fill-success-secondary',
      'growfund-text-fg-success',
    );
  });

  it('should render with custom className', () => {
    render(<Badge className="custom-badge">Custom Badge</Badge>);

    const badge = screen.getByText('Custom Badge');
    expect(badge).toHaveClass('custom-badge');
  });

  it('should render with secondary variant', () => {
    render(<Badge variant="secondary">Secondary Badge</Badge>);

    const badge = screen.getByText('Secondary Badge');
    expect(badge).toHaveClass('growfund-border-transparent', 'growfund-bg-border', 'growfund-text-fg-primary');
  });

  it('should render with destructive variant', () => {
    render(<Badge variant="destructive">Destructive Badge</Badge>);

    const badge = screen.getByText('Destructive Badge');
    expect(badge).toHaveClass(
      'growfund-border-transparent',
      'growfund-bg-background-fill-critical-secondary',
      'growfund-text-fg-critical',
    );
  });

  it('should render with warning variant', () => {
    render(<Badge variant="warning">Warning Badge</Badge>);

    const badge = screen.getByText('Warning Badge');
    expect(badge).toHaveClass(
      'growfund-border-transparent',
      'growfund-bg-background-fill-warning-secondary',
      'growfund-text-fg-warning',
    );
  });

  it('should render with info variant', () => {
    render(<Badge variant="info">Info Badge</Badge>);

    const badge = screen.getByText('Info Badge');
    expect(badge).toHaveClass(
      'growfund-border-transparent',
      'growfund-bg-background-fill-special-secondary',
      'growfund-text-fg-special-2',
    );
  });

  it('should render with special variant', () => {
    render(<Badge variant="special">Special Badge</Badge>);

    const badge = screen.getByText('Special Badge');
    expect(badge).toHaveClass(
      'growfund-border-transparent',
      'growfund-bg-background-fill-special-2-secondary',
      'growfund-text-fg-special-3',
    );
  });

  it('should render with outline variant', () => {
    render(<Badge variant="outline">Outline Badge</Badge>);

    const badge = screen.getByText('Outline Badge');
    expect(badge).toHaveClass('growfund-text-fg-primary');
  });

  it('should render with ghost variant', () => {
    render(<Badge variant="ghost">Ghost Badge</Badge>);

    const badge = screen.getByText('Ghost Badge');
    expect(badge).toHaveClass('growfund-border-none', 'growfund-bg-transparent', 'growfund-text-fg-primary');
  });

  it('should forward ref correctly', () => {
    const ref = { current: null };
    render(<Badge ref={ref}>Ref Badge</Badge>);

    expect(ref.current).toBeInstanceOf(HTMLDivElement);
  });

  it('should handle click events', () => {
    const handleClick = vi.fn();
    render(<Badge onClick={handleClick}>Clickable Badge</Badge>);

    const badge = screen.getByText('Clickable Badge');
    fireEvent.click(badge);

    expect(handleClick).toHaveBeenCalled();
  });

  it('should handle hover states', () => {
    render(<Badge variant="primary">Hover Badge</Badge>);

    const badge = screen.getByText('Hover Badge');
    expect(badge).toHaveClass('hover:growfund-bg-background-fill-success-secondary/80');
  });

  it('should handle focus states', () => {
    render(<Badge variant="primary">Focus Badge</Badge>);

    const badge = screen.getByText('Focus Badge');
    expect(badge).toHaveClass(
      'focus:growfund-outline-none',
      'focus:growfund-ring-2',
      'focus:growfund-ring-ring',
      'focus:growfund-ring-offset-2',
    );
  });

  it('should render with children content', () => {
    render(
      <Badge>
        <span>Icon</span>
        Badge Text
      </Badge>,
    );

    expect(screen.getByText('Icon')).toBeInTheDocument();
    expect(screen.getByText('Badge Text')).toBeInTheDocument();
  });

  it('should render with different content types', () => {
    render(
      <Badge>
        <strong>Bold</strong> and <em>italic</em> text
      </Badge>,
    );

    expect(screen.getByText('Bold')).toBeInTheDocument();
    expect(screen.getByText('italic')).toBeInTheDocument();
  });

  it('should maintain consistent styling across variants', () => {
    const variants = [
      'primary',
      'secondary',
      'destructive',
      'warning',
      'info',
      'special',
      'outline',
      'ghost',
    ] as const;

    variants.forEach((variant) => {
      const { unmount } = render(<Badge variant={variant}>Test Badge</Badge>);

      const badge = screen.getByText('Test Badge');
      expect(badge).toHaveClass('growfund-inline-flex', 'growfund-gap-1', 'growfund-items-center', 'growfund-rounded-md');

      unmount();
    });
  });

  it('should handle all HTML div attributes', () => {
    render(
      <Badge
        id="test-badge"
        title="Test badge title"
        role="status"
        aria-label="Test badge"
        data-testid="badge-test"
      >
        Test Badge
      </Badge>,
    );

    const badge = screen.getByText('Test Badge');
    expect(badge).toHaveAttribute('id', 'test-badge');
    expect(badge).toHaveAttribute('title', 'Test badge title');
    expect(badge).toHaveAttribute('role', 'status');
    expect(badge).toHaveAttribute('aria-label', 'Test badge');
    expect(badge).toHaveAttribute('data-testid', 'badge-test');
  });

  it('should handle keyboard events', () => {
    const handleKeyDown = vi.fn();
    render(<Badge onKeyDown={handleKeyDown}>Keyboard Badge</Badge>);

    const badge = screen.getByText('Keyboard Badge');
    fireEvent.keyDown(badge, { key: 'Enter' });

    expect(handleKeyDown).toHaveBeenCalled();
  });
});

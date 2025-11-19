import { render, screen } from '@testing-library/react';
import { Plus } from 'lucide-react';
import { describe, expect, it } from 'vitest';

import { Button } from '@/components/ui/button';

describe('Button Component', () => {
  it('should render a button with custom className', () => {
    render(<Button className="custom-class">Click me</Button>);
    const button = screen.getByRole('button');
    expect(button).toHaveClass('custom-class');
  });

  it('should render a disabled button', () => {
    render(<Button disabled>Click me</Button>);
    const button = screen.getByRole('button');
    expect(button).toBeDisabled();
  });

  it('should render a button with variant', () => {
    render(<Button variant="primary">Click me</Button>);
    const button = screen.getByRole('button');
    expect(button).toHaveClass('growfund-bg-background-fill-brand-var');
  });

  it('should render a secondary button', () => {
    render(<Button variant="secondary">Click me</Button>);
    const button = screen.getByRole('button');
    expect(button).toHaveClass('growfund-bg-background-fill-secondary');
  });

  it('should render a destructive button', () => {
    render(<Button variant="destructive">Click me</Button>);
    const button = screen.getByRole('button');
    expect(button).toHaveClass('growfund-bg-background-fill-critical');
  });

  it('should render an outline button', () => {
    render(<Button variant="outline">Click me</Button>);
    const button = screen.getByRole('button');
    expect(button).toHaveClass('growfund-border');
    expect(button).toHaveClass('growfund-border-border');
  });

  it('should render a button with size', () => {
    render(<Button size="sm">Click me</Button>);
    const button = screen.getByRole('button');
    expect(button).toHaveClass('growfund-typo-tiny');
  });

  it('should render a button with an icon', () => {
    render(
      <Button size="icon">
        <Plus />
        Click me
      </Button>,
    );
    const button = screen.getByRole('button');
    const icon = button.querySelector('svg');
    expect(icon).toBeInTheDocument();
  });
});

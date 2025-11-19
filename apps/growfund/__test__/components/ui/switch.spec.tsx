import { fireEvent, render, screen } from '@testing-library/react';
import * as React from 'react';
import { describe, expect, it, vi } from 'vitest';

import { Switch } from '@/components/ui/switch';

describe('Switch Component', () => {
  it('should render with default props', () => {
    render(<Switch />);

    const switchElement = screen.getByRole('switch');
    expect(switchElement).toBeInTheDocument();
    expect(switchElement).toHaveClass(
      'peer',
      'growfund-inline-flex',
      'growfund-h-5',
      'growfund-w-9',
      'growfund-shrink-0',
      'growfund-cursor-pointer',
      'growfund-items-center',
      'growfund-rounded-full',
      'growfund-border-2',
      'growfund-border-transparent',
      'growfund-transition-colors',
    );
  });

  it('should render as checked when checked prop is true', () => {
    render(<Switch checked={true} />);

    const switchElement = screen.getByRole('switch');
    expect(switchElement).toBeChecked();
    expect(switchElement).toHaveAttribute('data-state', 'checked');
  });

  it('should render as unchecked when checked prop is false', () => {
    render(<Switch checked={false} />);

    const switchElement = screen.getByRole('switch');
    expect(switchElement).not.toBeChecked();
    expect(switchElement).toHaveAttribute('data-state', 'unchecked');
  });

  it('should render with custom className', () => {
    render(<Switch className="custom-switch" />);

    const switchElement = screen.getByRole('switch');
    expect(switchElement).toHaveClass('custom-switch');
  });

  it('should render as disabled when disabled prop is true', () => {
    render(<Switch disabled={true} />);

    const switchElement = screen.getByRole('switch');
    expect(switchElement).toBeDisabled();
    expect(switchElement).toHaveClass('disabled:growfund-cursor-not-allowed', 'disabled:growfund-opacity-50');
  });

  it('should handle click events', () => {
    const handleClick = vi.fn();
    render(<Switch onClick={handleClick} />);

    const switchElement = screen.getByRole('switch');
    fireEvent.click(switchElement);

    expect(handleClick).toHaveBeenCalled();
  });

  it('should handle change events', () => {
    const handleCheckedChange = vi.fn();
    render(<Switch onCheckedChange={handleCheckedChange} />);

    const switchElement = screen.getByRole('switch');
    fireEvent.click(switchElement);

    expect(handleCheckedChange).toHaveBeenCalled();
  });

  it('should handle focus and blur events', () => {
    const handleFocus = vi.fn();
    const handleBlur = vi.fn();

    render(<Switch onFocus={handleFocus} onBlur={handleBlur} />);

    const switchElement = screen.getByRole('switch');

    fireEvent.focus(switchElement);
    expect(handleFocus).toHaveBeenCalled();

    fireEvent.blur(switchElement);
    expect(handleBlur).toHaveBeenCalled();
  });

  it('should handle keyboard events', () => {
    const handleKeyDown = vi.fn();
    render(<Switch onKeyDown={handleKeyDown} />);

    const switchElement = screen.getByRole('switch');
    fireEvent.keyDown(switchElement, { key: 'Enter' });

    expect(handleKeyDown).toHaveBeenCalled();
  });

  it('should forward ref correctly', () => {
    const ref = { current: null };
    render(<Switch ref={ref} />);

    expect(ref.current).toBeInstanceOf(HTMLButtonElement);
  });

  it('should handle controlled state changes', () => {
    const TestComponent = () => {
      const [checked, setChecked] = React.useState(false);

      return <Switch checked={checked} onCheckedChange={setChecked} />;
    };

    render(<TestComponent />);

    const switchElement = screen.getByRole('switch');
    expect(switchElement).not.toBeChecked();

    fireEvent.click(switchElement);
    expect(switchElement).toBeChecked();
  });

  it('should handle value prop', () => {
    render(<Switch value="test-value" />);

    const switchElement = screen.getByRole('switch');
    expect(switchElement).toHaveAttribute('value', 'test-value');
  });

  it('should handle name prop', () => {
    render(<Switch name="test-switch" />);

    const switchElement = screen.getByRole('switch');
    expect(switchElement).toBeInTheDocument();
  });

  it('should handle required prop', () => {
    render(<Switch required />);

    const switchElement = screen.getByRole('switch');
    expect(switchElement).toBeInTheDocument();
  });

  it('should have proper focus styles', () => {
    render(<Switch />);

    const switchElement = screen.getByRole('switch');
    expect(switchElement).toHaveClass(
      'focus-visible:growfund-outline-none',
      'focus-visible:growfund-ring-2',
      'focus-visible:growfund-ring-ring',
      'focus-visible:growfund-ring-offset-2',
    );
  });

  it('should have proper checked state styles', () => {
    render(<Switch checked={true} />);

    const switchElement = screen.getByRole('switch');
    expect(switchElement).toHaveClass('data-[state=checked]:growfund-bg-background-fill-brand');
  });

  it('should have proper unchecked state styles', () => {
    render(<Switch checked={false} />);

    const switchElement = screen.getByRole('switch');
    expect(switchElement).toHaveClass('data-[state=unchecked]:growfund-bg-background-fill-tertiary');
  });

  it('should render thumb element', () => {
    render(<Switch />);

    const switchElement = screen.getByRole('switch');
    const thumb = switchElement.querySelector('[data-state]');

    expect(thumb).toBeInTheDocument();
    expect(thumb).toHaveClass(
      'growfund-pointer-events-none',
      'growfund-block',
      'growfund-h-4',
      'growfund-w-4',
      'growfund-rounded-full',
      'growfund-bg-white',
      'growfund-ring-0',
      'growfund-transition-transform',
    );
  });

  it('should handle form integration', () => {
    render(<Switch />);

    const switchElement = screen.getByRole('switch');
    expect(switchElement).toBeInTheDocument();
  });

  it('should work with labels', () => {
    render(
      <div>
        <label htmlFor="test-switch">Enable notifications</label>
        <Switch id="test-switch" />
      </div>,
    );

    const label = screen.getByText('Enable notifications');
    const switchElement = screen.getByRole('switch');

    expect(label).toBeInTheDocument();
    expect(switchElement).toHaveAttribute('id', 'test-switch');
  });

  it('should handle aria attributes', () => {
    render(
      <Switch
        aria-label="Toggle dark mode"
        aria-describedby="switch-description"
        aria-labelledby="switch-label"
      />,
    );

    const switchElement = screen.getByRole('switch');
    expect(switchElement).toHaveAttribute('aria-label', 'Toggle dark mode');
    expect(switchElement).toHaveAttribute('aria-describedby', 'switch-description');
    expect(switchElement).toHaveAttribute('aria-labelledby', 'switch-label');
  });

  it('should handle data attributes', () => {
    render(<Switch data-testid="toggle-switch" data-value="enabled" />);

    const switchElement = screen.getByRole('switch');
    expect(switchElement).toHaveAttribute('data-testid', 'toggle-switch');
    expect(switchElement).toHaveAttribute('data-value', 'enabled');
  });
});

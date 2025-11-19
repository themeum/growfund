import { fireEvent, render, screen } from '@testing-library/react';
import { describe, expect, it, vi } from 'vitest';

import { Input } from '@/components/ui/input';

describe('Input Component', () => {
  it('should render with default props', () => {
    render(<Input placeholder="Enter text" />);

    const input = screen.getByPlaceholderText('Enter text');
    expect(input).toBeInTheDocument();
    expect(input).toHaveClass('growfund-flex', 'growfund-min-h-9', 'growfund-w-full');
  });

  it('should render with custom className', () => {
    render(<Input className="custom-class" placeholder="Enter text" />);

    const input = screen.getByPlaceholderText('Enter text');
    expect(input).toHaveClass('custom-class');
  });

  it('should render with rootClassName', () => {
    render(<Input rootClassName="root-custom" placeholder="Enter text" />);

    const container = screen.getByPlaceholderText('Enter text').parentElement;
    expect(container).toHaveClass('root-custom');
  });

  it('should render search input with search icon', () => {
    render(<Input type="search" placeholder="Search..." />);

    const input = screen.getByPlaceholderText('Search...');
    const searchIcon = input.parentElement?.querySelector('svg');

    expect(input).toHaveAttribute('type', 'search');
    expect(input).toHaveClass('growfund-ps-8');
    expect(searchIcon).toBeInTheDocument();
  });

  it('should render with prefix text', () => {
    render(<Input prefixText="$" placeholder="Enter amount" />);

    const input = screen.getByPlaceholderText('Enter amount');
    const prefixText = input.parentElement?.querySelector('.growfund-absolute');

    expect(input).toHaveClass('growfund-ps-8');
    expect(prefixText).toHaveTextContent('$');
  });

  it('should render with postfix text', () => {
    render(<Input postfixText="USD" placeholder="Enter amount" />);

    const input = screen.getByPlaceholderText('Enter amount');
    const postfixText = input.parentElement?.querySelector('.growfund-absolute');

    expect(input).toHaveClass('growfund-pe-3');
    expect(postfixText).toHaveTextContent('USD');
  });

  it('should render search input with prefix text and correct padding', () => {
    render(<Input type="search" prefixText="$" placeholder="Search..." />);

    const input = screen.getByPlaceholderText('Search...');
    const prefixTextDiv = input.parentElement?.querySelector('div.growfund-absolute:not(svg)');

    expect(input).toHaveClass('growfund-ps-12');
    // The prefix text should be positioned after the search icon
    expect(prefixTextDiv).toHaveClass('growfund-left-8');
  });

  it('should handle autoFocusVisible prop', () => {
    render(<Input autoFocusVisible={true} placeholder="Enter text" />);

    const input = screen.getByPlaceholderText('Enter text');
    expect(input).toBeInTheDocument();
    // The autoFocusVisible prop is handled internally by the component
    expect(input).toBeInTheDocument();
  });

  it('should handle input change events', () => {
    const handleChange = vi.fn();
    render(<Input onChange={handleChange} placeholder="Enter text" />);

    const input = screen.getByPlaceholderText('Enter text');
    fireEvent.change(input, { target: { value: 'test value' } });

    expect(handleChange).toHaveBeenCalled();
    expect(input).toHaveValue('test value');
  });

  it('should handle disabled state', () => {
    render(<Input disabled placeholder="Enter text" />);

    const input = screen.getByPlaceholderText('Enter text');
    expect(input).toBeDisabled();
    expect(input).toHaveClass('disabled:growfund-cursor-not-allowed', 'disabled:growfund-opacity-50');
  });

  it('should handle different input types', () => {
    const { rerender } = render(<Input type="email" placeholder="Enter email" />);
    expect(screen.getByPlaceholderText('Enter email')).toHaveAttribute('type', 'email');

    rerender(<Input type="password" placeholder="Enter password" />);
    expect(screen.getByPlaceholderText('Enter password')).toHaveAttribute('type', 'password');

    rerender(<Input type="number" placeholder="Enter number" />);
    expect(screen.getByPlaceholderText('Enter number')).toHaveAttribute('type', 'number');
  });

  it('should handle focus and blur events', () => {
    const handleFocus = vi.fn();
    const handleBlur = vi.fn();

    render(<Input onFocus={handleFocus} onBlur={handleBlur} placeholder="Enter text" />);

    const input = screen.getByPlaceholderText('Enter text');

    fireEvent.focus(input);
    expect(handleFocus).toHaveBeenCalled();

    fireEvent.blur(input);
    expect(handleBlur).toHaveBeenCalled();
  });

  it('should forward ref correctly', () => {
    const ref = { current: null };
    render(<Input ref={ref} placeholder="Enter text" />);

    expect(ref.current).toBeInstanceOf(HTMLInputElement);
  });

  it('should handle all HTML input attributes', () => {
    render(
      <Input
        placeholder="Enter text"
        name="test-input"
        id="test-id"
        required
        maxLength={50}
        minLength={2}
        pattern="[A-Za-z]+"
        title="Only letters allowed"
      />,
    );

    const input = screen.getByPlaceholderText('Enter text');
    expect(input).toHaveAttribute('name', 'test-input');
    expect(input).toHaveAttribute('id', 'test-id');
    expect(input).toHaveAttribute('required');
    expect(input).toHaveAttribute('maxLength', '50');
    expect(input).toHaveAttribute('minLength', '2');
    expect(input).toHaveAttribute('pattern', '[A-Za-z]+');
    expect(input).toHaveAttribute('title', 'Only letters allowed');
  });
});

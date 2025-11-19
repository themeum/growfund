import { fireEvent, render, screen } from '@testing-library/react';
import * as React from 'react';
import { describe, expect, it, vi } from 'vitest';

import { Textarea } from '@/components/ui/textarea';

describe('Textarea Component', () => {
  it('should render with default props', () => {
    render(<Textarea placeholder="Enter text" />);

    const textarea = screen.getByPlaceholderText('Enter text');
    expect(textarea).toBeInTheDocument();
    expect(textarea).toHaveClass(
      'growfund-flex',
      'growfund-min-h-[3.75rem]',
      'growfund-w-full',
      'growfund-rounded-md',
      'growfund-border',
      'growfund-border-border',
      'growfund-bg-transparent',
      'growfund-px-3',
      'growfund-py-2',
      'growfund-typo-small',
      'growfund-text-fg-primary',
    );
  });

  it('should render with custom className', () => {
    render(<Textarea className="custom-textarea" placeholder="Enter text" />);

    const textarea = screen.getByPlaceholderText('Enter text');
    expect(textarea).toHaveClass('custom-textarea');
  });

  it('should render as disabled when disabled prop is true', () => {
    render(<Textarea disabled placeholder="Enter text" />);

    const textarea = screen.getByPlaceholderText('Enter text');
    expect(textarea).toBeDisabled();
    expect(textarea).toHaveClass('disabled:growfund-cursor-not-allowed', 'disabled:growfund-opacity-50');
  });

  it('should handle input change events', () => {
    const handleChange = vi.fn();
    render(<Textarea onChange={handleChange} placeholder="Enter text" />);

    const textarea = screen.getByPlaceholderText('Enter text');
    fireEvent.change(textarea, { target: { value: 'test value' } });

    expect(handleChange).toHaveBeenCalled();
    expect(textarea).toHaveValue('test value');
  });

  it('should handle focus and blur events', () => {
    const handleFocus = vi.fn();
    const handleBlur = vi.fn();

    render(<Textarea onFocus={handleFocus} onBlur={handleBlur} placeholder="Enter text" />);

    const textarea = screen.getByPlaceholderText('Enter text');

    fireEvent.focus(textarea);
    expect(handleFocus).toHaveBeenCalled();

    fireEvent.blur(textarea);
    expect(handleBlur).toHaveBeenCalled();
  });

  it('should handle keyboard events', () => {
    const handleKeyDown = vi.fn();
    render(<Textarea onKeyDown={handleKeyDown} placeholder="Enter text" />);

    const textarea = screen.getByPlaceholderText('Enter text');
    fireEvent.keyDown(textarea, { key: 'Enter' });

    expect(handleKeyDown).toHaveBeenCalled();
  });

  it('should forward ref correctly', () => {
    const ref = { current: null };
    render(<Textarea ref={ref} placeholder="Enter text" />);

    expect(ref.current).toBeInstanceOf(HTMLTextAreaElement);
  });

  it('should handle all HTML textarea attributes', () => {
    render(
      <Textarea
        placeholder="Enter text"
        name="test-textarea"
        id="test-id"
        required
        maxLength={100}
        minLength={2}
        rows={5}
        cols={50}
        wrap="soft"
        readOnly
        autoComplete="off"
        spellCheck={false}
      />,
    );

    const textarea = screen.getByPlaceholderText('Enter text');
    expect(textarea).toHaveAttribute('name', 'test-textarea');
    expect(textarea).toHaveAttribute('id', 'test-id');
    expect(textarea).toHaveAttribute('required');
    expect(textarea).toHaveAttribute('maxLength', '100');
    expect(textarea).toHaveAttribute('minLength', '2');
    expect(textarea).toHaveAttribute('rows', '5');
    expect(textarea).toHaveAttribute('cols', '50');
    expect(textarea).toHaveAttribute('wrap', 'soft');
    expect(textarea).toHaveAttribute('readOnly');
    expect(textarea).toHaveAttribute('autoComplete', 'off');
    expect(textarea).toHaveAttribute('spellCheck', 'false');
  });

  it('should handle controlled value', () => {
    const TestComponent = () => {
      const [value, setValue] = React.useState('initial value');

      return (
        <Textarea
          value={value}
          onChange={(e) => {
            setValue(e.target.value);
          }}
          placeholder="Enter text"
        />
      );
    };

    render(<TestComponent />);

    const textarea = screen.getByPlaceholderText('Enter text');
    expect(textarea).toHaveValue('initial value');

    fireEvent.change(textarea, { target: { value: 'updated value' } });
    expect(textarea).toHaveValue('updated value');
  });

  it('should handle placeholder text', () => {
    render(<Textarea placeholder="Please enter your message here" />);

    const textarea = screen.getByPlaceholderText('Please enter your message here');
    expect(textarea).toBeInTheDocument();
    expect(textarea).toHaveClass('placeholder:growfund-text-fg-subdued', 'placeholder:growfund-font-regular');
  });

  it('should have proper focus styles', () => {
    render(<Textarea placeholder="Enter text" />);

    const textarea = screen.getByPlaceholderText('Enter text');
    expect(textarea).toHaveClass(
      'growfund-ring-offset-2',
      'focus-visible:growfund-ring-2',
      'focus-visible:growfund-ring-ring',
    );
  });

  it('should handle resize behavior', () => {
    render(<Textarea placeholder="Enter text" style={{ resize: 'vertical' }} />);

    const textarea = screen.getByPlaceholderText('Enter text');
    expect(textarea).toHaveStyle('resize: vertical');
  });

  it('should handle form integration', () => {
    render(
      <form>
        <Textarea name="message" required />
        <button type="submit">Submit</button>
      </form>,
    );

    const textarea = screen.getByRole('textbox');
    const submitButton = screen.getByRole('button', { name: 'Submit' });

    expect(textarea).toBeInTheDocument();
    expect(submitButton).toBeInTheDocument();
  });

  it('should work with labels', () => {
    render(
      <div>
        <label htmlFor="test-textarea">Message</label>
        <Textarea id="test-textarea" placeholder="Enter your message" />
      </div>,
    );

    const label = screen.getByText('Message');
    const textarea = screen.getByRole('textbox');

    expect(label).toBeInTheDocument();
    expect(textarea).toHaveAttribute('id', 'test-textarea');
  });

  it('should handle different content types', () => {
    const longText =
      'This is a very long text that should wrap to multiple lines in the textarea component. It should handle line breaks and maintain proper formatting.';

    render(<Textarea defaultValue={longText} placeholder="Enter text" />);

    const textarea = screen.getByPlaceholderText('Enter text');
    expect(textarea).toHaveValue(longText);
  });
});

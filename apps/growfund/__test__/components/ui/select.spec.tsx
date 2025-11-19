import { fireEvent, render, screen, waitFor } from '@testing-library/react';
import { beforeEach, describe, expect, it, vi } from 'vitest';

import {
    Select,
    SelectContent,
    SelectGroup,
    SelectItem,
    SelectLabel,
    SelectSeparator,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';

// Mock the portal container and scrollIntoView
beforeEach(() => {
  const portalContainer = document.createElement('div');
  portalContainer.id = 'growfund-root';
  document.body.appendChild(portalContainer);

  // Mock scrollIntoView for Radix UI components
  Element.prototype.scrollIntoView = vi.fn();
});

describe('Select Components', () => {
  describe('Select (Root)', () => {
    it('should render with default props', () => {
      render(
        <Select>
          <SelectTrigger>
            <SelectValue placeholder="Select an option" />
          </SelectTrigger>
          <SelectContent>
            <SelectItem value="option1">Option 1</SelectItem>
            <SelectItem value="option2">Option 2</SelectItem>
          </SelectContent>
        </Select>,
      );

      const trigger = screen.getByRole('combobox');
      expect(trigger).toBeInTheDocument();
      expect(trigger).toHaveTextContent('Select an option');
    });

    it('should handle value changes', async () => {
      const handleValueChange = vi.fn();

      render(
        <Select onValueChange={handleValueChange}>
          <SelectTrigger>
            <SelectValue placeholder="Select an option" />
          </SelectTrigger>
          <SelectContent>
            <SelectItem value="option1">Option 1</SelectItem>
            <SelectItem value="option2">Option 2</SelectItem>
          </SelectContent>
        </Select>,
      );

      const trigger = screen.getByRole('combobox');
      fireEvent.click(trigger);

      await waitFor(() => {
        const option1 = screen.getByText('Option 1');
        expect(option1).toBeInTheDocument();
      });

      fireEvent.click(screen.getByText('Option 1'));

      expect(handleValueChange).toHaveBeenCalledWith('option1');
    });
  });

  describe('SelectTrigger', () => {
    it('should render with default props', () => {
      render(
        <Select>
          <SelectTrigger>
            <SelectValue placeholder="Select an option" />
          </SelectTrigger>
        </Select>,
      );

      const trigger = screen.getByRole('combobox');
      expect(trigger).toBeInTheDocument();
      expect(trigger).toHaveClass(
        'growfund-flex',
        'growfund-w-full',
        'growfund-items-center',
        'growfund-justify-between',
        'growfund-whitespace-nowrap',
        'growfund-rounded-md',
        'growfund-border',
        'growfund-border-border',
        'growfund-bg-transparent',
        'growfund-px-3',
        'growfund-py-2',
        'growfund-typo-small',
      );
    });

    it('should render with custom className', () => {
      render(
        <Select>
          <SelectTrigger className="custom-trigger">
            <SelectValue placeholder="Select an option" />
          </SelectTrigger>
        </Select>,
      );

      const trigger = screen.getByRole('combobox');
      expect(trigger).toHaveClass('custom-trigger');
    });

    it('should render as disabled when disabled prop is true', () => {
      render(
        <Select disabled>
          <SelectTrigger>
            <SelectValue placeholder="Select an option" />
          </SelectTrigger>
        </Select>,
      );

      const trigger = screen.getByRole('combobox');
      expect(trigger).toHaveAttribute('data-disabled', '');
      expect(trigger).toHaveClass('disabled:growfund-cursor-not-allowed', 'disabled:growfund-opacity-50');
    });

    it('should show chevron down icon', () => {
      render(
        <Select>
          <SelectTrigger>
            <SelectValue placeholder="Select an option" />
          </SelectTrigger>
        </Select>,
      );

      const trigger = screen.getByRole('combobox');
      const chevronIcon = trigger.querySelector('svg');
      expect(chevronIcon).toBeInTheDocument();
    });

    it('should forward ref correctly', () => {
      const ref = { current: null };
      render(
        <Select>
          <SelectTrigger ref={ref}>
            <SelectValue placeholder="Select an option" />
          </SelectTrigger>
        </Select>,
      );

      expect(ref.current).toBeInstanceOf(HTMLButtonElement);
    });
  });

  describe('SelectValue', () => {
    it('should render placeholder when no value is selected', () => {
      render(
        <Select>
          <SelectTrigger>
            <SelectValue placeholder="Select an option" />
          </SelectTrigger>
        </Select>,
      );

      const trigger = screen.getByRole('combobox');
      expect(trigger).toHaveTextContent('Select an option');
    });

    it('should render selected value', () => {
      render(
        <Select value="option1">
          <SelectTrigger>
            <SelectValue placeholder="Select an option" />
          </SelectTrigger>
          <SelectContent>
            <SelectItem value="option1">Option 1</SelectItem>
          </SelectContent>
        </Select>,
      );

      const trigger = screen.getByRole('combobox');
      expect(trigger).toHaveTextContent('Option 1');
    });

    it('should render with custom className', () => {
      render(
        <Select>
          <SelectTrigger>
            <SelectValue className="custom-value" placeholder="Select an option" />
          </SelectTrigger>
        </Select>,
      );

      const trigger = screen.getByRole('combobox');
      expect(trigger).toBeInTheDocument();
    });
  });

  describe('SelectContent', () => {
    it('should render with default props', async () => {
      render(
        <Select>
          <SelectTrigger>
            <SelectValue placeholder="Select an option" />
          </SelectTrigger>
          <SelectContent>
            <SelectItem value="option1">Option 1</SelectItem>
          </SelectContent>
        </Select>,
      );

      const trigger = screen.getByRole('combobox');
      fireEvent.click(trigger);

      await waitFor(() => {
        const content = screen.getByRole('listbox');
        expect(content).toBeInTheDocument();
        expect(content).toHaveClass(
          'growfund-relative',
          'growfund-z-dialog',
          'growfund-max-h-96',
          'growfund-min-w-[8rem]',
          'growfund-overflow-hidden',
          'growfund-rounded-md',
          'growfund-border',
          'growfund-bg-popover',
          'growfund-text-popover-fg',
          'growfund-shadow-md',
        );
      });
    });

    it('should render with custom className', async () => {
      render(
        <Select>
          <SelectTrigger>
            <SelectValue placeholder="Select an option" />
          </SelectTrigger>
          <SelectContent className="custom-content">
            <SelectItem value="option1">Option 1</SelectItem>
          </SelectContent>
        </Select>,
      );

      const trigger = screen.getByRole('combobox');
      fireEvent.click(trigger);

      await waitFor(() => {
        const content = screen.getByRole('listbox');
        expect(content).toHaveClass('custom-content');
      });
    });

    it('should render with popper position', async () => {
      render(
        <Select>
          <SelectTrigger>
            <SelectValue placeholder="Select an option" />
          </SelectTrigger>
          <SelectContent position="popper">
            <SelectItem value="option1">Option 1</SelectItem>
          </SelectContent>
        </Select>,
      );

      const trigger = screen.getByRole('combobox');
      fireEvent.click(trigger);

      await waitFor(() => {
        const content = screen.getByRole('listbox');
        expect(content).toHaveClass('data-[side=bottom]:growfund-translate-y-1');
      });
    });
  });

  describe('SelectItem', () => {
    it('should render with default props', async () => {
      render(
        <Select>
          <SelectTrigger>
            <SelectValue placeholder="Select an option" />
          </SelectTrigger>
          <SelectContent>
            <SelectItem value="option1">Option 1</SelectItem>
          </SelectContent>
        </Select>,
      );

      const trigger = screen.getByRole('combobox');
      fireEvent.click(trigger);

      await waitFor(() => {
        const option = screen.getByRole('option');
        expect(option).toBeInTheDocument();
        expect(option).toHaveClass(
          'growfund-relative',
          'growfund-flex',
          'growfund-w-full',
          'growfund-cursor-default',
          'growfund-select-none',
          'growfund-items-center',
          'growfund-rounded-sm',
          'growfund-py-1.5',
          'growfund-pl-2',
          'growfund-pr-8',
          'growfund-typo-small',
        );
      });
    });

    it('should render with custom className', async () => {
      render(
        <Select>
          <SelectTrigger>
            <SelectValue placeholder="Select an option" />
          </SelectTrigger>
          <SelectContent>
            <SelectItem className="custom-item" value="option1">
              Option 1
            </SelectItem>
          </SelectContent>
        </Select>,
      );

      const trigger = screen.getByRole('combobox');
      fireEvent.click(trigger);

      await waitFor(() => {
        const option = screen.getByRole('option');
        expect(option).toHaveClass('custom-item');
      });
    });

    it('should render as disabled when disabled prop is true', async () => {
      render(
        <Select>
          <SelectTrigger>
            <SelectValue placeholder="Select an option" />
          </SelectTrigger>
          <SelectContent>
            <SelectItem disabled value="option1">
              Option 1
            </SelectItem>
          </SelectContent>
        </Select>,
      );

      const trigger = screen.getByRole('combobox');
      fireEvent.click(trigger);

      await waitFor(() => {
        const option = screen.getByRole('option');
        expect(option).toHaveAttribute('data-disabled', '');
        expect(option).toHaveClass(
          'data-[disabled]:growfund-pointer-events-none',
          'data-[disabled]:growfund-opacity-50',
        );
      });
    });

    it('should show check icon when selected', async () => {
      render(
        <Select value="option1">
          <SelectTrigger>
            <SelectValue placeholder="Select an option" />
          </SelectTrigger>
          <SelectContent>
            <SelectItem value="option1">Option 1</SelectItem>
          </SelectContent>
        </Select>,
      );

      const trigger = screen.getByRole('combobox');
      fireEvent.click(trigger);

      await waitFor(() => {
        const items = screen.getAllByText('Option 1');
        const dropdownItem = items.find((item) => item.closest('[role="option"]'));
        const checkIcon = dropdownItem?.closest('[role="option"]')?.querySelector('svg');
        expect(checkIcon).toBeInTheDocument();
      });
    });
  });

  describe('SelectGroup', () => {
    it('should render with children', async () => {
      render(
        <Select>
          <SelectTrigger>
            <SelectValue placeholder="Select an option" />
          </SelectTrigger>
          <SelectContent>
            <SelectGroup>
              <SelectLabel>Group 1</SelectLabel>
              <SelectItem value="option1">Option 1</SelectItem>
            </SelectGroup>
          </SelectContent>
        </Select>,
      );

      const trigger = screen.getByRole('combobox');
      fireEvent.click(trigger);

      await waitFor(() => {
        const group = screen.getByText('Group 1').closest('[role="group"]');
        expect(group).toBeInTheDocument();
      });
    });
  });

  describe('SelectLabel', () => {
    it('should render with default props', async () => {
      render(
        <Select>
          <SelectTrigger>
            <SelectValue placeholder="Select an option" />
          </SelectTrigger>
          <SelectContent>
            <SelectGroup>
              <SelectLabel>Group Label</SelectLabel>
              <SelectItem value="option1">Option 1</SelectItem>
            </SelectGroup>
          </SelectContent>
        </Select>,
      );

      const trigger = screen.getByRole('combobox');
      fireEvent.click(trigger);

      await waitFor(() => {
        const label = screen.getByText('Group Label');
        expect(label).toBeInTheDocument();
        expect(label).toHaveClass('growfund-px-2', 'growfund-py-1.5', 'growfund-typo-small', 'growfund-font-semibold');
      });
    });
  });

  describe('SelectSeparator', () => {
    it('should render with default props', async () => {
      render(
        <Select>
          <SelectTrigger>
            <SelectValue placeholder="Select an option" />
          </SelectTrigger>
          <SelectContent>
            <SelectItem value="option1">Option 1</SelectItem>
            <SelectSeparator />
            <SelectItem value="option2">Option 2</SelectItem>
          </SelectContent>
        </Select>,
      );

      const trigger = screen.getByRole('combobox');
      fireEvent.click(trigger);

      await waitFor(() => {
        const separator = document.querySelector(
          '[aria-hidden="true"].growfund--mx-1.growfund-my-1.growfund-h-px.growfund-bg-border',
        );
        expect(separator).toBeInTheDocument();
        expect(separator).toHaveClass('growfund--mx-1', 'growfund-my-1', 'growfund-h-px', 'growfund-bg-border');
      });
    });
  });

  describe('Complete Select Example', () => {
    it('should render a complete select with all components', async () => {
      const handleValueChange = vi.fn();

      render(
        <Select onValueChange={handleValueChange}>
          <SelectTrigger>
            <SelectValue placeholder="Choose a fruit" />
          </SelectTrigger>
          <SelectContent>
            <SelectGroup>
              <SelectLabel>Fruits</SelectLabel>
              <SelectItem value="apple">Apple</SelectItem>
              <SelectItem value="banana">Banana</SelectItem>
            </SelectGroup>
            <SelectSeparator />
            <SelectGroup>
              <SelectLabel>Vegetables</SelectLabel>
              <SelectItem value="carrot">Carrot</SelectItem>
              <SelectItem value="broccoli">Broccoli</SelectItem>
            </SelectGroup>
          </SelectContent>
        </Select>,
      );

      const trigger = screen.getByRole('combobox');
      expect(trigger).toHaveTextContent('Choose a fruit');

      fireEvent.click(trigger);

      await waitFor(() => {
        expect(screen.getByText('Fruits')).toBeInTheDocument();
        expect(screen.getByText('Apple')).toBeInTheDocument();
        expect(screen.getByText('Banana')).toBeInTheDocument();
        expect(screen.getByText('Vegetables')).toBeInTheDocument();
        expect(screen.getByText('Carrot')).toBeInTheDocument();
        expect(screen.getByText('Broccoli')).toBeInTheDocument();
      });

      fireEvent.click(screen.getByText('Apple'));

      expect(handleValueChange).toHaveBeenCalledWith('apple');
    });
  });
});

import { fireEvent, render, screen } from '@testing-library/react';
import { describe, expect, it, vi } from 'vitest';

import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';

describe('Card Components', () => {
  describe('Card', () => {
    it('should render with default props', () => {
      render(<Card>Card content</Card>);

      const card = screen.getByText('Card content');
      expect(card).toBeInTheDocument();
      expect(card).toHaveClass(
        'growfund-rounded-lg',
        'growfund-border',
        'growfund-bg-background-surface',
        'growfund-text-fg-primary',
        'growfund-group/card',
      );
    });

    it('should render with custom className', () => {
      render(<Card className="custom-card">Card content</Card>);

      const card = screen.getByText('Card content');
      expect(card).toHaveClass('custom-card');
    });

    it('should forward ref correctly', () => {
      const ref = { current: null };
      render(<Card ref={ref}>Card content</Card>);

      expect(ref.current).toBeInstanceOf(HTMLDivElement);
    });

    it('should handle click events', () => {
      const handleClick = vi.fn();
      render(<Card onClick={handleClick}>Card content</Card>);

      const card = screen.getByText('Card content');
      fireEvent.click(card);

      expect(handleClick).toHaveBeenCalled();
    });
  });

  describe('CardHeader', () => {
    it('should render with default props', () => {
      render(
        <Card>
          <CardHeader>Header content</CardHeader>
        </Card>,
      );

      const header = screen.getByText('Header content');
      expect(header).toBeInTheDocument();
      expect(header).toHaveClass(
        'growfund-relative',
        'growfund-flex',
        'growfund-flex-col',
        'growfund-space-y-1.5',
        'growfund-p-4',
      );
    });

    it('should render with custom className', () => {
      render(
        <Card>
          <CardHeader className="custom-header">Header content</CardHeader>
        </Card>,
      );

      const header = screen.getByText('Header content');
      expect(header).toHaveClass('custom-header');
    });

    it('should forward ref correctly', () => {
      const ref = { current: null };
      render(
        <Card>
          <CardHeader ref={ref}>Header content</CardHeader>
        </Card>,
      );

      expect(ref.current).toBeInstanceOf(HTMLDivElement);
    });
  });

  describe('CardTitle', () => {
    it('should render with default props', () => {
      render(
        <Card>
          <CardHeader>
            <CardTitle>Card Title</CardTitle>
          </CardHeader>
        </Card>,
      );

      const title = screen.getByText('Card Title');
      expect(title).toBeInTheDocument();
      expect(title).toHaveClass(
        'growfund-typo-h6',
        'growfund-text-fg-primary',
        'growfund-font-semibold',
        'growfund-leading-none',
        'growfund-tracking-tight',
        'growfund-w-full',
        'growfund-flex',
        'growfund-items-center',
        'growfund-gap-2',
      );
    });

    it('should render with custom className', () => {
      render(
        <Card>
          <CardHeader>
            <CardTitle className="custom-title">Card Title</CardTitle>
          </CardHeader>
        </Card>,
      );

      const title = screen.getByText('Card Title');
      expect(title).toHaveClass('custom-title');
    });

    it('should forward ref correctly', () => {
      const ref = { current: null };
      render(
        <Card>
          <CardHeader>
            <CardTitle ref={ref}>Card Title</CardTitle>
          </CardHeader>
        </Card>,
      );

      expect(ref.current).toBeInstanceOf(HTMLDivElement);
    });
  });

  describe('CardDescription', () => {
    it('should render with default props', () => {
      render(
        <Card>
          <CardHeader>
            <CardDescription>Card description</CardDescription>
          </CardHeader>
        </Card>,
      );

      const description = screen.getByText('Card description');
      expect(description).toBeInTheDocument();
      expect(description).toHaveClass('growfund-typo-small', 'growfund-text-fg-muted');
    });

    it('should render with custom className', () => {
      render(
        <Card>
          <CardHeader>
            <CardDescription className="custom-description">Card description</CardDescription>
          </CardHeader>
        </Card>,
      );

      const description = screen.getByText('Card description');
      expect(description).toHaveClass('custom-description');
    });

    it('should forward ref correctly', () => {
      const ref = { current: null };
      render(
        <Card>
          <CardHeader>
            <CardDescription ref={ref}>Card description</CardDescription>
          </CardHeader>
        </Card>,
      );

      expect(ref.current).toBeInstanceOf(HTMLDivElement);
    });
  });

  describe('CardContent', () => {
    it('should render with default props', () => {
      render(
        <Card>
          <CardContent>Card content</CardContent>
        </Card>,
      );

      const content = screen.getByText('Card content');
      expect(content).toBeInTheDocument();
      expect(content).toHaveClass('growfund-p-4', 'growfund-pt-0');
    });

    it('should render with custom className', () => {
      render(
        <Card>
          <CardContent className="custom-content">Card content</CardContent>
        </Card>,
      );

      const content = screen.getByText('Card content');
      expect(content).toHaveClass('custom-content');
    });

    it('should forward ref correctly', () => {
      const ref = { current: null };
      render(
        <Card>
          <CardContent ref={ref}>Card content</CardContent>
        </Card>,
      );

      expect(ref.current).toBeInstanceOf(HTMLDivElement);
    });
  });

  describe('CardFooter', () => {
    it('should render with default props', () => {
      render(
        <Card>
          <CardFooter>Footer content</CardFooter>
        </Card>,
      );

      const footer = screen.getByText('Footer content');
      expect(footer).toBeInTheDocument();
      expect(footer).toHaveClass('growfund-flex', 'growfund-items-center', 'growfund-p-6', 'growfund-pt-0');
    });

    it('should render with custom className', () => {
      render(
        <Card>
          <CardFooter className="custom-footer">Footer content</CardFooter>
        </Card>,
      );

      const footer = screen.getByText('Footer content');
      expect(footer).toHaveClass('custom-footer');
    });

    it('should forward ref correctly', () => {
      const ref = { current: null };
      render(
        <Card>
          <CardFooter ref={ref}>Footer content</CardFooter>
        </Card>,
      );

      expect(ref.current).toBeInstanceOf(HTMLDivElement);
    });
  });

  describe('Complete Card Structure', () => {
    it('should render a complete card with all components', () => {
      render(
        <Card>
          <CardHeader>
            <CardTitle>Test Card</CardTitle>
            <CardDescription>This is a test card description</CardDescription>
          </CardHeader>
          <CardContent>
            <p>This is the main content of the card.</p>
          </CardContent>
          <CardFooter>
            <button>Action Button</button>
          </CardFooter>
        </Card>,
      );

      expect(screen.getByText('Test Card')).toBeInTheDocument();
      expect(screen.getByText('This is a test card description')).toBeInTheDocument();
      expect(screen.getByText('This is the main content of the card.')).toBeInTheDocument();
      expect(screen.getByRole('button', { name: 'Action Button' })).toBeInTheDocument();
    });

    it('should handle nested content correctly', () => {
      render(
        <Card>
          <CardHeader>
            <CardTitle>
              <span>Nested Title</span>
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div>
              <p>Nested content</p>
            </div>
          </CardContent>
        </Card>,
      );

      expect(screen.getByText('Nested Title')).toBeInTheDocument();
      expect(screen.getByText('Nested content')).toBeInTheDocument();
    });
  });
});

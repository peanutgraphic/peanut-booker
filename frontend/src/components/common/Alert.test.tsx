import { describe, it, expect, vi } from 'vitest';
import { render, screen, fireEvent } from '@testing-library/react';
import Alert, { HelpCard, Tip } from './Alert';
import { Star } from 'lucide-react';

describe('Alert', () => {
  describe('rendering', () => {
    it('renders children', () => {
      render(<Alert>Alert message</Alert>);

      expect(screen.getByText('Alert message')).toBeInTheDocument();
    });

    it('renders title when provided', () => {
      render(<Alert title="Alert Title">Message</Alert>);

      expect(screen.getByText('Alert Title')).toBeInTheDocument();
    });

    it('does not render title when not provided', () => {
      render(<Alert>Message only</Alert>);

      expect(screen.queryByText('Alert Title')).not.toBeInTheDocument();
    });

    it('has role="alert"', () => {
      render(<Alert>Message</Alert>);

      expect(screen.getByRole('alert')).toBeInTheDocument();
    });

    it('applies custom className', () => {
      render(<Alert className="custom-alert">Message</Alert>);

      expect(screen.getByRole('alert')).toHaveClass('custom-alert');
    });
  });

  describe('variants', () => {
    it('renders info variant by default', () => {
      render(<Alert>Info message</Alert>);

      const alert = screen.getByRole('alert');
      expect(alert).toHaveClass('bg-blue-50', 'border-blue-200', 'text-blue-800');
    });

    it('renders success variant', () => {
      render(<Alert variant="success">Success message</Alert>);

      const alert = screen.getByRole('alert');
      expect(alert).toHaveClass('bg-green-50', 'border-green-200', 'text-green-800');
    });

    it('renders warning variant', () => {
      render(<Alert variant="warning">Warning message</Alert>);

      const alert = screen.getByRole('alert');
      expect(alert).toHaveClass('bg-amber-50', 'border-amber-200', 'text-amber-800');
    });

    it('renders error variant', () => {
      render(<Alert variant="error">Error message</Alert>);

      const alert = screen.getByRole('alert');
      expect(alert).toHaveClass('bg-red-50', 'border-red-200', 'text-red-800');
    });
  });

  describe('icons', () => {
    it('renders default icon for info variant', () => {
      const { container } = render(<Alert variant="info">Message</Alert>);

      expect(container.querySelector('.lucide-info')).toBeInTheDocument();
    });

    it('renders default icon for success variant', () => {
      const { container } = render(<Alert variant="success">Message</Alert>);

      // CheckCircle from lucide-react renders as lucide-check-circle
      expect(container.querySelector('[class*="lucide"]')).toBeInTheDocument();
    });

    it('renders default icon for warning variant', () => {
      const { container } = render(<Alert variant="warning">Message</Alert>);

      expect(container.querySelector('.lucide-triangle-alert')).toBeInTheDocument();
    });

    it('renders default icon for error variant', () => {
      const { container } = render(<Alert variant="error">Message</Alert>);

      expect(container.querySelector('.lucide-circle-alert')).toBeInTheDocument();
    });

    it('renders custom icon when provided', () => {
      const { container } = render(
        <Alert icon={<Star data-testid="custom-icon" />}>Message</Alert>
      );

      expect(screen.getByTestId('custom-icon')).toBeInTheDocument();
      // Should not have default info icon
      expect(container.querySelector('.lucide-info')).not.toBeInTheDocument();
    });
  });

  describe('dismissible', () => {
    it('does not render dismiss button by default', () => {
      render(<Alert>Message</Alert>);

      const buttons = screen.queryAllByRole('button');
      expect(buttons).toHaveLength(0);
    });

    it('does not render dismiss button when dismissible but no onDismiss', () => {
      render(<Alert dismissible>Message</Alert>);

      const buttons = screen.queryAllByRole('button');
      expect(buttons).toHaveLength(0);
    });

    it('renders dismiss button when dismissible with onDismiss', () => {
      const onDismiss = vi.fn();
      render(
        <Alert dismissible onDismiss={onDismiss}>
          Message
        </Alert>
      );

      expect(screen.getByRole('button')).toBeInTheDocument();
    });

    it('calls onDismiss when dismiss button is clicked', () => {
      const onDismiss = vi.fn();
      render(
        <Alert dismissible onDismiss={onDismiss}>
          Message
        </Alert>
      );

      fireEvent.click(screen.getByRole('button'));

      expect(onDismiss).toHaveBeenCalledTimes(1);
    });

    it('renders X icon in dismiss button', () => {
      const onDismiss = vi.fn();
      const { container } = render(
        <Alert dismissible onDismiss={onDismiss}>
          Message
        </Alert>
      );

      expect(container.querySelector('.lucide-x')).toBeInTheDocument();
    });
  });
});

describe('HelpCard', () => {
  it('renders title', () => {
    render(<HelpCard title="Help Title">Content</HelpCard>);

    expect(screen.getByText('Help Title')).toBeInTheDocument();
  });

  it('renders children', () => {
    render(<HelpCard title="Title">Help content here</HelpCard>);

    expect(screen.getByText('Help content here')).toBeInTheDocument();
  });

  it('renders icon when provided', () => {
    render(
      <HelpCard title="Title" icon={<Star data-testid="help-icon" />}>
        Content
      </HelpCard>
    );

    expect(screen.getByTestId('help-icon')).toBeInTheDocument();
  });

  it('does not render icon container when not provided', () => {
    const { container } = render(<HelpCard title="Title">Content</HelpCard>);

    // Should only have the content div, not the icon div
    const flexContainer = container.querySelector('.flex');
    // Only one child (the content div)
    expect(flexContainer?.querySelectorAll(':scope > div').length).toBe(1);
  });

  it('applies custom className', () => {
    const { container } = render(
      <HelpCard title="Title" className="custom-help">
        Content
      </HelpCard>
    );

    expect(container.firstChild).toHaveClass('custom-help');
  });

  it('has correct styling', () => {
    const { container } = render(<HelpCard title="Title">Content</HelpCard>);

    expect(container.firstChild).toHaveClass(
      'bg-slate-50',
      'border',
      'border-slate-200',
      'rounded-lg'
    );
  });
});

describe('Tip', () => {
  it('renders children', () => {
    render(<Tip>Helpful tip text</Tip>);

    expect(screen.getByText('Helpful tip text')).toBeInTheDocument();
  });

  it('renders info icon', () => {
    const { container } = render(<Tip>Tip text</Tip>);

    expect(container.querySelector('.lucide-info')).toBeInTheDocument();
  });

  it('applies custom className', () => {
    const { container } = render(<Tip className="custom-tip">Tip text</Tip>);

    expect(container.firstChild).toHaveClass('custom-tip');
  });

  it('has small text styling', () => {
    const { container } = render(<Tip>Tip text</Tip>);

    expect(container.firstChild).toHaveClass('text-xs', 'text-slate-500');
  });
});

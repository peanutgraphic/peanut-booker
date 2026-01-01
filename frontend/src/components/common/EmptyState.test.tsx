import { describe, it, expect, vi } from 'vitest';
import { render, screen, fireEvent } from '@testing-library/react';
import { Users } from 'lucide-react';
import EmptyState, { Skeleton, emptyStates } from './EmptyState';

describe('EmptyState', () => {
  describe('rendering', () => {
    it('renders title', () => {
      render(<EmptyState title="No items found" />);
      expect(screen.getByText('No items found')).toBeInTheDocument();
    });

    it('renders title with correct heading level', () => {
      render(<EmptyState title="No items" />);
      expect(screen.getByRole('heading', { level: 3 })).toHaveTextContent('No items');
    });

    it('renders description when provided', () => {
      render(
        <EmptyState
          title="No items"
          description="Items will appear here when added."
        />
      );
      expect(screen.getByText('Items will appear here when added.')).toBeInTheDocument();
    });

    it('does not render description when not provided', () => {
      render(<EmptyState title="No items" />);
      const description = document.querySelector('.text-slate-500.max-w-sm');
      expect(description).not.toBeInTheDocument();
    });

    it('renders icon when provided', () => {
      render(
        <EmptyState
          title="No items"
          icon={<Users data-testid="users-icon" />}
        />
      );
      expect(screen.getByTestId('users-icon')).toBeInTheDocument();
    });

    it('wraps icon in styled container', () => {
      render(
        <EmptyState
          title="No items"
          icon={<Users data-testid="users-icon" />}
        />
      );
      const iconContainer = screen.getByTestId('users-icon').closest('div');
      expect(iconContainer).toHaveClass('bg-slate-100', 'rounded-full');
    });

    it('does not render icon container when icon not provided', () => {
      const { container } = render(<EmptyState title="No items" />);
      expect(container.querySelector('.bg-slate-100.rounded-full')).not.toBeInTheDocument();
    });
  });

  describe('tips', () => {
    it('renders tips list when provided', () => {
      render(
        <EmptyState
          title="No items"
          tips={['Tip 1', 'Tip 2', 'Tip 3']}
        />
      );
      expect(screen.getByText('Tip 1')).toBeInTheDocument();
      expect(screen.getByText('Tip 2')).toBeInTheDocument();
      expect(screen.getByText('Tip 3')).toBeInTheDocument();
    });

    it('renders tips as list items', () => {
      render(
        <EmptyState
          title="No items"
          tips={['Tip 1', 'Tip 2']}
        />
      );
      const listItems = screen.getAllByRole('listitem');
      expect(listItems).toHaveLength(2);
    });

    it('does not render tips container when tips is empty', () => {
      const { container } = render(<EmptyState title="No items" tips={[]} />);
      expect(container.querySelector('ul')).not.toBeInTheDocument();
    });

    it('does not render tips when not provided', () => {
      const { container } = render(<EmptyState title="No items" />);
      expect(container.querySelector('ul')).not.toBeInTheDocument();
    });
  });

  describe('action button', () => {
    it('renders action button when provided', () => {
      const handleClick = vi.fn();
      render(
        <EmptyState
          title="No items"
          action={{ label: 'Add Item', onClick: handleClick }}
        />
      );
      expect(screen.getByRole('button', { name: 'Add Item' })).toBeInTheDocument();
    });

    it('calls onClick when action button is clicked', () => {
      const handleClick = vi.fn();
      render(
        <EmptyState
          title="No items"
          action={{ label: 'Add Item', onClick: handleClick }}
        />
      );
      fireEvent.click(screen.getByRole('button', { name: 'Add Item' }));
      expect(handleClick).toHaveBeenCalledTimes(1);
    });

    it('does not render button when action not provided', () => {
      render(<EmptyState title="No items" />);
      expect(screen.queryByRole('button')).not.toBeInTheDocument();
    });
  });

  describe('styling', () => {
    it('applies centered layout', () => {
      const { container } = render(<EmptyState title="No items" />);
      expect(container.firstChild).toHaveClass('flex', 'flex-col', 'items-center', 'justify-center', 'text-center');
    });

    it('applies custom className', () => {
      const { container } = render(<EmptyState title="No items" className="custom-empty" />);
      expect(container.firstChild).toHaveClass('custom-empty');
    });
  });
});

describe('Skeleton', () => {
  it('renders with default styling', () => {
    const { container } = render(<Skeleton />);
    expect(container.firstChild).toHaveClass('bg-slate-200', 'rounded', 'animate-pulse');
  });

  it('applies custom className', () => {
    const { container } = render(<Skeleton className="w-full h-4" />);
    expect(container.firstChild).toHaveClass('w-full', 'h-4');
  });

  it('merges custom className with default classes', () => {
    const { container } = render(<Skeleton className="w-full" />);
    expect(container.firstChild).toHaveClass('bg-slate-200', 'animate-pulse', 'w-full');
  });
});

describe('emptyStates presets', () => {
  it('has performers preset', () => {
    expect(emptyStates.performers).toBeDefined();
    expect(emptyStates.performers.title).toBe('No performers found');
    expect(emptyStates.performers.tips).toHaveLength(2);
  });

  it('has bookings preset', () => {
    expect(emptyStates.bookings).toBeDefined();
    expect(emptyStates.bookings.title).toBe('No bookings yet');
  });

  it('has reviews preset', () => {
    expect(emptyStates.reviews).toBeDefined();
    expect(emptyStates.reviews.title).toBe('No reviews yet');
  });

  it('has payouts preset', () => {
    expect(emptyStates.payouts).toBeDefined();
    expect(emptyStates.payouts.title).toBe('No pending payouts');
  });

  it('has market preset', () => {
    expect(emptyStates.market).toBeDefined();
    expect(emptyStates.market.title).toBe('No market events');
  });

  it('has microsites preset', () => {
    expect(emptyStates.microsites).toBeDefined();
    expect(emptyStates.microsites.title).toBe('No microsites yet');
  });

  it('has messages preset', () => {
    expect(emptyStates.messages).toBeDefined();
    expect(emptyStates.messages.title).toBe('No messages');
  });

  it('has customers preset', () => {
    expect(emptyStates.customers).toBeDefined();
    expect(emptyStates.customers.title).toBe('No customers yet');
  });

  it('has analytics preset', () => {
    expect(emptyStates.analytics).toBeDefined();
    expect(emptyStates.analytics.title).toBe('No data available');
  });

  it('has search preset', () => {
    expect(emptyStates.search).toBeDefined();
    expect(emptyStates.search.title).toBe('No results found');
  });

  it('has error preset', () => {
    expect(emptyStates.error).toBeDefined();
    expect(emptyStates.error.title).toBe('Something went wrong');
  });

  it('renders a preset correctly', () => {
    render(<EmptyState {...emptyStates.performers} />);
    expect(screen.getByText('No performers found')).toBeInTheDocument();
    expect(screen.getByText('Performers will appear here once they sign up and create profiles.')).toBeInTheDocument();
  });
});

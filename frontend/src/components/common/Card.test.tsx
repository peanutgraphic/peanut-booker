import { describe, it, expect } from 'vitest';
import { render, screen } from '@testing-library/react';
import { Star } from 'lucide-react';
import Card, { CardHeader, StatCard } from './Card';

describe('Card', () => {
  describe('rendering', () => {
    it('renders children correctly', () => {
      render(<Card>Card content</Card>);
      expect(screen.getByText('Card content')).toBeInTheDocument();
    });

    it('renders with default styling', () => {
      render(<Card>Content</Card>);
      const card = screen.getByText('Content').closest('div');
      expect(card).toHaveClass('bg-white', 'rounded-xl', 'border', 'shadow-sm');
    });
  });

  describe('padding', () => {
    it('applies no padding when padding is none', () => {
      render(<Card padding="none">Content</Card>);
      const card = screen.getByText('Content').closest('div');
      expect(card).not.toHaveClass('p-4', 'p-6', 'p-8');
    });

    it('applies small padding', () => {
      render(<Card padding="sm">Content</Card>);
      const card = screen.getByText('Content').closest('div');
      expect(card).toHaveClass('p-4');
    });

    it('applies medium padding by default', () => {
      render(<Card>Content</Card>);
      const card = screen.getByText('Content').closest('div');
      expect(card).toHaveClass('p-6');
    });

    it('applies large padding', () => {
      render(<Card padding="lg">Content</Card>);
      const card = screen.getByText('Content').closest('div');
      expect(card).toHaveClass('p-8');
    });
  });

  describe('custom className', () => {
    it('applies custom className', () => {
      render(<Card className="custom-class">Content</Card>);
      const card = screen.getByText('Content').closest('div');
      expect(card).toHaveClass('custom-class');
    });

    it('merges custom className with default classes', () => {
      render(<Card className="custom-class">Content</Card>);
      const card = screen.getByText('Content').closest('div');
      expect(card).toHaveClass('bg-white', 'custom-class');
    });
  });

  describe('HTML attributes', () => {
    it('passes through additional props', () => {
      render(<Card data-testid="test-card">Content</Card>);
      expect(screen.getByTestId('test-card')).toBeInTheDocument();
    });

    it('supports onClick handler', () => {
      const handleClick = vi.fn();
      render(<Card onClick={handleClick}>Content</Card>);
      screen.getByText('Content').closest('div')?.click();
      expect(handleClick).toHaveBeenCalled();
    });
  });
});

describe('CardHeader', () => {
  describe('rendering', () => {
    it('renders title', () => {
      render(<CardHeader title="Test Title" />);
      expect(screen.getByText('Test Title')).toBeInTheDocument();
    });

    it('renders title with correct heading level', () => {
      render(<CardHeader title="Test Title" />);
      expect(screen.getByRole('heading', { level: 3 })).toHaveTextContent('Test Title');
    });

    it('renders description when provided', () => {
      render(<CardHeader title="Title" description="Test description" />);
      expect(screen.getByText('Test description')).toBeInTheDocument();
    });

    it('does not render description when not provided', () => {
      render(<CardHeader title="Title" />);
      expect(screen.queryByText('description')).not.toBeInTheDocument();
    });

    it('renders action when provided', () => {
      render(<CardHeader title="Title" action={<button>Action</button>} />);
      expect(screen.getByRole('button', { name: 'Action' })).toBeInTheDocument();
    });
  });

  describe('styling', () => {
    it('applies flex layout', () => {
      const { container } = render(<CardHeader title="Title" />);
      expect(container.firstChild).toHaveClass('flex', 'items-start', 'justify-between');
    });

    it('applies custom className', () => {
      const { container } = render(<CardHeader title="Title" className="custom-header" />);
      expect(container.firstChild).toHaveClass('custom-header');
    });
  });
});

describe('StatCard', () => {
  describe('rendering', () => {
    it('renders title and value', () => {
      render(<StatCard title="Total Users" value={100} />);
      expect(screen.getByText('Total Users')).toBeInTheDocument();
      expect(screen.getByText('100')).toBeInTheDocument();
    });

    it('renders string value', () => {
      render(<StatCard title="Revenue" value="$1,234" />);
      expect(screen.getByText('$1,234')).toBeInTheDocument();
    });

    it('renders icon when provided', () => {
      render(<StatCard title="Rating" value={4.5} icon={<Star data-testid="star-icon" />} />);
      expect(screen.getByTestId('star-icon')).toBeInTheDocument();
    });

    it('does not render icon container when icon not provided', () => {
      const { container } = render(<StatCard title="Title" value={0} />);
      expect(container.querySelector('.bg-primary-50')).not.toBeInTheDocument();
    });
  });

  describe('change indicator', () => {
    it('renders increase change with plus sign', () => {
      render(
        <StatCard
          title="Users"
          value={100}
          change={{ value: 15, type: 'increase' }}
        />
      );
      expect(screen.getByText('+15%')).toBeInTheDocument();
    });

    it('applies green color for increase', () => {
      render(
        <StatCard
          title="Users"
          value={100}
          change={{ value: 15, type: 'increase' }}
        />
      );
      expect(screen.getByText('+15%')).toHaveClass('text-green-600');
    });

    it('renders decrease change with minus sign', () => {
      render(
        <StatCard
          title="Users"
          value={100}
          change={{ value: 10, type: 'decrease' }}
        />
      );
      expect(screen.getByText('-10%')).toBeInTheDocument();
    });

    it('applies red color for decrease', () => {
      render(
        <StatCard
          title="Users"
          value={100}
          change={{ value: 10, type: 'decrease' }}
        />
      );
      expect(screen.getByText('-10%')).toHaveClass('text-red-600');
    });

    it('renders neutral change without sign', () => {
      render(
        <StatCard
          title="Users"
          value={100}
          change={{ value: 0, type: 'neutral' }}
        />
      );
      expect(screen.getByText('0%')).toBeInTheDocument();
    });

    it('applies slate color for neutral', () => {
      render(
        <StatCard
          title="Users"
          value={100}
          change={{ value: 0, type: 'neutral' }}
        />
      );
      expect(screen.getByText('0%')).toHaveClass('text-slate-500');
    });

    it('does not render change when not provided', () => {
      render(<StatCard title="Users" value={100} />);
      expect(screen.queryByText('%')).not.toBeInTheDocument();
    });
  });

  describe('styling', () => {
    it('applies custom className', () => {
      const { container } = render(
        <StatCard title="Title" value={0} className="custom-stat" />
      );
      expect(container.querySelector('.custom-stat')).toBeInTheDocument();
    });

    it('renders inside a Card component', () => {
      const { container } = render(<StatCard title="Title" value={0} />);
      expect(container.querySelector('.bg-white.rounded-xl')).toBeInTheDocument();
    });
  });
});

// Need to import vi for the onClick test
import { vi } from 'vitest';

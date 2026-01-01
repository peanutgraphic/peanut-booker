import { describe, it, expect } from 'vitest';
import { render, screen } from '@testing-library/react';
import Badge, { StatusBadge, TierBadge, LevelBadge } from './Badge';

describe('Badge', () => {
  describe('rendering', () => {
    it('renders children correctly', () => {
      render(<Badge>Test Badge</Badge>);
      expect(screen.getByText('Test Badge')).toBeInTheDocument();
    });

    it('applies default variant (default)', () => {
      render(<Badge>Default</Badge>);
      const badge = screen.getByText('Default');
      expect(badge).toHaveClass('bg-slate-100', 'text-slate-700');
    });

    it('applies default size (md)', () => {
      render(<Badge>Medium</Badge>);
      const badge = screen.getByText('Medium');
      expect(badge).toHaveClass('px-2.5', 'py-1');
    });
  });

  describe('variants', () => {
    it('renders default variant', () => {
      render(<Badge variant="default">Default</Badge>);
      expect(screen.getByText('Default')).toHaveClass('bg-slate-100', 'text-slate-700');
    });

    it('renders primary variant', () => {
      render(<Badge variant="primary">Primary</Badge>);
      expect(screen.getByText('Primary')).toHaveClass('bg-primary-100', 'text-primary-700');
    });

    it('renders success variant', () => {
      render(<Badge variant="success">Success</Badge>);
      expect(screen.getByText('Success')).toHaveClass('bg-green-100', 'text-green-700');
    });

    it('renders warning variant', () => {
      render(<Badge variant="warning">Warning</Badge>);
      expect(screen.getByText('Warning')).toHaveClass('bg-amber-100', 'text-amber-700');
    });

    it('renders danger variant', () => {
      render(<Badge variant="danger">Danger</Badge>);
      expect(screen.getByText('Danger')).toHaveClass('bg-red-100', 'text-red-700');
    });

    it('renders info variant', () => {
      render(<Badge variant="info">Info</Badge>);
      expect(screen.getByText('Info')).toHaveClass('bg-blue-100', 'text-blue-700');
    });
  });

  describe('sizes', () => {
    it('renders small size', () => {
      render(<Badge size="sm">Small</Badge>);
      expect(screen.getByText('Small')).toHaveClass('px-2', 'py-0.5');
    });

    it('renders medium size', () => {
      render(<Badge size="md">Medium</Badge>);
      expect(screen.getByText('Medium')).toHaveClass('px-2.5', 'py-1');
    });
  });

  describe('custom className', () => {
    it('applies custom className', () => {
      render(<Badge className="custom-class">Custom</Badge>);
      expect(screen.getByText('Custom')).toHaveClass('custom-class');
    });
  });
});

describe('StatusBadge', () => {
  describe('booking statuses', () => {
    it('renders pending status', () => {
      render(<StatusBadge status="pending" />);
      expect(screen.getByText('Pending')).toHaveClass('bg-amber-100');
    });

    it('renders confirmed status', () => {
      render(<StatusBadge status="confirmed" />);
      expect(screen.getByText('Confirmed')).toHaveClass('bg-blue-100');
    });

    it('renders completed status', () => {
      render(<StatusBadge status="completed" />);
      expect(screen.getByText('Completed')).toHaveClass('bg-green-100');
    });

    it('renders cancelled status', () => {
      render(<StatusBadge status="cancelled" />);
      expect(screen.getByText('Cancelled')).toHaveClass('bg-red-100');
    });
  });

  describe('performer statuses', () => {
    it('renders active status', () => {
      render(<StatusBadge status="active" />);
      expect(screen.getByText('Active')).toHaveClass('bg-green-100');
    });

    it('renders inactive status', () => {
      render(<StatusBadge status="inactive" />);
      expect(screen.getByText('Inactive')).toHaveClass('bg-slate-100');
    });

    it('renders suspended status', () => {
      render(<StatusBadge status="suspended" />);
      expect(screen.getByText('Suspended')).toHaveClass('bg-red-100');
    });
  });

  describe('escrow statuses', () => {
    it('renders held status', () => {
      render(<StatusBadge status="held" />);
      expect(screen.getByText('Held')).toHaveClass('bg-amber-100');
    });

    it('renders released status', () => {
      render(<StatusBadge status="released" />);
      expect(screen.getByText('Released')).toHaveClass('bg-green-100');
    });

    it('renders refunded status', () => {
      render(<StatusBadge status="refunded" />);
      expect(screen.getByText('Refunded')).toHaveClass('bg-blue-100');
    });
  });

  describe('market statuses', () => {
    it('renders open status', () => {
      render(<StatusBadge status="open" />);
      expect(screen.getByText('Open')).toHaveClass('bg-green-100');
    });

    it('renders closed status', () => {
      render(<StatusBadge status="closed" />);
      expect(screen.getByText('Closed')).toHaveClass('bg-slate-100');
    });

    it('renders booked status', () => {
      render(<StatusBadge status="booked" />);
      expect(screen.getByText('Booked')).toHaveClass('bg-blue-100');
    });
  });

  describe('tier badges', () => {
    it('renders free tier', () => {
      render(<StatusBadge status="free" />);
      expect(screen.getByText('Free')).toHaveClass('bg-slate-100');
    });

    it('renders pro tier', () => {
      render(<StatusBadge status="pro" />);
      expect(screen.getByText('Pro')).toHaveClass('bg-primary-100');
    });
  });

  describe('unknown status', () => {
    it('renders unknown status as-is with default variant', () => {
      render(<StatusBadge status="unknown_status" />);
      expect(screen.getByText('unknown_status')).toHaveClass('bg-slate-100');
    });
  });

  describe('case insensitivity', () => {
    it('handles uppercase status', () => {
      render(<StatusBadge status="PENDING" />);
      expect(screen.getByText('Pending')).toBeInTheDocument();
    });

    it('handles mixed case status', () => {
      render(<StatusBadge status="Completed" />);
      expect(screen.getByText('Completed')).toBeInTheDocument();
    });
  });
});

describe('TierBadge', () => {
  it('renders free tier with default variant', () => {
    render(<TierBadge tier="free" />);
    expect(screen.getByText('Free')).toHaveClass('bg-slate-100');
  });

  it('renders pro tier with primary variant', () => {
    render(<TierBadge tier="pro" />);
    expect(screen.getByText('Pro')).toHaveClass('bg-primary-100');
  });

  it('applies custom className', () => {
    render(<TierBadge tier="pro" className="custom-class" />);
    expect(screen.getByText('Pro')).toHaveClass('custom-class');
  });
});

describe('LevelBadge', () => {
  it('renders bronze level', () => {
    render(<LevelBadge level="bronze" />);
    expect(screen.getByText('Bronze')).toHaveClass('bg-slate-100');
  });

  it('renders silver level', () => {
    render(<LevelBadge level="silver" />);
    expect(screen.getByText('Silver')).toHaveClass('bg-slate-100');
  });

  it('renders gold level with warning variant', () => {
    render(<LevelBadge level="gold" />);
    expect(screen.getByText('Gold')).toHaveClass('bg-amber-100');
  });

  it('renders platinum level with info variant', () => {
    render(<LevelBadge level="platinum" />);
    expect(screen.getByText('Platinum')).toHaveClass('bg-blue-100');
  });

  it('applies custom className', () => {
    render(<LevelBadge level="gold" className="custom-class" />);
    expect(screen.getByText('Gold')).toHaveClass('custom-class');
  });
});

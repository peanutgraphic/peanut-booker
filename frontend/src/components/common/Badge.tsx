import { clsx } from 'clsx';
import type { ReactNode } from 'react';

interface BadgeProps {
  children: ReactNode;
  variant?: 'default' | 'primary' | 'success' | 'warning' | 'danger' | 'info';
  size?: 'sm' | 'md';
  className?: string;
}

export default function Badge({
  children,
  variant = 'default',
  size = 'md',
  className,
}: BadgeProps) {
  const variants = {
    default: 'bg-slate-100 text-slate-700',
    primary: 'bg-primary-100 text-primary-700',
    success: 'bg-green-100 text-green-700',
    warning: 'bg-amber-100 text-amber-700',
    danger: 'bg-red-100 text-red-700',
    info: 'bg-blue-100 text-blue-700',
  };

  const sizes = {
    sm: 'px-2 py-0.5 text-xs',
    md: 'px-2.5 py-1 text-xs',
  };

  return (
    <span
      className={clsx(
        'inline-flex items-center font-medium rounded-full',
        variants[variant],
        sizes[size],
        className
      )}
    >
      {children}
    </span>
  );
}

// Status badge for common status values
interface StatusBadgeProps {
  status: string;
  className?: string;
}

export function StatusBadge({ status, className }: StatusBadgeProps) {
  const statusConfig: Record<string, { variant: BadgeProps['variant']; label: string }> = {
    // Booking statuses
    pending: { variant: 'warning', label: 'Pending' },
    confirmed: { variant: 'info', label: 'Confirmed' },
    completed: { variant: 'success', label: 'Completed' },
    cancelled: { variant: 'danger', label: 'Cancelled' },

    // Performer statuses
    active: { variant: 'success', label: 'Active' },
    inactive: { variant: 'default', label: 'Inactive' },
    suspended: { variant: 'danger', label: 'Suspended' },

    // Escrow statuses
    held: { variant: 'warning', label: 'Held' },
    released: { variant: 'success', label: 'Released' },
    refunded: { variant: 'info', label: 'Refunded' },

    // Market statuses
    open: { variant: 'success', label: 'Open' },
    closed: { variant: 'default', label: 'Closed' },
    booked: { variant: 'info', label: 'Booked' },

    // Tier badges
    free: { variant: 'default', label: 'Free' },
    pro: { variant: 'primary', label: 'Pro' },

    // Achievement levels
    bronze: { variant: 'default', label: 'Bronze' },
    silver: { variant: 'default', label: 'Silver' },
    gold: { variant: 'warning', label: 'Gold' },
    platinum: { variant: 'info', label: 'Platinum' },
  };

  const config = statusConfig[status.toLowerCase()] || {
    variant: 'default' as const,
    label: status,
  };

  return (
    <Badge variant={config.variant} className={className}>
      {config.label}
    </Badge>
  );
}

// Tier badge with special styling
interface TierBadgeProps {
  tier: 'free' | 'pro';
  className?: string;
}

export function TierBadge({ tier, className }: TierBadgeProps) {
  return (
    <Badge
      variant={tier === 'pro' ? 'primary' : 'default'}
      className={className}
    >
      {tier === 'pro' ? 'Pro' : 'Free'}
    </Badge>
  );
}

// Achievement level badge
interface LevelBadgeProps {
  level: 'bronze' | 'silver' | 'gold' | 'platinum';
  className?: string;
}

export function LevelBadge({ level, className }: LevelBadgeProps) {
  const levelConfig = {
    bronze: { variant: 'default' as const, label: 'Bronze' },
    silver: { variant: 'default' as const, label: 'Silver' },
    gold: { variant: 'warning' as const, label: 'Gold' },
    platinum: { variant: 'info' as const, label: 'Platinum' },
  };

  const config = levelConfig[level];

  return (
    <Badge variant={config.variant} className={className}>
      {config.label}
    </Badge>
  );
}

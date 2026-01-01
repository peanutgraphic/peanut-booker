import { type ReactNode } from 'react';
import { clsx } from 'clsx';
import { Users, Calendar, Star, DollarSign, ShoppingBag, AlertCircle, Globe, MessageSquare, BarChart3 } from 'lucide-react';
import Button from './Button';

interface EmptyStateProps {
  icon?: ReactNode;
  title: string;
  description?: string;
  tips?: string[];
  action?: {
    label: string;
    onClick: () => void;
  };
  className?: string;
}

export default function EmptyState({
  icon,
  title,
  description,
  tips,
  action,
  className,
}: EmptyStateProps) {
  return (
    <div className={clsx('flex flex-col items-center justify-center py-12 text-center', className)}>
      {icon && (
        <div className="w-12 h-12 bg-slate-100 rounded-full flex items-center justify-center mb-4 text-slate-400">
          {icon}
        </div>
      )}
      <h3 className="text-lg font-semibold text-slate-900 mb-1">{title}</h3>
      {description && (
        <p className="text-sm text-slate-500 max-w-sm mb-4">{description}</p>
      )}
      {tips && tips.length > 0 && (
        <ul className="text-xs text-slate-400 max-w-md mb-4 space-y-1">
          {tips.map((tip, index) => (
            <li key={index}>{tip}</li>
          ))}
        </ul>
      )}
      {action && (
        <Button onClick={action.onClick}>{action.label}</Button>
      )}
    </div>
  );
}

// Skeleton loader
interface SkeletonProps {
  className?: string;
}

export function Skeleton({ className }: SkeletonProps) {
  return (
    <div className={clsx('bg-slate-200 rounded animate-pulse', className)} />
  );
}

// Pre-defined empty states for common scenarios
export const emptyStates = {
  performers: {
    icon: <Users className="w-6 h-6" />,
    title: 'No performers found',
    description: 'Performers will appear here once they sign up and create profiles.',
    tips: [
      'Performers can register through your website frontend',
      'Use Demo Mode to generate sample performer data for testing',
    ],
  },
  bookings: {
    icon: <Calendar className="w-6 h-6" />,
    title: 'No bookings yet',
    description: 'Bookings will appear here once customers start booking performers.',
    tips: [
      'Customers can book performers directly from performer profiles',
      'Bookings go through: Pending > Confirmed > Completed',
    ],
  },
  reviews: {
    icon: <Star className="w-6 h-6" />,
    title: 'No reviews yet',
    description: 'Reviews will appear here after bookings are completed.',
    tips: [
      'Customers can leave reviews after a booking is marked complete',
      'Flagged reviews will need your arbitration',
    ],
  },
  payouts: {
    icon: <DollarSign className="w-6 h-6" />,
    title: 'No pending payouts',
    description: 'All performer payouts have been processed.',
    tips: [
      'Payouts become available after bookings are completed',
      'Auto-release happens after the configured escrow period',
    ],
  },
  market: {
    icon: <ShoppingBag className="w-6 h-6" />,
    title: 'No market events',
    description: 'Market events will appear here when customers post event requests.',
    tips: [
      'Customers can post events to receive bids from performers',
      'Events go through: Open > Bidding > Booked > Completed',
    ],
  },
  microsites: {
    icon: <Globe className="w-6 h-6" />,
    title: 'No microsites yet',
    description: 'Microsites are landing pages for performers.',
    tips: [
      'Microsites are created when performers upgrade to Pro tier',
      'Each microsite has a customizable design and unique URL',
    ],
  },
  messages: {
    icon: <MessageSquare className="w-6 h-6" />,
    title: 'No messages',
    description: 'Messages between customers and performers will appear here.',
    tips: [
      'Messages are linked to bookings and inquiries',
      'You can view but not edit message content',
    ],
  },
  customers: {
    icon: <Users className="w-6 h-6" />,
    title: 'No customers yet',
    description: 'Customers will appear here once they register on your site.',
    tips: [
      'Customers can register when making their first booking',
      'Customer data includes contact info and booking history',
    ],
  },
  analytics: {
    icon: <BarChart3 className="w-6 h-6" />,
    title: 'No data available',
    description: 'Analytics data will populate as your platform grows.',
    tips: [
      'Revenue, bookings, and performance metrics are tracked automatically',
      'Data updates in real-time as transactions occur',
    ],
  },
  search: {
    icon: <AlertCircle className="w-6 h-6" />,
    title: 'No results found',
    description: 'Try adjusting your search or filter criteria.',
  },
  error: {
    icon: <AlertCircle className="w-6 h-6" />,
    title: 'Something went wrong',
    description: 'Failed to load data. Please try again.',
  },
};

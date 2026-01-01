import { useQuery } from '@tanstack/react-query';
import { useNavigate } from 'react-router-dom';
import { Layout } from '@/components/layout';
import { Card, CardHeader, StatCard, HelpCard } from '@/components/common';
import { dashboardApi } from '@/api';
import {
  Users,
  Calendar,
  DollarSign,
  AlertTriangle,
  ArrowRight,
  Clock,
  CheckCircle,
  BookOpen,
  Lightbulb,
} from 'lucide-react';

export default function Dashboard() {
  const navigate = useNavigate();

  const { data: stats, isLoading } = useQuery({
    queryKey: ['dashboard-stats'],
    queryFn: dashboardApi.getStats,
  });

  const quickActions = [
    { label: 'View Performers', href: '/performers', icon: Users },
    { label: 'View Bookings', href: '/bookings', icon: Calendar },
    { label: 'Process Payouts', href: '/payouts', icon: DollarSign },
    { label: 'Review Flags', href: '/reviews', icon: AlertTriangle },
  ];

  return (
    <Layout title="Dashboard" description="Overview of your booking platform">
      {/* Demo Mode Banner */}
      {stats?.demo_mode && (
        <div className="mb-6 p-4 bg-amber-50 border border-amber-200 rounded-lg flex items-center gap-3">
          <AlertTriangle className="w-5 h-5 text-amber-600" />
          <p className="text-sm text-amber-800">
            <strong>Demo Mode Active</strong> - You're viewing sample data.
            <button
              onClick={() => navigate('/demo')}
              className="ml-2 underline hover:no-underline"
            >
              Manage Demo Mode
            </button>
          </p>
        </div>
      )}

      {/* Stats Grid */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4 mb-8">
        <StatCard
          title="Total Performers"
          value={isLoading ? '...' : stats?.total_performers ?? 0}
          icon={<Users className="w-5 h-5" />}
        />
        <StatCard
          title="Total Bookings"
          value={isLoading ? '...' : stats?.total_bookings ?? 0}
          icon={<Calendar className="w-5 h-5" />}
        />
        <StatCard
          title="Pending Bookings"
          value={isLoading ? '...' : stats?.pending_bookings ?? 0}
          icon={<Clock className="w-5 h-5" />}
        />
        <StatCard
          title="Total Revenue"
          value={isLoading ? '...' : `$${(stats?.total_revenue ?? 0).toLocaleString()}`}
          icon={<DollarSign className="w-5 h-5" />}
        />
        <StatCard
          title="Platform Commission"
          value={isLoading ? '...' : `$${(stats?.platform_commission ?? 0).toLocaleString()}`}
          icon={<CheckCircle className="w-5 h-5" />}
        />
        <StatCard
          title="Reviews to Arbitrate"
          value={isLoading ? '...' : stats?.reviews_needing_arbitration ?? 0}
          icon={<AlertTriangle className="w-5 h-5" />}
        />
      </div>

      {/* Quick Actions */}
      <Card className="mb-6">
        <CardHeader
          title="Quick Actions"
          description="Common tasks you can perform from here"
        />
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
          {quickActions.map((action) => (
            <button
              key={action.href}
              onClick={() => navigate(action.href)}
              className="flex items-center justify-between p-4 bg-slate-50 hover:bg-slate-100 rounded-lg transition-colors group"
            >
              <div className="flex items-center gap-3">
                <div className="w-10 h-10 bg-white rounded-lg flex items-center justify-center shadow-sm">
                  <action.icon className="w-5 h-5 text-primary-600" />
                </div>
                <span className="font-medium text-slate-900">{action.label}</span>
              </div>
              <ArrowRight className="w-4 h-4 text-slate-400 group-hover:text-slate-600 transition-colors" />
            </button>
          ))}
        </div>
      </Card>

      {/* Getting Started / How To */}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <HelpCard title="Getting Started" icon={<BookOpen className="w-5 h-5" />}>
          <ol className="list-decimal list-inside space-y-2">
            <li><strong>Configure Settings</strong> - Set your commission rates, escrow periods, and achievement thresholds</li>
            <li><strong>Enable Demo Mode</strong> - Generate sample data to explore the platform features</li>
            <li><strong>Review Performers</strong> - Verify and feature top performers to boost visibility</li>
            <li><strong>Process Payouts</strong> - Release funds to performers after successful bookings</li>
          </ol>
        </HelpCard>

        <HelpCard title="Platform Tips" icon={<Lightbulb className="w-5 h-5" />}>
          <ul className="space-y-2">
            <li><strong>Booking Flow:</strong> Pending → Confirmed → Completed → Payout Released</li>
            <li><strong>Market Events:</strong> Customers post events, performers bid, best bid wins</li>
            <li><strong>Tier System:</strong> Free tier pays higher commission, Pro tier gets microsites</li>
            <li><strong>Escrow:</strong> Funds are held until booking completion + escrow period</li>
          </ul>
        </HelpCard>
      </div>
    </Layout>
  );
}

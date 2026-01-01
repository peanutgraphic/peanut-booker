import { useQuery } from '@tanstack/react-query';
import { Layout } from '@/components/layout';
import {
  Card,
  CardHeader,
  StatCard,
  EmptyState,
  emptyStates,
  HelpCard,
} from '@/components/common';
import { analyticsApi, type AnalyticsData } from '@/api/endpoints';
import {
  BarChart3,
  TrendingUp,
  TrendingDown,
  DollarSign,
  Calendar,
  Users,
  Star,
  CheckCircle,
  Clock,
  XCircle,
} from 'lucide-react';
import { format } from 'date-fns';

export default function Analytics() {
  const { data, isLoading, isError } = useQuery({
    queryKey: ['analytics'],
    queryFn: analyticsApi.getOverview,
  });

  if (isError) {
    return (
      <Layout title="Analytics" description="Platform performance and insights">
        <Card>
          <EmptyState {...emptyStates.error} />
        </Card>
      </Layout>
    );
  }

  if (isLoading) {
    return (
      <Layout title="Analytics" description="Platform performance and insights">
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
          {[1, 2, 3, 4].map((i) => (
            <Card key={i}>
              <div className="animate-pulse space-y-3">
                <div className="h-4 bg-slate-200 rounded w-1/2" />
                <div className="h-8 bg-slate-200 rounded w-3/4" />
              </div>
            </Card>
          ))}
        </div>
      </Layout>
    );
  }

  const analytics = data as AnalyticsData;

  return (
    <Layout title="Analytics" description="Platform performance and insights">
      {/* Help Section */}
      <HelpCard
        title="Understanding Analytics"
        icon={<BarChart3 className="w-5 h-5" />}
        className="mb-6"
      >
        <p>
          This dashboard provides an overview of your platform's performance. Revenue, booking
          completion rates, and performer metrics are updated in real-time. Use these insights
          to make informed decisions about pricing, marketing, and platform improvements.
        </p>
      </HelpCard>

      {/* Revenue Stats */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <StatCard
          title="Total Revenue"
          value={`$${analytics.revenue.total.toLocaleString()}`}
          icon={<DollarSign className="w-5 h-5" />}
        />
        <StatCard
          title="This Month"
          value={`$${analytics.revenue.this_month.toLocaleString()}`}
          icon={<Calendar className="w-5 h-5" />}
          change={
            analytics.revenue.growth_percentage !== 0
              ? {
                  value: Math.abs(analytics.revenue.growth_percentage),
                  type: analytics.revenue.growth_percentage > 0 ? 'increase' : 'decrease',
                }
              : undefined
          }
        />
        <StatCard
          title="Last Month"
          value={`$${analytics.revenue.last_month.toLocaleString()}`}
          icon={<Calendar className="w-5 h-5" />}
        />
        <StatCard
          title="Growth"
          value={`${analytics.revenue.growth_percentage > 0 ? '+' : ''}${analytics.revenue.growth_percentage}%`}
          icon={
            analytics.revenue.growth_percentage >= 0 ? (
              <TrendingUp className="w-5 h-5" />
            ) : (
              <TrendingDown className="w-5 h-5" />
            )
          }
        />
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        {/* Booking Stats */}
        <Card>
          <CardHeader
            title="Booking Statistics"
            description="Overview of booking performance"
          />
          <div className="grid grid-cols-2 gap-4">
            <div className="p-4 bg-slate-50 rounded-lg">
              <div className="flex items-center gap-2 mb-1">
                <Calendar className="w-4 h-4 text-slate-500" />
                <span className="text-sm text-slate-600">Total Bookings</span>
              </div>
              <p className="text-2xl font-bold text-slate-900">
                {analytics.bookings.total.toLocaleString()}
              </p>
            </div>
            <div className="p-4 bg-green-50 rounded-lg">
              <div className="flex items-center gap-2 mb-1">
                <CheckCircle className="w-4 h-4 text-green-500" />
                <span className="text-sm text-green-700">Completed</span>
              </div>
              <p className="text-2xl font-bold text-green-700">
                {analytics.bookings.completed.toLocaleString()}
              </p>
            </div>
            <div className="p-4 bg-amber-50 rounded-lg">
              <div className="flex items-center gap-2 mb-1">
                <Clock className="w-4 h-4 text-amber-500" />
                <span className="text-sm text-amber-700">Pending</span>
              </div>
              <p className="text-2xl font-bold text-amber-700">
                {analytics.bookings.pending.toLocaleString()}
              </p>
            </div>
            <div className="p-4 bg-red-50 rounded-lg">
              <div className="flex items-center gap-2 mb-1">
                <XCircle className="w-4 h-4 text-red-500" />
                <span className="text-sm text-red-700">Cancelled</span>
              </div>
              <p className="text-2xl font-bold text-red-700">
                {analytics.bookings.cancelled.toLocaleString()}
              </p>
            </div>
          </div>
          <div className="mt-4 p-4 bg-primary-50 rounded-lg">
            <div className="flex items-center justify-between">
              <span className="text-sm font-medium text-primary-700">Completion Rate</span>
              <span className="text-lg font-bold text-primary-700">
                {analytics.bookings.completion_rate}%
              </span>
            </div>
            <div className="mt-2 bg-primary-200 rounded-full h-2">
              <div
                className="bg-primary-600 rounded-full h-2 transition-all"
                style={{ width: `${analytics.bookings.completion_rate}%` }}
              />
            </div>
          </div>
        </Card>

        {/* Performer & Customer Stats */}
        <Card>
          <CardHeader
            title="User Statistics"
            description="Performers and customers on your platform"
          />
          <div className="space-y-4">
            <div className="p-4 border border-slate-200 rounded-lg">
              <h4 className="font-medium text-slate-900 mb-3 flex items-center gap-2">
                <Users className="w-4 h-4" />
                Performers
              </h4>
              <div className="grid grid-cols-2 gap-3">
                <div>
                  <p className="text-sm text-slate-500">Total</p>
                  <p className="text-xl font-bold">{analytics.performers.total}</p>
                </div>
                <div>
                  <p className="text-sm text-slate-500">Active</p>
                  <p className="text-xl font-bold text-green-600">{analytics.performers.active}</p>
                </div>
                <div>
                  <p className="text-sm text-slate-500">Verified</p>
                  <p className="text-xl font-bold text-blue-600">{analytics.performers.verified}</p>
                </div>
                <div>
                  <p className="text-sm text-slate-500">Pro Tier</p>
                  <p className="text-xl font-bold text-purple-600">{analytics.performers.pro_tier}</p>
                </div>
              </div>
            </div>

            <div className="p-4 border border-slate-200 rounded-lg">
              <h4 className="font-medium text-slate-900 mb-3 flex items-center gap-2">
                <Users className="w-4 h-4" />
                Customers
              </h4>
              <div className="grid grid-cols-3 gap-3">
                <div>
                  <p className="text-sm text-slate-500">Total</p>
                  <p className="text-xl font-bold">{analytics.customers.total}</p>
                </div>
                <div>
                  <p className="text-sm text-slate-500">Active</p>
                  <p className="text-xl font-bold text-green-600">{analytics.customers.active}</p>
                </div>
                <div>
                  <p className="text-sm text-slate-500">New This Month</p>
                  <p className="text-xl font-bold text-blue-600">{analytics.customers.new_this_month}</p>
                </div>
              </div>
            </div>
          </div>
        </Card>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {/* Top Performers */}
        <Card>
          <CardHeader
            title="Top Performers"
            description="Highest earning performers this period"
          />
          {analytics.top_performers.length > 0 ? (
            <div className="space-y-3">
              {analytics.top_performers.map((performer, index) => (
                <div
                  key={performer.id}
                  className="flex items-center justify-between p-3 bg-slate-50 rounded-lg"
                >
                  <div className="flex items-center gap-3">
                    <span className="w-6 h-6 bg-primary-100 text-primary-600 rounded-full flex items-center justify-center text-sm font-bold">
                      {index + 1}
                    </span>
                    <div>
                      <p className="font-medium text-slate-900">{performer.name}</p>
                      <p className="text-sm text-slate-500">
                        {performer.bookings} bookings
                      </p>
                    </div>
                  </div>
                  <div className="text-right">
                    <p className="font-medium text-green-600">
                      ${performer.revenue.toLocaleString()}
                    </p>
                    <div className="flex items-center gap-1 text-sm text-amber-500">
                      <Star className="w-3 h-3 fill-current" />
                      {performer.rating.toFixed(1)}
                    </div>
                  </div>
                </div>
              ))}
            </div>
          ) : (
            <p className="text-slate-500 text-center py-4">No performer data yet</p>
          )}
        </Card>

        {/* Recent Activity */}
        <Card>
          <CardHeader
            title="Recent Activity"
            description="Latest platform activity"
          />
          {analytics.recent_activity.length > 0 ? (
            <div className="space-y-3">
              {analytics.recent_activity.map((activity) => (
                <div
                  key={activity.id}
                  className="flex items-center justify-between p-3 border-b border-slate-100 last:border-0"
                >
                  <div className="flex items-center gap-3">
                    <div
                      className={`w-8 h-8 rounded-full flex items-center justify-center ${
                        activity.type === 'booking'
                          ? 'bg-blue-100 text-blue-600'
                          : activity.type === 'review'
                          ? 'bg-amber-100 text-amber-600'
                          : 'bg-green-100 text-green-600'
                      }`}
                    >
                      {activity.type === 'booking' ? (
                        <Calendar className="w-4 h-4" />
                      ) : activity.type === 'review' ? (
                        <Star className="w-4 h-4" />
                      ) : (
                        <DollarSign className="w-4 h-4" />
                      )}
                    </div>
                    <div>
                      <p className="text-sm text-slate-900">{activity.description}</p>
                      <p className="text-xs text-slate-500">
                        {format(new Date(activity.created_at), 'MMM d, h:mm a')}
                      </p>
                    </div>
                  </div>
                  {activity.amount && (
                    <span className="font-medium text-green-600">
                      ${activity.amount.toLocaleString()}
                    </span>
                  )}
                </div>
              ))}
            </div>
          ) : (
            <p className="text-slate-500 text-center py-4">No recent activity</p>
          )}
        </Card>
      </div>
    </Layout>
  );
}

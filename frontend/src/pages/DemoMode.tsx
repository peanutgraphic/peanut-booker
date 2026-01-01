import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { Layout } from '@/components/layout';
import {
  Card,
  CardHeader,
  StatCard,
  Button,
  ConfirmModal,
  useToast,
} from '@/components/common';
import { demoMode as helpContent } from '@/constants/helpContent';
import { demoApi } from '@/api';
import { FlaskConical, Users, Calendar, Star, ShoppingBag, MessageSquare, AlertTriangle } from 'lucide-react';
import { useState } from 'react';

export default function DemoMode() {
  const toast = useToast();
  const queryClient = useQueryClient();
  const [disableModal, setDisableModal] = useState(false);

  const { data, isLoading } = useQuery({
    queryKey: ['demo-status'],
    queryFn: demoApi.getStatus,
  });

  const enableMutation = useMutation({
    mutationFn: demoApi.enable,
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['demo-status'] });
      queryClient.invalidateQueries({ queryKey: ['dashboard-stats'] });
      toast.success('Demo mode enabled with sample data');
    },
    onError: () => {
      toast.error('Failed to enable demo mode');
    },
  });

  const disableMutation = useMutation({
    mutationFn: demoApi.disable,
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['demo-status'] });
      queryClient.invalidateQueries({ queryKey: ['dashboard-stats'] });
      toast.success('Demo mode disabled and data cleaned up');
      setDisableModal(false);
    },
    onError: () => {
      toast.error('Failed to disable demo mode');
    },
  });

  const stats = data?.stats || {};

  return (
    <Layout title="Demo Mode" description="Generate sample data for testing" helpContent={helpContent.helpCard}>
      {/* Status Banner */}
      {data?.enabled && (
        <div className="mb-6 p-4 bg-amber-50 border border-amber-200 rounded-lg flex items-center gap-3">
          <AlertTriangle className="w-5 h-5 text-amber-600" />
          <p className="text-sm text-amber-800 flex-1">
            <strong>Demo Mode is Active</strong> - Sample data is being displayed.
            Disable demo mode before going to production.
          </p>
        </div>
      )}

      {/* Demo Controls */}
      <Card className="mb-6">
        <CardHeader
          title="Demo Mode Controls"
          description="Enable demo mode to populate your plugin with realistic sample data for testing and demonstrations."
        />

        <div className="flex items-center gap-4">
          {!data?.enabled ? (
            <Button
              onClick={() => enableMutation.mutate()}
              loading={enableMutation.isPending}
              icon={<FlaskConical className="w-4 h-4" />}
            >
              Enable Demo Mode
            </Button>
          ) : (
            <Button
              variant="danger"
              onClick={() => setDisableModal(true)}
              loading={disableMutation.isPending}
            >
              Disable Demo Mode
            </Button>
          )}
        </div>
      </Card>

      {/* Demo Data Summary */}
      {data?.enabled && (
        <div>
          <h2 className="text-lg font-semibold text-slate-900 mb-4">Demo Data Summary</h2>
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <StatCard
              title="Demo Performers"
              value={isLoading ? '...' : stats.performers || 0}
              icon={<Users className="w-5 h-5" />}
            />
            <StatCard
              title="Demo Customers"
              value={isLoading ? '...' : stats.customers || 0}
              icon={<Users className="w-5 h-5" />}
            />
            <StatCard
              title="Demo Bookings"
              value={isLoading ? '...' : stats.bookings || 0}
              icon={<Calendar className="w-5 h-5" />}
            />
            <StatCard
              title="Demo Reviews"
              value={isLoading ? '...' : stats.reviews || 0}
              icon={<Star className="w-5 h-5" />}
            />
            <StatCard
              title="Demo Market Events"
              value={isLoading ? '...' : stats.events || 0}
              icon={<ShoppingBag className="w-5 h-5" />}
            />
            <StatCard
              title="Demo Bids"
              value={isLoading ? '...' : stats.bids || 0}
              icon={<MessageSquare className="w-5 h-5" />}
            />
          </div>
        </div>
      )}

      {/* Info Card when disabled */}
      {!data?.enabled && (
        <Card className="bg-slate-50">
          <div className="text-center py-8">
            <FlaskConical className="w-12 h-12 text-slate-400 mx-auto mb-4" />
            <h3 className="text-lg font-semibold text-slate-900 mb-2">
              Demo Mode is Disabled
            </h3>
            <p className="text-slate-500 max-w-md mx-auto">
              Enable demo mode to populate your plugin with sample performers,
              bookings, reviews, and market events. This is useful for testing
              and demonstrations.
            </p>
          </div>
        </Card>
      )}

      {/* Disable Confirmation */}
      <ConfirmModal
        isOpen={disableModal}
        onClose={() => setDisableModal(false)}
        onConfirm={() => disableMutation.mutate()}
        title="Disable Demo Mode"
        message="This will delete ALL demo data including performers, customers, bookings, reviews, and market events. This action cannot be undone. Are you sure?"
        confirmText="Disable & Delete Data"
        variant="danger"
        loading={disableMutation.isPending}
      />
    </Layout>
  );
}

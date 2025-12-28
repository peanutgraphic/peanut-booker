import { useState } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { Layout } from '@/components/layout';
import {
  Card,
  CardHeader,
  Button,
  Input,
  Textarea,
  TierBadge,
  LevelBadge,
  useToast,
} from '@/components/common';
import { performersApi } from '@/api';
import type { Performer } from '@/types';
import { ArrowLeft, Save } from 'lucide-react';
import { clsx } from 'clsx';

type TabId = 'basic' | 'photos' | 'pricing' | 'location' | 'categories' | 'bio';

const tabs: { id: TabId; label: string }[] = [
  { id: 'basic', label: 'Basic Info' },
  { id: 'photos', label: 'Photos & Videos' },
  { id: 'pricing', label: 'Pricing' },
  { id: 'location', label: 'Location & Travel' },
  { id: 'categories', label: 'Categories' },
  { id: 'bio', label: 'Bio' },
];

export default function PerformerEditor() {
  const { id } = useParams<{ id: string }>();
  const navigate = useNavigate();
  const toast = useToast();
  const queryClient = useQueryClient();
  const [activeTab, setActiveTab] = useState<TabId>('basic');
  const [formData, setFormData] = useState<Partial<Performer>>({});

  const { data: performer, isLoading } = useQuery({
    queryKey: ['performer', id],
    queryFn: () => performersApi.getById(Number(id)),
    enabled: !!id,
  });

  const updateMutation = useMutation({
    mutationFn: (data: Partial<Performer>) => performersApi.update(Number(id), data),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['performer', id] });
      queryClient.invalidateQueries({ queryKey: ['performers'] });
      toast.success('Performer updated successfully');
    },
    onError: () => {
      toast.error('Failed to update performer');
    },
  });

  const handleChange = (field: keyof Performer, value: unknown) => {
    setFormData((prev) => ({ ...prev, [field]: value }));
  };

  const handleSave = () => {
    updateMutation.mutate(formData);
  };

  if (isLoading) {
    return (
      <Layout title="Edit Performer">
        <Card>
          <div className="animate-pulse space-y-4">
            <div className="h-8 bg-slate-200 rounded w-1/4" />
            <div className="h-4 bg-slate-200 rounded w-1/2" />
            <div className="h-32 bg-slate-200 rounded" />
          </div>
        </Card>
      </Layout>
    );
  }

  if (!performer) {
    return (
      <Layout title="Edit Performer">
        <Card>
          <p className="text-slate-500">Performer not found</p>
        </Card>
      </Layout>
    );
  }

  const currentData = { ...performer, ...formData };

  return (
    <Layout title="Edit Performer" description={performer.stage_name}>
      {/* Header */}
      <div className="flex items-center justify-between mb-6">
        <div className="flex items-center gap-4">
          <Button
            variant="ghost"
            onClick={() => navigate('/performers')}
            icon={<ArrowLeft className="w-4 h-4" />}
          >
            Back
          </Button>
          <div className="flex items-center gap-2">
            <TierBadge tier={performer.tier} />
            <LevelBadge level={performer.achievement_level} />
            {performer.is_verified && (
              <span className="px-2 py-0.5 bg-green-100 text-green-700 text-xs font-medium rounded-full">
                Verified
              </span>
            )}
          </div>
        </div>
        <Button
          onClick={handleSave}
          loading={updateMutation.isPending}
          icon={<Save className="w-4 h-4" />}
        >
          Save Changes
        </Button>
      </div>

      {/* Tabs */}
      <div className="flex gap-1 mb-6 border-b border-slate-200">
        {tabs.map((tab) => (
          <button
            key={tab.id}
            onClick={() => setActiveTab(tab.id)}
            className={clsx(
              'px-4 py-2 text-sm font-medium border-b-2 -mb-px transition-colors',
              activeTab === tab.id
                ? 'border-primary-600 text-primary-600'
                : 'border-transparent text-slate-500 hover:text-slate-700'
            )}
          >
            {tab.label}
          </button>
        ))}
      </div>

      {/* Tab Content */}
      <Card>
        {activeTab === 'basic' && (
          <div className="space-y-6">
            <CardHeader title="Basic Information" />
            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
              <Input
                label="Stage Name"
                value={currentData.stage_name || ''}
                onChange={(e) => handleChange('stage_name', e.target.value)}
              />
              <Input
                label="Tagline"
                value={currentData.tagline || ''}
                onChange={(e) => handleChange('tagline', e.target.value)}
              />
              <Input
                label="Years of Experience"
                type="number"
                value={currentData.experience_years || ''}
                onChange={(e) => handleChange('experience_years', Number(e.target.value))}
              />
              <Input
                label="Website"
                type="url"
                value={currentData.website || ''}
                onChange={(e) => handleChange('website', e.target.value)}
              />
              <Input
                label="Phone"
                type="tel"
                value={currentData.phone || ''}
                onChange={(e) => handleChange('phone', e.target.value)}
              />
              <Input
                label="Public Email"
                type="email"
                value={currentData.email_public || ''}
                onChange={(e) => handleChange('email_public', e.target.value)}
              />
            </div>
          </div>
        )}

        {activeTab === 'photos' && (
          <div className="space-y-6">
            <CardHeader
              title="Photos & Videos"
              description="Manage performer gallery and video links"
            />
            <p className="text-slate-500 text-sm">
              Photo and video management coming soon...
            </p>
          </div>
        )}

        {activeTab === 'pricing' && (
          <div className="space-y-6">
            <CardHeader title="Pricing" />
            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
              <Input
                label="Hourly Rate ($)"
                type="number"
                value={currentData.hourly_rate || ''}
                onChange={(e) => handleChange('hourly_rate', Number(e.target.value))}
              />
              <Input
                label="Minimum Hours"
                type="number"
                value={currentData.minimum_booking || ''}
                onChange={(e) => handleChange('minimum_booking', Number(e.target.value))}
              />
              <Input
                label="Deposit Percentage (%)"
                type="number"
                value={currentData.deposit_percentage || ''}
                onChange={(e) => handleChange('deposit_percentage', Number(e.target.value))}
              />
              <div className="flex items-center gap-4">
                <Input
                  label="Sale Price ($)"
                  type="number"
                  value={currentData.sale_price || ''}
                  onChange={(e) => handleChange('sale_price', Number(e.target.value))}
                  className="flex-1"
                />
                <label className="flex items-center gap-2 mt-6">
                  <input
                    type="checkbox"
                    checked={currentData.sale_active || false}
                    onChange={(e) => handleChange('sale_active', e.target.checked)}
                    className="w-4 h-4 rounded border-slate-300 text-primary-600 focus:ring-primary-500"
                  />
                  <span className="text-sm text-slate-700">Sale Active</span>
                </label>
              </div>
            </div>
          </div>
        )}

        {activeTab === 'location' && (
          <div className="space-y-6">
            <CardHeader title="Location & Travel" />
            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
              <Input
                label="City"
                value={currentData.location_city || ''}
                onChange={(e) => handleChange('location_city', e.target.value)}
              />
              <Input
                label="State"
                value={currentData.location_state || ''}
                onChange={(e) => handleChange('location_state', e.target.value)}
              />
              <label className="flex items-center gap-2">
                <input
                  type="checkbox"
                  checked={currentData.travel_willing || false}
                  onChange={(e) => handleChange('travel_willing', e.target.checked)}
                  className="w-4 h-4 rounded border-slate-300 text-primary-600 focus:ring-primary-500"
                />
                <span className="text-sm text-slate-700">Willing to Travel</span>
              </label>
              <Input
                label="Travel Radius (miles)"
                type="number"
                value={currentData.travel_radius || ''}
                onChange={(e) => handleChange('travel_radius', Number(e.target.value))}
                disabled={!currentData.travel_willing}
              />
            </div>
          </div>
        )}

        {activeTab === 'categories' && (
          <div className="space-y-6">
            <CardHeader
              title="Categories & Service Areas"
              description="Select performer categories and service areas"
            />
            <p className="text-slate-500 text-sm">
              Category and service area selection coming soon...
            </p>
          </div>
        )}

        {activeTab === 'bio' && (
          <div className="space-y-6">
            <CardHeader title="Bio" description="Performer biography and description" />
            <Textarea
              rows={10}
              placeholder="Enter performer bio..."
              className="w-full"
            />
          </div>
        )}
      </Card>
    </Layout>
  );
}

import { useState, useEffect } from 'react';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { Layout } from '@/components/layout';
import {
  Card,
  CardHeader,
  Button,
  Input,
  useToast,
  Badge,
} from '@/components/common';
import { settingsApi } from '@/api';
import type { Settings as SettingsType } from '@/types';
import { Save, Key, Check } from 'lucide-react';
import { clsx } from 'clsx';

type TabId = 'general' | 'license' | 'commission' | 'subscription' | 'booking' | 'achievements' | 'google';

const tabs: { id: TabId; label: string }[] = [
  { id: 'general', label: 'General' },
  { id: 'license', label: 'License' },
  { id: 'commission', label: 'Commission' },
  { id: 'subscription', label: 'Pro Subscription' },
  { id: 'booking', label: 'Booking' },
  { id: 'achievements', label: 'Achievements' },
  { id: 'google', label: 'Google Login' },
];

export default function Settings() {
  const toast = useToast();
  const queryClient = useQueryClient();
  const [activeTab, setActiveTab] = useState<TabId>('general');
  const [formData, setFormData] = useState<Partial<SettingsType>>({});
  const [licenseKey, setLicenseKey] = useState('');

  const { data: settings, isLoading } = useQuery({
    queryKey: ['settings'],
    queryFn: settingsApi.getAll,
  });

  useEffect(() => {
    if (settings) {
      setFormData(settings);
    }
  }, [settings]);

  const updateMutation = useMutation({
    mutationFn: (data: Partial<SettingsType>) => settingsApi.update(data),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['settings'] });
      toast.success('Settings saved');
    },
    onError: () => {
      toast.error('Failed to save settings');
    },
  });

  const activateLicenseMutation = useMutation({
    mutationFn: (key: string) => settingsApi.activateLicense(key),
    onSuccess: (data) => {
      queryClient.invalidateQueries({ queryKey: ['settings'] });
      toast.success(data.message || 'License activated');
      setLicenseKey('');
    },
    onError: (error: Error) => {
      toast.error(error.message || 'Failed to activate license');
    },
  });

  const deactivateLicenseMutation = useMutation({
    mutationFn: () => settingsApi.deactivateLicense(),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['settings'] });
      toast.success('License deactivated');
    },
    onError: () => {
      toast.error('Failed to deactivate license');
    },
  });

  const handleChange = (field: keyof SettingsType, value: unknown) => {
    setFormData((prev) => ({ ...prev, [field]: value }));
  };

  const handleSave = () => {
    updateMutation.mutate(formData);
  };

  if (isLoading) {
    return (
      <Layout title="Settings">
        <Card>
          <div className="animate-pulse space-y-4">
            <div className="h-8 bg-slate-200 rounded w-1/4" />
            <div className="h-32 bg-slate-200 rounded" />
          </div>
        </Card>
      </Layout>
    );
  }

  return (
    <Layout title="Settings" description="Configure Peanut Booker">
      {/* Tabs */}
      <div className="flex gap-1 mb-6 border-b border-slate-200 overflow-x-auto">
        {tabs.map((tab) => (
          <button
            key={tab.id}
            onClick={() => setActiveTab(tab.id)}
            className={clsx(
              'px-4 py-2 text-sm font-medium border-b-2 -mb-px transition-colors whitespace-nowrap',
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
        {activeTab === 'general' && (
          <div className="space-y-6">
            <CardHeader
              title="General Settings"
              description="Basic platform configuration options"
            />
            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
              <Input
                label="Currency"
                value={formData.currency || 'USD'}
                onChange={(e) => handleChange('currency', e.target.value)}
                hint="Currency code for all transactions (e.g., USD, EUR, GBP)"
              />
              <div>
                <label className="block text-sm font-medium text-slate-700 mb-1">
                  WooCommerce Status
                </label>
                <div className="flex items-center gap-2">
                  {formData.woocommerce_active ? (
                    <Badge variant="success">Active</Badge>
                  ) : (
                    <Badge variant="danger">Inactive</Badge>
                  )}
                </div>
                <p className="text-xs text-slate-500 mt-1">
                  {formData.woocommerce_active
                    ? 'WooCommerce is installed and active for payment processing'
                    : 'Install WooCommerce to enable payment processing'}
                </p>
              </div>
            </div>
          </div>
        )}

        {activeTab === 'license' && (
          <div className="space-y-6">
            <CardHeader
              title="License"
              description="Manage your Peanut Booker license"
            />
            {formData.license_status === 'active' ? (
              <div className="p-4 bg-green-50 border border-green-200 rounded-lg">
                <div className="flex items-center justify-between">
                  <div className="flex items-center gap-3">
                    <Check className="w-5 h-5 text-green-600" />
                    <div>
                      <p className="font-medium text-green-800">License Active</p>
                      {formData.license_expires && (
                        <p className="text-sm text-green-600">
                          Expires: {formData.license_expires}
                        </p>
                      )}
                    </div>
                  </div>
                  <Button
                    variant="outline"
                    size="sm"
                    onClick={() => deactivateLicenseMutation.mutate()}
                    loading={deactivateLicenseMutation.isPending}
                  >
                    Deactivate
                  </Button>
                </div>
              </div>
            ) : (
              <div className="space-y-4">
                <div className="flex gap-4">
                  <Input
                    label="License Key"
                    value={licenseKey}
                    onChange={(e) => setLicenseKey(e.target.value)}
                    placeholder="Enter your license key"
                    className="flex-1"
                  />
                  <div className="flex items-end">
                    <Button
                      onClick={() => activateLicenseMutation.mutate(licenseKey)}
                      loading={activateLicenseMutation.isPending}
                      disabled={!licenseKey}
                      icon={<Key className="w-4 h-4" />}
                    >
                      Activate
                    </Button>
                  </div>
                </div>
              </div>
            )}
          </div>
        )}

        {activeTab === 'commission' && (
          <div className="space-y-6">
            <CardHeader
              title="Commission Settings"
              description="Configure platform commission rates taken from each booking"
            />
            <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
              <Input
                label="Free Tier Commission (%)"
                type="number"
                value={formData.free_tier_commission || 0}
                onChange={(e) =>
                  handleChange('free_tier_commission', Number(e.target.value))
                }
                hint="Commission taken from performers on the free tier (typically higher)"
              />
              <Input
                label="Pro Tier Commission (%)"
                type="number"
                value={formData.pro_tier_commission || 0}
                onChange={(e) =>
                  handleChange('pro_tier_commission', Number(e.target.value))
                }
                hint="Reduced commission for Pro subscribers as a membership benefit"
              />
              <Input
                label="Flat Fee per Transaction ($)"
                type="number"
                value={formData.flat_fee_per_transaction || 0}
                onChange={(e) =>
                  handleChange('flat_fee_per_transaction', Number(e.target.value))
                }
                hint="Additional flat fee added to each transaction (optional)"
              />
            </div>
          </div>
        )}

        {activeTab === 'subscription' && (
          <div className="space-y-6">
            <CardHeader
              title="Pro Subscription Pricing"
              description="Set pricing for Pro tier subscriptions that give performers reduced commission and microsites"
            />
            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
              <Input
                label="Monthly Price ($)"
                type="number"
                value={formData.pro_monthly_price || 0}
                onChange={(e) =>
                  handleChange('pro_monthly_price', Number(e.target.value))
                }
                hint="Monthly subscription price for Pro tier membership"
              />
              <Input
                label="Annual Price ($)"
                type="number"
                value={formData.pro_annual_price || 0}
                onChange={(e) =>
                  handleChange('pro_annual_price', Number(e.target.value))
                }
                hint="Annual price (typically offers a discount over monthly)"
              />
            </div>
          </div>
        )}

        {activeTab === 'booking' && (
          <div className="space-y-6">
            <CardHeader
              title="Booking Settings"
              description="Configure deposit requirements and escrow release timing"
            />
            <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
              <Input
                label="Min Deposit (%)"
                type="number"
                value={formData.min_deposit_percentage || 0}
                onChange={(e) =>
                  handleChange('min_deposit_percentage', Number(e.target.value))
                }
                hint="Minimum deposit customers must pay upfront to confirm booking"
              />
              <Input
                label="Max Deposit (%)"
                type="number"
                value={formData.max_deposit_percentage || 0}
                onChange={(e) =>
                  handleChange('max_deposit_percentage', Number(e.target.value))
                }
                hint="Maximum deposit allowed (100% = full payment upfront)"
              />
              <Input
                label="Auto-Release Escrow (days)"
                type="number"
                value={formData.auto_release_escrow_days || 0}
                onChange={(e) =>
                  handleChange('auto_release_escrow_days', Number(e.target.value))
                }
                hint="Days after completion before funds are automatically released"
              />
            </div>
          </div>
        )}

        {activeTab === 'achievements' && (
          <div className="space-y-6">
            <CardHeader
              title="Achievement Thresholds"
              description="Set point thresholds for performer achievement levels (Bronze is the starting level)"
            />
            <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
              <Input
                label="Silver Threshold (points)"
                type="number"
                value={formData.silver_threshold || 0}
                onChange={(e) =>
                  handleChange('silver_threshold', Number(e.target.value))
                }
                hint="Points needed to reach Silver level from Bronze"
              />
              <Input
                label="Gold Threshold (points)"
                type="number"
                value={formData.gold_threshold || 0}
                onChange={(e) =>
                  handleChange('gold_threshold', Number(e.target.value))
                }
                hint="Points needed to reach Gold level"
              />
              <Input
                label="Platinum Threshold (points)"
                type="number"
                value={formData.platinum_threshold || 0}
                onChange={(e) =>
                  handleChange('platinum_threshold', Number(e.target.value))
                }
                hint="Points needed for the highest Platinum level"
              />
            </div>
            <p className="text-xs text-slate-500">
              Performers earn points from completed bookings and positive reviews. Higher levels can be displayed as badges on their profiles.
            </p>
          </div>
        )}

        {activeTab === 'google' && (
          <div className="space-y-6">
            <CardHeader
              title="Google Login"
              description="Allow users to sign in with their Google accounts"
            />
            <div className="grid grid-cols-1 gap-6">
              <Input
                label="Google Client ID"
                value={formData.google_client_id || ''}
                onChange={(e) => handleChange('google_client_id', e.target.value)}
                placeholder="Enter your Google Client ID"
                hint="Get this from Google Cloud Console > APIs & Services > Credentials"
              />
              <Input
                label="Google Client Secret"
                type="password"
                value={formData.google_client_secret || ''}
                onChange={(e) =>
                  handleChange('google_client_secret', e.target.value)
                }
                placeholder="Enter your Google Client Secret"
                hint="Keep this secret - never share or expose it publicly"
              />
            </div>
            <p className="text-xs text-slate-500">
              To set up Google Login, create OAuth credentials at{' '}
              <a
                href="https://console.cloud.google.com/apis/credentials"
                target="_blank"
                rel="noopener noreferrer"
                className="text-primary-600 hover:underline"
              >
                console.cloud.google.com
              </a>
            </p>
          </div>
        )}

        {/* Save Button */}
        {activeTab !== 'license' && (
          <div className="mt-6 pt-6 border-t border-slate-200 flex justify-end">
            <Button
              onClick={handleSave}
              loading={updateMutation.isPending}
              icon={<Save className="w-4 h-4" />}
            >
              Save Changes
            </Button>
          </div>
        )}
      </Card>
    </Layout>
  );
}

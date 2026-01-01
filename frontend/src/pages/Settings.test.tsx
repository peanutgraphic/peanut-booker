import { describe, it, expect, vi, beforeEach } from 'vitest';
import { render, screen, fireEvent, waitFor } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { MemoryRouter } from 'react-router-dom';
import Settings from './Settings';
import { ToastProvider } from '@/components/common';

// Mock the API
vi.mock('@/api', () => ({
  settingsApi: {
    getAll: vi.fn().mockResolvedValue({
      currency: 'USD',
      woocommerce_active: true,
      license_status: 'inactive',
      free_tier_commission: 15,
      pro_tier_commission: 8,
      flat_fee_per_transaction: 2,
      pro_monthly_price: 29,
      pro_annual_price: 249,
      min_deposit_percentage: 20,
      max_deposit_percentage: 100,
      auto_release_escrow_days: 7,
      silver_threshold: 100,
      gold_threshold: 500,
      platinum_threshold: 1000,
      google_client_id: '',
      google_client_secret: '',
    }),
    update: vi.fn().mockResolvedValue({ success: true }),
    activateLicense: vi.fn().mockResolvedValue({ message: 'License activated' }),
    deactivateLicense: vi.fn().mockResolvedValue({ success: true }),
  },
  demoApi: {
    getStatus: vi.fn().mockResolvedValue({ enabled: false }),
  },
}));

import { settingsApi } from '@/api';

const createQueryClient = () =>
  new QueryClient({
    defaultOptions: {
      queries: { retry: false },
      mutations: { retry: false },
    },
  });

const renderWithProviders = (ui: React.ReactElement) => {
  const queryClient = createQueryClient();
  return render(
    <QueryClientProvider client={queryClient}>
      <ToastProvider>
        <MemoryRouter>{ui}</MemoryRouter>
      </ToastProvider>
    </QueryClientProvider>
  );
};

describe('Settings', () => {
  beforeEach(() => {
    vi.clearAllMocks();
  });

  describe('rendering', () => {
    it('renders page title', async () => {
      renderWithProviders(<Settings />);

      await waitFor(() => {
        expect(screen.getByRole('heading', { level: 1 })).toHaveTextContent('Settings');
      });
    });

    it('renders page description', async () => {
      renderWithProviders(<Settings />);

      await waitFor(() => {
        expect(screen.getByText('Configure Peanut Booker')).toBeInTheDocument();
      });
    });

    it('shows loading skeleton initially', () => {
      renderWithProviders(<Settings />);

      // Check for animate-pulse skeleton
      const skeletons = document.querySelectorAll('.animate-pulse');
      expect(skeletons.length).toBeGreaterThan(0);
    });
  });

  describe('tabs navigation', () => {
    it('renders all tabs', async () => {
      renderWithProviders(<Settings />);

      await waitFor(() => {
        expect(screen.getByText('General')).toBeInTheDocument();
      });

      expect(screen.getByText('License')).toBeInTheDocument();
      expect(screen.getByText('Commission')).toBeInTheDocument();
      expect(screen.getByText('Pro Subscription')).toBeInTheDocument();
      expect(screen.getByText('Booking')).toBeInTheDocument();
      expect(screen.getByText('Achievements')).toBeInTheDocument();
      expect(screen.getByText('Google Login')).toBeInTheDocument();
    });

    it('shows General tab content by default', async () => {
      renderWithProviders(<Settings />);

      await waitFor(() => {
        expect(screen.getByText('General Settings')).toBeInTheDocument();
      });
    });

    it('switches to License tab when clicked', async () => {
      const user = userEvent.setup();
      renderWithProviders(<Settings />);

      await waitFor(() => {
        expect(screen.getByText('License')).toBeInTheDocument();
      });

      await user.click(screen.getByText('License'));

      expect(screen.getByText('Manage your Peanut Booker license')).toBeInTheDocument();
    });

    it('switches to Commission tab when clicked', async () => {
      const user = userEvent.setup();
      renderWithProviders(<Settings />);

      await waitFor(() => {
        expect(screen.getByText('Commission')).toBeInTheDocument();
      });

      await user.click(screen.getByText('Commission'));

      expect(screen.getByText('Commission Settings')).toBeInTheDocument();
    });
  });

  describe('General tab', () => {
    it('renders currency input', async () => {
      renderWithProviders(<Settings />);

      await waitFor(() => {
        expect(screen.getByText('Currency')).toBeInTheDocument();
      });
    });

    it('renders WooCommerce status', async () => {
      renderWithProviders(<Settings />);

      await waitFor(() => {
        expect(screen.getByText('WooCommerce Status')).toBeInTheDocument();
      });
    });

    it('shows Active badge when WooCommerce is active', async () => {
      renderWithProviders(<Settings />);

      await waitFor(() => {
        expect(screen.getByText('Active')).toBeInTheDocument();
      });
    });
  });

  describe('License tab', () => {
    it('shows license activation form when inactive', async () => {
      const user = userEvent.setup();
      renderWithProviders(<Settings />);

      await waitFor(() => {
        expect(screen.getByText('License')).toBeInTheDocument();
      });

      await user.click(screen.getByText('License'));

      expect(screen.getByPlaceholderText('Enter your license key')).toBeInTheDocument();
      expect(screen.getByRole('button', { name: 'Activate' })).toBeInTheDocument();
    });

    it('shows license active state when license is active', async () => {
      vi.mocked(settingsApi.getAll).mockResolvedValueOnce({
        license_status: 'active',
        license_expires: '2025-12-31',
        currency: 'USD',
        woocommerce_active: true,
      });

      const user = userEvent.setup();
      renderWithProviders(<Settings />);

      await waitFor(() => {
        expect(screen.getByText('License')).toBeInTheDocument();
      });

      await user.click(screen.getByText('License'));

      await waitFor(() => {
        expect(screen.getByText('License Active')).toBeInTheDocument();
      });
    });
  });

  describe('Commission tab', () => {
    it('renders commission inputs', async () => {
      const user = userEvent.setup();
      renderWithProviders(<Settings />);

      await waitFor(() => {
        expect(screen.getByText('Commission')).toBeInTheDocument();
      });

      await user.click(screen.getByText('Commission'));

      expect(screen.getByText('Free Tier Commission (%)')).toBeInTheDocument();
      expect(screen.getByText('Pro Tier Commission (%)')).toBeInTheDocument();
      expect(screen.getByText('Flat Fee per Transaction ($)')).toBeInTheDocument();
    });
  });

  describe('Pro Subscription tab', () => {
    it('renders subscription pricing inputs', async () => {
      const user = userEvent.setup();
      renderWithProviders(<Settings />);

      await waitFor(() => {
        expect(screen.getByText('Pro Subscription')).toBeInTheDocument();
      });

      await user.click(screen.getByText('Pro Subscription'));

      expect(screen.getByText('Pro Subscription Pricing')).toBeInTheDocument();
      expect(screen.getByText('Monthly Price ($)')).toBeInTheDocument();
      expect(screen.getByText('Annual Price ($)')).toBeInTheDocument();
    });
  });

  describe('Booking tab', () => {
    it('renders booking settings inputs', async () => {
      const user = userEvent.setup();
      renderWithProviders(<Settings />);

      await waitFor(() => {
        expect(screen.getByText('Booking')).toBeInTheDocument();
      });

      await user.click(screen.getByText('Booking'));

      expect(screen.getByText('Booking Settings')).toBeInTheDocument();
      expect(screen.getByText('Min Deposit (%)')).toBeInTheDocument();
      expect(screen.getByText('Max Deposit (%)')).toBeInTheDocument();
      expect(screen.getByText('Auto-Release Escrow (days)')).toBeInTheDocument();
    });
  });

  describe('Achievements tab', () => {
    it('renders achievement threshold inputs', async () => {
      const user = userEvent.setup();
      renderWithProviders(<Settings />);

      await waitFor(() => {
        expect(screen.getByText('Achievements')).toBeInTheDocument();
      });

      await user.click(screen.getByText('Achievements'));

      expect(screen.getByText('Achievement Thresholds')).toBeInTheDocument();
      expect(screen.getByText('Silver Threshold (points)')).toBeInTheDocument();
      expect(screen.getByText('Gold Threshold (points)')).toBeInTheDocument();
      expect(screen.getByText('Platinum Threshold (points)')).toBeInTheDocument();
    });
  });

  describe('Google Login tab', () => {
    it('renders Google OAuth inputs', async () => {
      const user = userEvent.setup();
      renderWithProviders(<Settings />);

      await waitFor(() => {
        expect(screen.getByText('Google Login')).toBeInTheDocument();
      });

      await user.click(screen.getByText('Google Login'));

      expect(screen.getByText('Google Client ID')).toBeInTheDocument();
      expect(screen.getByText('Google Client Secret')).toBeInTheDocument();
    });

    it('renders link to Google Cloud Console', async () => {
      const user = userEvent.setup();
      renderWithProviders(<Settings />);

      await waitFor(() => {
        expect(screen.getByText('Google Login')).toBeInTheDocument();
      });

      await user.click(screen.getByText('Google Login'));

      expect(screen.getByText('console.cloud.google.com')).toBeInTheDocument();
    });
  });

  describe('API integration', () => {
    it('fetches settings on mount', async () => {
      renderWithProviders(<Settings />);

      await waitFor(() => {
        expect(settingsApi.getAll).toHaveBeenCalled();
      });
    });
  });
});

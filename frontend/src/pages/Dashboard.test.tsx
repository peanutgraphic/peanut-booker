import { describe, it, expect, vi, beforeEach } from 'vitest';
import { render, screen, fireEvent, waitFor } from '@testing-library/react';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { MemoryRouter } from 'react-router-dom';
import Dashboard from './Dashboard';

// Mock the API
vi.mock('@/api', () => ({
  dashboardApi: {
    getStats: vi.fn().mockResolvedValue({
      total_performers: 45,
      total_bookings: 128,
      pending_bookings: 12,
      total_revenue: 15750,
      platform_commission: 2362,
      reviews_needing_arbitration: 3,
      demo_mode: false,
    }),
  },
  demoApi: {
    getStatus: vi.fn().mockResolvedValue({ enabled: false }),
  },
}));

import { dashboardApi } from '@/api';

const mockNavigate = vi.fn();
vi.mock('react-router-dom', async () => {
  const actual = await vi.importActual('react-router-dom');
  return {
    ...actual,
    useNavigate: () => mockNavigate,
  };
});

const createQueryClient = () =>
  new QueryClient({
    defaultOptions: {
      queries: { retry: false },
    },
  });

const renderWithProviders = (ui: React.ReactElement) => {
  const queryClient = createQueryClient();
  return render(
    <QueryClientProvider client={queryClient}>
      <MemoryRouter>{ui}</MemoryRouter>
    </QueryClientProvider>
  );
};

describe('Dashboard', () => {
  beforeEach(() => {
    vi.clearAllMocks();
  });

  describe('rendering', () => {
    it('renders page title', async () => {
      renderWithProviders(<Dashboard />);

      await waitFor(() => {
        expect(screen.getByRole('heading', { level: 1 })).toHaveTextContent('Dashboard');
      });
    });

    it('renders page description', async () => {
      renderWithProviders(<Dashboard />);

      await waitFor(() => {
        expect(screen.getByText('Overview of your booking platform')).toBeInTheDocument();
      });
    });
  });

  describe('stat cards', () => {
    it('renders Total Performers stat', async () => {
      renderWithProviders(<Dashboard />);

      expect(screen.getByText('Total Performers')).toBeInTheDocument();

      await waitFor(() => {
        expect(screen.getByText('45')).toBeInTheDocument();
      });
    });

    it('renders Total Bookings stat', async () => {
      renderWithProviders(<Dashboard />);

      expect(screen.getByText('Total Bookings')).toBeInTheDocument();

      await waitFor(() => {
        expect(screen.getByText('128')).toBeInTheDocument();
      });
    });

    it('renders Pending Bookings stat', async () => {
      renderWithProviders(<Dashboard />);

      expect(screen.getByText('Pending Bookings')).toBeInTheDocument();

      await waitFor(() => {
        expect(screen.getByText('12')).toBeInTheDocument();
      });
    });

    it('renders Total Revenue stat', async () => {
      renderWithProviders(<Dashboard />);

      expect(screen.getByText('Total Revenue')).toBeInTheDocument();

      await waitFor(() => {
        expect(screen.getByText('$15,750')).toBeInTheDocument();
      });
    });

    it('renders Platform Commission stat', async () => {
      renderWithProviders(<Dashboard />);

      expect(screen.getByText('Platform Commission')).toBeInTheDocument();

      await waitFor(() => {
        expect(screen.getByText('$2,362')).toBeInTheDocument();
      });
    });

    it('renders Reviews to Arbitrate stat', async () => {
      renderWithProviders(<Dashboard />);

      expect(screen.getByText('Reviews to Arbitrate')).toBeInTheDocument();

      await waitFor(() => {
        expect(screen.getByText('3')).toBeInTheDocument();
      });
    });

    it('shows loading state initially', () => {
      renderWithProviders(<Dashboard />);

      // Multiple "..." placeholders while loading
      const loadingIndicators = screen.getAllByText('...');
      expect(loadingIndicators.length).toBeGreaterThanOrEqual(1);
    });
  });

  describe('quick actions', () => {
    it('renders Quick Actions section', async () => {
      renderWithProviders(<Dashboard />);

      await waitFor(() => {
        expect(screen.getByText('Quick Actions')).toBeInTheDocument();
      });
    });

    it('renders section description', async () => {
      renderWithProviders(<Dashboard />);

      await waitFor(() => {
        expect(screen.getByText('Common tasks you can perform from here')).toBeInTheDocument();
      });
    });

    it('renders View Performers action', async () => {
      renderWithProviders(<Dashboard />);

      await waitFor(() => {
        expect(screen.getByText('View Performers')).toBeInTheDocument();
      });
    });

    it('renders View Bookings action', async () => {
      renderWithProviders(<Dashboard />);

      await waitFor(() => {
        expect(screen.getByText('View Bookings')).toBeInTheDocument();
      });
    });

    it('renders Process Payouts action', async () => {
      renderWithProviders(<Dashboard />);

      await waitFor(() => {
        // "Process Payouts" appears in both quick actions and getting started
        const elements = screen.getAllByText(/Process Payouts/);
        expect(elements.length).toBeGreaterThanOrEqual(1);
      });
    });

    it('renders Review Flags action', async () => {
      renderWithProviders(<Dashboard />);

      await waitFor(() => {
        expect(screen.getByText('Review Flags')).toBeInTheDocument();
      });
    });

    it('navigates to performers on View Performers click', async () => {
      renderWithProviders(<Dashboard />);

      await waitFor(() => {
        expect(screen.getByText('View Performers')).toBeInTheDocument();
      });

      fireEvent.click(screen.getByText('View Performers'));
      expect(mockNavigate).toHaveBeenCalledWith('/performers');
    });

    it('navigates to bookings on View Bookings click', async () => {
      renderWithProviders(<Dashboard />);

      await waitFor(() => {
        expect(screen.getByText('View Bookings')).toBeInTheDocument();
      });

      fireEvent.click(screen.getByText('View Bookings'));
      expect(mockNavigate).toHaveBeenCalledWith('/bookings');
    });

    it('navigates to payouts on Process Payouts click', async () => {
      renderWithProviders(<Dashboard />);

      await waitFor(() => {
        const elements = screen.getAllByText(/Process Payouts/);
        expect(elements.length).toBeGreaterThanOrEqual(1);
      });

      // Click the quick action button (first occurrence in actions section)
      const elements = screen.getAllByText(/Process Payouts/);
      fireEvent.click(elements[0]);
      expect(mockNavigate).toHaveBeenCalledWith('/payouts');
    });

    it('navigates to reviews on Review Flags click', async () => {
      renderWithProviders(<Dashboard />);

      await waitFor(() => {
        expect(screen.getByText('Review Flags')).toBeInTheDocument();
      });

      fireEvent.click(screen.getByText('Review Flags'));
      expect(mockNavigate).toHaveBeenCalledWith('/reviews');
    });
  });

  describe('help cards', () => {
    it('renders Getting Started card', async () => {
      renderWithProviders(<Dashboard />);

      await waitFor(() => {
        expect(screen.getByText('Getting Started')).toBeInTheDocument();
      });
    });

    it('renders Platform Tips card', async () => {
      renderWithProviders(<Dashboard />);

      await waitFor(() => {
        expect(screen.getByText('Platform Tips')).toBeInTheDocument();
      });
    });

    it('renders Getting Started steps', async () => {
      renderWithProviders(<Dashboard />);

      await waitFor(() => {
        expect(screen.getByText(/Configure Settings/)).toBeInTheDocument();
        expect(screen.getByText(/Enable Demo Mode/)).toBeInTheDocument();
        expect(screen.getByText(/Review Performers/)).toBeInTheDocument();
        // "Process Payouts" appears in both quick actions and getting started
        const processPayoutsElements = screen.getAllByText(/Process Payouts/);
        expect(processPayoutsElements.length).toBeGreaterThanOrEqual(1);
      });
    });

    it('renders Platform Tips content', async () => {
      renderWithProviders(<Dashboard />);

      await waitFor(() => {
        expect(screen.getByText(/Booking Flow:/)).toBeInTheDocument();
        expect(screen.getByText(/Market Events:/)).toBeInTheDocument();
        expect(screen.getByText(/Tier System:/)).toBeInTheDocument();
        expect(screen.getByText(/Escrow:/)).toBeInTheDocument();
      });
    });
  });

  describe('demo mode', () => {
    it('does not show demo banner when demo_mode is false', async () => {
      renderWithProviders(<Dashboard />);

      await waitFor(() => {
        expect(screen.getByText('45')).toBeInTheDocument();
      });

      expect(screen.queryByText('Demo Mode Active')).not.toBeInTheDocument();
    });

    it('shows demo banner when demo_mode is true', async () => {
      vi.mocked(dashboardApi.getStats).mockResolvedValueOnce({
        total_performers: 45,
        total_bookings: 128,
        pending_bookings: 12,
        total_revenue: 15750,
        platform_commission: 2362,
        reviews_needing_arbitration: 3,
        demo_mode: true,
      });

      renderWithProviders(<Dashboard />);

      await waitFor(() => {
        expect(screen.getByText('Demo Mode Active')).toBeInTheDocument();
      });
    });

    it('shows Manage Demo Mode link when in demo mode', async () => {
      vi.mocked(dashboardApi.getStats).mockResolvedValueOnce({
        total_performers: 45,
        total_bookings: 128,
        pending_bookings: 12,
        total_revenue: 15750,
        platform_commission: 2362,
        reviews_needing_arbitration: 3,
        demo_mode: true,
      });

      renderWithProviders(<Dashboard />);

      await waitFor(() => {
        expect(screen.getByText('Manage Demo Mode')).toBeInTheDocument();
      });
    });

    it('navigates to demo page on Manage Demo Mode click', async () => {
      vi.mocked(dashboardApi.getStats).mockResolvedValueOnce({
        total_performers: 45,
        total_bookings: 128,
        pending_bookings: 12,
        total_revenue: 15750,
        platform_commission: 2362,
        reviews_needing_arbitration: 3,
        demo_mode: true,
      });

      renderWithProviders(<Dashboard />);

      await waitFor(() => {
        expect(screen.getByText('Manage Demo Mode')).toBeInTheDocument();
      });

      fireEvent.click(screen.getByText('Manage Demo Mode'));
      expect(mockNavigate).toHaveBeenCalledWith('/demo');
    });
  });

  describe('API integration', () => {
    it('fetches dashboard stats on mount', async () => {
      renderWithProviders(<Dashboard />);

      await waitFor(() => {
        expect(dashboardApi.getStats).toHaveBeenCalled();
      });
    });

    it('handles null stats gracefully', async () => {
      vi.mocked(dashboardApi.getStats).mockResolvedValueOnce(null);

      renderWithProviders(<Dashboard />);

      await waitFor(() => {
        // Should show 0 for missing values (multiple cards show 0)
        const zeros = screen.getAllByText('0');
        expect(zeros.length).toBeGreaterThanOrEqual(1);
      });
    });
  });
});

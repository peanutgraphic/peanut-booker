import { describe, it, expect, vi } from 'vitest';
import { render, screen, fireEvent, waitFor } from '@testing-library/react';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import Layout from './Layout';

// Mock the demoApi
vi.mock('@/api', () => ({
  demoApi: {
    getStatus: vi.fn().mockResolvedValue({ enabled: false }),
  },
}));

import { demoApi } from '@/api';

const createQueryClient = () =>
  new QueryClient({
    defaultOptions: {
      queries: { retry: false },
    },
  });

const renderWithProviders = (ui: React.ReactElement) => {
  const queryClient = createQueryClient();
  return render(
    <QueryClientProvider client={queryClient}>{ui}</QueryClientProvider>
  );
};

describe('Layout', () => {
  describe('rendering', () => {
    it('renders children', () => {
      renderWithProviders(
        <Layout title="Test Page">
          <div>Child content</div>
        </Layout>
      );

      expect(screen.getByText('Child content')).toBeInTheDocument();
    });

    it('passes title to Header', () => {
      renderWithProviders(
        <Layout title="Dashboard">
          <div>Content</div>
        </Layout>
      );

      expect(screen.getByRole('heading', { level: 1 })).toHaveTextContent('Dashboard');
    });

    it('passes description to Header', () => {
      renderWithProviders(
        <Layout title="Dashboard" description="Your overview">
          <div>Content</div>
        </Layout>
      );

      expect(screen.getByText('Your overview')).toBeInTheDocument();
    });

    it('passes helpContent to Header', () => {
      const helpContent = {
        title: 'Help Title',
        content: 'Help text',
      };
      renderWithProviders(
        <Layout title="Dashboard" helpContent={helpContent}>
          <div>Content</div>
        </Layout>
      );

      expect(screen.getByText('How to')).toBeInTheDocument();
    });
  });

  describe('demo banner', () => {
    it('does not show demo banner when demo is disabled', async () => {
      vi.mocked(demoApi.getStatus).mockResolvedValueOnce({ enabled: false });

      renderWithProviders(
        <Layout title="Dashboard">
          <div>Content</div>
        </Layout>
      );

      await waitFor(() => {
        expect(screen.queryByText('Sample Data Preview')).not.toBeInTheDocument();
      });
    });

    it('shows demo banner when demo is enabled', async () => {
      vi.mocked(demoApi.getStatus).mockResolvedValueOnce({ enabled: true });

      renderWithProviders(
        <Layout title="Dashboard">
          <div>Content</div>
        </Layout>
      );

      await waitFor(() => {
        expect(screen.getByText('Sample Data Preview')).toBeInTheDocument();
      });
    });

    it('dismisses demo banner when clicking X', async () => {
      vi.mocked(demoApi.getStatus).mockResolvedValueOnce({ enabled: true });

      renderWithProviders(
        <Layout title="Dashboard">
          <div>Content</div>
        </Layout>
      );

      await waitFor(() => {
        expect(screen.getByText('Sample Data Preview')).toBeInTheDocument();
      });

      // Find and click the dismiss button (X button in the banner container)
      const bannerContainer = screen.getByText('Sample Data Preview').closest('.mb-6');
      const dismissButton = bannerContainer?.querySelector('button');
      expect(dismissButton).toBeTruthy();
      fireEvent.click(dismissButton!);

      expect(screen.queryByText('Sample Data Preview')).not.toBeInTheDocument();
    });
  });

  describe('styling', () => {
    it('applies correct container styles', () => {
      const { container } = renderWithProviders(
        <Layout title="Dashboard">
          <div>Content</div>
        </Layout>
      );

      const wrapper = container.firstChild;
      expect(wrapper).toHaveClass('min-h-screen', 'bg-slate-50');
    });

    it('applies correct main styles', () => {
      const { container } = renderWithProviders(
        <Layout title="Dashboard">
          <div>Content</div>
        </Layout>
      );

      const main = container.querySelector('main');
      expect(main).toHaveClass('p-6', 'overflow-x-hidden');
    });
  });
});

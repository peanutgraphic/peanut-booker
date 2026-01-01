import { describe, it, expect, vi, beforeEach } from 'vitest';
import api from './client';
import {
  dashboardApi,
  performersApi,
  bookingsApi,
  marketApi,
  reviewsApi,
  payoutsApi,
  categoriesApi,
  serviceAreasApi,
  settingsApi,
  demoApi,
  micrositesApi,
  messagesApi,
  customersApi,
  analyticsApi,
} from './endpoints';

// Mock the API client
vi.mock('./client', () => ({
  default: {
    get: vi.fn(),
    post: vi.fn(),
    put: vi.fn(),
    delete: vi.fn(),
  },
}));

describe('Dashboard API', () => {
  beforeEach(() => {
    vi.clearAllMocks();
  });

  it('getStats fetches dashboard statistics', async () => {
    const mockData = { total_bookings: 100, total_revenue: 5000 };
    vi.mocked(api.get).mockResolvedValueOnce({ data: mockData });

    const result = await dashboardApi.getStats();

    expect(api.get).toHaveBeenCalledWith('/dashboard/stats');
    expect(result).toEqual(mockData);
  });
});

describe('Performers API', () => {
  beforeEach(() => {
    vi.clearAllMocks();
  });

  it('getAll fetches performers with pagination', async () => {
    const mockData = { data: [], total: 0, page: 1 };
    vi.mocked(api.get).mockResolvedValueOnce({ data: mockData });

    const result = await performersApi.getAll({ page: 1, per_page: 10 });

    expect(api.get).toHaveBeenCalledWith('/admin/performers', { params: { page: 1, per_page: 10 } });
    expect(result).toEqual(mockData);
  });

  it('getById fetches a single performer', async () => {
    const mockData = { id: 1, name: 'Test Performer' };
    vi.mocked(api.get).mockResolvedValueOnce({ data: mockData });

    const result = await performersApi.getById(1);

    expect(api.get).toHaveBeenCalledWith('/admin/performers/1');
    expect(result).toEqual(mockData);
  });

  it('update updates performer data', async () => {
    const mockData = { id: 1, name: 'Updated Performer' };
    vi.mocked(api.put).mockResolvedValueOnce({ data: mockData });

    const result = await performersApi.update(1, { name: 'Updated Performer' });

    expect(api.put).toHaveBeenCalledWith('/admin/performers/1', { name: 'Updated Performer' });
    expect(result).toEqual(mockData);
  });

  it('verify sets performer verification status', async () => {
    const mockData = { id: 1, verified: true };
    vi.mocked(api.post).mockResolvedValueOnce({ data: mockData });

    const result = await performersApi.verify(1, true);

    expect(api.post).toHaveBeenCalledWith('/admin/performers/1/verify', { verified: true });
    expect(result).toEqual(mockData);
  });

  it('feature sets performer featured status', async () => {
    const mockData = { id: 1, featured: true };
    vi.mocked(api.post).mockResolvedValueOnce({ data: mockData });

    const result = await performersApi.feature(1, true);

    expect(api.post).toHaveBeenCalledWith('/admin/performers/1/feature', { featured: true });
    expect(result).toEqual(mockData);
  });

  it('bulkDelete deletes multiple performers', async () => {
    vi.mocked(api.delete).mockResolvedValueOnce({ data: {} });

    await performersApi.bulkDelete([1, 2, 3]);

    expect(api.delete).toHaveBeenCalledWith('/admin/performers/bulk', { data: { ids: [1, 2, 3] } });
  });

  it('getEditorData fetches performer editor data', async () => {
    const mockData = { performer: {}, microsite: {} };
    vi.mocked(api.get).mockResolvedValueOnce({ data: mockData });

    const result = await performersApi.getEditorData(1);

    expect(api.get).toHaveBeenCalledWith('/admin/performers/1/editor');
    expect(result).toEqual(mockData);
  });

  it('blockDates blocks dates for performer', async () => {
    const mockData = { success: true, message: 'Dates blocked', blocked: 3 };
    vi.mocked(api.post).mockResolvedValueOnce({ data: mockData });

    const result = await performersApi.blockDates(1, { dates: ['2024-01-01', '2024-01-02'] });

    expect(api.post).toHaveBeenCalledWith('/admin/performers/1/availability/block', {
      dates: ['2024-01-01', '2024-01-02'],
    });
    expect(result).toEqual(mockData);
  });

  it('unblockDate removes date block', async () => {
    const mockData = { success: true, message: 'Date unblocked' };
    vi.mocked(api.delete).mockResolvedValueOnce({ data: mockData });

    const result = await performersApi.unblockDate(1, 100);

    expect(api.delete).toHaveBeenCalledWith('/admin/performers/1/availability/100');
    expect(result).toEqual(mockData);
  });
});

describe('Bookings API', () => {
  beforeEach(() => {
    vi.clearAllMocks();
  });

  it('getAll fetches bookings with filters', async () => {
    const mockData = { data: [], total: 0 };
    vi.mocked(api.get).mockResolvedValueOnce({ data: mockData });

    const result = await bookingsApi.getAll({ status: 'pending', page: 1 });

    expect(api.get).toHaveBeenCalledWith('/admin/bookings', { params: { status: 'pending', page: 1 } });
    expect(result).toEqual(mockData);
  });

  it('getById fetches a single booking', async () => {
    const mockData = { id: 1, status: 'pending' };
    vi.mocked(api.get).mockResolvedValueOnce({ data: mockData });

    const result = await bookingsApi.getById(1);

    expect(api.get).toHaveBeenCalledWith('/admin/bookings/1');
    expect(result).toEqual(mockData);
  });

  it('updateStatus updates booking status', async () => {
    const mockData = { id: 1, status: 'confirmed' };
    vi.mocked(api.post).mockResolvedValueOnce({ data: mockData });

    const result = await bookingsApi.updateStatus(1, 'confirmed');

    expect(api.post).toHaveBeenCalledWith('/admin/bookings/1/status', { status: 'confirmed' });
    expect(result).toEqual(mockData);
  });

  it('cancel cancels a booking with reason', async () => {
    const mockData = { id: 1, status: 'cancelled' };
    vi.mocked(api.post).mockResolvedValueOnce({ data: mockData });

    const result = await bookingsApi.cancel(1, 'Customer request');

    expect(api.post).toHaveBeenCalledWith('/admin/bookings/1/cancel', { reason: 'Customer request' });
    expect(result).toEqual(mockData);
  });
});

describe('Market API', () => {
  beforeEach(() => {
    vi.clearAllMocks();
  });

  it('getAll fetches market events', async () => {
    const mockData = { data: [], total: 0 };
    vi.mocked(api.get).mockResolvedValueOnce({ data: mockData });

    const result = await marketApi.getAll({ status: 'open' });

    expect(api.get).toHaveBeenCalledWith('/admin/market', { params: { status: 'open' } });
    expect(result).toEqual(mockData);
  });

  it('updateStatus updates market event status', async () => {
    const mockData = { id: 1, status: 'closed' };
    vi.mocked(api.post).mockResolvedValueOnce({ data: mockData });

    const result = await marketApi.updateStatus(1, 'closed');

    expect(api.post).toHaveBeenCalledWith('/admin/market/1/status', { status: 'closed' });
    expect(result).toEqual(mockData);
  });
});

describe('Reviews API', () => {
  beforeEach(() => {
    vi.clearAllMocks();
  });

  it('getAll fetches reviews with flagged filter', async () => {
    const mockData = { data: [], total: 0 };
    vi.mocked(api.get).mockResolvedValueOnce({ data: mockData });

    const result = await reviewsApi.getAll({ flagged: true });

    expect(api.get).toHaveBeenCalledWith('/admin/reviews', { params: { flagged: true } });
    expect(result).toEqual(mockData);
  });

  it('respond adds response to review', async () => {
    const mockData = { id: 1, response: 'Thank you!' };
    vi.mocked(api.post).mockResolvedValueOnce({ data: mockData });

    const result = await reviewsApi.respond(1, 'Thank you!');

    expect(api.post).toHaveBeenCalledWith('/admin/reviews/1/respond', { response: 'Thank you!' });
    expect(result).toEqual(mockData);
  });

  it('arbitrate makes arbitration decision', async () => {
    const mockData = { id: 1, arbitration: 'keep' };
    vi.mocked(api.post).mockResolvedValueOnce({ data: mockData });

    const result = await reviewsApi.arbitrate(1, 'keep', 'Review is valid');

    expect(api.post).toHaveBeenCalledWith('/admin/reviews/1/arbitrate', {
      decision: 'keep',
      notes: 'Review is valid',
    });
    expect(result).toEqual(mockData);
  });

  it('toggleVisibility changes review visibility', async () => {
    const mockData = { id: 1, visible: false };
    vi.mocked(api.post).mockResolvedValueOnce({ data: mockData });

    const result = await reviewsApi.toggleVisibility(1, false);

    expect(api.post).toHaveBeenCalledWith('/admin/reviews/1/visibility', { visible: false });
    expect(result).toEqual(mockData);
  });
});

describe('Payouts API', () => {
  beforeEach(() => {
    vi.clearAllMocks();
  });

  it('getPending fetches pending payouts', async () => {
    const mockData = [{ booking_id: 1, amount: 100 }];
    vi.mocked(api.get).mockResolvedValueOnce({ data: mockData });

    const result = await payoutsApi.getPending();

    expect(api.get).toHaveBeenCalledWith('/admin/payouts/pending');
    expect(result).toEqual(mockData);
  });

  it('getStats fetches payout statistics', async () => {
    const mockData = { pending: 500, released: 1000 };
    vi.mocked(api.get).mockResolvedValueOnce({ data: mockData });

    const result = await payoutsApi.getStats();

    expect(api.get).toHaveBeenCalledWith('/admin/payouts/stats');
    expect(result).toEqual(mockData);
  });

  it('release releases a payout', async () => {
    vi.mocked(api.post).mockResolvedValueOnce({ data: {} });

    await payoutsApi.release(1);

    expect(api.post).toHaveBeenCalledWith('/admin/payouts/1/release');
  });

  it('bulkRelease releases multiple payouts', async () => {
    vi.mocked(api.post).mockResolvedValueOnce({ data: {} });

    await payoutsApi.bulkRelease([1, 2, 3]);

    expect(api.post).toHaveBeenCalledWith('/admin/payouts/bulk-release', { booking_ids: [1, 2, 3] });
  });
});

describe('Categories API', () => {
  it('getAll fetches all categories', async () => {
    const mockData = [{ id: 1, name: 'Music' }];
    vi.mocked(api.get).mockResolvedValueOnce({ data: mockData });

    const result = await categoriesApi.getAll();

    expect(api.get).toHaveBeenCalledWith('/admin/categories');
    expect(result).toEqual(mockData);
  });
});

describe('Service Areas API', () => {
  it('getAll fetches all service areas', async () => {
    const mockData = [{ id: 1, name: 'New York' }];
    vi.mocked(api.get).mockResolvedValueOnce({ data: mockData });

    const result = await serviceAreasApi.getAll();

    expect(api.get).toHaveBeenCalledWith('/admin/service-areas');
    expect(result).toEqual(mockData);
  });
});

describe('Settings API', () => {
  beforeEach(() => {
    vi.clearAllMocks();
  });

  it('getAll fetches all settings', async () => {
    const mockData = { commission_rate: 15, currency: 'USD' };
    vi.mocked(api.get).mockResolvedValueOnce({ data: mockData });

    const result = await settingsApi.getAll();

    expect(api.get).toHaveBeenCalledWith('/admin/settings');
    expect(result).toEqual(mockData);
  });

  it('update updates settings', async () => {
    const mockData = { commission_rate: 20 };
    vi.mocked(api.put).mockResolvedValueOnce({ data: mockData });

    const result = await settingsApi.update({ commission_rate: 20 });

    expect(api.put).toHaveBeenCalledWith('/admin/settings', { commission_rate: 20 });
    expect(result).toEqual(mockData);
  });

  it('activateLicense activates a license key', async () => {
    const mockData = { success: true, message: 'License activated' };
    vi.mocked(api.post).mockResolvedValueOnce({ data: mockData });

    const result = await settingsApi.activateLicense('LICENSE-KEY-123');

    expect(api.post).toHaveBeenCalledWith('/admin/settings/license/activate', {
      license_key: 'LICENSE-KEY-123',
    });
    expect(result).toEqual(mockData);
  });

  it('deactivateLicense deactivates the license', async () => {
    const mockData = { success: true, message: 'License deactivated' };
    vi.mocked(api.post).mockResolvedValueOnce({ data: mockData });

    const result = await settingsApi.deactivateLicense();

    expect(api.post).toHaveBeenCalledWith('/admin/settings/license/deactivate');
    expect(result).toEqual(mockData);
  });
});

describe('Demo API', () => {
  beforeEach(() => {
    vi.clearAllMocks();
  });

  it('getStatus fetches demo mode status', async () => {
    const mockData = { enabled: true, stats: { performers: 10 } };
    vi.mocked(api.get).mockResolvedValueOnce({ data: mockData });

    const result = await demoApi.getStatus();

    expect(api.get).toHaveBeenCalledWith('/admin/demo/status');
    expect(result).toEqual(mockData);
  });

  it('enable enables demo mode', async () => {
    vi.mocked(api.post).mockResolvedValueOnce({ data: {} });

    await demoApi.enable();

    expect(api.post).toHaveBeenCalledWith('/admin/demo/enable');
  });

  it('disable disables demo mode', async () => {
    vi.mocked(api.post).mockResolvedValueOnce({ data: {} });

    await demoApi.disable();

    expect(api.post).toHaveBeenCalledWith('/admin/demo/disable');
  });
});

describe('Microsites API', () => {
  beforeEach(() => {
    vi.clearAllMocks();
  });

  it('getAll fetches microsites', async () => {
    const mockData = { data: [], total: 0 };
    vi.mocked(api.get).mockResolvedValueOnce({ data: mockData });

    const result = await micrositesApi.getAll({ status: 'active' });

    expect(api.get).toHaveBeenCalledWith('/admin/microsites', { params: { status: 'active' } });
    expect(result).toEqual(mockData);
  });

  it('delete removes a microsite', async () => {
    vi.mocked(api.delete).mockResolvedValueOnce({ data: {} });

    await micrositesApi.delete(1);

    expect(api.delete).toHaveBeenCalledWith('/admin/microsites/1');
  });

  it('getAnalytics fetches microsite analytics', async () => {
    const mockData = { views: 100, bookings: 5 };
    vi.mocked(api.get).mockResolvedValueOnce({ data: mockData });

    const result = await micrositesApi.getAnalytics(1);

    expect(api.get).toHaveBeenCalledWith('/admin/microsites/1/analytics');
    expect(result).toEqual(mockData);
  });
});

describe('Messages API', () => {
  beforeEach(() => {
    vi.clearAllMocks();
  });

  it('getConversations fetches conversations', async () => {
    const mockData = { data: [], total: 0 };
    vi.mocked(api.get).mockResolvedValueOnce({ data: mockData });

    const result = await messagesApi.getConversations({ page: 1 });

    expect(api.get).toHaveBeenCalledWith('/admin/messages/conversations', { params: { page: 1 } });
    expect(result).toEqual(mockData);
  });

  it('getMessages fetches messages for a conversation', async () => {
    const mockData = { data: [], total: 0 };
    vi.mocked(api.get).mockResolvedValueOnce({ data: mockData });

    const result = await messagesApi.getMessages(1, { page: 1 });

    expect(api.get).toHaveBeenCalledWith('/admin/messages/conversations/1', { params: { page: 1 } });
    expect(result).toEqual(mockData);
  });
});

describe('Customers API', () => {
  beforeEach(() => {
    vi.clearAllMocks();
  });

  it('getAll fetches customers', async () => {
    const mockData = { data: [], total: 0 };
    vi.mocked(api.get).mockResolvedValueOnce({ data: mockData });

    const result = await customersApi.getAll({ page: 1 });

    expect(api.get).toHaveBeenCalledWith('/admin/customers', { params: { page: 1 } });
    expect(result).toEqual(mockData);
  });

  it('getById fetches a single customer', async () => {
    const mockData = { id: 1, email: 'test@example.com' };
    vi.mocked(api.get).mockResolvedValueOnce({ data: mockData });

    const result = await customersApi.getById(1);

    expect(api.get).toHaveBeenCalledWith('/admin/customers/1');
    expect(result).toEqual(mockData);
  });
});

describe('Analytics API', () => {
  it('getOverview fetches analytics overview', async () => {
    const mockData = { revenue: { total: 5000 }, bookings: { total: 100 } };
    vi.mocked(api.get).mockResolvedValueOnce({ data: mockData });

    const result = await analyticsApi.getOverview();

    expect(api.get).toHaveBeenCalledWith('/admin/analytics/overview');
    expect(result).toEqual(mockData);
  });
});

import api from './client';
import type {
  Performer,
  Booking,
  MarketEvent,
  Review,
  Payout,
  Category,
  ServiceArea,
  DashboardStats,
  Settings,
  Microsite,
  PaginationParams,
  PaginatedResponse,
} from '@/types';

// Dashboard API
export const dashboardApi = {
  getStats: async (): Promise<DashboardStats> => {
    const { data } = await api.get('/dashboard/stats');
    return data;
  },
};

import type {
  MicrositePreviewData,
  MicrositeAnalytics,
  ExternalGig,
  ExtendedMicrositeDesignSettings,
} from '../types';

// Performers API
export const performersApi = {
  getAll: async (params?: PaginationParams): Promise<PaginatedResponse<Performer>> => {
    const { data } = await api.get('/admin/performers', { params });
    return data;
  },

  getById: async (id: number): Promise<Performer> => {
    const { data } = await api.get(`/admin/performers/${id}`);
    return data;
  },

  update: async (id: number, performer: Partial<Performer>): Promise<Performer> => {
    const { data } = await api.put(`/admin/performers/${id}`, performer);
    return data;
  },

  verify: async (id: number, verified: boolean): Promise<Performer> => {
    const { data } = await api.post(`/admin/performers/${id}/verify`, { verified });
    return data;
  },

  feature: async (id: number, featured: boolean): Promise<Performer> => {
    const { data } = await api.post(`/admin/performers/${id}/feature`, { featured });
    return data;
  },

  bulkDelete: async (ids: number[]): Promise<void> => {
    await api.delete('/admin/performers/bulk', { data: { ids } });
  },

  // Unified editor endpoints - work with performer IDs
  getEditorData: async (performerId: number): Promise<MicrositePreviewData> => {
    const { data } = await api.get(`/admin/performers/${performerId}/editor`);
    return data;
  },

  updateEditorData: async (
    performerId: number,
    editorData: {
      performer?: Partial<Performer>;
      microsite?: Partial<Microsite>;
      design_settings?: Partial<ExtendedMicrositeDesignSettings>;
    }
  ): Promise<MicrositePreviewData> => {
    const { data } = await api.put(`/admin/performers/${performerId}/editor`, editorData);
    return data;
  },

  getAnalytics: async (performerId: number): Promise<MicrositeAnalytics> => {
    const { data } = await api.get(`/admin/performers/${performerId}/analytics`);
    return data;
  },

  getExternalGigs: async (performerId: number): Promise<{ data: ExternalGig[] }> => {
    const { data } = await api.get(`/admin/performers/${performerId}/external-gigs`);
    return data;
  },

  createExternalGig: async (
    performerId: number,
    gig: Omit<ExternalGig, 'id' | 'performer_id'>
  ): Promise<{ success: boolean; data: ExternalGig }> => {
    const { data } = await api.post(`/admin/performers/${performerId}/external-gigs`, gig);
    return data;
  },

  updateExternalGig: async (
    performerId: number,
    gigId: number,
    gig: Partial<Omit<ExternalGig, 'id' | 'performer_id'>>
  ): Promise<{ success: boolean; data: ExternalGig }> => {
    const { data } = await api.put(`/admin/performers/${performerId}/external-gigs/${gigId}`, gig);
    return data;
  },

  deleteExternalGig: async (performerId: number, gigId: number): Promise<{ success: boolean; message: string }> => {
    const { data } = await api.delete(`/admin/performers/${performerId}/external-gigs/${gigId}`);
    return data;
  },

  // Performer's bookings for dashboard
  getBookings: async (
    performerId: number,
    params?: { status?: string; page?: number; per_page?: number }
  ): Promise<PerformerBookingsResponse> => {
    const { data } = await api.get(`/admin/performers/${performerId}/bookings`, { params });
    return data;
  },

  // Performer's reviews for dashboard
  getReviews: async (
    performerId: number,
    params?: { page?: number; per_page?: number }
  ): Promise<PerformerReviewsResponse> => {
    const { data } = await api.get(`/admin/performers/${performerId}/reviews`, { params });
    return data;
  },

  // Performer's availability for calendar
  getAvailability: async (
    performerId: number,
    params?: { start_date?: string; end_date?: string }
  ): Promise<PerformerAvailabilityResponse> => {
    const { data } = await api.get(`/admin/performers/${performerId}/availability`, { params });
    return data;
  },

  // Block dates for performer
  blockDates: async (
    performerId: number,
    blockData: { dates: string[]; title?: string; notes?: string; block_type?: string }
  ): Promise<{ success: boolean; message: string; blocked: number }> => {
    console.log('blockDates called:', { performerId, blockData });
    console.log('API config:', { nonce: window.peanutBooker?.nonce });
    const { data } = await api.post(`/admin/performers/${performerId}/availability/block`, blockData);
    console.log('blockDates response:', data);
    return data;
  },

  // Unblock a date for performer
  unblockDate: async (
    performerId: number,
    slotId: number
  ): Promise<{ success: boolean; message: string }> => {
    const { data } = await api.delete(`/admin/performers/${performerId}/availability/${slotId}`);
    return data;
  },

  // Update an availability slot (blocked date or external gig)
  updateAvailabilitySlot: async (
    performerId: number,
    slotId: number,
    updateData: {
      event_name?: string;
      venue_name?: string;
      event_location?: string;
      notes?: string;
      block_type?: 'manual' | 'external_gig';
    }
  ): Promise<{ success: boolean; message: string; data: AvailabilityEvent }> => {
    const { data } = await api.put(`/admin/performers/${performerId}/availability/${slotId}`, updateData);
    return data;
  },

  // Performer's conversations for dashboard
  getConversations: async (
    performerId: number,
    params?: { page?: number; per_page?: number }
  ): Promise<PerformerConversationsResponse> => {
    const { data } = await api.get(`/admin/performers/${performerId}/conversations`, { params });
    return data;
  },

  // Performer's messages in a conversation
  getConversationMessages: async (
    performerId: number,
    conversationId: number
  ): Promise<PerformerMessagesResponse> => {
    const { data } = await api.get(`/admin/performers/${performerId}/conversations/${conversationId}`);
    return data;
  },

  // Performer's payouts for dashboard
  getPayouts: async (
    performerId: number,
    params?: { status?: string; page?: number; per_page?: number }
  ): Promise<PerformerPayoutsResponse> => {
    const { data } = await api.get(`/admin/performers/${performerId}/payouts`, { params });
    return data;
  },

  // Performer's dashboard overview
  getOverview: async (performerId: number): Promise<PerformerOverviewResponse> => {
    const { data } = await api.get(`/admin/performers/${performerId}/overview`);
    return data;
  },
};

// Type for performer bookings response
export interface PerformerBookingsResponse {
  success: boolean;
  data: Booking[];
  stats: {
    upcoming: number;
    pending: number;
    completed: number;
    cancelled: number;
    total_earned: number;
  };
  total: number;
  page: number;
  per_page: number;
  total_pages: number;
}

// Type for performer reviews response
export interface PerformerReviewsResponse {
  success: boolean;
  data: Review[];
  stats: {
    total_reviews: number;
    average_rating: number;
    rating_distribution: {
      5: number;
      4: number;
      3: number;
      2: number;
      1: number;
    };
  };
  total: number;
  page: number;
  per_page: number;
  total_pages: number;
}

// Type for availability calendar event
export interface AvailabilityEvent {
  id: number | string;
  title: string;
  start: string;
  end: string;
  allDay: boolean;
  type: 'manual' | 'external_gig' | 'booking';
  status: string;
  venue_name?: string;
  event_location?: string;
  notes?: string;
  booking_id?: number;
  customer_name?: string;
  event_time?: string;
  canDelete: boolean;
}

// Type for performer availability response (unwrapped by interceptor)
export type PerformerAvailabilityResponse = AvailabilityEvent[];

// Type for performer conversation
export interface PerformerConversation {
  id: number;
  other_user_id: number;
  other_user_name: string;
  last_message: string | null;
  last_message_at: string | null;
  unread_count: number;
  booking_id: number | null;
}

// Type for performer conversations response
export interface PerformerConversationsResponse {
  success: boolean;
  data: PerformerConversation[];
  total: number;
  page: number;
  per_page: number;
  total_pages: number;
}

// Type for performer message
export interface PerformerMessage {
  id: number;
  sender_id: number;
  sender_name: string;
  content: string;
  is_read: boolean;
  is_from_me: boolean;
  booking_id: number | null;
  created_at: string;
}

// Type for performer messages response
export interface PerformerMessagesResponse {
  success: boolean;
  data: PerformerMessage[];
  total: number;
}

// Type for performer payout item
export interface PerformerPayout {
  booking_id: number;
  booking_number: string;
  event_title: string;
  customer_name: string;
  event_date: string;
  completion_date: string | null;
  total_amount: number;
  commission_amount: number;
  payout_amount: number;
  escrow_status: string;
  auto_release_date: string | null;
}

// Type for performer payouts response
export interface PerformerPayoutsResponse {
  success: boolean;
  data: PerformerPayout[];
  stats: {
    total_earned: number;
    total_released: number;
    total_pending: number;
    pending_count: number;
    released_count: number;
  };
  total: number;
  page: number;
  per_page: number;
  total_pages: number;
}

// Type for recent activity item
export interface PerformerActivityItem {
  type: 'booking' | 'review' | 'message' | 'payout';
  description: string;
  timestamp: string;
  meta?: {
    booking_id?: number;
    review_id?: number;
    amount?: number;
    rating?: number;
  };
}

// Type for performer overview response
export interface PerformerOverviewResponse {
  success: boolean;
  data: {
    bookings: {
      upcoming: number;
      pending_approval: number;
      completed: number;
    };
    earnings: {
      total: number;
      pending: number;
    };
    messages: {
      unread: number;
    };
    reviews: {
      average: number;
      total: number;
      recent_count: number;
    };
    next_booking: {
      id: number;
      event_title: string;
      event_date: string;
      customer_name: string;
      status: string;
    } | null;
    recent_activity: PerformerActivityItem[];
  };
}

// Bookings API
export const bookingsApi = {
  getAll: async (params?: PaginationParams & { status?: string }): Promise<PaginatedResponse<Booking>> => {
    const { data } = await api.get('/admin/bookings', { params });
    return data;
  },

  getById: async (id: number): Promise<Booking> => {
    const { data } = await api.get(`/admin/bookings/${id}`);
    return data;
  },

  updateStatus: async (id: number, status: string): Promise<Booking> => {
    const { data } = await api.post(`/admin/bookings/${id}/status`, { status });
    return data;
  },

  cancel: async (id: number, reason: string): Promise<Booking> => {
    const { data } = await api.post(`/admin/bookings/${id}/cancel`, { reason });
    return data;
  },

  export: async (params?: { status?: string; date_from?: string; date_to?: string }): Promise<Blob> => {
    const { data } = await api.get('/admin/bookings/export', {
      params,
      responseType: 'blob',
    });
    return data;
  },
};

// Market Events API
export const marketApi = {
  getAll: async (params?: PaginationParams & { status?: string }): Promise<PaginatedResponse<MarketEvent>> => {
    const { data } = await api.get('/admin/market', { params });
    return data;
  },

  getById: async (id: number): Promise<MarketEvent> => {
    const { data } = await api.get(`/admin/market/${id}`);
    return data;
  },

  updateStatus: async (id: number, status: string): Promise<MarketEvent> => {
    const { data } = await api.post(`/admin/market/${id}/status`, { status });
    return data;
  },
};

// Reviews API
export const reviewsApi = {
  getAll: async (params?: PaginationParams & { flagged?: boolean }): Promise<PaginatedResponse<Review>> => {
    const { data } = await api.get('/admin/reviews', { params });
    return data;
  },

  getById: async (id: number): Promise<Review> => {
    const { data } = await api.get(`/admin/reviews/${id}`);
    return data;
  },

  respond: async (id: number, response: string): Promise<Review> => {
    const { data } = await api.post(`/admin/reviews/${id}/respond`, { response });
    return data;
  },

  arbitrate: async (
    id: number,
    decision: 'keep' | 'remove' | 'edit',
    notes: string
  ): Promise<Review> => {
    const { data } = await api.post(`/admin/reviews/${id}/arbitrate`, { decision, notes });
    return data;
  },

  toggleVisibility: async (id: number, visible: boolean): Promise<Review> => {
    const { data } = await api.post(`/admin/reviews/${id}/visibility`, { visible });
    return data;
  },
};

// Payouts API
export const payoutsApi = {
  getPending: async (): Promise<Payout[]> => {
    const { data } = await api.get('/admin/payouts/pending');
    return data;
  },

  getStats: async (): Promise<{ pending: number; released: number }> => {
    const { data } = await api.get('/admin/payouts/stats');
    return data;
  },

  release: async (bookingId: number): Promise<void> => {
    await api.post(`/admin/payouts/${bookingId}/release`);
  },

  bulkRelease: async (bookingIds: number[]): Promise<void> => {
    await api.post('/admin/payouts/bulk-release', { booking_ids: bookingIds });
  },
};

// Categories API
export const categoriesApi = {
  getAll: async (): Promise<Category[]> => {
    const { data } = await api.get('/admin/categories');
    return data;
  },
};

// Service Areas API
export const serviceAreasApi = {
  getAll: async (): Promise<ServiceArea[]> => {
    const { data } = await api.get('/admin/service-areas');
    return data;
  },
};

// Settings API
export const settingsApi = {
  getAll: async (): Promise<Settings> => {
    const { data } = await api.get('/admin/settings');
    return data;
  },

  update: async (settings: Partial<Settings>): Promise<Settings> => {
    const { data } = await api.put('/admin/settings', settings);
    return data;
  },

  activateLicense: async (licenseKey: string): Promise<{ success: boolean; message: string }> => {
    const { data } = await api.post('/admin/settings/license/activate', { license_key: licenseKey });
    return data;
  },

  deactivateLicense: async (): Promise<{ success: boolean; message: string }> => {
    const { data } = await api.post('/admin/settings/license/deactivate');
    return data;
  },
};

// Demo Mode API
export const demoApi = {
  getStatus: async (): Promise<{ enabled: boolean; stats: Record<string, number> }> => {
    const { data } = await api.get('/admin/demo/status');
    return data;
  },

  enable: async (): Promise<void> => {
    await api.post('/admin/demo/enable');
  },

  disable: async (): Promise<void> => {
    await api.post('/admin/demo/disable');
  },
};

// Microsites API
export const micrositesApi = {
  getAll: async (params?: PaginationParams & { status?: string }): Promise<PaginatedResponse<Microsite>> => {
    const { data } = await api.get('/admin/microsites', { params });
    return data;
  },

  getById: async (id: number): Promise<Microsite> => {
    const { data } = await api.get(`/admin/microsites/${id}`);
    return data;
  },

  update: async (id: number, microsite: Partial<Microsite>): Promise<Microsite> => {
    const { data } = await api.put(`/admin/microsites/${id}`, microsite);
    return data;
  },

  updateStatus: async (id: number, status: string): Promise<Microsite> => {
    const { data } = await api.post(`/admin/microsites/${id}/status`, { status });
    return data;
  },

  delete: async (id: number): Promise<void> => {
    await api.delete(`/admin/microsites/${id}`);
  },

  // Preview endpoints
  getPreview: async (id: number): Promise<MicrositePreviewData> => {
    const { data } = await api.get(`/admin/microsites/${id}/preview`);
    return data;
  },

  getPreviewWithChanges: async (
    id: number,
    changes: {
      slug?: string;
      meta_title?: string;
      meta_description?: string;
      design_settings?: Partial<ExtendedMicrositeDesignSettings>;
    }
  ): Promise<MicrositePreviewData> => {
    const { data } = await api.post(`/admin/microsites/${id}/preview`, changes);
    return data;
  },

  // Analytics endpoints
  getAnalytics: async (id: number): Promise<MicrositeAnalytics> => {
    const { data } = await api.get(`/admin/microsites/${id}/analytics`);
    return data;
  },

  // External gigs endpoints
  getExternalGigs: async (id: number): Promise<{ data: ExternalGig[] }> => {
    const { data } = await api.get(`/admin/microsites/${id}/external-gigs`);
    return data;
  },

  createExternalGig: async (
    id: number,
    gig: Omit<ExternalGig, 'id' | 'performer_id'>
  ): Promise<{ success: boolean; data: ExternalGig }> => {
    const { data } = await api.post(`/admin/microsites/${id}/external-gigs`, gig);
    return data;
  },

  updateExternalGig: async (
    id: number,
    gigId: number,
    gig: Partial<Omit<ExternalGig, 'id' | 'performer_id'>>
  ): Promise<{ success: boolean; data: ExternalGig }> => {
    const { data } = await api.put(`/admin/microsites/${id}/external-gigs/${gigId}`, gig);
    return data;
  },

  deleteExternalGig: async (id: number, gigId: number): Promise<{ success: boolean; message: string }> => {
    const { data } = await api.delete(`/admin/microsites/${id}/external-gigs/${gigId}`);
    return data;
  },
};

// Messages API
export interface Message {
  id: number;
  conversation_id: number;
  sender_id: number;
  sender_name: string;
  sender_type: 'performer' | 'customer';
  recipient_id: number;
  recipient_name: string;
  content: string;
  is_read: boolean;
  booking_id?: number;
  created_at: string;
}

export interface Conversation {
  id: number;
  participant_1_id: number;
  participant_1_name: string;
  participant_1_type: 'performer' | 'customer';
  participant_2_id: number;
  participant_2_name: string;
  participant_2_type: 'performer' | 'customer';
  last_message?: string;
  last_message_at?: string;
  unread_count: number;
  booking_id?: number;
  created_at: string;
}

export const messagesApi = {
  getConversations: async (params?: PaginationParams): Promise<PaginatedResponse<Conversation>> => {
    const { data } = await api.get('/admin/messages/conversations', { params });
    return data;
  },

  getMessages: async (conversationId: number, params?: PaginationParams): Promise<PaginatedResponse<Message>> => {
    const { data } = await api.get(`/admin/messages/conversations/${conversationId}`, { params });
    return data;
  },
};

// Customers API
export interface Customer {
  id: number;
  user_id: number;
  email: string;
  display_name: string;
  first_name?: string;
  last_name?: string;
  phone?: string;
  total_bookings: number;
  total_spent: number;
  last_booking_date?: string;
  created_at: string;
}

export const customersApi = {
  getAll: async (params?: PaginationParams): Promise<PaginatedResponse<Customer>> => {
    const { data } = await api.get('/admin/customers', { params });
    return data;
  },

  getById: async (id: number): Promise<Customer> => {
    const { data } = await api.get(`/admin/customers/${id}`);
    return data;
  },
};

// Analytics API
export interface AnalyticsData {
  revenue: {
    total: number;
    this_month: number;
    last_month: number;
    growth_percentage: number;
  };
  bookings: {
    total: number;
    completed: number;
    pending: number;
    cancelled: number;
    completion_rate: number;
  };
  performers: {
    total: number;
    active: number;
    verified: number;
    pro_tier: number;
  };
  customers: {
    total: number;
    active: number;
    new_this_month: number;
  };
  top_performers: Array<{
    id: number;
    name: string;
    bookings: number;
    revenue: number;
    rating: number;
  }>;
  recent_activity: Array<{
    id: number;
    type: 'booking' | 'review' | 'payout';
    description: string;
    amount?: number;
    created_at: string;
  }>;
}

export const analyticsApi = {
  getOverview: async (): Promise<AnalyticsData> => {
    const { data } = await api.get('/admin/analytics/overview');
    return data;
  },
};

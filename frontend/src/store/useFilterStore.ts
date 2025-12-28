import { create } from 'zustand';
import type { PerformerFilters, BookingFilters, MarketFilters, ReviewFilters } from '@/types';

interface FilterState {
  // Performer filters
  performerFilters: PerformerFilters;
  setPerformerFilter: (key: keyof PerformerFilters, value: string) => void;
  resetPerformerFilters: () => void;

  // Booking filters
  bookingFilters: BookingFilters;
  setBookingFilter: (key: keyof BookingFilters, value: string) => void;
  resetBookingFilters: () => void;

  // Market filters
  marketFilters: MarketFilters;
  setMarketFilter: (key: keyof MarketFilters, value: string) => void;
  resetMarketFilters: () => void;

  // Review filters
  reviewFilters: ReviewFilters;
  setReviewFilter: (key: keyof ReviewFilters, value: string) => void;
  resetReviewFilters: () => void;
}

const defaultPerformerFilters: PerformerFilters = {
  search: '',
  tier: '',
  status: '',
  verified: '',
};

const defaultBookingFilters: BookingFilters = {
  search: '',
  status: '',
  date_from: '',
  date_to: '',
};

const defaultMarketFilters: MarketFilters = {
  search: '',
  status: '',
  date_from: '',
  date_to: '',
};

const defaultReviewFilters: ReviewFilters = {
  search: '',
  flagged: '',
};

export const useFilterStore = create<FilterState>((set) => ({
  // Performers
  performerFilters: defaultPerformerFilters,
  setPerformerFilter: (key, value) =>
    set((state) => ({
      performerFilters: { ...state.performerFilters, [key]: value },
    })),
  resetPerformerFilters: () => set({ performerFilters: defaultPerformerFilters }),

  // Bookings
  bookingFilters: defaultBookingFilters,
  setBookingFilter: (key, value) =>
    set((state) => ({
      bookingFilters: { ...state.bookingFilters, [key]: value },
    })),
  resetBookingFilters: () => set({ bookingFilters: defaultBookingFilters }),

  // Market
  marketFilters: defaultMarketFilters,
  setMarketFilter: (key, value) =>
    set((state) => ({
      marketFilters: { ...state.marketFilters, [key]: value },
    })),
  resetMarketFilters: () => set({ marketFilters: defaultMarketFilters }),

  // Reviews
  reviewFilters: defaultReviewFilters,
  setReviewFilter: (key, value) =>
    set((state) => ({
      reviewFilters: { ...state.reviewFilters, [key]: value },
    })),
  resetReviewFilters: () => set({ reviewFilters: defaultReviewFilters }),
}));

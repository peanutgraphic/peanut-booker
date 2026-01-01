import axios, { AxiosInstance, AxiosError } from 'axios';
import type { ApiResponse } from '@/types';

// WordPress passes these via wp_localize_script
declare global {
  interface Window {
    peanutBooker?: {
      apiUrl: string;
      nonce: string;
      version: string;
      tier: string;
    };
  }
}

// Get config from WordPress or use defaults for development
const getConfig = () => {
  if (window.peanutBooker) {
    return {
      baseURL: window.peanutBooker.apiUrl,
      nonce: window.peanutBooker.nonce,
    };
  }

  // Development fallback
  return {
    baseURL: '/wp-json/peanut-booker/v1',
    nonce: '',
  };
};

const config = getConfig();

// Create axios instance
const api: AxiosInstance = axios.create({
  baseURL: config.baseURL,
  headers: {
    'Content-Type': 'application/json',
  },
  withCredentials: true,
});

// Request interceptor - dynamically add nonce to every request
api.interceptors.request.use(
  (requestConfig) => {
    // Always get fresh nonce from window.peanutBooker
    const nonce = window.peanutBooker?.nonce;
    if (nonce) {
      requestConfig.headers['X-WP-Nonce'] = nonce;
    }
    return requestConfig;
  },
  (error) => Promise.reject(error)
);

// Response interceptor
api.interceptors.response.use(
  (response) => {
    // Handle Peanut API response format
    const data = response.data;
    if (data && typeof data === 'object' && 'success' in data) {
      if (!data.success) {
        return Promise.reject(new Error(data.message || 'Request failed'));
      }
      return { ...response, data: data.data };
    }
    return response;
  },
  (error: AxiosError<ApiResponse<unknown>>) => {
    const message = error.response?.data?.message || error.message || 'An error occurred';
    return Promise.reject(new Error(message));
  }
);

export default api;

// Helper to check if we're in WordPress admin
export const isWordPressAdmin = (): boolean => {
  return typeof window.peanutBooker !== 'undefined';
};

// Helper to get current tier
export const getCurrentTier = (): string => {
  return window.peanutBooker?.tier || 'free';
};

// Helper to get plugin version
export const getVersion = (): string => {
  return window.peanutBooker?.version || '1.0.0';
};

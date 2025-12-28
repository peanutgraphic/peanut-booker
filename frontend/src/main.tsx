import React from 'react';
import ReactDOM from 'react-dom/client';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { HashRouter } from 'react-router-dom';
import App from './App';
import './index.css';

const queryClient = new QueryClient({
  defaultOptions: {
    queries: {
      staleTime: 5 * 60 * 1000, // 5 minutes
      retry: 1,
      refetchOnWindowFocus: false,
    },
  },
});

// Map WordPress page slugs to React routes
const pageToRoute: Record<string, string> = {
  'peanut-booker': '/',
  'pb-performers': '/performers',
  'pb-bookings': '/bookings',
  'pb-market': '/market',
  'pb-reviews': '/reviews',
  'pb-payouts': '/payouts',
  'pb-microsites': '/microsites',
  'pb-messages': '/messages',
  'pb-customers': '/customers',
  'pb-analytics': '/analytics',
  'pb-settings': '/settings',
  'pb-demo': '/demo',
  'pb-edit-performer': '/performers/edit',
};

// Get the initial route from WordPress page parameter
const getInitialRoute = (): string => {
  const urlParams = new URLSearchParams(window.location.search);
  const page = urlParams.get('page');

  if (page && pageToRoute[page]) {
    return pageToRoute[page];
  }

  return '/';
};

// Set the initial hash before React mounts
const initialRoute = getInitialRoute();
if (window.location.hash !== `#${initialRoute}`) {
  window.location.hash = initialRoute;
}

// Mount point for WordPress admin
const rootElement = document.getElementById('peanut-booker-app');

if (rootElement) {
  ReactDOM.createRoot(rootElement).render(
    <React.StrictMode>
      <QueryClientProvider client={queryClient}>
        <HashRouter>
          <App />
        </HashRouter>
      </QueryClientProvider>
    </React.StrictMode>
  );
}

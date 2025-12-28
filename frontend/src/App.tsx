import { Routes, Route, Navigate } from 'react-router-dom';
import Dashboard from './pages/Dashboard';
import Performers from './pages/Performers';
import MicrositeEditor from './pages/MicrositeEditor';
import Bookings from './pages/Bookings';
import MarketEvents from './pages/MarketEvents';
import Reviews from './pages/Reviews';
import Payouts from './pages/Payouts';
import Messages from './pages/Messages';
import Customers from './pages/Customers';
import Analytics from './pages/Analytics';
import Settings from './pages/Settings';
import DemoMode from './pages/DemoMode';
import { ToastProvider } from './components/common';

export default function App() {
  return (
    <ToastProvider>
      <Routes>
        <Route path="/" element={<Dashboard />} />
        <Route path="/performers" element={<Performers />} />
        <Route path="/performers/:id/edit" element={<MicrositeEditor />} />
        <Route path="/performers/:id/profile" element={<MicrositeEditor />} />
        <Route path="/bookings" element={<Bookings />} />
        <Route path="/market" element={<MarketEvents />} />
        <Route path="/reviews" element={<Reviews />} />
        <Route path="/payouts" element={<Payouts />} />
        {/* Redirect old microsite routes to performers */}
        <Route path="/microsites" element={<Navigate to="/performers" replace />} />
        <Route path="/microsites/:id/edit" element={<Navigate to="/performers" replace />} />
        <Route path="/messages" element={<Messages />} />
        <Route path="/customers" element={<Customers />} />
        <Route path="/analytics" element={<Analytics />} />
        <Route path="/settings" element={<Settings />} />
        <Route path="/demo" element={<DemoMode />} />
      </Routes>
    </ToastProvider>
  );
}

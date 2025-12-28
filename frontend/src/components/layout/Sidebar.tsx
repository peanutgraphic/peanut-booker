import { useRef, useCallback, useEffect } from 'react';
import { NavLink, useLocation, useNavigate } from 'react-router-dom';
import { clsx } from 'clsx';
import {
  LayoutDashboard,
  Users,
  Calendar,
  ShoppingBag,
  Star,
  DollarSign,
  Settings,
  ChevronLeft,
  ChevronRight,
  FlaskConical,
  MessageSquare,
  UserCircle,
  BarChart3,
} from 'lucide-react';
import { getVersion } from '@/api/client';

interface SidebarProps {
  collapsed: boolean;
  onToggle: () => void;
}

interface NavItem {
  name: string;
  href: string;
  icon: typeof LayoutDashboard;
}

const navigation: NavItem[] = [
  { name: 'Dashboard', href: '/', icon: LayoutDashboard },
  { name: 'Performers', href: '/performers', icon: Users },
  { name: 'Bookings', href: '/bookings', icon: Calendar },
  { name: 'Market Events', href: '/market', icon: ShoppingBag },
  { name: 'Reviews', href: '/reviews', icon: Star },
  { name: 'Payouts', href: '/payouts', icon: DollarSign },
  { name: 'Messages', href: '/messages', icon: MessageSquare },
  { name: 'Customers', href: '/customers', icon: UserCircle },
  { name: 'Analytics', href: '/analytics', icon: BarChart3 },
  { name: 'Settings', href: '/settings', icon: Settings },
  { name: 'Demo Mode', href: '/demo', icon: FlaskConical },
];

export default function Sidebar({ collapsed, onToggle }: SidebarProps) {
  const location = useLocation();
  const navigate = useNavigate();
  const navRef = useRef<HTMLElement>(null);
  const navItemRefs = useRef<(HTMLAnchorElement | null)[]>([]);

  // Find current active index based on location
  const getCurrentIndex = useCallback(() => {
    return navigation.findIndex((item) => {
      if (item.href === '/') {
        return location.pathname === '/';
      }
      return location.pathname.startsWith(item.href);
    });
  }, [location.pathname]);

  // Focus a nav item by index
  const focusNavItem = useCallback((index: number) => {
    const clampedIndex = Math.max(0, Math.min(index, navigation.length - 1));
    navItemRefs.current[clampedIndex]?.focus();
  }, []);

  // Handle keyboard navigation within the nav
  const handleNavKeyDown = useCallback(
    (e: React.KeyboardEvent, currentIndex: number) => {
      switch (e.key) {
        case 'ArrowDown':
          e.preventDefault();
          focusNavItem(currentIndex + 1);
          break;
        case 'ArrowUp':
          e.preventDefault();
          focusNavItem(currentIndex - 1);
          break;
        case 'Home':
          e.preventDefault();
          focusNavItem(0);
          break;
        case 'End':
          e.preventDefault();
          focusNavItem(navigation.length - 1);
          break;
        case 'Enter':
        case ' ':
          e.preventDefault();
          navigate(navigation[currentIndex].href);
          break;
      }
    },
    [focusNavItem, navigate]
  );

  // Handle toggle button keyboard
  const handleToggleKeyDown = useCallback(
    (e: React.KeyboardEvent) => {
      if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        onToggle();
      }
    },
    [onToggle]
  );

  // Global keyboard shortcut: [ to toggle sidebar
  useEffect(() => {
    const handleGlobalKeyDown = (e: KeyboardEvent) => {
      // Skip if user is typing in an input
      if (
        e.target instanceof HTMLInputElement ||
        e.target instanceof HTMLTextAreaElement ||
        e.target instanceof HTMLSelectElement
      ) {
        return;
      }

      // [ key toggles sidebar
      if (e.key === '[' && !e.ctrlKey && !e.metaKey && !e.altKey) {
        e.preventDefault();
        onToggle();
      }
    };

    document.addEventListener('keydown', handleGlobalKeyDown);
    return () => document.removeEventListener('keydown', handleGlobalKeyDown);
  }, [onToggle]);

  return (
    <aside
      className={clsx(
        'fixed left-0 top-0 h-screen bg-white border-r border-slate-200 transition-all duration-300 z-50',
        collapsed ? 'w-16' : 'w-56'
      )}
      aria-label="Main navigation"
    >
      {/* Logo */}
      <div className="h-14 flex items-center justify-between px-4 border-b border-slate-200">
        {!collapsed && (
          <span className="text-lg font-bold text-primary-600">Peanut Booker</span>
        )}
        <button
          onClick={onToggle}
          onKeyDown={handleToggleKeyDown}
          className={clsx(
            'p-1.5 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors',
            'focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500'
          )}
          aria-expanded={!collapsed}
          aria-controls="sidebar-nav"
          aria-label={collapsed ? 'Expand sidebar' : 'Collapse sidebar'}
          title={`${collapsed ? 'Expand' : 'Collapse'} sidebar (press [)`}
        >
          {collapsed ? (
            <ChevronRight className="w-5 h-5" aria-hidden="true" />
          ) : (
            <ChevronLeft className="w-5 h-5" aria-hidden="true" />
          )}
        </button>
      </div>

      {/* Navigation */}
      <nav
        id="sidebar-nav"
        ref={navRef}
        className="p-3 space-y-1"
        role="navigation"
        aria-label="Primary"
      >
        <ul role="menubar" aria-orientation="vertical" className="space-y-1">
          {navigation.map((item, index) => {
            const isActive =
              item.href === '/'
                ? location.pathname === '/'
                : location.pathname.startsWith(item.href);

            return (
              <li key={item.name} role="none">
                <NavLink
                  ref={(el) => {
                    navItemRefs.current[index] = el;
                  }}
                  to={item.href}
                  role="menuitem"
                  tabIndex={index === getCurrentIndex() ? 0 : -1}
                  aria-current={isActive ? 'page' : undefined}
                  onKeyDown={(e) => handleNavKeyDown(e, index)}
                  className={({ isActive }) =>
                    clsx(
                      'flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors',
                      'focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 focus-visible:ring-inset',
                      isActive
                        ? 'bg-primary-50 text-primary-700'
                        : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'
                    )
                  }
                  title={collapsed ? item.name : undefined}
                >
                  <item.icon
                    className={clsx(
                      'w-5 h-5 flex-shrink-0',
                      isActive ? 'text-primary-600' : 'text-slate-500'
                    )}
                    aria-hidden="true"
                  />
                  {!collapsed && <span className="flex-1">{item.name}</span>}
                  {collapsed && (
                    <span className="sr-only">{item.name}</span>
                  )}
                </NavLink>
              </li>
            );
          })}
        </ul>
      </nav>

      {/* Version Badge */}
      {!collapsed && (
        <div className="absolute bottom-0 left-0 right-0 p-4 border-t border-slate-200">
          <div className="flex items-center justify-between">
            <span className="text-xs text-slate-400">Peanut Booker v{getVersion()}</span>
          </div>
        </div>
      )}

      {/* Screen reader announcement for collapsed state */}
      <div className="sr-only" aria-live="polite" aria-atomic="true">
        {collapsed ? 'Sidebar collapsed' : 'Sidebar expanded'}
      </div>
    </aside>
  );
}

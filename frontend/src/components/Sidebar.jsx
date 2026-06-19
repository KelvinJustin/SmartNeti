import { NavLink, useLocation } from 'react-router-dom';
import { useEffect } from 'react';
import {
  LayoutDashboard,
  Wifi,
  Layers,
  Ticket,
  Users,
  CreditCard,
  BarChart3,
  Settings,
  X,
} from 'lucide-react';

const navItems = [
  { to: '/', icon: LayoutDashboard, label: 'Dashboard' },
  { to: '/hotspots', icon: Wifi, label: 'Hotspots' },
  { to: '/plans', icon: Layers, label: 'Plans' },
  { to: '/vouchers', icon: Ticket, label: 'Vouchers' },
  { to: '/users', icon: Users, label: 'Users' },
  { to: '/payments', icon: CreditCard, label: 'Payments' },
  { to: '/analytics', icon: BarChart3, label: 'Analytics' },
  { to: '/settings', icon: Settings, label: 'Settings' },
];

export default function Sidebar({ isOpen, onClose }) {
  const { pathname } = useLocation();

  // Close drawer on route change (mobile)
  useEffect(() => {
    if (isOpen && onClose) onClose();
  }, [pathname]);

  return (
    <>
      {/* Mobile backdrop */}
      {isOpen && (
        <div
          className="fixed inset-0 bg-black/40 z-40 lg:hidden"
          onClick={onClose}
          aria-hidden="true"
        />
      )}

      <aside
        className={`
          fixed inset-y-0 left-0 z-50 w-64 bg-white border-r border-slate-200 flex flex-col h-screen
          transform transition-transform duration-200 ease-out
          dark:bg-slate-900 dark:border-slate-800
          lg:static lg:translate-x-0
          ${isOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'}
        `}
      >
        <div className="h-16 flex items-center justify-between px-6 border-b border-slate-200 dark:border-slate-800">
          <img src="/logo.png" alt="SmartNeti" className="h-8 w-auto" />
          <button
            onClick={onClose}
            className="lg:hidden p-1 text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200"
            aria-label="Close menu"
          >
            <X className="w-5 h-5" />
          </button>
        </div>
        <nav className="flex-1 p-4 space-y-1 overflow-y-auto">
          {navItems.map((item) => (
            <NavLink
              key={item.to}
              to={item.to}
              onClick={() => { if (window.innerWidth < 1024 && onClose) onClose(); }}
              className={({ isActive }) =>
                `flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-medium transition-colors ${
                  isActive
                    ? 'bg-brand-50 text-brand-700 dark:bg-brand-900/40 dark:text-brand-300'
                    : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-slate-100'
                }`
              }
            >
              <item.icon className="w-5 h-5" />
              {item.label}
            </NavLink>
          ))}
        </nav>
        <div className="p-4 border-t border-slate-200 text-xs text-slate-500 dark:border-slate-800 dark:text-slate-400">
          v0.1.0
        </div>
      </aside>
    </>
  );
}

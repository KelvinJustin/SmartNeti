import { useState } from 'react';
import { useLocation, Outlet } from 'react-router-dom';
import Sidebar from './Sidebar';
import Header from './Header';

const pageTitles = {
  '/': 'Dashboard',
  '/hotspots': 'Hotspots',
  '/plans': 'Plans',
  '/vouchers': 'Vouchers',
  '/users': 'Users',
  '/payments': 'Payments',
  '/analytics': 'Analytics',
  '/settings': 'Settings',
};

export default function Layout() {
  const { pathname } = useLocation();
  const title = pageTitles[pathname] || 'SmartNeti';
  const [sidebarOpen, setSidebarOpen] = useState(false);

  return (
    <div className="flex min-h-screen">
      <Sidebar isOpen={sidebarOpen} onClose={() => setSidebarOpen(false)} />
      <div className="flex-1 flex flex-col min-w-0">
        <Header title={title} onMenuClick={() => setSidebarOpen(true)} />
        <main className="flex-1 p-4 lg:p-6 overflow-auto">
          <Outlet />
        </main>
      </div>
    </div>
  );
}

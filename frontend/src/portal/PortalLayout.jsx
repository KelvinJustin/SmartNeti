import { Outlet, Link } from 'react-router-dom';

export default function PortalLayout() {
  return (
    <div className="min-h-screen bg-slate-50 text-slate-900 dark:bg-slate-950 dark:text-slate-100 flex flex-col">
      <header className="bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 sticky top-0 z-10">
        <div className="max-w-lg mx-auto px-4 h-14 flex items-center justify-between">
          <Link to="/portal" className="flex items-center">
            <img src="/logo.png" alt="SmartNeti" className="h-8 w-auto" />
          </Link>
          <nav className="flex items-center gap-4 text-sm">
            <Link to="/portal/plans" className="text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-100">
              Plans
            </Link>
            <Link to="/portal/voucher" className="text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-100">
              Voucher
            </Link>
            <Link to="/login" className="text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-100">
              Admin
            </Link>
          </nav>
        </div>
      </header>
      <main className="flex-1 max-w-lg mx-auto w-full px-4 py-6">
        <Outlet />
      </main>
      <footer className="text-center text-xs text-slate-400 py-4 dark:text-slate-500">
        &copy; {new Date().getFullYear()} SmartNeti
      </footer>
    </div>
  );
}

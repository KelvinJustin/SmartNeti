import { Bell, Moon, Sun, User, LogOut, Menu } from 'lucide-react';
import { useNavigate } from 'react-router-dom';
import { useTheme } from '../context/ThemeContext';
import { useAuth } from '../context/AuthContext';

export default function Header({ title, onMenuClick }) {
  const navigate = useNavigate();
  const { theme, toggleTheme } = useTheme();
  const { user, logout } = useAuth();

  const handleLogout = async () => {
    await logout();
    navigate('/login');
  };

  return (
    <header className="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-4 lg:px-6 sticky top-0 z-10 dark:bg-slate-900 dark:border-slate-800">
      <div className="flex items-center gap-3">
        <button
          onClick={onMenuClick}
          className="lg:hidden p-2 -ml-2 text-slate-500 hover:text-slate-700 hover:bg-slate-100 rounded-lg dark:text-slate-400 dark:hover:text-slate-200 dark:hover:bg-slate-800"
          aria-label="Open menu"
        >
          <Menu className="w-5 h-5" />
        </button>
        <h1 className="text-lg font-semibold text-slate-900 dark:text-slate-100">{title}</h1>
      </div>
      <div className="flex items-center gap-2 lg:gap-4">
        <button
          onClick={toggleTheme}
          className="p-2 text-slate-500 hover:text-slate-700 hover:bg-slate-100 rounded-lg dark:text-slate-400 dark:hover:text-slate-200 dark:hover:bg-slate-800"
          aria-label="Toggle theme"
        >
          {theme === 'dark' ? <Sun className="w-5 h-5" /> : <Moon className="w-5 h-5" />}
        </button>
        <button className="p-2 text-slate-500 hover:text-slate-700 hover:bg-slate-100 rounded-lg dark:text-slate-400 dark:hover:text-slate-200 dark:hover:bg-slate-800">
          <Bell className="w-5 h-5" />
        </button>
        <button
          onClick={handleLogout}
          className="p-2 text-slate-500 hover:text-slate-700 hover:bg-slate-100 rounded-lg dark:text-slate-400 dark:hover:text-slate-200 dark:hover:bg-slate-800"
          aria-label="Logout"
        >
          <LogOut className="w-5 h-5" />
        </button>
        <div className="flex items-center gap-2 text-sm text-slate-700 dark:text-slate-300">
          <div className="w-8 h-8 bg-brand-100 text-brand-700 rounded-full flex items-center justify-center dark:bg-brand-900 dark:text-brand-300">
            <User className="w-5 h-5" />
          </div>
          <span className="hidden sm:inline">{user?.fullName || user?.email || 'Admin'}</span>
        </div>
      </div>
    </header>
  );
}

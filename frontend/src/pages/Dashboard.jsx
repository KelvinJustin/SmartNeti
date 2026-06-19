import { useEffect, useState } from 'react';
import {
  Users,
  Wifi,
  CreditCard,
  Ticket,
  TrendingUp,
  ArrowUpRight,
} from 'lucide-react';
import { fetchDashboardStats } from '../api/client';

const statCards = [
  { key: 'onlineUsers', label: 'Online Users', icon: Users, color: 'text-blue-600', bg: 'bg-blue-50' },
  { key: 'activeHotspots', label: 'Active Hotspots', icon: Wifi, color: 'text-emerald-600', bg: 'bg-emerald-50' },
  { key: 'vouchersSold', label: 'Vouchers Sold Today', icon: Ticket, color: 'text-amber-600', bg: 'bg-amber-50' },
  { key: 'revenue', label: 'Revenue Today', icon: CreditCard, color: 'text-violet-600', bg: 'bg-violet-50' },
];

export default function Dashboard() {
  const [stats, setStats] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    fetchDashboardStats()
      .then((data) => {
        setStats(data);
        setLoading(false);
      })
      .catch((err) => {
        setError(err.message);
        setLoading(false);
      });
  }, []);

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <div>
          <h2 className="text-2xl font-bold text-slate-900 dark:text-slate-100">Dashboard</h2>
          <p className="text-slate-500 mt-1 dark:text-slate-400">Overview of your hotspot network.</p>
        </div>
        <span className="text-sm text-slate-500 dark:text-slate-400">
          {new Date().toLocaleDateString(undefined, {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric',
          })}
        </span>
      </div>

      {loading && (
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
          {statCards.map((card) => (
            <div
              key={card.key}
              className="bg-white p-6 rounded-xl border border-slate-200 shadow-sm animate-pulse dark:bg-slate-900 dark:border-slate-800"
            >
              <div className="h-8 bg-slate-200 rounded w-1/3 mb-4 dark:bg-slate-700"></div>
              <div className="h-6 bg-slate-200 rounded w-2/3 dark:bg-slate-700"></div>
            </div>
          ))}
        </div>
      )}

      {error && (
        <div className="bg-red-50 text-red-700 p-4 rounded-lg border border-red-200 dark:bg-red-950/30 dark:text-red-400 dark:border-red-900/50">
          Failed to load dashboard stats: {error}
        </div>
      )}

      {stats && !loading && (
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
          {statCards.map((card) => (
            <div
              key={card.key}
              className="bg-white p-6 rounded-xl border border-slate-200 shadow-sm dark:bg-slate-900 dark:border-slate-800"
            >
              <div className="flex items-center justify-between">
                <div>
                  <p className="text-sm font-medium text-slate-500 dark:text-slate-400">{card.label}</p>
                  <p className="text-2xl font-bold text-slate-900 mt-1 dark:text-slate-100">
                    {stats[card.key] ?? 0}
                  </p>
                </div>
                <div className={`p-3 rounded-lg ${card.bg} ${card.color} dark:bg-opacity-15`}>
                  <card.icon className="w-6 h-6" />
                </div>
              </div>
              <div className="mt-4 flex items-center text-sm text-emerald-600 dark:text-emerald-400">
                <TrendingUp className="w-4 h-4 mr-1" />
                <span>Live data</span>
                <ArrowUpRight className="w-4 h-4 ml-auto text-slate-400 dark:text-slate-600" />
              </div>
            </div>
          ))}
        </div>
      )}

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div className="lg:col-span-2 bg-white rounded-xl border border-slate-200 shadow-sm p-6 dark:bg-slate-900 dark:border-slate-800">
          <h3 className="text-lg font-semibold text-slate-900 mb-4 dark:text-slate-100">Recent Activity</h3>
          {!stats || !stats.recentActivity || stats.recentActivity.length === 0 ? (
            <div className="text-sm text-slate-500 dark:text-slate-400">No recent activity.</div>
          ) : (
            <div className="space-y-3">
              {stats.recentActivity.map((item) => (
                <div
                  key={item.id}
                  className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 py-3 border-b border-slate-100 dark:border-slate-800 last:border-0 last:pb-0"
                >
                  <div>
                    <p className="text-sm font-medium text-slate-900 dark:text-slate-100">
                      {item.customer_name || item.customer_phone || 'Guest'} — {item.plan_name || 'Unknown plan'}
                    </p>
                    <p className="text-xs text-slate-500 dark:text-slate-400">
                      {item.voucher_code ? `Voucher ${item.voucher_code}` : 'Payment initiated'} • {new Date(item.created_at).toLocaleString()}
                    </p>
                  </div>
                  <div className="text-right">
                    <p className="text-sm font-semibold text-slate-900 dark:text-slate-100">
                      {item.currency} {Number(item.amount).toFixed(2)}
                    </p>
                    <span
                      className={`inline-flex items-center px-2 py-0.5 rounded text-xs font-medium ${
                        item.status === 'paid'
                          ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400'
                          : item.status === 'pending'
                          ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400'
                          : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400'
                      }`}
                    >
                      {item.status}
                    </span>
                  </div>
                </div>
              ))}
            </div>
          )}
        </div>
        <div className="bg-white rounded-xl border border-slate-200 shadow-sm p-6 dark:bg-slate-900 dark:border-slate-800">
          <h3 className="text-lg font-semibold text-slate-900 mb-4 dark:text-slate-100">Quick Actions</h3>
          <div className="space-y-2">
            <button className="w-full text-left px-4 py-2.5 rounded-lg border border-slate-200 text-sm font-medium text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">
              Create Hotspot
            </button>
            <button className="w-full text-left px-4 py-2.5 rounded-lg border border-slate-200 text-sm font-medium text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">
              Generate Vouchers
            </button>
            <button className="w-full text-left px-4 py-2.5 rounded-lg border border-slate-200 text-sm font-medium text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">
              View Reports
            </button>
          </div>
        </div>
      </div>
    </div>
  );
}

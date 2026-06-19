import { useEffect, useState } from 'react';
import { Users, Wifi, Ticket, CreditCard, TrendingUp } from 'lucide-react';
import { fetchAnalytics } from '../api/client';

const statCard = (key, label, icon, color, bg) => ({ key, label, icon, color, bg });
const cards = [
  statCard('totalCustomers', 'Total Customers', Users, 'text-blue-600', 'bg-blue-50'),
  statCard('onlineUsers', 'Online Now', Wifi, 'text-emerald-600', 'bg-emerald-50'),
  statCard('totalVouchers', 'Total Vouchers', Ticket, 'text-amber-600', 'bg-amber-50'),
  statCard('totalRevenue', 'Total Revenue', CreditCard, 'text-violet-600', 'bg-violet-50'),
];

export default function Analytics() {
  const [data, setData] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    fetchAnalytics()
      .then((d) => {
        setData(d);
        setLoading(false);
      })
      .catch((err) => {
        setError(err.message);
        setLoading(false);
      });
  }, []);

  return (
    <div className="space-y-6">
      <div>
        <h2 className="text-2xl font-bold text-slate-900 dark:text-slate-100">Analytics</h2>
        <p className="text-slate-500 mt-1 dark:text-slate-400">Session usage, revenue, and network insights.</p>
      </div>

      {error && (
        <div className="bg-red-50 text-red-700 p-4 rounded-lg border border-red-200 dark:bg-red-950/30 dark:text-red-400 dark:border-red-900/50">
          Failed to load analytics: {error}
        </div>
      )}

      {loading && (
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
          {cards.map((c) => (
            <div key={c.key} className="bg-white p-6 rounded-xl border border-slate-200 shadow-sm animate-pulse dark:bg-slate-900 dark:border-slate-800">
              <div className="h-8 bg-slate-200 rounded w-1/3 mb-4 dark:bg-slate-700"></div>
              <div className="h-6 bg-slate-200 rounded w-2/3 dark:bg-slate-700"></div>
            </div>
          ))}
        </div>
      )}

      {!loading && data && (
        <>
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            {cards.map((c) => (
              <div key={c.key} className="bg-white p-6 rounded-xl border border-slate-200 shadow-sm dark:bg-slate-900 dark:border-slate-800">
                <div className="flex items-center justify-between">
                  <div>
                    <p className="text-sm font-medium text-slate-500 dark:text-slate-400">{c.label}</p>
                    <p className="text-2xl font-bold text-slate-900 mt-1 dark:text-slate-100">
                      {c.key === 'totalRevenue' ? `MWK ${Number(data[c.key] || 0).toLocaleString()}` : data[c.key] ?? 0}
                    </p>
                  </div>
                  <div className={`p-3 rounded-lg ${c.bg} ${c.color} dark:bg-opacity-15`}>
                    <c.icon className="w-6 h-6" />
                  </div>
                </div>
              </div>
            ))}
          </div>

          <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div className="bg-white rounded-xl border border-slate-200 shadow-sm p-6 dark:bg-slate-900 dark:border-slate-800">
              <h3 className="text-lg font-semibold text-slate-900 mb-4 dark:text-slate-100">Top Selling Plans</h3>
              {(!data.topPlans || data.topPlans.length === 0) ? (
                <div className="text-sm text-slate-500 dark:text-slate-400">No sales data yet.</div>
              ) : (
                <div className="space-y-3">
                  {data.topPlans.map((plan, i) => (
                    <div key={i} className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1 py-2 border-b border-slate-100 dark:border-slate-800 last:border-0">
                      <div>
                        <p className="text-sm font-medium text-slate-900 dark:text-slate-100">{plan.plan_name}</p>
                        <p className="text-xs text-slate-500 dark:text-slate-400">{plan.sales} sold</p>
                      </div>
                      <p className="text-sm font-semibold text-slate-900 dark:text-slate-100">MWK {Number(plan.revenue).toFixed(2)}</p>
                    </div>
                  ))}
                </div>
              )}
            </div>

            <div className="bg-white rounded-xl border border-slate-200 shadow-sm p-6 dark:bg-slate-900 dark:border-slate-800">
              <h3 className="text-lg font-semibold text-slate-900 mb-4 dark:text-slate-100">Revenue (Last 30 Days)</h3>
              {(!data.dailyRevenue || data.dailyRevenue.length === 0) ? (
                <div className="text-sm text-slate-500 dark:text-slate-400">No daily revenue data yet.</div>
              ) : (
                <div className="space-y-3">
                  {data.dailyRevenue.map((day, i) => (
                    <div key={i} className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1 py-2 border-b border-slate-100 dark:border-slate-800 last:border-0">
                      <div>
                        <p className="text-sm font-medium text-slate-900 dark:text-slate-100">{new Date(day.date).toLocaleDateString()}</p>
                        <p className="text-xs text-slate-500 dark:text-slate-400">{day.sales} sales</p>
                      </div>
                      <p className="text-sm font-semibold text-slate-900 dark:text-slate-100">MWK {Number(day.revenue).toFixed(2)}</p>
                    </div>
                  ))}
                </div>
              )}
            </div>
          </div>
        </>
      )}
    </div>
  );
}

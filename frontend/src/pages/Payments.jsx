import { useEffect, useState } from 'react';
import { CreditCard, CheckCircle, XCircle, Clock } from 'lucide-react';
import { fetchPayments } from '../api/client';

export default function Payments() {
  const [payments, setPayments] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    fetchPayments()
      .then(setPayments)
      .catch((err) => setError(err.response?.data?.error || err.message))
      .finally(() => setLoading(false));
  }, []);

  return (
    <div className="space-y-6">
      <div>
        <h2 className="text-2xl font-bold text-slate-900 dark:text-slate-100">Payments</h2>
        <p className="text-slate-500 mt-1 dark:text-slate-400">View payment transactions and gateway status.</p>
      </div>

      {error && (
        <div className="bg-red-50 text-red-700 p-4 rounded-lg border border-red-200 dark:bg-red-950/30 dark:text-red-400 dark:border-red-900/50">
          {error}
        </div>
      )}

      <div className="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden dark:bg-slate-900 dark:border-slate-800">
        {loading ? (
          <div className="p-8 text-center text-slate-500 dark:text-slate-400">Loading payments...</div>
        ) : payments.length === 0 ? (
          <div className="p-8 text-center text-slate-500 dark:text-slate-400">
            No payments yet. Transactions will appear here when customers make purchases.
          </div>
        ) : (
          <div className="overflow-x-auto">
            <table className="w-full text-sm text-left">
              <thead className="bg-slate-50 dark:bg-slate-950/50 border-b border-slate-200 dark:border-slate-800 text-slate-500 dark:text-slate-400">
                <tr>
                  <th className="px-6 py-3 font-medium">Reference</th>
                  <th className="px-6 py-3 font-medium">Customer</th>
                  <th className="px-6 py-3 font-medium">Plan</th>
                  <th className="px-6 py-3 font-medium">Amount</th>
                  <th className="px-6 py-3 font-medium">Gateway</th>
                  <th className="px-6 py-3 font-medium">Status</th>
                  <th className="px-6 py-3 font-medium">Date</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-slate-200 dark:divide-slate-800">
                {payments.map((p) => (
                  <tr key={p.id} className="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                    <td className="px-6 py-4 font-mono text-xs text-slate-900 dark:text-slate-100">{p.reference}</td>
                    <td className="px-6 py-4 text-slate-600 dark:text-slate-300">{p.customer_phone || '—'}</td>
                    <td className="px-6 py-4 text-slate-600 dark:text-slate-300">{p.plan_name || '—'}</td>
                    <td className="px-6 py-4 text-slate-900 dark:text-slate-100 font-medium">
                      {p.currency} {Number(p.amount).toFixed(2)}
                    </td>
                    <td className="px-6 py-4 text-slate-600 dark:text-slate-300 capitalize">
                      <span className="flex items-center gap-1">
                        <CreditCard className="w-3.5 h-3.5" />
                        {p.gateway?.replace('-', ' ')}
                      </span>
                    </td>
                    <td className="px-6 py-4">
                      <span
                        className={`inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium ${
                          p.status === 'paid'
                            ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400'
                            : p.status === 'pending'
                            ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400'
                            : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400'
                        }`}
                      >
                        {p.status === 'paid' && <CheckCircle className="w-3 h-3" />}
                        {p.status === 'pending' && <Clock className="w-3 h-3" />}
                        {p.status === 'failed' && <XCircle className="w-3 h-3" />}
                        {p.status}
                      </span>
                    </td>
                    <td className="px-6 py-4 text-slate-500 dark:text-slate-400 text-xs">
                      {new Date(p.created_at).toLocaleString()}
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        )}
      </div>
    </div>
  );
}

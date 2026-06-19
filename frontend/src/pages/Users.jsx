import { useEffect, useState } from 'react';
import { Search, User, Phone, Mail, Calendar } from 'lucide-react';
import Modal from '../components/Modal';
import { fetchCustomers, fetchCustomerDetail } from '../api/client';

export default function Users() {
  const [customers, setCustomers] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [search, setSearch] = useState('');
  const [detail, setDetail] = useState(null);
  const [detailLoading, setDetailLoading] = useState(false);

  const loadCustomers = async () => {
    try {
      setLoading(true);
      const data = await fetchCustomers(search ? { search } : {});
      setCustomers(data);
      setError(null);
    } catch (err) {
      setError(err.response?.data?.error || err.message);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    loadCustomers();
  }, []);

  const handleSearch = (e) => {
    e.preventDefault();
    loadCustomers();
  };

  const openDetail = async (customer) => {
    setDetailLoading(true);
    try {
      const data = await fetchCustomerDetail(customer.id);
      setDetail(data);
    } catch (err) {
      setError(err.response?.data?.error || 'Failed to load customer details');
    } finally {
      setDetailLoading(false);
    }
  };

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <div>
          <h2 className="text-2xl font-bold text-slate-900 dark:text-slate-100">Users</h2>
          <p className="text-slate-500 mt-1 dark:text-slate-400">Manage customer accounts and purchase history.</p>
        </div>
      </div>

      {error && (
        <div className="bg-red-50 text-red-700 p-4 rounded-lg border border-red-200 dark:bg-red-950/30 dark:text-red-400 dark:border-red-900/50">
          {error}
        </div>
      )}

      <form onSubmit={handleSearch} className="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
        <div className="relative flex-1 max-w-md">
          <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
          <input
            type="text"
            value={search}
            onChange={(e) => setSearch(e.target.value)}
            placeholder="Search by name, phone, or email..."
            className="w-full pl-10 pr-4 py-2 rounded-lg bg-white border border-slate-200 text-sm text-slate-900 dark:bg-slate-900 dark:border-slate-700 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-600"
          />
        </div>
        <button
          type="submit"
          className="px-4 py-2 bg-brand-600 text-white rounded-lg text-sm font-medium hover:bg-brand-700"
        >
          Search
        </button>
      </form>

      <div className="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden dark:bg-slate-900 dark:border-slate-800">
        {loading ? (
          <div className="p-8 text-center text-slate-500 dark:text-slate-400">Loading customers...</div>
        ) : customers.length === 0 ? (
          <div className="p-8 text-center text-slate-500 dark:text-slate-400">
            No customers found. They appear here when they make a purchase.
          </div>
        ) : (
          <div className="overflow-x-auto">
            <table className="w-full text-sm text-left">
              <thead className="bg-slate-50 dark:bg-slate-950/50 border-b border-slate-200 dark:border-slate-800 text-slate-500 dark:text-slate-400">
                <tr>
                  <th className="px-6 py-3 font-medium">Customer</th>
                  <th className="px-6 py-3 font-medium">Phone</th>
                  <th className="px-6 py-3 font-medium">Email</th>
                  <th className="px-6 py-3 font-medium">Joined</th>
                  <th className="px-6 py-3 font-medium text-right">Actions</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-slate-200 dark:divide-slate-800">
                {customers.map((c) => (
                  <tr key={c.id} className="hover:bg-slate-50 dark:hover:bg-slate-800/50 cursor-pointer" onClick={() => openDetail(c)}>
                    <td className="px-6 py-4">
                      <div className="flex items-center gap-3">
                        <div className="w-8 h-8 rounded-full bg-brand-100 dark:bg-brand-900/30 flex items-center justify-center">
                          <User className="w-4 h-4 text-brand-600 dark:text-brand-400" />
                        </div>
                        <span className="font-medium text-slate-900 dark:text-slate-100">
                          {c.full_name || 'Unnamed'}
                        </span>
                      </div>
                    </td>
                    <td className="px-6 py-4 text-slate-600 dark:text-slate-300">{c.phone}</td>
                    <td className="px-6 py-4 text-slate-600 dark:text-slate-300">{c.email || '—'}</td>
                    <td className="px-6 py-4 text-slate-500 dark:text-slate-400">{new Date(c.created_at).toLocaleDateString()}</td>
                    <td className="px-6 py-4 text-right">
                      <button
                        onClick={(e) => { e.stopPropagation(); openDetail(c); }}
                        className="text-sm text-brand-600 dark:text-brand-400 hover:underline"
                      >
                        View
                      </button>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        )}
      </div>

      <Modal isOpen={!!detail} onClose={() => setDetail(null)} title="Customer Details">
        {detailLoading ? (
          <div className="p-4 text-center text-slate-500 dark:text-slate-400">Loading...</div>
        ) : detail ? (
          <div className="space-y-5">
            <div className="flex items-center gap-3">
              <div className="w-10 h-10 rounded-full bg-brand-100 dark:bg-brand-900/30 flex items-center justify-center">
                <User className="w-5 h-5 text-brand-600 dark:text-brand-400" />
              </div>
              <div>
                <p className="text-base font-semibold text-slate-900 dark:text-slate-100">
                  {detail.customer.full_name || 'Unnamed'}
                </p>
                <p className="text-xs text-slate-500 dark:text-slate-400">ID: {detail.customer.id}</p>
              </div>
            </div>

            <div className="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
              <div className="flex items-center gap-2 text-slate-600 dark:text-slate-300">
                <Phone className="w-4 h-4 text-slate-400" />
                {detail.customer.phone}
              </div>
              {detail.customer.email && (
                <div className="flex items-center gap-2 text-slate-600 dark:text-slate-300">
                  <Mail className="w-4 h-4 text-slate-400" />
                  {detail.customer.email}
                </div>
              )}
              <div className="flex items-center gap-2 text-slate-600 dark:text-slate-300">
                <Calendar className="w-4 h-4 text-slate-400" />
                Joined {new Date(detail.customer.created_at).toLocaleDateString()}
              </div>
            </div>

            <div>
              <h4 className="text-sm font-semibold text-slate-900 dark:text-slate-100 mb-2">Purchase History</h4>
              {(!detail.payments || detail.payments.length === 0) ? (
                <p className="text-sm text-slate-500 dark:text-slate-400">No purchases yet.</p>
              ) : (
                <div className="space-y-2 max-h-64 overflow-y-auto pr-1">
                  {detail.payments.map((p) => (
                    <div
                      key={p.id}
                      className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 p-3 rounded-lg bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800"
                    >
                      <div>
                        <p className="text-sm font-medium text-slate-900 dark:text-slate-100">
                          {p.plan_name || 'Unknown plan'}
                        </p>
                        <p className="text-xs text-slate-500 dark:text-slate-400">
                          {p.voucher_code ? `Voucher: ${p.voucher_code}` : p.reference || 'No reference'}
                        </p>
                      </div>
                      <div className="text-right">
                        <p className="text-sm font-semibold text-slate-900 dark:text-slate-100">
                          {p.currency} {Number(p.amount).toFixed(2)}
                        </p>
                        <span
                          className={`inline-flex items-center px-2 py-0.5 rounded text-xs font-medium ${
                            p.status === 'paid'
                              ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400'
                              : p.status === 'pending'
                              ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400'
                              : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400'
                          }`}
                        >
                          {p.status}
                        </span>
                      </div>
                    </div>
                  ))}
                </div>
              )}
            </div>
          </div>
        ) : null}
      </Modal>
    </div>
  );
}

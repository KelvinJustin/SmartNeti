import { useEffect, useState } from 'react';
import { Plus, Copy, Printer, QrCode, Download, X } from 'lucide-react';
import { useSearchParams } from 'react-router-dom';
import QRCode from 'qrcode';
import Modal from '../components/Modal';
import { fetchVouchers, fetchPlans, generateVouchers } from '../api/client';

const CAPTIVE_URL = import.meta.env.VITE_CAPTIVE_PORTAL_URL || window.location.origin + '/captive';

export default function Vouchers() {
  const [searchParams, setSearchParams] = useSearchParams();
  const [vouchers, setVouchers] = useState([]);
  const [plans, setPlans] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [isGenerateOpen, setIsGenerateOpen] = useState(false);
  const [generatePlanId, setGeneratePlanId] = useState('');
  const [generateCount, setGenerateCount] = useState(10);
  const [generating, setGenerating] = useState(false);
  const [qrModal, setQrModal] = useState({ open: false, code: '', dataUrl: '' });

  const filterPlanId = searchParams.get('planId') || '';
  const filterStatus = searchParams.get('status') || '';

  const loadData = async () => {
    try {
      setLoading(true);
      const [voucherData, planData] = await Promise.all([
        fetchVouchers({ planId: filterPlanId || undefined, status: filterStatus || undefined }),
        fetchPlans(),
      ]);
      setVouchers(voucherData);
      setPlans(planData);
      setError(null);
    } catch (err) {
      setError(err.response?.data?.error || err.message);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    loadData();
  }, [filterPlanId, filterStatus]);

  const handleFilterChange = (key, value) => {
    const next = new URLSearchParams(searchParams);
    if (value) {
      next.set(key, value);
    } else {
      next.delete(key);
    }
    setSearchParams(next);
  };

  const handleGenerate = async (e) => {
    e.preventDefault();
    if (!generatePlanId || generateCount <= 0) return;
    setGenerating(true);
    try {
      await generateVouchers(generatePlanId, generateCount);
      await loadData();
      setIsGenerateOpen(false);
      setGeneratePlanId('');
      setGenerateCount(10);
    } catch (err) {
      setError(err.response?.data?.error || 'Failed to generate vouchers');
    } finally {
      setGenerating(false);
    }
  };

  const copyCodes = () => {
    const text = vouchers.map((v) => v.code).join('\n');
    navigator.clipboard.writeText(text).catch(() => {});
  };

  const printSelected = () => {
    const printWindow = window.open('', '_blank');
    if (!printWindow) return;
    const rows = vouchers
      .map(
        (v) => `
      <tr>
        <td style="padding:8px;border:1px solid #cbd5e1;font-family:monospace;font-size:18px;">${v.code}</td>
        <td style="padding:8px;border:1px solid #cbd5e1;">${v.plan_name}</td>
        <td style="padding:8px;border:1px solid #cbd5e1;">${v.status}</td>
      </tr>`
      )
      .join('');
    printWindow.document.write(`
      <html><head><title>Vouchers</title></head><body>
        <table style="width:100%;border-collapse:collapse;">
          <thead><tr>
            <th style="padding:8px;border:1px solid #cbd5e1;text-align:left;">Code</th>
            <th style="padding:8px;border:1px solid #cbd5e1;text-align:left;">Plan</th>
            <th style="padding:8px;border:1px solid #cbd5e1;text-align:left;">Status</th>
          </tr></thead>
          <tbody>${rows}</tbody>
        </table>
        <script>window.print();</script>
      </body></html>
    `);
    printWindow.document.close();
  };

  const showQr = async (code) => {
    try {
      const url = `${CAPTIVE_URL}?code=${encodeURIComponent(code)}`;
      const dataUrl = await QRCode.toDataURL(url, { width: 256, margin: 2 });
      setQrModal({ open: true, code, dataUrl });
    } catch {
      setError('Failed to generate QR code');
    }
  };

  const downloadQr = () => {
    const link = document.createElement('a');
    link.href = qrModal.dataUrl;
    link.download = `voucher-${qrModal.code}.png`;
    link.click();
  };

  return (
    <div className="space-y-6">
      <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h2 className="text-2xl font-bold text-slate-900 dark:text-slate-100">Vouchers</h2>
          <p className="text-slate-500 mt-1 dark:text-slate-400">Generate and manage access voucher codes.</p>
        </div>
        <div className="flex flex-wrap items-center gap-2">
          <button
            onClick={copyCodes}
            className="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 dark:bg-slate-900 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
            title="Copy all codes"
          >
            <Copy className="w-4 h-4" />
            Copy
          </button>
          <button
            onClick={printSelected}
            className="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 dark:bg-slate-900 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
            title="Print vouchers"
          >
            <Printer className="w-4 h-4" />
            Print
          </button>
          <button
            onClick={() => setIsGenerateOpen(true)}
            className="inline-flex items-center gap-2 px-4 py-2 bg-brand-600 text-white rounded-lg text-sm font-medium hover:bg-brand-700"
          >
            <Plus className="w-4 h-4" />
            Generate
          </button>
        </div>
      </div>

      {error && (
        <div className="bg-red-50 text-red-700 p-4 rounded-lg border border-red-200 dark:bg-red-950/30 dark:text-red-400 dark:border-red-900/50">
          {error}
        </div>
      )}

      <div className="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
        <select
          value={filterPlanId}
          onChange={(e) => handleFilterChange('planId', e.target.value)}
          className="px-3 py-2 rounded-lg bg-white border border-slate-200 text-sm text-slate-900 dark:bg-slate-900 dark:border-slate-700 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-600"
        >
          <option value="">All Plans</option>
          {plans.map((p) => (
            <option key={p.id} value={p.id}>
              {p.name}
            </option>
          ))}
        </select>
        <select
          value={filterStatus}
          onChange={(e) => handleFilterChange('status', e.target.value)}
          className="px-3 py-2 rounded-lg bg-white border border-slate-200 text-sm text-slate-900 dark:bg-slate-900 dark:border-slate-700 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-600"
        >
          <option value="">All Statuses</option>
          <option value="available">Available</option>
          <option value="used">Used</option>
          <option value="expired">Expired</option>
        </select>
      </div>

      <div className="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden dark:bg-slate-900 dark:border-slate-800">
        {loading ? (
          <div className="p-8 text-center text-slate-500 dark:text-slate-400">Loading vouchers...</div>
        ) : vouchers.length === 0 ? (
          <div className="p-8 text-center text-slate-500 dark:text-slate-400">
            No vouchers match the current filters. Click "Generate" to create new vouchers.
          </div>
        ) : (
          <div className="overflow-x-auto">
            <table className="w-full text-sm text-left">
              <thead className="bg-slate-50 dark:bg-slate-950/50 border-b border-slate-200 dark:border-slate-800 text-slate-500 dark:text-slate-400">
                <tr>
                  <th className="px-6 py-3 font-medium">Code</th>
                  <th className="px-6 py-3 font-medium">Plan</th>
                  <th className="px-6 py-3 font-medium">Status</th>
                  <th className="px-6 py-3 font-medium">Source</th>
                  <th className="px-6 py-3 font-medium">Created</th>
                  <th className="px-6 py-3 font-medium text-right">Actions</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-slate-200 dark:divide-slate-800">
                {vouchers.map((v) => (
                  <tr key={v.id} className="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                    <td className="px-6 py-4 font-mono font-medium text-slate-900 dark:text-slate-100">{v.code}</td>
                    <td className="px-6 py-4 text-slate-600 dark:text-slate-300">
                      {v.plan_name} ({v.plan_duration_minutes} min)
                    </td>
                    <td className="px-6 py-4">
                      <span
                        className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${
                          v.status === 'available'
                            ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400'
                            : v.status === 'used'
                            ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400'
                            : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400'
                        }`}
                      >
                        {v.status}
                      </span>
                    </td>
                    <td className="px-6 py-4">
                      <span
                        className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${
                          v.source === 'purchased'
                            ? 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400'
                            : v.source === 'bulk'
                            ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400'
                            : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400'
                        }`}
                        title={
                          v.source === 'purchased'
                            ? `Customer: ${v.customer_name || v.customer_phone || 'Unknown'}`
                            : v.source === 'bulk'
                            ? `Generated by: ${v.generated_by_name || 'Unknown'}`
                            : ''
                        }
                      >
                        {v.source}
                      </span>
                    </td>
                    <td className="px-6 py-4 text-slate-500 dark:text-slate-400">
                      {new Date(v.created_at).toLocaleString()}
                    </td>
                    <td className="px-6 py-4 text-right">
                      <div className="flex items-center justify-end gap-2">
                        <button
                          onClick={() => showQr(v.code)}
                          className="p-2 text-slate-500 hover:text-brand-600 hover:bg-brand-50 rounded-lg dark:text-slate-400 dark:hover:text-brand-400 dark:hover:bg-brand-900/20"
                          title="Show QR code"
                        >
                          <QrCode className="w-4 h-4" />
                        </button>
                        <button
                          onClick={() => navigator.clipboard.writeText(v.code).catch(() => {})}
                          className="p-2 text-slate-500 hover:text-brand-600 hover:bg-brand-50 rounded-lg dark:text-slate-400 dark:hover:text-brand-400 dark:hover:bg-brand-900/20"
                          title="Copy code"
                        >
                          <Copy className="w-4 h-4" />
                        </button>
                      </div>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        )}
      </div>

      <Modal isOpen={isGenerateOpen} onClose={() => setIsGenerateOpen(false)} title="Generate Vouchers">
        <form onSubmit={handleGenerate} className="space-y-4">
          <div>
            <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Plan *</label>
            <select
              value={generatePlanId}
              onChange={(e) => setGeneratePlanId(e.target.value)}
              required
              className="w-full px-4 py-2 rounded-lg bg-slate-50 border border-slate-200 text-slate-900 dark:bg-slate-950 dark:border-slate-700 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-600"
            >
              <option value="">Select a plan</option>
              {plans
                .filter((p) => p.status === 'active')
                .map((p) => (
                  <option key={p.id} value={p.id}>
                    {p.name} ({p.duration_minutes} min — {p.currency} {Number(p.price).toFixed(2)})
                  </option>
                ))}
            </select>
          </div>
          <div>
            <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Count *</label>
            <input
              type="number"
              min={1}
              max={100}
              value={generateCount}
              onChange={(e) => setGenerateCount(Math.min(100, Math.max(1, Number(e.target.value))))}
              required
              className="w-full px-4 py-2 rounded-lg bg-slate-50 border border-slate-200 text-slate-900 dark:bg-slate-950 dark:border-slate-700 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-600"
            />
          </div>
          <div className="flex justify-end gap-3 pt-2">
            <button
              type="button"
              onClick={() => setIsGenerateOpen(false)}
              className="px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100 rounded-lg dark:text-slate-300 dark:hover:bg-slate-800"
            >
              Cancel
            </button>
            <button
              type="submit"
              disabled={generating}
              className="px-4 py-2 text-sm font-medium text-white bg-brand-600 hover:bg-brand-700 rounded-lg disabled:opacity-60"
            >
              {generating ? 'Generating...' : 'Generate'}
            </button>
          </div>
        </form>
      </Modal>

      {qrModal.open && (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
          <div className="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-xl w-full max-w-sm">
            <div className="flex items-center justify-between px-6 py-4 border-b border-slate-200 dark:border-slate-800">
              <h3 className="text-lg font-semibold text-slate-900 dark:text-slate-100">Voucher QR</h3>
              <button
                onClick={() => setQrModal({ open: false, code: '', dataUrl: '' })}
                className="p-1 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300"
                aria-label="Close"
              >
                <X className="w-5 h-5" />
              </button>
            </div>
            <div className="p-6 flex flex-col items-center gap-4">
              <div className="font-mono text-lg font-medium text-slate-900 dark:text-slate-100">{qrModal.code}</div>
              <img src={qrModal.dataUrl} alt="QR code" className="rounded-lg border border-slate-200 dark:border-slate-700" />
              <button
                onClick={downloadQr}
                className="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-brand-600 hover:bg-brand-700 rounded-lg"
              >
                <Download className="w-4 h-4" />
                Download QR
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}


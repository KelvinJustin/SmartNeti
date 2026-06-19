import { useState, useRef } from 'react';
import { Ticket, ArrowRight, CheckCircle, XCircle, Wifi } from 'lucide-react';
import { validateVoucher } from '../api/client';

export default function PortalVoucher() {
  const [code, setCode] = useState('');
  const [result, setResult] = useState(null);
  const [error, setError] = useState(null);
  const [loading, setLoading] = useState(false);
  const submittingRef = useRef(false);

  const handleSubmit = async (e) => {
    e.preventDefault();
    if (submittingRef.current || !code.trim()) return;
    submittingRef.current = true;
    setLoading(true);
    setError(null);
    setResult(null);

    try {
      const data = await validateVoucher(code.trim().toUpperCase());
      setResult(data.voucher);
    } catch (err) {
      setError(err.response?.data?.error || 'Failed to validate voucher');
    } finally {
      setLoading(false);
      submittingRef.current = false;
    }
  };

  return (
    <div className="space-y-6 py-4">
      <div className="text-center space-y-2">
        <div className="w-12 h-12 bg-brand-100 dark:bg-brand-900/30 rounded-xl flex items-center justify-center mx-auto">
          <Ticket className="w-6 h-6 text-brand-600 dark:text-brand-400" />
        </div>
        <h2 className="text-xl font-bold text-slate-900 dark:text-slate-100">Enter Voucher Code</h2>
        <p className="text-sm text-slate-500 dark:text-slate-400">
          Type or paste your voucher code to get online.
        </p>
      </div>

      <form onSubmit={handleSubmit} className="space-y-4">
        <div>
          <label className="sr-only">Voucher Code</label>
          <input
            type="text"
            value={code}
            onChange={(e) => setCode(e.target.value.toUpperCase())}
            required
            maxLength={10}
            placeholder="e.g. A1B2C3D4"
            className="w-full px-4 py-3 text-center text-lg font-mono tracking-widest rounded-lg bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-600 uppercase placeholder:normal-case placeholder:tracking-normal"
          />
        </div>

        <button
          type="submit"
          disabled={loading}
          className="w-full py-2.5 bg-brand-600 text-white rounded-lg text-sm font-medium hover:bg-brand-700 disabled:opacity-60 flex items-center justify-center gap-2"
        >
          {loading ? (
            <Wifi className="w-4 h-4 animate-pulse" />
          ) : (
            <>
              Get Online
              <ArrowRight className="w-4 h-4" />
            </>
          )}
        </button>
      </form>

      {error && (
        <div className="flex items-center gap-3 p-4 bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-900/50 rounded-xl">
          <XCircle className="w-5 h-5 text-red-500 shrink-0" />
          <p className="text-sm text-red-700 dark:text-red-400">{error}</p>
        </div>
      )}

      {result && (
        <div className="space-y-4">
          <div className="flex items-center gap-3 p-4 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-900/50 rounded-xl">
            <CheckCircle className="w-5 h-5 text-emerald-500 shrink-0" />
            <div>
              <p className="text-sm font-medium text-emerald-800 dark:text-emerald-400">Voucher is valid!</p>
              <p className="text-xs text-emerald-600 dark:text-emerald-500">
                {result.plan} &bull; {result.durationMinutes} minutes
              </p>
            </div>
          </div>

          <div className="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-6 text-center space-y-3">
            <p className="text-sm text-slate-500 dark:text-slate-400">Your voucher code</p>
            <div className="font-mono text-3xl font-bold tracking-wider text-slate-900 dark:text-slate-100">
              {result.code}
            </div>
            <button
              onClick={() => navigator.clipboard.writeText(result.code).catch(() => {})}
              className="text-sm text-brand-600 hover:text-brand-700 dark:text-brand-400"
            >
              Copy to clipboard
            </button>
          </div>

          <button
            onClick={() => { setCode(''); setResult(null); setError(null); }}
            className="w-full py-2.5 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 rounded-lg text-sm font-medium hover:bg-slate-200 dark:hover:bg-slate-700"
          >
            Enter Another Code
          </button>
        </div>
      )}
    </div>
  );
}

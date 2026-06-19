import { useEffect, useState } from 'react';
import { useParams, Link } from 'react-router-dom';
import { Loader2, CheckCircle, XCircle, Ticket, Copy } from 'lucide-react';
import { getPaymentStatus, mockCompletePayment } from '../api/client';

export default function PortalPaymentStatus() {
  const { reference } = useParams();
  const [payment, setPayment] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [polling, setPolling] = useState(true);
  const [mocking, setMocking] = useState(false);

  const loadStatus = async () => {
    try {
      const data = await getPaymentStatus(reference);
      setPayment(data.payment);
      if (data.payment.status === 'paid' || data.payment.status === 'failed') {
        setPolling(false);
      }
    } catch (err) {
      setError(err.response?.data?.error || 'Failed to check status');
      setPolling(false);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    loadStatus();
  }, [reference]);

  useEffect(() => {
    if (!polling) return;
    const interval = setInterval(loadStatus, 3000);
    return () => clearInterval(interval);
  }, [polling, reference]);

  const handleMockComplete = async () => {
    setMocking(true);
    try {
      await mockCompletePayment(reference);
      await loadStatus();
    } catch (err) {
      setError(err.response?.data?.error || 'Mock completion failed');
    } finally {
      setMocking(false);
    }
  };

  if (loading) {
    return (
      <div className="py-12 text-center space-y-3">
        <Loader2 className="w-8 h-8 animate-spin mx-auto text-brand-600" />
        <p className="text-slate-500 dark:text-slate-400 text-sm">Checking payment status...</p>
      </div>
    );
  }

  if (error) {
    return (
      <div className="py-12 text-center space-y-3">
        <XCircle className="w-10 h-10 mx-auto text-red-500" />
        <p className="text-red-600 dark:text-red-400 text-sm">{error}</p>
        <Link to="/portal/plans" className="text-brand-600 dark:text-brand-400 text-sm font-medium">
          Back to Plans
        </Link>
      </div>
    );
  }

  if (payment?.status === 'paid') {
    return (
      <div className="py-8 text-center space-y-5">
        <CheckCircle className="w-14 h-14 mx-auto text-emerald-500" />
        <div>
          <h2 className="text-xl font-bold text-slate-900 dark:text-slate-100">Payment Successful!</h2>
          <p className="text-sm text-slate-500 dark:text-slate-400 mt-1">
            Your voucher has been generated. Use the code below to get online.
          </p>
        </div>

        {payment.voucher_code && (
          <div className="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-6 space-y-3">
            <div className="flex items-center justify-center gap-2 text-slate-500 dark:text-slate-400 text-sm">
              <Ticket className="w-4 h-4" />
              Your Voucher Code
            </div>
            <div className="font-mono text-3xl font-bold tracking-wider text-slate-900 dark:text-slate-100">
              {payment.voucher_code}
            </div>
            <button
              onClick={() => navigator.clipboard.writeText(payment.voucher_code).catch(() => {})}
              className="inline-flex items-center gap-1 text-sm text-brand-600 hover:text-brand-700 dark:text-brand-400"
            >
              <Copy className="w-4 h-4" />
              Copy Code
            </button>
            <div className="text-xs text-slate-400 dark:text-slate-500">
              Plan: {payment.plan_name} &bull; {payment.duration_minutes} minutes
            </div>
          </div>
        )}

        <div className="space-y-2">
          <Link
            to="/portal/voucher"
            className="block w-full py-2.5 bg-brand-600 text-white rounded-lg text-sm font-medium hover:bg-brand-700 text-center"
          >
            Go to Voucher Page
          </Link>
          <Link to="/portal/plans" className="block text-sm text-slate-500 dark:text-slate-400 text-center">
            Browse more plans
          </Link>
        </div>
      </div>
    );
  }

  return (
    <div className="py-8 text-center space-y-5">
      <Loader2 className="w-10 h-10 animate-spin mx-auto text-brand-600" />
      <div>
        <h2 className="text-lg font-bold text-slate-900 dark:text-slate-100">Waiting for Payment</h2>
        <p className="text-sm text-slate-500 dark:text-slate-400 mt-1">
          Please complete the payment on your phone. This page will update automatically.
        </p>
      </div>

      <div className="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-4 text-left space-y-2 text-sm">
        <div className="flex justify-between">
          <span className="text-slate-500 dark:text-slate-400">Reference</span>
          <span className="font-mono text-slate-900 dark:text-slate-100">{payment?.reference}</span>
        </div>
        <div className="flex justify-between">
          <span className="text-slate-500 dark:text-slate-400">Amount</span>
          <span className="text-slate-900 dark:text-slate-100">
            {payment?.currency} {Number(payment?.amount).toFixed(0)}
          </span>
        </div>
        <div className="flex justify-between">
          <span className="text-slate-500 dark:text-slate-400">Gateway</span>
          <span className="text-slate-900 dark:text-slate-100 capitalize">{payment?.gateway?.replace('-', ' ')}</span>
        </div>
      </div>

      {process.env.NODE_ENV === 'development' && (
        <button
          onClick={handleMockComplete}
          disabled={mocking}
          className="text-xs text-slate-400 underline hover:text-slate-600 dark:hover:text-slate-300 disabled:opacity-50"
        >
          {mocking ? 'Completing...' : '[Dev] Simulate payment completion'}
        </button>
      )}

      <p className="text-xs text-slate-400 dark:text-slate-500">
        Do not close this window until payment is confirmed.
      </p>
    </div>
  );
}

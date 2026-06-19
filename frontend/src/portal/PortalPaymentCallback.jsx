import { useEffect, useState } from 'react';
import { useSearchParams, useNavigate, Link } from 'react-router-dom';
import { Loader2, CheckCircle, XCircle } from 'lucide-react';
import { verifyPayment } from '../api/client';

export default function PortalPaymentCallback() {
  const [searchParams] = useSearchParams();
  const navigate = useNavigate();
  const [verifying, setVerifying] = useState(true);
  const [error, setError] = useState(null);

  const txRef = searchParams.get('tx_ref');
  const status = searchParams.get('status');

  useEffect(() => {
    if (!txRef) {
      setError('Missing transaction reference');
      setVerifying(false);
      return;
    }

    // PayChangu redirects to callback_url on success; no status param is passed.
    // We proceed to verify the tx_ref regardless. If the backend says it's not paid,
    // the verify endpoint will return verified:false.
    if (status && status !== 'success') {
      setError(`Payment status: ${status}. Please try again.`);
      setVerifying(false);
      return;
    }

    let cancelled = false;
    verifyPayment(txRef)
      .then((res) => {
        if (cancelled) return;
        if (res.verified) {
          navigate(`/portal/payment/${txRef}`, { replace: true });
        } else {
          setError(res.error || 'Payment verification failed. Please contact support.');
          setVerifying(false);
        }
      })
      .catch((err) => {
        if (cancelled) return;
        setError(err.response?.data?.error || 'Failed to verify payment');
        setVerifying(false);
      });

    return () => {
      cancelled = true;
    };
  }, [txRef, status, navigate]);

  if (verifying) {
    return (
      <div className="py-12 text-center space-y-3">
        <Loader2 className="w-8 h-8 animate-spin mx-auto text-brand-600" />
        <p className="text-slate-500 dark:text-slate-400 text-sm">Verifying payment...</p>
      </div>
    );
  }

  if (error) {
    return (
      <div className="py-12 text-center space-y-3">
        <XCircle className="w-10 h-10 mx-auto text-red-500" />
        <p className="text-red-600 dark:text-red-400 text-sm">{error}</p>
        <div className="space-y-2">
          <Link
            to="/portal/plans"
            className="block w-full py-2.5 bg-brand-600 text-white rounded-lg text-sm font-medium hover:bg-brand-700 text-center"
          >
            Back to Plans
          </Link>
          {txRef && (
            <button
              onClick={() => window.location.reload()}
              className="block w-full text-sm text-slate-500 dark:text-slate-400 text-center hover:text-slate-700 dark:hover:text-slate-200"
            >
              Retry verification
            </button>
          )}
        </div>
      </div>
    );
  }

  return (
    <div className="py-12 text-center space-y-3">
      <CheckCircle className="w-10 h-10 mx-auto text-emerald-500" />
      <p className="text-emerald-600 dark:text-emerald-400 text-sm">Payment verified successfully!</p>
    </div>
  );
}

import { useEffect, useState } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { ArrowLeft, Smartphone, CreditCard, Loader2, User, Mail } from 'lucide-react';
import { fetchPublicPlans, initiatePayment, fetchPublicGateways, verifyPayment } from '../api/client';

export default function PortalBuy() {
  const { planId } = useParams();
  const navigate = useNavigate();
  const [plan, setPlan] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [fullName, setFullName] = useState('');
  const [phone, setPhone] = useState('');
  const [email, setEmail] = useState('');
  const [gateways, setGateways] = useState([]);
  const [gateway, setGateway] = useState('');
  const [submitting, setSubmitting] = useState(false);

  useEffect(() => {
    Promise.all([fetchPublicPlans(), fetchPublicGateways()])
      .then(([plans, gwList]) => {
        const found = plans.find((p) => p.id === planId);
        if (found) {
          setPlan(found);
        } else {
          setError('Plan not found');
        }
        setGateways(gwList);
        if (gwList.length > 0) {
          setGateway(gwList[0].key);
        }
      })
      .catch((err) => setError(err.response?.data?.error || err.message))
      .finally(() => setLoading(false));
  }, [planId]);

  function loadScript(src) {
    return new Promise((resolve, reject) => {
      const script = document.createElement('script');
      script.src = src;
      script.onload = () => resolve();
      script.onerror = () => reject(new Error(`Failed to load ${src}`));
      document.body.appendChild(script);
    });
  }

  async function loadPayChanguScript() {
    if (window.PaychanguCheckout) return;
    if (!window.jQuery && !window.$) {
      await loadScript('https://code.jquery.com/jquery-3.6.0.min.js');
    }
    await loadScript('https://in.paychangu.com/js/popup.js');
  }

  function splitName(name) {
    const parts = (name || '').trim().split(/\s+/);
    return { firstName: parts[0] || '', lastName: parts.slice(1).join(' ') || '' };
  }

  const handleSubmit = async (e) => {
    e.preventDefault();
    if (!phone.trim()) return;
    setSubmitting(true);
    setError(null);
    try {
      const result = await initiatePayment({
        gateway,
        planId,
        customerPhone: phone.trim(),
        customerEmail: email.trim() || undefined,
        customerName: fullName.trim() || undefined,
      });

      if (gateway === 'paychangu' && result.checkout) {
        try {
          await loadPayChanguScript();
        } catch (scriptErr) {
          setError(scriptErr.message || 'Failed to load PayChangu checkout');
          setSubmitting(false);
          return;
        }
        const { firstName, lastName } = splitName(fullName);
        try {
          window.PaychanguCheckout({
            ...result.checkout,
            customer: {
              email: email.trim() || `${phone.trim().replace(/\D/g, '')}@smartneti.local`,
              first_name: firstName || 'Customer',
              last_name: lastName || 'SmartNeti',
            },
          });
        } catch (checkoutErr) {
          setError(checkoutErr.message || 'PayChangu checkout failed');
        }
      } else {
        navigate(`/portal/payment/${result.reference}`);
      }
    } catch (err) {
      setError(err.response?.data?.error || 'Failed to initiate payment');
    } finally {
      setSubmitting(false);
    }
  };

  if (loading) {
    return (
      <div className="py-12 text-center text-slate-500 dark:text-slate-400">
        <Loader2 className="w-6 h-6 animate-spin mx-auto mb-2" />
        Loading...
      </div>
    );
  }

  if (!plan) {
    return (
      <div className="py-12 text-center text-red-600 dark:text-red-400">
        {error || 'Plan not found'}
        <div className="mt-4">
          <button
            onClick={() => navigate('/portal/plans')}
            className="text-brand-600 dark:text-brand-400 text-sm font-medium"
          >
            Back to Plans
          </button>
        </div>
      </div>
    );
  }

  return (
    <div className="space-y-6 py-4">
      <button
        onClick={() => navigate('/portal/plans')}
        className="flex items-center gap-1 text-sm text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-100"
      >
        <ArrowLeft className="w-4 h-4" />
        Back
      </button>

      <div className="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-4 space-y-2">
        <h2 className="text-lg font-bold text-slate-900 dark:text-slate-100">{plan.name}</h2>
        <p className="text-sm text-slate-500 dark:text-slate-400">{plan.description}</p>
        <div className="flex items-center justify-between pt-2">
          <span className="text-sm text-slate-500 dark:text-slate-400">{plan.duration_minutes} minutes</span>
          <span className="text-xl font-bold text-brand-600 dark:text-brand-400">
            {plan.currency} {Number(plan.price).toFixed(0)}
          </span>
        </div>
      </div>

      <form onSubmit={handleSubmit} className="space-y-4">
        {error && (
          <div className="bg-red-50 text-red-700 p-3 rounded-lg text-sm border border-red-200 dark:bg-red-950/30 dark:text-red-400 dark:border-red-900/50">
            {error}
          </div>
        )}

        <div>
          <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Full Name</label>
          <div className="relative">
            <User className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
            <input
              type="text"
              value={fullName}
              onChange={(e) => setFullName(e.target.value)}
              placeholder="e.g. John Banda"
              className="w-full pl-9 pr-4 py-2.5 rounded-lg bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-600"
            />
          </div>
        </div>

        <div>
          <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Phone Number *</label>
          <div className="relative">
            <Smartphone className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
            <input
              type="tel"
              value={phone}
              onChange={(e) => setPhone(e.target.value)}
              required
              placeholder="e.g. 0881234567"
              className="w-full pl-9 pr-4 py-2.5 rounded-lg bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-600"
            />
          </div>
        </div>

        <div>
          <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Email</label>
          <div className="relative">
            <Mail className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
            <input
              type="email"
              value={email}
              onChange={(e) => setEmail(e.target.value)}
              placeholder="yourmail@example.com"
              className="w-full pl-9 pr-4 py-2.5 rounded-lg bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-600"
            />
          </div>
          <p className="text-xs text-slate-400 dark:text-slate-500 mt-1">
            Optional. Used for PayChangu receipts.
          </p>
        </div>

        {gateways.length > 0 ? (
          <div>
            <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Payment Method</label>
            <div className="space-y-2">
              {gateways.map((g) => (
                <label
                  key={g.key}
                  className={`flex items-center gap-3 p-3 rounded-lg border cursor-pointer transition-colors ${
                    gateway === g.key
                      ? 'border-brand-500 bg-brand-50 dark:bg-brand-900/20 dark:border-brand-500'
                      : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900'
                  }`}
                >
                  <input
                    type="radio"
                    name="gateway"
                    value={g.key}
                    checked={gateway === g.key}
                    onChange={() => setGateway(g.key)}
                    className="sr-only"
                  />
                  <CreditCard className="w-5 h-5 text-slate-500 dark:text-slate-400" />
                  <span className="text-sm font-medium text-slate-900 dark:text-slate-100">{g.label}</span>
                </label>
              ))}
            </div>
          </div>
        ) : (
          <div className="bg-amber-50 text-amber-700 p-3 rounded-lg text-sm border border-amber-200 dark:bg-amber-950/30 dark:text-amber-400 dark:border-amber-900/50">
            No payment gateways are currently enabled. Please contact support.
          </div>
        )}

        <div id="wrapper"></div>

        <button
          type="submit"
          disabled={submitting}
          className="w-full py-2.5 bg-brand-600 text-white rounded-lg text-sm font-medium hover:bg-brand-700 disabled:opacity-60 flex items-center justify-center gap-2"
        >
          {submitting ? (
            <>
              <Loader2 className="w-4 h-4 animate-spin" />
              Processing...
            </>
          ) : (
            <>
              Pay {plan.currency} {Number(plan.price).toFixed(0)}
            </>
          )}
        </button>
      </form>
    </div>
  );
}

import { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import { Clock, ArrowRight, Wifi } from 'lucide-react';
import { fetchPublicPlans } from '../api/client';

export default function PortalPlans() {
  const [plans, setPlans] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    fetchPublicPlans()
      .then(setPlans)
      .catch((err) => setError(err.response?.data?.error || err.message))
      .finally(() => setLoading(false));
  }, []);

  if (loading) {
    return (
      <div className="py-12 text-center text-slate-500 dark:text-slate-400">
        <Wifi className="w-6 h-6 animate-pulse mx-auto mb-2" />
        Loading plans...
      </div>
    );
  }

  if (error) {
    return (
      <div className="py-12 text-center text-red-600 dark:text-red-400">{error}</div>
    );
  }

  return (
    <div className="space-y-6 py-4">
      <div>
        <h2 className="text-xl font-bold text-slate-900 dark:text-slate-100">Internet Plans</h2>
        <p className="text-sm text-slate-500 dark:text-slate-400 mt-1">Choose a plan that fits your needs.</p>
      </div>

      {plans.length === 0 ? (
        <div className="text-center py-12 text-slate-500 dark:text-slate-400">
          No plans available at the moment.
        </div>
      ) : (
        <div className="space-y-3">
          {plans.map((plan) => (
            <div
              key={plan.id}
              className="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-4 space-y-3"
            >
              <div className="flex items-start justify-between">
                <div>
                  <h3 className="font-semibold text-slate-900 dark:text-slate-100">{plan.name}</h3>
                  {plan.description && (
                    <p className="text-sm text-slate-500 dark:text-slate-400">{plan.description}</p>
                  )}
                </div>
                <div className="text-right">
                  <div className="text-lg font-bold text-brand-600 dark:text-brand-400">
                    {plan.currency} {Number(plan.price).toFixed(0)}
                  </div>
                </div>
              </div>

              <div className="flex items-center gap-4 text-sm text-slate-500 dark:text-slate-400">
                <span className="flex items-center gap-1">
                  <Clock className="w-4 h-4" />
                  {plan.duration_minutes} min
                </span>
                {plan.speed_down_kbps && (
                  <span>{plan.speed_down_kbps} kbps</span>
                )}
              </div>

              <Link
                to={`/portal/buy/${plan.id}`}
                className="flex items-center justify-center gap-2 w-full py-2.5 bg-brand-600 text-white rounded-lg text-sm font-medium hover:bg-brand-700"
              >
                Buy Now
                <ArrowRight className="w-4 h-4" />
              </Link>
            </div>
          ))}
        </div>
      )}
    </div>
  );
}

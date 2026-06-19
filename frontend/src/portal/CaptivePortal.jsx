import { useState, useEffect, useRef } from 'react';

import { Link, useSearchParams } from 'react-router-dom';
import { Wifi, Globe, ArrowRight, AlertCircle, Loader2 } from 'lucide-react';
import { captiveAuthorize } from '../api/client';

export default function CaptivePortal() {
  const [searchParams] = useSearchParams();
  const [code, setCode] = useState('');
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);
  const [authorized, setAuthorized] = useState(false);
  const [creds, setCreds] = useState(null);
  const formRef = useRef(null);
  const submittingRef = useRef(false);

  const mac = searchParams.get('mac') || '';
  const ip = searchParams.get('ip') || '';
  const linkLogin = searchParams.get('link-login') || '';
  const linkOrig = searchParams.get('link-orig') || '';

  const handleSubmit = async (e) => {
    e.preventDefault();
    if (submittingRef.current || !code.trim()) return;
    submittingRef.current = true;

    setLoading(true);
    setError(null);

    try {
      const data = await captiveAuthorize({
        code: code.trim().toUpperCase(),
        mac,
        ip,
      });
      setCreds(data);
      setAuthorized(true);
    } catch (err) {
      setError(err.response?.data?.error || 'Failed to authorize. Please try again.');
    } finally {
      setLoading(false);
      submittingRef.current = false;
    }
  };

  // Auto-submit to MikroTik link-login when authorized and link-login is present
  useEffect(() => {
    if (authorized && linkLogin && creds && formRef.current) {
      // Give UI a moment to render the success state before auto-submitting
      const timer = setTimeout(() => {
        formRef.current.submit();
      }, 1500);
      return () => clearTimeout(timer);
    }
  }, [authorized, linkLogin, creds]);

  return (
    <div className="min-h-screen bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 flex items-center justify-center px-4">
      <div className="w-full max-w-sm">
        {/* Brand */}
        <div className="text-center mb-8">
          <img src="/logo.png" alt="SmartNeti" className="h-16 w-auto mx-auto mb-4" />
          <p className="text-sm text-slate-400 mt-1">Internet Access Portal</p>
        </div>

        {/* Card */}
        <div className="bg-white dark:bg-slate-950 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-800 p-6">
          {authorized ? (
            <div className="text-center space-y-5">
              <div className="w-12 h-12 bg-emerald-100 dark:bg-emerald-900/30 rounded-full flex items-center justify-center mx-auto">
                <Globe className="w-6 h-6 text-emerald-600 dark:text-emerald-400" />
              </div>
              <div>
                <h2 className="text-lg font-bold text-slate-900 dark:text-slate-100">You&apos;re Online!</h2>
                <p className="text-sm text-slate-500 dark:text-slate-400 mt-1">
                  {creds?.plan} &bull; {creds?.durationMinutes} minutes
                </p>
              </div>

              {linkLogin && creds ? (
                <>
                  <p className="text-xs text-slate-400 dark:text-slate-500">
                    Connecting you to the internet...
                  </p>
                  {/* Hidden form to POST credentials to MikroTik */}
                  <form
                    ref={formRef}
                    method="post"
                    action={linkLogin}
                    className="hidden"
                  >
                    <input type="hidden" name="username" value={creds.username} />
                    <input type="hidden" name="password" value={creds.password} />
                    {linkOrig && <input type="hidden" name="dst" value={linkOrig} />}
                  </form>
                </>
              ) : (
                <div className="bg-slate-50 dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 space-y-2">
                  <p className="text-xs text-slate-500 dark:text-slate-400 uppercase tracking-wide font-medium">
                    Your credentials
                  </p>
                  <div className="flex items-center justify-between text-sm">
                    <span className="text-slate-500 dark:text-slate-400">Username</span>
                    <span className="font-mono font-semibold text-slate-900 dark:text-slate-100">{creds?.username}</span>
                  </div>
                  <div className="flex items-center justify-between text-sm">
                    <span className="text-slate-500 dark:text-slate-400">Password</span>
                    <span className="font-mono font-semibold text-slate-900 dark:text-slate-100">{creds?.password}</span>
                  </div>
                </div>
              )}

              <button
                onClick={() => { setCode(''); setAuthorized(false); setCreds(null); setError(null); }}
                className="text-sm text-brand-600 dark:text-brand-400 hover:underline"
              >
                Use another voucher
              </button>
            </div>
          ) : (
            <form onSubmit={handleSubmit} className="space-y-5">
              <div className="text-center">
                <h2 className="text-lg font-bold text-slate-900 dark:text-slate-100">Enter Voucher Code</h2>
                <p className="text-sm text-slate-500 dark:text-slate-400 mt-1">
                  Type your code to get internet access
                </p>
              </div>

              <div>
                <label htmlFor="voucher-code" className="sr-only">Voucher Code</label>
                <input
                  id="voucher-code"
                  type="text"
                  value={code}
                  onChange={(e) => setCode(e.target.value.toUpperCase())}
                  required
                  maxLength={10}
                  autoFocus
                  placeholder="e.g. A1B2C3D4"
                  className="w-full px-4 py-3.5 text-center text-xl font-mono tracking-[0.2em] rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-600 focus:border-brand-600 uppercase placeholder:normal-case placeholder:tracking-normal placeholder:text-base"
                />
              </div>

              {error && (
                <div className="flex items-center gap-2 p-3 bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-900/50 rounded-xl">
                  <AlertCircle className="w-4 h-4 text-red-500 shrink-0" />
                  <p className="text-sm text-red-700 dark:text-red-400">{error}</p>
                </div>
              )}

              <button
                type="submit"
                disabled={loading}
                className="w-full py-3.5 bg-brand-600 text-white rounded-xl text-sm font-semibold hover:bg-brand-700 disabled:opacity-60 flex items-center justify-center gap-2 transition-colors"
              >
                {loading ? (
                  <Loader2 className="w-4 h-4 animate-spin" />
                ) : (
                  <>
                    Get Online
                    <ArrowRight className="w-4 h-4" />
                  </>
                )}
              </button>

              <div className="flex items-center justify-between pt-2">
                <Link
                  to="/portal"
                  className="text-xs text-slate-500 dark:text-slate-400 hover:text-brand-600 dark:hover:text-brand-400 transition-colors"
                >
                  Buy Internet Access
                </Link>
                <a
                  href="mailto:support@smartneti.com"
                  className="text-xs text-slate-500 dark:text-slate-400 hover:text-brand-600 dark:hover:text-brand-400 transition-colors"
                >
                  Contact Support
                </a>
              </div>
            </form>
          )}
        </div>

        {/* Footer */}
        <p className="text-center text-xs text-slate-500 mt-6">
          &copy; {new Date().getFullYear()} SmartNeti. All rights reserved.
        </p>
      </div>
    </div>
  );
}

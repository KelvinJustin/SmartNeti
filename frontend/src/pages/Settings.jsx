import { useEffect, useState } from 'react';
import { Save, Building2, CreditCard, Bell, AlertTriangle } from 'lucide-react';
import { fetchSettings, updateSettings } from '../api/client';

const GATEWAYS = [
  { key: 'paychangu', label: 'PayChangu' },
];

const emptySettings = {
  company_name: '',
  company_logo_url: '',
  support_phone: '',
  support_email: '',
  gateway_airtel_money_enabled: 'false',
  gateway_airtel_money_api_key: '',
  gateway_airtel_money_webhook_secret: '',
  gateway_tnm_mpamba_enabled: 'false',
  gateway_tnm_mpamba_api_key: '',
  gateway_tnm_mpamba_webhook_secret: '',
  gateway_paychangu_enabled: 'false',
  gateway_paychangu_public_key: '',
  gateway_paychangu_api_key: '',
  gateway_paychangu_webhook_secret: '',
  notifications_email_enabled: 'false',
  notifications_sms_enabled: 'false',
};

function SectionCard({ title, icon: Icon, children }) {
  return (
    <div className="bg-white rounded-xl border border-slate-200 shadow-sm dark:bg-slate-900 dark:border-slate-800">
      <div className="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center gap-3">
        <div className="p-2 bg-brand-50 rounded-lg dark:bg-brand-900/30">
          <Icon className="w-5 h-5 text-brand-600 dark:text-brand-400" />
        </div>
        <h3 className="text-lg font-semibold text-slate-900 dark:text-slate-100">{title}</h3>
      </div>
      <div className="p-6 space-y-5">{children}</div>
    </div>
  );
}

function Field({ label, value, onChange, type = 'text', placeholder, inputType = 'text' }) {
  return (
    <div>
      {label && (
        <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">{label}</label>
      )}
      {type === 'toggle' ? (
        <label className="inline-flex items-center cursor-pointer">
          <input
            type="checkbox"
            checked={value === 'true'}
            onChange={(e) => onChange(e.target.checked ? 'true' : 'false')}
            className="sr-only peer"
          />
          <div className="relative w-11 h-6 bg-slate-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-brand-300 dark:peer-focus:ring-brand-800 rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-slate-600 peer-checked:bg-brand-600" />
          <span className="ml-3 text-sm font-medium text-slate-900 dark:text-slate-100">
            {value === 'true' ? 'Enabled' : 'Disabled'}
          </span>
        </label>
      ) : (
        <input
          type={inputType}
          value={value || ''}
          onChange={(e) => onChange(e.target.value)}
          placeholder={placeholder}
          className="w-full px-4 py-2 rounded-lg bg-slate-50 border border-slate-200 text-slate-900 dark:bg-slate-950 dark:border-slate-700 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-600"
        />
      )}
    </div>
  );
}

export default function Settings() {
  const [settings, setSettings] = useState(emptySettings);
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState(null);
  const [success, setSuccess] = useState(null);

  const loadSettings = async () => {
    try {
      setLoading(true);
      const data = await fetchSettings();
      setSettings((prev) => ({ ...prev, ...data }));
      setError(null);
    } catch (err) {
      setError(err.response?.data?.error || 'Failed to load settings');
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    loadSettings();
  }, []);

  const updateField = (key, value) => {
    setSettings((prev) => ({ ...prev, [key]: String(value) }));
    setSuccess(null);
  };

  const handleSave = async () => {
    setSaving(true);
    setError(null);
    setSuccess(null);
    try {
      console.log('Saving settings:', settings);
      const updated = await updateSettings(settings);
      console.log('Settings saved, response:', updated);
      setSettings(updated);
      setSuccess('Settings saved successfully');
    } catch (err) {
      console.error('Save failed:', err);
      setError(err.response?.data?.error || 'Failed to save settings');
    } finally {
      setSaving(false);
    }
  };

  if (loading) {
    return (
      <div className="space-y-6">
        <div>
          <h2 className="text-2xl font-bold text-slate-900 dark:text-slate-100">Settings</h2>
          <p className="text-slate-500 mt-1 dark:text-slate-400">Configure branding, payment gateways, and notifications.</p>
        </div>
        <div className="p-8 text-center text-slate-500 dark:text-slate-400">Loading settings...</div>
      </div>
    );
  }

  return (
    <div className="space-y-6">
      <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h2 className="text-2xl font-bold text-slate-900 dark:text-slate-100">Settings</h2>
          <p className="text-slate-500 mt-1 dark:text-slate-400">Configure branding, payment gateways, and notifications.</p>
        </div>
        <button
          onClick={handleSave}
          disabled={saving}
          className="inline-flex items-center justify-center gap-2 px-4 py-2 bg-brand-600 text-white rounded-lg text-sm font-medium hover:bg-brand-700 disabled:opacity-60"
        >
          <Save className="w-4 h-4" />
          {saving ? 'Saving...' : 'Save Changes'}
        </button>
      </div>

      {error && (
        <div className="bg-red-50 text-red-700 p-4 rounded-lg border border-red-200 dark:bg-red-950/30 dark:text-red-400 dark:border-red-900/50 flex items-center gap-2">
          <AlertTriangle className="w-5 h-5" />
          {error}
        </div>
      )}

      {success && (
        <div className="bg-emerald-50 text-emerald-700 p-4 rounded-lg border border-emerald-200 dark:bg-emerald-950/30 dark:text-emerald-400 dark:border-emerald-900/50">
          {success}
        </div>
      )}

      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <SectionCard title="Branding" icon={Building2}>
          <Field
            label="Company Name"
            value={settings.company_name}
            onChange={(v) => updateField('company_name', v)}
            placeholder="SmartNeti"
          />
          <Field
            label="Logo URL"
            value={settings.company_logo_url}
            onChange={(v) => updateField('company_logo_url', v)}
            placeholder="https://example.com/logo.png"
          />
          <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <Field
              label="Support Phone"
              value={settings.support_phone}
              onChange={(v) => updateField('support_phone', v)}
              placeholder="+265..."
            />
            <Field
              label="Support Email"
              value={settings.support_email}
              onChange={(v) => updateField('support_email', v)}
              placeholder="support@example.com"
              inputType="email"
            />
          </div>
        </SectionCard>

        <SectionCard title="Notifications" icon={Bell}>
          <Field
            label="Email Notifications"
            value={settings.notifications_email_enabled}
            onChange={(v) => updateField('notifications_email_enabled', v)}
            type="toggle"
          />
          <Field
            label="SMS Notifications"
            value={settings.notifications_sms_enabled}
            onChange={(v) => updateField('notifications_sms_enabled', v)}
            type="toggle"
          />
          <p className="text-xs text-slate-500 dark:text-slate-400">
            Enabling notifications will send alerts to customers when vouchers are purchased or about to expire.
          </p>
        </SectionCard>
      </div>

      <SectionCard title="Payment Gateways" icon={CreditCard}>
        <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
          {GATEWAYS.map((gw) => (
            <div
              key={gw.key}
              className="border border-slate-200 rounded-lg p-5 dark:border-slate-700 space-y-4"
            >
              <div className="flex items-center justify-between">
                <h4 className="font-semibold text-slate-900 dark:text-slate-100">{gw.label}</h4>
                <Field
                  label=""
                  value={settings[`gateway_${gw.key}_enabled`]}
                  onChange={(v) => updateField(`gateway_${gw.key}_enabled`, v)}
                  type="toggle"
                />
              </div>
              {gw.key === 'paychangu' && (
                <Field
                  label="Public Key"
                  value={settings[`gateway_${gw.key}_public_key`]}
                  onChange={(v) => updateField(`gateway_${gw.key}_public_key`, v)}
                  placeholder="PayChangu Public Key"
                />
              )}
              <Field
                label="API Key / Secret Key"
                value={settings[`gateway_${gw.key}_api_key`]}
                onChange={(v) => updateField(`gateway_${gw.key}_api_key`, v)}
                placeholder={gw.key === 'paychangu' ? 'PayChangu Secret Key' : 'API Key'}
              />
              <Field
                label="Webhook Secret"
                value={settings[`gateway_${gw.key}_webhook_secret`]}
                onChange={(v) => updateField(`gateway_${gw.key}_webhook_secret`, v)}
                placeholder="Webhook signature secret"
              />
              {gw.key === 'paychangu' && (
                <p className="text-xs text-slate-500 dark:text-slate-400">
                  Reference:{' '}
                  <a
                    href="https://developer.paychangu.com/docs/api-keys"
                    target="_blank"
                    rel="noreferrer"
                    className="text-brand-600 hover:underline dark:text-brand-400"
                  >
                    PayChangu API Keys
                  </a>{' '}
                  &{' '}
                  <a
                    href="https://developer.paychangu.com/docs/webhooks"
                    target="_blank"
                    rel="noreferrer"
                    className="text-brand-600 hover:underline dark:text-brand-400"
                  >
                    Webhooks
                  </a>
                </p>
              )}
            </div>
          ))}
        </div>
        <div className="bg-blue-50 text-blue-800 p-4 rounded-lg border border-blue-200 dark:bg-blue-950/30 dark:text-blue-400 dark:border-blue-900/50 text-sm">
          <div className="flex items-start gap-2">
            <svg className="w-4 h-4 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            <div>
              <p className="font-medium">Payment Gateway Setup</p>
              <p className="mt-1">
                Enable the gateways you want to offer customers. PayChangu uses inline checkout.
                Airtel Money and TNM Mpamba are not available for direct integration.
              </p>
            </div>
          </div>
        </div>
      </SectionCard>
    </div>
  );
}


import { useEffect, useState } from 'react';
import { Plus, Pencil, Trash2, Wifi } from 'lucide-react';
import Modal from '../components/Modal';
import { fetchHotspots, createHotspot, updateHotspot, deleteHotspot } from '../api/client';

const emptyForm = {
  name: '',
  location: '',
  nasIdentifier: '',
  nasIp: '',
  radiusSecret: '',
  status: 'active',
};

export default function Hotspots() {
  const [hotspots, setHotspots] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [editing, setEditing] = useState(null);
  const [form, setForm] = useState(emptyForm);
  const [submitting, setSubmitting] = useState(false);

  const loadHotspots = async () => {
    try {
      setLoading(true);
      const data = await fetchHotspots();
      setHotspots(data);
      setError(null);
    } catch (err) {
      setError(err.response?.data?.error || err.message);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    loadHotspots();
  }, []);

  const openAdd = () => {
    setEditing(null);
    setForm(emptyForm);
    setIsModalOpen(true);
  };

  const openEdit = (hotspot) => {
    setEditing(hotspot);
    setForm({
      name: hotspot.name || '',
      location: hotspot.location || '',
      nasIdentifier: hotspot.nas_identifier || '',
      nasIp: hotspot.nas_ip || '',
      radiusSecret: hotspot.radius_secret || '',
      status: hotspot.status || 'active',
    });
    setIsModalOpen(true);
  };

  const closeModal = () => {
    setIsModalOpen(false);
    setEditing(null);
    setForm(emptyForm);
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setSubmitting(true);
    try {
      if (editing) {
        await updateHotspot(editing.id, form);
      } else {
        await createHotspot(form);
      }
      await loadHotspots();
      closeModal();
    } catch (err) {
      setError(err.response?.data?.error || 'Failed to save hotspot');
    } finally {
      setSubmitting(false);
    }
  };

  const handleDelete = async (id) => {
    if (!window.confirm('Are you sure you want to delete this hotspot?')) return;
    try {
      await deleteHotspot(id);
      await loadHotspots();
    } catch (err) {
      setError(err.response?.data?.error || 'Failed to delete hotspot');
    }
  };

  return (
    <div className="space-y-6">
      <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h2 className="text-2xl font-bold text-slate-900 dark:text-slate-100">Hotspots</h2>
          <p className="text-slate-500 mt-1 dark:text-slate-400">Manage MikroTik hotspots and NAS devices.</p>
        </div>
        <button
          onClick={openAdd}
          className="inline-flex items-center justify-center gap-2 px-4 py-2 bg-brand-600 text-white rounded-lg text-sm font-medium hover:bg-brand-700"
        >
          <Plus className="w-4 h-4" />
          Add Hotspot
        </button>
      </div>

      {error && (
        <div className="bg-red-50 text-red-700 p-4 rounded-lg border border-red-200 dark:bg-red-950/30 dark:text-red-400 dark:border-red-900/50">
          {error}
        </div>
      )}

      <div className="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden dark:bg-slate-900 dark:border-slate-800">
        {loading ? (
          <div className="p-8 text-center text-slate-500 dark:text-slate-400">Loading hotspots...</div>
        ) : hotspots.length === 0 ? (
          <div className="p-8 text-center text-slate-500 dark:text-slate-400">
            No hotspots yet. Click “Add Hotspot” to create one.
          </div>
        ) : (
          <div className="overflow-x-auto">
            <table className="w-full text-sm text-left">
              <thead className="bg-slate-50 dark:bg-slate-950/50 border-b border-slate-200 dark:border-slate-800 text-slate-500 dark:text-slate-400">
                <tr>
                  <th className="px-6 py-3 font-medium">Name</th>
                  <th className="px-6 py-3 font-medium">Location</th>
                  <th className="px-6 py-3 font-medium">NAS Identifier</th>
                  <th className="px-6 py-3 font-medium">NAS IP</th>
                  <th className="px-6 py-3 font-medium">Status</th>
                  <th className="px-6 py-3 font-medium text-right">Actions</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-slate-200 dark:divide-slate-800">
                {hotspots.map((hotspot) => (
                  <tr key={hotspot.id} className="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                    <td className="px-6 py-4 font-medium text-slate-900 dark:text-slate-100">
                      <div className="flex items-center gap-2">
                        <Wifi className="w-4 h-4 text-brand-500" />
                        {hotspot.name}
                      </div>
                    </td>
                    <td className="px-6 py-4 text-slate-600 dark:text-slate-300">{hotspot.location || '—'}</td>
                    <td className="px-6 py-4 text-slate-600 dark:text-slate-300">{hotspot.nas_identifier || '—'}</td>
                    <td className="px-6 py-4 text-slate-600 dark:text-slate-300">{hotspot.nas_ip || '—'}</td>
                    <td className="px-6 py-4">
                      <span
                        className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${
                          hotspot.status === 'active'
                            ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400'
                            : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400'
                        }`}
                      >
                        {hotspot.status}
                      </span>
                    </td>
                    <td className="px-6 py-4 text-right">
                      <div className="flex items-center justify-end gap-2">
                        <button
                          onClick={() => openEdit(hotspot)}
                          className="p-2 text-slate-500 hover:text-brand-600 hover:bg-brand-50 rounded-lg dark:text-slate-400 dark:hover:text-brand-400 dark:hover:bg-brand-900/20"
                          aria-label="Edit"
                        >
                          <Pencil className="w-4 h-4" />
                        </button>
                        <button
                          onClick={() => handleDelete(hotspot.id)}
                          className="p-2 text-slate-500 hover:text-red-600 hover:bg-red-50 rounded-lg dark:text-slate-400 dark:hover:text-red-400 dark:hover:bg-red-900/20"
                          aria-label="Delete"
                        >
                          <Trash2 className="w-4 h-4" />
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

      <Modal isOpen={isModalOpen} onClose={closeModal} title={editing ? 'Edit Hotspot' : 'Add Hotspot'}>
        <form onSubmit={handleSubmit} className="space-y-4">
          <div>
            <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Name *</label>
            <input
              type="text"
              value={form.name}
              onChange={(e) => setForm({ ...form, name: e.target.value })}
              required
              className="w-full px-4 py-2 rounded-lg bg-slate-50 border border-slate-200 text-slate-900 dark:bg-slate-950 dark:border-slate-700 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-600"
              placeholder="e.g. Mall Entrance"
            />
          </div>
          <div>
            <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Location</label>
            <input
              type="text"
              value={form.location}
              onChange={(e) => setForm({ ...form, location: e.target.value })}
              className="w-full px-4 py-2 rounded-lg bg-slate-50 border border-slate-200 text-slate-900 dark:bg-slate-950 dark:border-slate-700 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-600"
              placeholder="e.g. Lusaka, Zambia"
            />
          </div>
          <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">NAS Identifier</label>
              <input
                type="text"
                value={form.nasIdentifier}
                onChange={(e) => setForm({ ...form, nasIdentifier: e.target.value })}
                className="w-full px-4 py-2 rounded-lg bg-slate-50 border border-slate-200 text-slate-900 dark:bg-slate-950 dark:border-slate-700 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-600"
                placeholder="e.g. mikrotik-01"
              />
            </div>
            <div>
              <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">NAS IP</label>
              <input
                type="text"
                value={form.nasIp}
                onChange={(e) => setForm({ ...form, nasIp: e.target.value })}
                className="w-full px-4 py-2 rounded-lg bg-slate-50 border border-slate-200 text-slate-900 dark:bg-slate-950 dark:border-slate-700 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-600"
                placeholder="e.g. 192.168.88.1"
              />
            </div>
          </div>
          <div>
            <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">RADIUS Secret *</label>
            <input
              type="text"
              value={form.radiusSecret}
              onChange={(e) => setForm({ ...form, radiusSecret: e.target.value })}
              required
              className="w-full px-4 py-2 rounded-lg bg-slate-50 border border-slate-200 text-slate-900 dark:bg-slate-950 dark:border-slate-700 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-600"
              placeholder="Shared secret with MikroTik"
            />
          </div>
          <div>
            <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Status</label>
            <select
              value={form.status}
              onChange={(e) => setForm({ ...form, status: e.target.value })}
              className="w-full px-4 py-2 rounded-lg bg-slate-50 border border-slate-200 text-slate-900 dark:bg-slate-950 dark:border-slate-700 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-600"
            >
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
            </select>
          </div>
          <div className="flex justify-end gap-3 pt-2">
            <button
              type="button"
              onClick={closeModal}
              className="px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100 rounded-lg dark:text-slate-300 dark:hover:bg-slate-800"
            >
              Cancel
            </button>
            <button
              type="submit"
              disabled={submitting}
              className="px-4 py-2 text-sm font-medium text-white bg-brand-600 hover:bg-brand-700 rounded-lg disabled:opacity-60"
            >
              {submitting ? 'Saving...' : editing ? 'Save Changes' : 'Create Hotspot'}
            </button>
          </div>
        </form>
      </Modal>
    </div>
  );
}

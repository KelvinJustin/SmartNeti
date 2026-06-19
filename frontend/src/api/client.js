import axios from 'axios';

export const apiClient = axios.create({
  baseURL: '/api/v1',
  headers: {
    'Content-Type': 'application/json',
  },
  withCredentials: true,
});

apiClient.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.config?.url === '/auth/me' && error.response?.status === 401) {
      return Promise.resolve({ data: { user: null } });
    }
    return Promise.reject(error);
  }
);

export const fetchDashboardStats = async () => {
  const { data } = await apiClient.get('/dashboard/stats');
  return data;
};

export const fetchAnalytics = async () => {
  const { data } = await apiClient.get('/analytics/summary');
  return data;
};

export const fetchCustomers = async (params = {}) => {
  const { data } = await apiClient.get('/customers', { params });
  return data.customers;
};

export const fetchCustomerDetail = async (id) => {
  const { data } = await apiClient.get(`/customers/${id}`);
  return data;
};

export const login = async (email, password) => {
  const { data } = await apiClient.post('/auth/login', { email, password });
  return data.user;
};

export const logout = async () => {
  await apiClient.post('/auth/logout');
};

export const fetchMe = async () => {
  try {
    const { data } = await apiClient.get('/auth/me');
    return data.user;
  } catch (err) {
    if (err.response?.status === 401) return null;
    throw err;
  }
};

export const fetchHotspots = async () => {
  const { data } = await apiClient.get('/hotspots');
  return data.hotspots;
};

export const createHotspot = async (hotspot) => {
  const { data } = await apiClient.post('/hotspots', hotspot);
  return data.hotspot;
};

export const updateHotspot = async (id, hotspot) => {
  const { data } = await apiClient.patch(`/hotspots/${id}`, hotspot);
  return data.hotspot;
};

export const deleteHotspot = async (id) => {
  await apiClient.delete(`/hotspots/${id}`);
};

export const fetchPlans = async () => {
  const { data } = await apiClient.get('/plans');
  return data.plans;
};

export const createPlan = async (plan) => {
  const { data } = await apiClient.post('/plans', plan);
  return data.plan;
};

export const updatePlan = async (id, plan) => {
  const { data } = await apiClient.patch(`/plans/${id}`, plan);
  return data.plan;
};

export const deletePlan = async (id) => {
  await apiClient.delete(`/plans/${id}`);
};

export const fetchVouchers = async (params = {}) => {
  const { data } = await apiClient.get('/vouchers', { params });
  return data.vouchers;
};

export const generateVouchers = async (planId, count) => {
  const { data } = await apiClient.post('/vouchers/generate', { planId, count });
  return data.vouchers;
};

export const fetchPublicPlans = async () => {
  const { data } = await apiClient.get('/public/plans');
  return data.plans;
};

export const initiatePayment = async ({ gateway, planId, customerPhone, customerEmail, customerName }) => {
  const { data } = await apiClient.post('/public/payments/initiate', {
    gateway,
    planId,
    customerPhone,
    customerEmail,
    customerName,
  });
  return data;
};

export const getPaymentStatus = async (reference) => {
  const { data } = await apiClient.get(`/public/payments/status/${reference}`);
  return data;
};

export const mockCompletePayment = async (reference) => {
  const { data } = await apiClient.post(`/public/payments/mock-complete/${reference}`);
  return data;
};

export const validateVoucher = async (code) => {
  const { data } = await apiClient.post('/public/voucher/validate', { code });
  return data;
};

export const fetchPayments = async () => {
  const { data } = await apiClient.get('/payments');
  return data.payments;
};

export const captiveAuthorize = async ({ code, mac, ip }) => {
  const { data } = await apiClient.post('/captive/authorize', { code, mac, ip });
  return data;
};

export const fetchSettings = async () => {
  const { data } = await apiClient.get('/settings');
  return data.settings;
};

export const updateSettings = async (settings) => {
  const { data } = await apiClient.put('/settings', settings);
  return data.settings;
};

export const fetchPublicGateways = async () => {
  const { data } = await apiClient.get('/public/gateways');
  return data.gateways;
};

export const verifyPayment = async (txRef) => {
  const { data } = await apiClient.post('/public/payments/verify', { txRef });
  return data;
};

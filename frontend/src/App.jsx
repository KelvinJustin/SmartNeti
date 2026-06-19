import { Routes, Route } from 'react-router-dom';
import Layout from './components/Layout';
import ProtectedRoute from './components/ProtectedRoute';
import Dashboard from './pages/Dashboard';
import Hotspots from './pages/Hotspots';
import Plans from './pages/Plans';
import Vouchers from './pages/Vouchers';
import Users from './pages/Users';
import Payments from './pages/Payments';
import Analytics from './pages/Analytics';
import Settings from './pages/Settings';
import Login from './pages/Login';
import PortalLayout from './portal/PortalLayout';
import PortalHome from './portal/PortalHome';
import PortalPlans from './portal/PortalPlans';
import PortalBuy from './portal/PortalBuy';
import PortalPaymentStatus from './portal/PortalPaymentStatus';
import PortalPaymentCallback from './portal/PortalPaymentCallback';
import PortalVoucher from './portal/PortalVoucher';
import CaptivePortal from './portal/CaptivePortal';

export default function App() {
  return (
    <Routes>
      <Route path="/login" element={<Login />} />
      <Route path="/captive" element={<CaptivePortal />} />
      <Route path="/portal" element={<PortalLayout />}>
        <Route index element={<PortalHome />} />
        <Route path="plans" element={<PortalPlans />} />
        <Route path="buy/:planId" element={<PortalBuy />} />
        <Route path="payment/callback" element={<PortalPaymentCallback />} />
        <Route path="payment/:reference" element={<PortalPaymentStatus />} />
        <Route path="voucher" element={<PortalVoucher />} />
      </Route>
      <Route
        path="/"
        element={
          <ProtectedRoute>
            <Layout />
          </ProtectedRoute>
        }
      >
        <Route index element={<Dashboard />} />
        <Route path="hotspots" element={<Hotspots />} />
        <Route path="plans" element={<Plans />} />
        <Route path="vouchers" element={<Vouchers />} />
        <Route path="users" element={<Users />} />
        <Route path="payments" element={<Payments />} />
        <Route path="analytics" element={<Analytics />} />
        <Route path="settings" element={<Settings />} />
      </Route>
    </Routes>
  );
}

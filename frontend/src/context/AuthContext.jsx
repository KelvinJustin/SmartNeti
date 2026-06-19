import { createContext, useContext, useEffect, useState, useCallback } from 'react';
import { useLocation } from 'react-router-dom';
import { login as apiLogin, logout as apiLogout, fetchMe } from '../api/client';

const AuthContext = createContext(null);

export function AuthProvider({ children }) {
  const [user, setUser] = useState(null);
  const [loading, setLoading] = useState(true);
  const location = useLocation();

  const isPublicRoute = location.pathname.startsWith('/captive') || location.pathname.startsWith('/portal');

  const refreshUser = useCallback(async () => {
    try {
      const me = await fetchMe();
      setUser(me);
    } catch {
      setUser(null);
    } finally {
      setLoading(false);
    }
  }, []);

  useEffect(() => {
    if (isPublicRoute) {
      setLoading(false);
      return;
    }
    refreshUser();
  }, [refreshUser, isPublicRoute]);

  const login = async (email, password) => {
    const me = await apiLogin(email, password);
    setUser(me);
    return me;
  };

  const logout = async () => {
    await apiLogout();
    setUser(null);
  };

  const value = {
    user,
    isAuthenticated: !!user,
    isLoading: loading,
    login,
    logout,
  };

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
}

export function useAuth() {
  const context = useContext(AuthContext);
  if (!context) {
    throw new Error('useAuth must be used within an AuthProvider');
  }
  return context;
}

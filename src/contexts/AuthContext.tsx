import React, { createContext, useContext, useEffect, useState } from 'react';
import { authStorage } from '@/lib/api';
import { useCurrentUser } from '@/hooks/useApi';
import { User } from '@/lib/api';

interface AuthContextType {
  user: User | null;
  isAuthenticated: boolean;
  isLoading: boolean;
  logout: () => void;
}

const AuthContext = createContext<AuthContextType | undefined>(undefined);

interface AuthProviderProps {
  children: React.ReactNode;
}

export const AuthProvider: React.FC<AuthProviderProps> = ({ children }) => {
  const [isAuthenticated, setIsAuthenticated] = useState<boolean>(false);
  const [isInitialized, setIsInitialized] = useState<boolean>(false);
  
  // Check for token on mount and listen for token changes
  useEffect(() => {
    const checkAuthState = () => {
      const token = authStorage.getToken();
      setIsAuthenticated(!!token);
    };

    // Initial check
    checkAuthState();
    setIsInitialized(true);

    // Listen for token changes
    const handleTokenChange = () => {
      checkAuthState();
    };

    authStorage.addEventListener('tokenChanged', handleTokenChange);
    
    return () => {
      authStorage.removeEventListener('tokenChanged', handleTokenChange);
    };
  }, []);

  // Use the existing useCurrentUser hook when authenticated
  const { 
    data: userResponse, 
    isLoading: isUserLoading, 
    error: userError 
  } = useCurrentUser();

  // Handle authentication state based on user fetch result
  useEffect(() => {
    if (userError && isAuthenticated) {
      // If user fetch fails while authenticated, clear authentication
      setIsAuthenticated(false);
      authStorage.removeToken();
    }
  }, [userResponse, userError, isAuthenticated]);

  const logout = () => {
    authStorage.removeToken();
    setIsAuthenticated(false);
    window.location.href = '/';
  };

  const value: AuthContextType = {
    user: userResponse?.success ? userResponse.data.user : null,
    isAuthenticated,
    isLoading: !isInitialized || (isAuthenticated && isUserLoading),
    logout,
  };

  return (
    <AuthContext.Provider value={value}>
      {children}
    </AuthContext.Provider>
  );
};

export const useAuth = (): AuthContextType => {
  const context = useContext(AuthContext);
  if (context === undefined) {
    throw new Error('useAuth must be used within an AuthProvider');
  }
  return context;
};

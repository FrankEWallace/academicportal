import { useEffect, useCallback, useRef } from 'react';
import { useNavigate } from 'react-router-dom';
import { useToast } from './use-toast';
import { SessionManager } from '@/lib/security';

interface UseSessionTimeoutOptions {
  timeout?: number; // in milliseconds (default: 60 minutes)
  warningTime?: number; // time before timeout to show warning (default: 5 minutes)
  onTimeout?: () => void;
  onWarning?: () => void;
}

export function useSessionTimeout(options: UseSessionTimeoutOptions = {}) {
  const {
    timeout = 60 * 60 * 1000, // 60 minutes
    warningTime = 5 * 60 * 1000, // 5 minutes
    onTimeout,
    onWarning,
  } = options;

  const navigate = useNavigate();
  const { toast } = useToast();
  const sessionManager = useRef<SessionManager | null>(null);

  const handleTimeout = useCallback(() => {
    // Clear auth data
    localStorage.removeItem('academic_portal_token');
    localStorage.removeItem('user');

    toast({
      title: 'Session Expired',
      description: 'Your session has expired due to inactivity. Please log in again.',
      variant: 'destructive',
    });

    if (onTimeout) {
      onTimeout();
    }

    // Redirect to login
    navigate('/login');
  }, [navigate, toast, onTimeout]);

  const handleWarning = useCallback(() => {
    toast({
      title: 'Session Expiring Soon',
      description: 'Your session will expire in 5 minutes due to inactivity.',
      variant: 'default',
    });

    if (onWarning) {
      onWarning();
    }
  }, [toast, onWarning]);

  useEffect(() => {
    // Only run if user is logged in
    const token = localStorage.getItem('academic_portal_token');
    if (!token) {
      return;
    }

    // Initialize session manager
    if (!sessionManager.current) {
      sessionManager.current = new SessionManager(timeout, warningTime);
    }

    // Start session monitoring
    sessionManager.current.start(handleTimeout, handleWarning);

    // Cleanup on unmount
    return () => {
      if (sessionManager.current) {
        sessionManager.current.stop();
      }
    };
  }, [timeout, warningTime, handleTimeout, handleWarning]);

  const resetSession = useCallback(() => {
    if (sessionManager.current) {
      sessionManager.current.reset();
    }
  }, []);

  return { resetSession };
}

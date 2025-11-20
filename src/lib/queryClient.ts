import { QueryClient } from '@tanstack/react-query';
import { ApiClientError } from '@/lib/api';
import { toast } from '@/hooks/use-toast';

// Global error handler for React Query
export const handleGlobalQueryError = (error: unknown) => {
  console.error('React Query Global Error:', error);
  
  if (error instanceof ApiClientError) {
    // Handle authentication errors globally
    if (error.isAuthenticationError()) {
      // Clear auth state and redirect to login
      localStorage.removeItem('academic_portal_token');
      window.location.href = '/';
      return;
    }
    
    // Handle authorization errors
    if (error.isAuthorizationError()) {
      toast({
        title: "Access Denied",
        description: "You don't have permission to perform this action",
        variant: "destructive",
      });
      return;
    }
    
    // Handle server errors
    if (error.isServerError()) {
      toast({
        title: "Server Error",
        description: "Something went wrong on our end. Please try again later.",
        variant: "destructive",
      });
      return;
    }
  }
  
  // For other errors, let individual components handle them
};

// Create QueryClient with global error handling
export const createQueryClientWithErrorHandling = () => {
  return new QueryClient({
    defaultOptions: {
      queries: {
        retry: (failureCount, error) => {
          // Don't retry on auth errors
          if (error instanceof ApiClientError && 
              (error.isAuthenticationError() || error.isAuthorizationError())) {
            return false;
          }
          // Don't retry on 4xx errors (except auth)
          if (error instanceof ApiClientError && 
              error.statusCode >= 400 && error.statusCode < 500) {
            return false;
          }
          // Retry up to 2 times for other errors
          return failureCount < 2;
        },
        staleTime: 5 * 60 * 1000, // 5 minutes
        gcTime: 10 * 60 * 1000, // 10 minutes
      },
      mutations: {
        retry: false, // Don't retry mutations by default
      },
    },
  });
};

import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { api, Course, User, LoginRequest, RegisterRequest, Enrollment, authStorage, ApiClientError, PaginatedResponse } from '@/lib/api';
import { toast } from '@/hooks/use-toast';

// Enhanced error handler for API calls
const handleApiError = (error: unknown, defaultTitle: string = "Error", showToast: boolean = true) => {
  console.error('API Error:', error);
  
  if (!showToast) return;
  
  if (error instanceof ApiClientError) {
    let description = error.message;
    
    // Handle validation errors
    if (error.isValidationError() && error.errors) {
      const validationMessages = Object.values(error.errors)
        .flat()
        .join(', ');
      description = validationMessages || error.message;
    }
    
    toast({
      title: error.isAuthenticationError() ? "Authentication Error" : 
             error.isAuthorizationError() ? "Authorization Error" :
             error.isValidationError() ? "Validation Error" :
             error.isServerError() ? "Server Error" : defaultTitle,
      description,
      variant: "destructive",
    });
  } else if (error instanceof Error) {
    toast({
      title: defaultTitle,
      description: error.message || "An unexpected error occurred",
      variant: "destructive",
    });
  } else {
    toast({
      title: defaultTitle,
      description: "An unexpected error occurred",
      variant: "destructive",
    });
  }
};

// Query error handler (silent by default, can be overridden)
const handleQueryError = (error: unknown) => {
  // Log the error but don't show toast by default for queries
  // Individual queries can choose to show errors
  handleApiError(error, "Failed to load data", false);
};

// Authentication Hooks
export const useRegister = () => {
  const queryClient = useQueryClient();
  
  return useMutation({
    mutationFn: (userData: RegisterRequest) => api.register(userData),
    onSuccess: (data) => {
      if (data.success) {
        authStorage.setToken(data.data.token);
        queryClient.invalidateQueries({ queryKey: ['currentUser'] });
        toast({
          title: "Registration Successful",
          description: `Welcome to Academic Portal, ${data.data.user.name}!`,
        });
      }
    },
    onError: (error) => handleApiError(error, "Registration Failed")
  });
};

export const useLogin = () => {
  const queryClient = useQueryClient();
  
  return useMutation({
    mutationFn: (credentials: LoginRequest) => api.login(credentials),
    onSuccess: (data) => {
      if (data.success) {
        authStorage.setToken(data.data.token);
        queryClient.invalidateQueries({ queryKey: ['currentUser'] });
        toast({
          title: "Login Successful",
          description: `Welcome back, ${data.data.user.name}!`,
        });
      }
    },
    onError: (error) => handleApiError(error, "Login Failed")
  });
};

export const useLogout = () => {
  const queryClient = useQueryClient();
  
  return useMutation({
    mutationFn: () => api.logout(),
    onSuccess: () => {
      queryClient.clear();
      authStorage.removeToken();
      window.location.href = '/';
    }
  });
};

export const useCurrentUser = () => {
  return useQuery({
    queryKey: ['currentUser'],
    queryFn: () => api.getCurrentUser(),
    enabled: !!authStorage.getToken(),
    retry: false,
  });
};

// Courses Hooks
export const useCourses = () => {
  return useQuery<PaginatedResponse<Course>>({
    queryKey: ['courses'],
    queryFn: async () => {
      try {
        const response = await api.getCourses();
        return response.data; // This returns the pagination object with data: Course[]
      } catch (error) {
        handleQueryError(error);
        throw error;
      }
    },
    enabled: !!authStorage.getToken(),
    retry: (failureCount, error) => {
      // Don't retry on auth errors
      if (error instanceof ApiClientError && (error.isAuthenticationError() || error.isAuthorizationError())) {
        return false;
      }
      return failureCount < 2;
    },
  });
};

export const useCourse = (id: number) => {
  return useQuery({
    queryKey: ['course', id],
    queryFn: async () => {
      const response = await api.getCourse(id);
      return response.data;
    },
    enabled: !!authStorage.getToken() && !!id,
  });
};

export const useCreateCourse = () => {
  const queryClient = useQueryClient();
  
  return useMutation({
    mutationFn: (courseData: Partial<Course>) => api.createCourse(courseData),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['courses'] });
      toast({
        title: "Success",
        description: "Course created successfully",
      });
    },
    onError: (error) => handleApiError(error, "Failed to create course")
  });
};

export const useUpdateCourse = () => {
  const queryClient = useQueryClient();
  
  return useMutation({
    mutationFn: ({ id, ...courseData }: Partial<Course> & { id: number }) => 
      api.updateCourse(id, courseData),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['courses'] });
      toast({
        title: "Success",
        description: "Course updated successfully",
      });
    },
    onError: (error) => handleApiError(error, "Failed to update course")
  });
};

export const useDeleteCourse = () => {
  const queryClient = useQueryClient();
  
  return useMutation({
    mutationFn: (id: number) => api.deleteCourse(id),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['courses'] });
      toast({
        title: "Success",
        description: "Course deleted successfully",
      });
    },
    onError: (error) => handleApiError(error, "Failed to delete course")
  });
};

// Users Hooks
export const useUsers = () => {
  return useQuery<PaginatedResponse<User>>({
    queryKey: ['users'],
    queryFn: async () => {
      const response = await api.getUsers();
      return response.data; // This contains the paginated data
    },
    enabled: !!authStorage.getToken(),
  });
};

export const useUser = (id: number) => {
  return useQuery({
    queryKey: ['user', id],
    queryFn: async () => {
      const response = await api.getUser(id);
      return response;
    },
    enabled: !!authStorage.getToken() && !!id,
  });
};

// Enrollment Hooks
export const useEnrollStudent = () => {
  const queryClient = useQueryClient();
  
  return useMutation({
    mutationFn: ({ courseId, studentId }: { courseId: number; studentId: number }) => 
      api.enrollStudent(courseId, studentId),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['courses'] });
      queryClient.invalidateQueries({ queryKey: ['studentCourses'] });
      queryClient.invalidateQueries({ queryKey: ['courseEnrollments'] });
      toast({
        title: "Success",
        description: "Student enrolled successfully",
      });
    },
    onError: (error) => handleApiError(error, "Failed to enroll student")
  });
};

export const useUnenrollStudent = () => {
  const queryClient = useQueryClient();
  
  return useMutation({
    mutationFn: (enrollmentId: number) => api.unenrollStudent(enrollmentId),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['courses'] });
      queryClient.invalidateQueries({ queryKey: ['studentCourses'] });
      queryClient.invalidateQueries({ queryKey: ['courseEnrollments'] });
      toast({
        title: "Success",
        description: "Student unenrolled successfully",
      });
    },
    onError: (error) => handleApiError(error, "Failed to unenroll student")
  });
};

export const useStudentCourses = (studentId?: number) => {
  return useQuery({
    queryKey: ['studentCourses', studentId],
    queryFn: async () => {
      const response = await api.getStudentCourses(studentId);
      return response.data;
    },
    enabled: !!authStorage.getToken(),
  });
};

export const useCourseEnrollments = (courseId: number) => {
  return useQuery({
    queryKey: ['courseEnrollments', courseId],
    queryFn: async () => {
      const response = await api.getCourseEnrollments(courseId);
      return response.data;
    },
    enabled: !!authStorage.getToken() && !!courseId,
  });
};

// Dashboard Hooks
export const useAdminDashboard = () => {
  return useQuery({
    queryKey: ['adminDashboard'],
    queryFn: async () => {
      const response = await api.getAdminDashboard();
      return response.data;
    },
    enabled: !!authStorage.getToken(),
  });
};

export const useStudentDashboard = () => {
  return useQuery({
    queryKey: ['studentDashboard'],
    queryFn: async () => {
      const response = await api.getStudentDashboard();
      return response.data;
    },
    enabled: !!authStorage.getToken(),
  });
};

export const useTeacherDashboard = () => {
  return useQuery({
    queryKey: ['teacherDashboard'],
    queryFn: async () => {
      const response = await api.getTeacherDashboard();
      return response.data;
    },
    enabled: !!authStorage.getToken(),
  });
};

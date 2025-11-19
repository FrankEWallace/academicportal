import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { api, Course, User, LoginRequest, authStorage } from '@/lib/api';
import { toast } from '@/hooks/use-toast';

// Authentication Hooks
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
    onError: (error) => {
      toast({
        title: "Login Failed", 
        description: error.message || "Invalid credentials",
        variant: "destructive",
      });
    }
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
  return useQuery({
    queryKey: ['courses'],
    queryFn: async () => {
      const response = await api.getCourses();
      return response.data;
    },
    enabled: !!authStorage.getToken(),
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
    onError: (error) => {
      toast({
        title: "Error",
        description: error.message || "Failed to create course",
        variant: "destructive",
      });
    }
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
    onError: (error) => {
      toast({
        title: "Error",
        description: error.message || "Failed to update course",
        variant: "destructive",
      });
    }
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
    onError: (error) => {
      toast({
        title: "Error", 
        description: error.message || "Failed to delete course",
        variant: "destructive",
      });
    }
  });
};

// Users Hooks
export const useUsers = () => {
  return useQuery({
    queryKey: ['users'],
    queryFn: async () => {
      const response = await api.getUsers();
      return response.data;
    },
    enabled: !!authStorage.getToken(),
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

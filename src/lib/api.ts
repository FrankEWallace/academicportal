// API Configuration
const API_BASE_URL = 'http://localhost:8000/api';

// Types for our API responses
export interface User {
  id: number;
  name: string;
  email: string;
  role: 'admin' | 'student' | 'teacher';
  created_at: string;
  updated_at: string;
}

export interface Course {
  id: number;
  name: string;
  code: string;
  description: string;
  credits: number;
  department_id: number;
  teacher_id: number;
  semester: number;
  section: string;
  schedule: Array<{ day: string; time: string }>;
  room: string;
  max_students: number;
  enrolled_students: number;
  start_date: string;
  end_date: string;
  status: 'active' | 'inactive' | 'completed';
  department?: {
    id: number;
    name: string;
    code: string;
  };
  teacher?: {
    id: number;
    user: User;
  };
}

export interface ApiResponse<T> {
  success: boolean;
  data: T;
  message?: string;
}

export interface LoginRequest {
  email: string;
  password: string;
  role: 'admin' | 'student' | 'teacher';
}

export interface LoginResponse {
  user: User;
  token: string;
  token_type: string;
}

// Storage for auth token
const TOKEN_KEY = 'academic_portal_token';

export const authStorage = {
  getToken: (): string | null => localStorage.getItem(TOKEN_KEY),
  setToken: (token: string) => localStorage.setItem(TOKEN_KEY, token),
  removeToken: () => localStorage.removeItem(TOKEN_KEY),
};

// API Client Class
class ApiClient {
  private baseURL: string;

  constructor(baseURL: string) {
    this.baseURL = baseURL;
  }

  private async request<T>(
    endpoint: string,
    options: RequestInit = {}
  ): Promise<T> {
    const token = authStorage.getToken();
    
    const config: RequestInit = {
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        ...(token && { Authorization: `Bearer ${token}` }),
        ...options.headers,
      },
      ...options,
    };

    try {
      const response = await fetch(`${this.baseURL}${endpoint}`, config);
      
      if (!response.ok) {
        const errorData = await response.json().catch(() => ({}));
        throw new Error(errorData.message || `HTTP error! status: ${response.status}`);
      }

      return await response.json();
    } catch (error) {
      console.error('API request failed:', error);
      throw error;
    }
  }

  // Authentication Methods
  async login(credentials: LoginRequest): Promise<ApiResponse<LoginResponse>> {
    return this.request<ApiResponse<LoginResponse>>('/auth/login', {
      method: 'POST',
      body: JSON.stringify(credentials),
    });
  }

  async logout(): Promise<ApiResponse<{}>> {
    const response = await this.request<ApiResponse<{}>>('/auth/logout', {
      method: 'POST',
    });
    authStorage.removeToken();
    return response;
  }

  async getCurrentUser(): Promise<ApiResponse<User>> {
    return this.request<ApiResponse<User>>('/auth/me');
  }

  // Courses Methods
  async getCourses(): Promise<ApiResponse<{ data: Course[] }>> {
    return this.request<ApiResponse<{ data: Course[] }>>('/admin/courses');
  }

  async getCourse(id: number): Promise<ApiResponse<Course>> {
    return this.request<ApiResponse<Course>>(`/admin/courses/${id}`);
  }

  async createCourse(courseData: Partial<Course>): Promise<ApiResponse<Course>> {
    return this.request<ApiResponse<Course>>('/admin/courses', {
      method: 'POST',
      body: JSON.stringify(courseData),
    });
  }

  async updateCourse(id: number, courseData: Partial<Course>): Promise<ApiResponse<Course>> {
    return this.request<ApiResponse<Course>>(`/admin/courses/${id}`, {
      method: 'PUT',
      body: JSON.stringify(courseData),
    });
  }

  async deleteCourse(id: number): Promise<ApiResponse<{}>> {
    return this.request<ApiResponse<{}>>(`/admin/courses/${id}`, {
      method: 'DELETE',
    });
  }

  // Users Methods
  async getUsers(): Promise<ApiResponse<{ data: User[] }>> {
    return this.request<ApiResponse<{ data: User[] }>>('/admin/users');
  }

  async getUser(id: number): Promise<ApiResponse<User>> {
    return this.request<ApiResponse<User>>(`/admin/users/${id}`);
  }

  // Dashboard Methods
  async getAdminDashboard(): Promise<ApiResponse<any>> {
    return this.request<ApiResponse<any>>('/admin/dashboard');
  }

  async getStudentDashboard(): Promise<ApiResponse<any>> {
    return this.request<ApiResponse<any>>('/student/dashboard');
  }

  async getTeacherDashboard(): Promise<ApiResponse<any>> {
    return this.request<ApiResponse<any>>('/teacher/dashboard');
  }

  // Health Check
  async healthCheck(): Promise<any> {
    return this.request<any>('/health');
  }
}

// Create and export the API instance
export const api = new ApiClient(API_BASE_URL);

// Export default
export default api;

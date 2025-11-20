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

export interface PaginatedResponse<T> {
  data: T[];
  current_page: number;
  per_page: number;
  total: number;
  last_page: number;
  first_page_url: string;
  last_page_url: string;
  next_page_url: string | null;
  prev_page_url: string | null;
  path: string;
  from: number;
  to: number;
}

export interface ApiError {
  success: false;
  message: string;
  status_code: number;
  errors?: Record<string, string[]> | Record<string, any>;
  timestamp: string;
  request_id?: string;
}

export interface LoginRequest {
  email: string;
  password: string;
  role: 'admin' | 'student' | 'teacher';
}

export interface RegisterRequest {
  name: string;
  email: string;
  password: string;
  password_confirmation: string;
  role: 'admin' | 'student' | 'teacher';
}

export interface LoginResponse {
  user: User;
  token: string;
  token_type: string;
}

export interface Enrollment {
  id: number;
  student_id: number;
  course_id: number;
  enrollment_date: string;
  status: 'enrolled' | 'completed' | 'dropped' | 'failed';
  grade: string | null;
  credits_earned: number | null;
  student?: {
    id: number;
    user: User;
    student_id: string;
  };
  course?: Course;
}

// Storage for auth token
const TOKEN_KEY = 'academic_portal_token';

export const authStorage = {
  getToken: (): string | null => localStorage.getItem(TOKEN_KEY),
  setToken: (token: string) => localStorage.setItem(TOKEN_KEY, token),
  removeToken: () => localStorage.removeItem(TOKEN_KEY),
};

// Custom Error Class for API errors
export class ApiClientError extends Error {
  public statusCode: number;
  public errors?: Record<string, any>;
  public apiResponse?: ApiError;

  constructor(
    message: string, 
    statusCode: number = 500, 
    errors?: Record<string, any>,
    apiResponse?: ApiError
  ) {
    super(message);
    this.name = 'ApiClientError';
    this.statusCode = statusCode;
    this.errors = errors;
    this.apiResponse = apiResponse;
  }

  toJSON() {
    return {
      name: this.name,
      message: this.message,
      statusCode: this.statusCode,
      errors: this.errors,
      apiResponse: this.apiResponse,
    };
  }

  isValidationError(): boolean {
    return this.statusCode === 422;
  }

  isAuthenticationError(): boolean {
    return this.statusCode === 401;
  }

  isAuthorizationError(): boolean {
    return this.statusCode === 403;
  }

  isNotFoundError(): boolean {
    return this.statusCode === 404;
  }

  isServerError(): boolean {
    return this.statusCode >= 500;
  }
}

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
        'X-Request-ID': `req_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`,
        ...(token && { Authorization: `Bearer ${token}` }),
        ...options.headers,
      },
      ...options,
    };

    try {
      const response = await fetch(`${this.baseURL}${endpoint}`, config);
      
      if (!response.ok) {
        let errorData: ApiError;
        
        try {
          errorData = await response.json() as ApiError;
        } catch {
          // If response is not JSON, create a standard error
          errorData = {
            success: false,
            message: `HTTP ${response.status}: ${response.statusText}`,
            status_code: response.status,
            timestamp: new Date().toISOString(),
          };
        }

        // Create a more detailed error
        const error = new ApiClientError(
          errorData.message || `HTTP error! status: ${response.status}`,
          errorData.status_code || response.status,
          errorData.errors,
          errorData
        );
        
        throw error;
      }

      return await response.json();
    } catch (error) {
      console.error('API request failed:', {
        endpoint,
        error: error instanceof ApiClientError ? error.toJSON() : error,
      });
      throw error;
    }
  }

  // Authentication Methods
  async register(userData: RegisterRequest): Promise<ApiResponse<LoginResponse>> {
    return this.request<ApiResponse<LoginResponse>>('/auth/register', {
      method: 'POST',
      body: JSON.stringify(userData),
    });
  }

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
  async getCourses(): Promise<ApiResponse<PaginatedResponse<Course>>> {
    return this.request<ApiResponse<PaginatedResponse<Course>>>('/admin/courses');
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
  async getUsers(): Promise<ApiResponse<PaginatedResponse<User>>> {
    return this.request<ApiResponse<PaginatedResponse<User>>>('/admin/users');
  }

  async getUser(id: number): Promise<ApiResponse<User>> {
    return this.request<ApiResponse<User>>(`/admin/users/${id}`);
  }

  // Enrollment Methods
  async enrollStudent(courseId: number, studentId: number): Promise<ApiResponse<Enrollment>> {
    return this.request<ApiResponse<Enrollment>>('/admin/enrollments', {
      method: 'POST',
      body: JSON.stringify({ course_id: courseId, student_id: studentId }),
    });
  }

  async unenrollStudent(enrollmentId: number): Promise<ApiResponse<{}>> {
    return this.request<ApiResponse<{}>>(`/admin/enrollments/${enrollmentId}`, {
      method: 'DELETE',
    });
  }

  async getStudentCourses(studentId?: number): Promise<ApiResponse<Enrollment[]>> {
    const endpoint = studentId ? `/admin/students/${studentId}/courses` : '/student/courses';
    return this.request<ApiResponse<Enrollment[]>>(endpoint);
  }

  async getCourseEnrollments(courseId: number): Promise<ApiResponse<Enrollment[]>> {
    return this.request<ApiResponse<Enrollment[]>>(`/admin/courses/${courseId}/enrollments`);
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

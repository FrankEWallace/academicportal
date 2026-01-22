import apiClient from './apiClient';

export interface Department {
  id: number;
  name: string;
  code: string;
  description?: string;
  head_teacher_id?: number;
  headTeacher?: {
    id: number;
    name: string;
    email: string;
  };
  established_year?: number;
  budget?: number;
  location?: string;
  phone?: string;
  email?: string;
  status: 'active' | 'inactive';
  teachers_count?: number;
  students_count?: number;
  courses_count?: number;
  teachers?: Array<{
    id: number;
    name: string;
    email: string;
  }>;
  created_at: string;
  updated_at: string;
}

export interface DepartmentStatistics {
  total_departments: number;
  active_departments: number;
  inactive_departments: number;
  departments_with_head: number;
  total_budget: number;
  largest_department?: {
    name: string;
    student_count: number;
  };
}

export interface Teacher {
  id: number;
  name: string;
  email: string;
  department_id?: number;
  department?: {
    id: number;
    name: string;
  };
}

export const departmentApi = {
  /**
   * Get all departments
   */
  async getDepartments(status?: 'active' | 'inactive', search?: string) {
    const params: any = {};
    if (status) params.status = status;
    if (search) params.search = search;
    
    const response = await apiClient.get('/admin/departments', { params });
    return response.data;
  },

  /**
   * Get single department
   */
  async getDepartment(id: number) {
    const response = await apiClient.get(`/admin/departments/${id}`);
    return response.data;
  },

  /**
   * Create new department
   */
  async createDepartment(data: {
    name: string;
    code: string;
    description?: string;
    head_teacher_id?: number;
    established_year?: number;
    budget?: number;
    location?: string;
    phone?: string;
    email?: string;
    status?: 'active' | 'inactive';
  }) {
    const response = await apiClient.post('/admin/departments', data);
    return response.data;
  },

  /**
   * Update department
   */
  async updateDepartment(id: number, data: Partial<Department>) {
    const response = await apiClient.put(`/admin/departments/${id}`, data);
    return response.data;
  },

  /**
   * Delete department
   */
  async deleteDepartment(id: number) {
    const response = await apiClient.delete(`/admin/departments/${id}`);
    return response.data;
  },

  /**
   * Get department statistics
   */
  async getStatistics() {
    const response = await apiClient.get('/admin/departments/statistics');
    return response.data;
  },

  /**
   * Get available teachers
   */
  async getAvailableTeachers() {
    const response = await apiClient.get('/admin/departments/available-teachers');
    return response.data;
  },

  /**
   * Assign department head
   */
  async assignHead(departmentId: number, teacherId: number) {
    const response = await apiClient.post(`/admin/departments/${departmentId}/assign-head`, {
      head_teacher_id: teacherId
    });
    return response.data;
  },

  /**
   * Remove department head
   */
  async removeHead(departmentId: number) {
    const response = await apiClient.post(`/admin/departments/${departmentId}/remove-head`);
    return response.data;
  }
};

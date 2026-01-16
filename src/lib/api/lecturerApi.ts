import apiClient, { handleApiError } from './apiClient';

// Types
export interface Course {
  id: number;
  code: string;
  title: string;
  semester: number;
  academic_year: string;
  total_students: number;
  ca_submitted: number;
  ca_locked: boolean;
}

export interface Student {
  id: number;
  student_id: string;
  name: string;
  matric_number: string;
  current_score: number | null;
  max_score: number;
  submitted_at: string | null;
  locked: boolean;
}

export interface CAStatistics {
  total_courses: number;
  total_submissions: number;
  pending_approvals: number;
  locked_courses: number;
}

export interface UpdateScoreRequest {
  score: number;
}

export interface BulkUpdateRequest {
  scores: Array<{
    student_id: number;
    score: number;
  }>;
}

// CA Management API
export const lecturerCAApi = {
  // Get all courses for lecturer
  getCourses: async () => {
    try {
      const response = await apiClient.get('/lecturer/ca/courses');
      return response.data;
    } catch (error) {
      throw handleApiError(error);
    }
  },

  // Get students for a specific course
  getCourseStudents: async (courseId: number) => {
    try {
      const response = await apiClient.get(`/lecturer/ca/courses/${courseId}/students`);
      return response.data;
    } catch (error) {
      throw handleApiError(error);
    }
  },

  // Update single student score
  updateScore: async (scoreId: number, data: UpdateScoreRequest) => {
    try {
      const response = await apiClient.put(`/lecturer/ca/scores/${scoreId}`, data);
      return response.data;
    } catch (error) {
      throw handleApiError(error);
    }
  },

  // Bulk update scores
  bulkUpdateScores: async (courseId: number, data: BulkUpdateRequest) => {
    try {
      const response = await apiClient.post(`/lecturer/ca/courses/${courseId}/bulk-update`, data);
      return response.data;
    } catch (error) {
      throw handleApiError(error);
    }
  },

  // Lock course CA scores
  lockCourse: async (courseId: number) => {
    try {
      const response = await apiClient.post(`/lecturer/ca/courses/${courseId}/lock`);
      return response.data;
    } catch (error) {
      throw handleApiError(error);
    }
  },

  // Submit for approval
  submitForApproval: async (courseId: number) => {
    try {
      const response = await apiClient.post(`/lecturer/ca/courses/${courseId}/submit-approval`);
      return response.data;
    } catch (error) {
      throw handleApiError(error);
    }
  },

  // Get CA statistics
  getStatistics: async () => {
    try {
      const response = await apiClient.get('/lecturer/ca/statistics');
      return response.data;
    } catch (error) {
      throw handleApiError(error);
    }
  },
};

// Results Management API
export interface ResultsCourse {
  id: number;
  code: string;
  title: string;
  semester: number;
  academic_year: string;
  total_students: number;
  results_submitted: number;
  results_locked: boolean;
  moderation_status: 'pending' | 'approved' | 'rejected';
}

export interface StudentResult {
  id: number;
  student_id: string;
  name: string;
  matric_number: string;
  ca_score: number;
  exam_score: number | null;
  total_score: number | null;
  grade: string | null;
  locked: boolean;
}

export interface ResultStatistics {
  total_courses: number;
  completed_courses: number;
  pending_moderation: number;
  approved_courses: number;
}

export const lecturerResultsApi = {
  // Get all courses
  getCourses: async () => {
    try {
      const response = await apiClient.get('/lecturer/results/courses');
      return response.data;
    } catch (error) {
      throw handleApiError(error);
    }
  },

  // Get student results for a course
  getCourseResults: async (courseId: number) => {
    try {
      const response = await apiClient.get(`/lecturer/results/courses/${courseId}/students`);
      return response.data;
    } catch (error) {
      throw handleApiError(error);
    }
  },

  // Update exam score
  updateExamScore: async (resultId: number, data: { exam_score: number }) => {
    try {
      const response = await apiClient.put(`/lecturer/results/scores/${resultId}`, data);
      return response.data;
    } catch (error) {
      throw handleApiError(error);
    }
  },

  // Bulk update exam scores
  bulkUpdateScores: async (courseId: number, data: BulkUpdateRequest) => {
    try {
      const response = await apiClient.post(`/lecturer/results/courses/${courseId}/bulk-update`, data);
      return response.data;
    } catch (error) {
      throw handleApiError(error);
    }
  },

  // Lock results
  lockResults: async (courseId: number) => {
    try {
      const response = await apiClient.post(`/lecturer/results/courses/${courseId}/lock`);
      return response.data;
    } catch (error) {
      throw handleApiError(error);
    }
  },

  // Submit for moderation
  submitForModeration: async (courseId: number) => {
    try {
      const response = await apiClient.post(`/lecturer/results/courses/${courseId}/submit-moderation`);
      return response.data;
    } catch (error) {
      throw handleApiError(error);
    }
  },

  // Get statistics
  getStatistics: async () => {
    try {
      const response = await apiClient.get('/lecturer/results/statistics');
      return response.data;
    } catch (error) {
      throw handleApiError(error);
    }
  },
};

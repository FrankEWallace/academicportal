import axios from 'axios';

const API_BASE_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000/api';

// Types
export interface EnrolledCourse {
  course_id: number;
  course_code: string;
  course_name: string;
  units: number;
  instructor: string;
}

export interface EnrollmentSummary {
  semester_code: string;
  courses: EnrolledCourse[];
  total_units: number;
  max_units: number;
  can_confirm: boolean;
}

export interface ValidationResult {
  course_id: number;
  course_code: string;
  course_name: string;
  prerequisites_met: boolean;
  missing_prerequisites: string[];
  schedule_conflicts: any[];
  can_enroll: boolean;
}

export interface ConfirmationEmail {
  student_name: string;
  student_id: string;
  semester_code: string;
  courses: EnrolledCourse[];
  total_units: number;
  confirmation_date: string;
}

// API Client
const getAuthHeader = () => {
  const token = localStorage.getItem('token');
  return {
    headers: {
      Authorization: `Bearer ${token}`,
      'Content-Type': 'application/json',
    },
  };
};

export const enrollmentApi = {
  // Get enrollment summary
  getEnrollmentSummary: async (): Promise<EnrollmentSummary> => {
    const response = await axios.get(
      `${API_BASE_URL}/student/enrollment/summary`,
      getAuthHeader()
    );
    return response.data;
  },

  // Validate enrollment
  validateEnrollment: async (courseIds: number[]): Promise<{ validation_results: ValidationResult[]; all_valid: boolean }> => {
    const response = await axios.post(
      `${API_BASE_URL}/student/enrollment/validate`,
      { course_ids: courseIds },
      getAuthHeader()
    );
    return response.data;
  },

  // Confirm enrollment
  confirmEnrollment: async (data: {
    timetable_understood: boolean;
    attendance_policy_agreed: boolean;
    academic_calendar_checked: boolean;
  }): Promise<any> => {
    const response = await axios.post(
      `${API_BASE_URL}/student/enrollment/confirm`,
      data,
      getAuthHeader()
    );
    return response.data;
  },

  // Get confirmation email
  getConfirmationEmail: async (confirmationId: number): Promise<ConfirmationEmail> => {
    const response = await axios.get(
      `${API_BASE_URL}/student/enrollment/confirmation-email/${confirmationId}`,
      getAuthHeader()
    );
    return response.data;
  },
};

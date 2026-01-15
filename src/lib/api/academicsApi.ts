import axios from 'axios';

const API_BASE_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000/api';

// Types
export interface CAScore {
  assessment_type: string;
  assessment_name: string;
  score: number;
  max_score: number;
  weight_percentage: number;
  weighted_score: number;
}

export interface CoursePerformance {
  course_id: number;
  course_code: string;
  course_name: string;
  units: number;
  ca_scores: CAScore[];
  ca_total: number;
  ca_percentage: number;
  exam_score: number;
  exam_max: number;
  exam_percentage: number;
  total_score: number;
  grade: string;
  grade_point: number;
}

export interface SemesterPerformance {
  semester_code: string;
  courses: CoursePerformance[];
  total_units: number;
  semester_gpa: number;
  cumulative_gpa: number;
  academic_standing: string;
}

export interface CourseBreakdown {
  course_code: string;
  course_name: string;
  ca_breakdown: {
    quizzes: CAScore[];
    assignments: CAScore[];
    midterms: CAScore[];
    projects: CAScore[];
    subtotals: {
      quizzes: number;
      assignments: number;
      midterms: number;
      projects: number;
      total_ca: number;
    };
  };
  final_exam: {
    score: number;
    max_score: number;
    percentage: number;
    weight: number;
  };
  overall: {
    total_score: number;
    grade: string;
    grade_point: number;
  };
}

export interface HistoricalRecord {
  semester_code: string;
  academic_year: string;
  courses_count: number;
  total_units: number;
  semester_gpa: number;
  cumulative_gpa: number;
  academic_standing: string;
}

export interface GPASummary {
  current_semester: {
    semester_code: string;
    gpa: number;
    units: number;
  };
  cumulative: {
    gpa: number;
    total_units: number;
    total_credits_earned: number;
  };
  academic_standing: string;
  gpa_trend: Array<{
    semester: string;
    gpa: number;
  }>;
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

export const academicsApi = {
  // Get current semester performance
  getCurrentSemesterPerformance: async (): Promise<SemesterPerformance> => {
    const response = await axios.get(
      `${API_BASE_URL}/student/academics/current-semester`,
      getAuthHeader()
    );
    return response.data;
  },

  // Get course breakdown
  getCourseBreakdown: async (courseId: number): Promise<CourseBreakdown> => {
    const response = await axios.get(
      `${API_BASE_URL}/student/academics/course/${courseId}/breakdown`,
      getAuthHeader()
    );
    return response.data;
  },

  // Get historical records
  getHistoricalRecords: async (): Promise<{ semesters: HistoricalRecord[] }> => {
    const response = await axios.get(
      `${API_BASE_URL}/student/academics/historical`,
      getAuthHeader()
    );
    return response.data;
  },

  // Get semester performance
  getSemesterPerformance: async (semesterCode: string): Promise<SemesterPerformance> => {
    const response = await axios.get(
      `${API_BASE_URL}/student/academics/semester/${semesterCode}`,
      getAuthHeader()
    );
    return response.data;
  },

  // Download transcript
  downloadTranscript: async (): Promise<Blob> => {
    const token = localStorage.getItem('token');
    const response = await axios.get(
      `${API_BASE_URL}/student/academics/transcript/download`,
      {
        headers: {
          Authorization: `Bearer ${token}`,
        },
        responseType: 'blob',
      }
    );
    return response.data;
  },

  // Get GPA summary
  getGPASummary: async (): Promise<GPASummary> => {
    const response = await axios.get(
      `${API_BASE_URL}/student/academics/gpa-summary`,
      getAuthHeader()
    );
    return response.data;
  },
};

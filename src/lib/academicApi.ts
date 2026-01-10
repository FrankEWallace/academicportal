// Academic Features API - Types and Functions
const API_BASE_URL = 'http://localhost:8000/api';

// ==================== TYPES ====================

// Timetable Types
export interface Timetable {
  id: number;
  course_id: number;
  teacher_id: number;
  day_of_week: 'Monday' | 'Tuesday' | 'Wednesday' | 'Thursday' | 'Friday' | 'Saturday' | 'Sunday';
  start_time: string;
  end_time: string;
  room: string;
  capacity: number;
  semester: number;
  academic_year: string;
  status: 'scheduled' | 'cancelled' | 'completed';
  notes?: string;
  course?: any;
  teacher?: any;
  created_at: string;
  updated_at: string;
}

// Academic Calendar Types
export interface AcademicEvent {
  id: number;
  title: string;
  description?: string;
  event_type: 'exam' | 'holiday' | 'registration' | 'orientation' | 'break' | 'deadline' | 'other';
  start_date: string;
  end_date: string;
  semester?: number;
  academic_year: string;
  status: 'scheduled' | 'ongoing' | 'completed' | 'cancelled';
  is_holiday: boolean;
  created_at: string;
  updated_at: string;
}

// Prerequisite Types
export interface CoursePrerequisite {
  id: number;
  course_id: number;
  prerequisite_course_id: number;
  minimum_grade?: number;
  requirement_type: 'required' | 'recommended' | 'corequisite';
  prerequisite_course?: any;
  created_at: string;
  updated_at: string;
}

export interface PrerequisiteEligibility {
  eligible: boolean;
  message: string;
  failed_prerequisites?: Array<{
    course: any;
    reason: string;
  }>;
}

// Waitlist Types
export interface CourseWaitlist {
  id: number;
  course_id: number;
  student_id: number;
  position: number;
  semester: number;
  academic_year: string;
  status: 'waiting' | 'enrolled' | 'removed' | 'expired';
  added_at: string;
  enrolled_at?: string;
  removed_at?: string;
  notes?: string;
  student?: any;
  course?: any;
  created_at: string;
  updated_at: string;
}

// Degree Program Types
export interface DegreeProgram {
  id: number;
  program_code: string;
  program_name: string;
  department: string;
  level: 'undergraduate' | 'graduate' | 'postgraduate';
  duration_years: number;
  total_credits_required: number;
  minimum_cgpa?: number;
  description?: string;
  status: 'active' | 'inactive';
  created_at: string;
  updated_at: string;
}

export interface ProgramRequirement {
  id: number;
  degree_program_id: number;
  course_id: number;
  requirement_type: 'core' | 'major' | 'minor' | 'elective' | 'general_education';
  semester_recommended?: number;
  is_mandatory: boolean;
  course?: any;
  created_at: string;
  updated_at: string;
}

// Degree Progress Types
export interface DegreeProgress {
  student: any;
  program: DegreeProgram;
  progress: {
    credits_earned: number;
    credits_required: number;
    credits_percentage: number;
    cgpa: number;
    minimum_cgpa?: number;
    requirement_completion: {
      [key: string]: {
        total: number;
        completed: number;
        percentage: number;
        mandatory: number;
        mandatory_completed: number;
        mandatory_percentage: number;
      };
    };
  };
  graduation_eligibility: {
    eligible: boolean;
    reasons: string[];
  };
  completed_courses: any[];
}

export interface Transcript {
  student: {
    id: number;
    name: string;
    email: string;
    student_id: string;
  };
  transcript: Array<{
    course_code: string;
    course_name: string;
    credits: number;
    grade_point: number;
    letter_grade: string;
    quality_points: number;
  }>;
  summary: {
    total_credits: number;
    total_quality_points: number;
    cgpa: number;
    total_courses: number;
  };
  generated_at: string;
}

// API Response wrapper
interface ApiResponse<T> {
  success: boolean;
  data: T;
  message?: string;
  error?: string;
}

// ==================== API FUNCTIONS ====================

// Helper function to get auth token
const getAuthToken = () => {
  return localStorage.getItem('token');
};

// Helper function to create headers
const getHeaders = () => {
  const token = getAuthToken();
  return {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    ...(token && { 'Authorization': `Bearer ${token}` })
  };
};

// ==================== TIMETABLE API ====================

export const timetableApi = {
  // Get all timetables with filters
  getAll: async (filters?: {
    course_id?: number;
    teacher_id?: number;
    day?: string;
    semester?: number;
    academic_year?: string;
    room?: string;
    status?: string;
  }): Promise<ApiResponse<Timetable[]>> => {
    const params = new URLSearchParams();
    if (filters) {
      Object.entries(filters).forEach(([key, value]) => {
        if (value !== undefined) params.append(key, value.toString());
      });
    }
    const response = await fetch(`${API_BASE_URL}/timetables?${params}`, {
      headers: getHeaders()
    });
    return response.json();
  },

  // Get student timetable
  getStudentTimetable: async (studentId: number): Promise<ApiResponse<Timetable[]>> => {
    const response = await fetch(`${API_BASE_URL}/timetables/student/${studentId}`, {
      headers: getHeaders()
    });
    return response.json();
  },

  // Get teacher timetable
  getTeacherTimetable: async (teacherId: number): Promise<ApiResponse<Timetable[]>> => {
    const response = await fetch(`${API_BASE_URL}/timetables/teacher/${teacherId}`, {
      headers: getHeaders()
    });
    return response.json();
  },

  // Get room schedule
  getRoomSchedule: async (room: string, filters?: { day?: string; semester?: number; academic_year?: string }): Promise<ApiResponse<Timetable[]>> => {
    const params = new URLSearchParams();
    if (filters) {
      Object.entries(filters).forEach(([key, value]) => {
        if (value !== undefined) params.append(key, value.toString());
      });
    }
    const response = await fetch(`${API_BASE_URL}/timetables/room/${room}?${params}`, {
      headers: getHeaders()
    });
    return response.json();
  },

  // Create timetable
  create: async (data: Partial<Timetable>): Promise<ApiResponse<Timetable>> => {
    const response = await fetch(`${API_BASE_URL}/timetables`, {
      method: 'POST',
      headers: getHeaders(),
      body: JSON.stringify(data)
    });
    return response.json();
  },

  // Update timetable
  update: async (id: number, data: Partial<Timetable>): Promise<ApiResponse<Timetable>> => {
    const response = await fetch(`${API_BASE_URL}/timetables/${id}`, {
      method: 'PUT',
      headers: getHeaders(),
      body: JSON.stringify(data)
    });
    return response.json();
  },

  // Delete timetable
  delete: async (id: number): Promise<ApiResponse<null>> => {
    const response = await fetch(`${API_BASE_URL}/timetables/${id}`, {
      method: 'DELETE',
      headers: getHeaders()
    });
    return response.json();
  }
};

// ==================== ACADEMIC CALENDAR API ====================

export const academicCalendarApi = {
  // Get all events
  getAll: async (filters?: {
    event_type?: string;
    semester?: number;
    academic_year?: string;
    status?: string;
  }): Promise<ApiResponse<AcademicEvent[]>> => {
    const params = new URLSearchParams();
    if (filters) {
      Object.entries(filters).forEach(([key, value]) => {
        if (value !== undefined) params.append(key, value.toString());
      });
    }
    const response = await fetch(`${API_BASE_URL}/academic-calendar?${params}`, {
      headers: getHeaders()
    });
    return response.json();
  },

  // Get upcoming events
  getUpcoming: async (): Promise<ApiResponse<AcademicEvent[]>> => {
    const response = await fetch(`${API_BASE_URL}/academic-calendar/upcoming`, {
      headers: getHeaders()
    });
    return response.json();
  },

  // Get current events
  getCurrent: async (): Promise<ApiResponse<AcademicEvent[]>> => {
    const response = await fetch(`${API_BASE_URL}/academic-calendar/current`, {
      headers: getHeaders()
    });
    return response.json();
  },

  // Get holidays
  getHolidays: async (): Promise<ApiResponse<AcademicEvent[]>> => {
    const response = await fetch(`${API_BASE_URL}/academic-calendar/holidays`, {
      headers: getHeaders()
    });
    return response.json();
  },

  // Check if date is holiday
  checkHoliday: async (date: string): Promise<ApiResponse<{ is_holiday: boolean; event?: AcademicEvent }>> => {
    const response = await fetch(`${API_BASE_URL}/academic-calendar/check-holiday/${date}`, {
      headers: getHeaders()
    });
    return response.json();
  },

  // Create event
  create: async (data: Partial<AcademicEvent>): Promise<ApiResponse<AcademicEvent>> => {
    const response = await fetch(`${API_BASE_URL}/academic-calendar`, {
      method: 'POST',
      headers: getHeaders(),
      body: JSON.stringify(data)
    });
    return response.json();
  },

  // Update event
  update: async (id: number, data: Partial<AcademicEvent>): Promise<ApiResponse<AcademicEvent>> => {
    const response = await fetch(`${API_BASE_URL}/academic-calendar/${id}`, {
      method: 'PUT',
      headers: getHeaders(),
      body: JSON.stringify(data)
    });
    return response.json();
  },

  // Delete event
  delete: async (id: number): Promise<ApiResponse<null>> => {
    const response = await fetch(`${API_BASE_URL}/academic-calendar/${id}`, {
      method: 'DELETE',
      headers: getHeaders()
    });
    return response.json();
  }
};

// ==================== PREREQUISITE API ====================

export const prerequisiteApi = {
  // Get course prerequisites
  getCoursePrerequisites: async (courseId: number): Promise<ApiResponse<{ course: any; prerequisites: CoursePrerequisite[] }>> => {
    const response = await fetch(`${API_BASE_URL}/prerequisites/course/${courseId}`, {
      headers: getHeaders()
    });
    return response.json();
  },

  // Check eligibility
  checkEligibility: async (courseId: number, studentId: number): Promise<ApiResponse<PrerequisiteEligibility>> => {
    const response = await fetch(`${API_BASE_URL}/prerequisites/check/${courseId}/${studentId}`, {
      headers: getHeaders()
    });
    return response.json();
  },

  // Add prerequisite
  create: async (data: Partial<CoursePrerequisite>): Promise<ApiResponse<CoursePrerequisite>> => {
    const response = await fetch(`${API_BASE_URL}/prerequisites`, {
      method: 'POST',
      headers: getHeaders(),
      body: JSON.stringify(data)
    });
    return response.json();
  },

  // Update prerequisite
  update: async (id: number, data: Partial<CoursePrerequisite>): Promise<ApiResponse<CoursePrerequisite>> => {
    const response = await fetch(`${API_BASE_URL}/prerequisites/${id}`, {
      method: 'PUT',
      headers: getHeaders(),
      body: JSON.stringify(data)
    });
    return response.json();
  },

  // Delete prerequisite
  delete: async (id: number): Promise<ApiResponse<null>> => {
    const response = await fetch(`${API_BASE_URL}/prerequisites/${id}`, {
      method: 'DELETE',
      headers: getHeaders()
    });
    return response.json();
  }
};

// ==================== WAITLIST API ====================

export const waitlistApi = {
  // Get course waitlist
  getCourseWaitlist: async (courseId: number): Promise<ApiResponse<{ course: any; waitlist: CourseWaitlist[]; count: number }>> => {
    const response = await fetch(`${API_BASE_URL}/waitlist/course/${courseId}`, {
      headers: getHeaders()
    });
    return response.json();
  },

  // Get student waitlist
  getStudentWaitlist: async (studentId: number): Promise<ApiResponse<CourseWaitlist[]>> => {
    const response = await fetch(`${API_BASE_URL}/waitlist/student/${studentId}`, {
      headers: getHeaders()
    });
    return response.json();
  },

  // Join waitlist
  join: async (data: { course_id: number; student_id: number }): Promise<ApiResponse<CourseWaitlist>> => {
    const response = await fetch(`${API_BASE_URL}/waitlist`, {
      method: 'POST',
      headers: getHeaders(),
      body: JSON.stringify(data)
    });
    return response.json();
  },

  // Leave waitlist
  leave: async (id: number): Promise<ApiResponse<null>> => {
    const response = await fetch(`${API_BASE_URL}/waitlist/${id}`, {
      method: 'DELETE',
      headers: getHeaders()
    });
    return response.json();
  },

  // Process waitlist (admin only)
  process: async (courseId: number): Promise<ApiResponse<{ enrolled_count: number; enrolled_students: any[] }>> => {
    const response = await fetch(`${API_BASE_URL}/waitlist/process/${courseId}`, {
      method: 'POST',
      headers: getHeaders()
    });
    return response.json();
  }
};

// ==================== DEGREE PROGRAM API ====================

export const degreeProgramApi = {
  // Get all programs
  getAll: async (filters?: {
    department?: string;
    level?: string;
    status?: string;
  }): Promise<ApiResponse<DegreeProgram[]>> => {
    const params = new URLSearchParams();
    if (filters) {
      Object.entries(filters).forEach(([key, value]) => {
        if (value !== undefined) params.append(key, value.toString());
      });
    }
    const response = await fetch(`${API_BASE_URL}/degree-programs?${params}`, {
      headers: getHeaders()
    });
    return response.json();
  },

  // Get program details
  getDetails: async (id: number): Promise<ApiResponse<{ program: DegreeProgram; requirements: any; statistics: any }>> => {
    const response = await fetch(`${API_BASE_URL}/degree-programs/${id}`, {
      headers: getHeaders()
    });
    return response.json();
  },

  // Create program
  create: async (data: Partial<DegreeProgram>): Promise<ApiResponse<DegreeProgram>> => {
    const response = await fetch(`${API_BASE_URL}/degree-programs`, {
      method: 'POST',
      headers: getHeaders(),
      body: JSON.stringify(data)
    });
    return response.json();
  },

  // Update program
  update: async (id: number, data: Partial<DegreeProgram>): Promise<ApiResponse<DegreeProgram>> => {
    const response = await fetch(`${API_BASE_URL}/degree-programs/${id}`, {
      method: 'PUT',
      headers: getHeaders(),
      body: JSON.stringify(data)
    });
    return response.json();
  },

  // Delete program
  delete: async (id: number): Promise<ApiResponse<null>> => {
    const response = await fetch(`${API_BASE_URL}/degree-programs/${id}`, {
      method: 'DELETE',
      headers: getHeaders()
    });
    return response.json();
  },

  // Add requirement
  addRequirement: async (programId: number, data: Partial<ProgramRequirement>): Promise<ApiResponse<ProgramRequirement>> => {
    const response = await fetch(`${API_BASE_URL}/degree-programs/${programId}/requirements`, {
      method: 'POST',
      headers: getHeaders(),
      body: JSON.stringify(data)
    });
    return response.json();
  },

  // Remove requirement
  removeRequirement: async (programId: number, requirementId: number): Promise<ApiResponse<null>> => {
    const response = await fetch(`${API_BASE_URL}/degree-programs/${programId}/requirements/${requirementId}`, {
      method: 'DELETE',
      headers: getHeaders()
    });
    return response.json();
  }
};

// ==================== DEGREE PROGRESS API ====================

export const degreeProgressApi = {
  // Get student progress
  getProgress: async (studentId: number): Promise<ApiResponse<DegreeProgress>> => {
    const response = await fetch(`${API_BASE_URL}/degree-progress/student/${studentId}`, {
      headers: getHeaders()
    });
    return response.json();
  },

  // Get transcript
  getTranscript: async (studentId: number): Promise<ApiResponse<Transcript>> => {
    const response = await fetch(`${API_BASE_URL}/degree-progress/student/${studentId}/transcript`, {
      headers: getHeaders()
    });
    return response.json();
  },

  // Get remaining requirements
  getRemainingRequirements: async (studentId: number): Promise<ApiResponse<any>> => {
    const response = await fetch(`${API_BASE_URL}/degree-progress/student/${studentId}/remaining`, {
      headers: getHeaders()
    });
    return response.json();
  }
};

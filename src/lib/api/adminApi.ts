import apiClient, { handleApiError } from './apiClient';

// Registration Control Types
export interface Registration {
  id: number;
  student_id: number;
  student_name: string;
  matric_number: string;
  semester: number;
  academic_year: string;
  courses_count: number;
  total_units: number;
  status: 'pending' | 'verified' | 'blocked';
  fee_status: 'paid' | 'partial' | 'unpaid';
  submitted_at: string;
}

export interface RegistrationStatistics {
  total_registrations: number;
  pending_verification: number;
  verified: number;
  blocked: number;
}

export interface RegistrationAuditLog {
  id: number;
  action: string;
  performed_by: string;
  timestamp: string;
  details: string;
}

// Accommodation Types
export interface Hostel {
  id: number;
  name: string;
  code: string;
  gender: 'male' | 'female';
  total_rooms: number;
  capacity: number;
  current_occupancy: number;
  available_spaces: number;
  location: string;
  is_active: boolean;
}

export interface Room {
  id: number;
  hostel_id: number;
  room_number: string;
  floor: number;
  capacity: number;
  current_occupancy: number;
  status: 'available' | 'occupied' | 'full' | 'maintenance';
}

export interface AccommodationRequest {
  id: number;
  student_id: number;
  student_name: string;
  matric_number: string;
  gender: 'male' | 'female';
  level: number;
  status: 'pending' | 'allocated' | 'rejected';
  requested_at: string;
  allocated_hostel?: string;
  allocated_room?: string;
}

export interface AccommodationStatistics {
  total_requests: number;
  pending_allocation: number;
  allocated: number;
  total_capacity: number;
  current_occupancy: number;
}

// Insurance Types
export interface InsuranceSubmission {
  id: number;
  student_id: number;
  student_name: string;
  matric_number: string;
  policy_number: string;
  provider: string;
  coverage_amount: number;
  premium_amount: number;
  status: 'pending' | 'verified' | 'rejected';
  submitted_at: string;
  verified_at?: string;
  rejected_reason?: string;
}

export interface InsuranceStatistics {
  total_submissions: number;
  pending_verification: number;
  verified: number;
  rejected: number;
}

// Enrollment Types
export interface Enrollment {
  id: number;
  student_id: number;
  student_name: string;
  matric_number: string;
  semester: number;
  academic_year: string;
  courses_count: number;
  total_units: number;
  status: 'pending' | 'approved' | 'rejected';
  submitted_at: string;
}

export interface EnrollmentStatistics {
  total_enrollments: number;
  pending_approval: number;
  approved: number;
  rejected: number;
}

// API Services
export const adminRegistrationApi = {
  // Get all registrations
  getRegistrations: async (status?: string) => {
    try {
      const params = status && status !== 'all' ? { status } : {};
      const response = await apiClient.get('/admin/registrations', { params });
      return response.data;
    } catch (error) {
      throw handleApiError(error);
    }
  },

  // Get pending registrations
  getPendingRegistrations: async () => {
    try {
      const response = await apiClient.get('/admin/registrations/pending');
      return response.data;
    } catch (error) {
      throw handleApiError(error);
    }
  },

  // Get blocked registrations
  getBlockedRegistrations: async () => {
    try {
      const response = await apiClient.get('/admin/registrations/blocked');
      return response.data;
    } catch (error) {
      throw handleApiError(error);
    }
  },

  // Get single registration
  getRegistration: async (id: number) => {
    try {
      const response = await apiClient.get(`/admin/registrations/${id}`);
      return response.data;
    } catch (error) {
      throw handleApiError(error);
    }
  },

  // Verify fees
  verifyFees: async (id: number) => {
    try {
      const response = await apiClient.post(`/admin/registrations/${id}/verify-fees`);
      return response.data;
    } catch (error) {
      throw handleApiError(error);
    }
  },

  // Block registration
  blockRegistration: async (id: number, reason: string) => {
    try {
      const response = await apiClient.post(`/admin/registrations/${id}/block`, { reason });
      return response.data;
    } catch (error) {
      throw handleApiError(error);
    }
  },

  // Unblock registration
  unblockRegistration: async (id: number) => {
    try {
      const response = await apiClient.post(`/admin/registrations/${id}/unblock`);
      return response.data;
    } catch (error) {
      throw handleApiError(error);
    }
  },

  // Get audit logs
  getAuditLogs: async (registrationId: number) => {
    try {
      const response = await apiClient.get(`/admin/registrations/${registrationId}/audit-logs`);
      return response.data;
    } catch (error) {
      throw handleApiError(error);
    }
  },

  // Get statistics
  getStatistics: async () => {
    try {
      const response = await apiClient.get('/admin/registrations/statistics');
      return response.data;
    } catch (error) {
      throw handleApiError(error);
    }
  },
};

export const adminAccommodationApi = {
  // Get all hostels
  getHostels: async () => {
    try {
      const response = await apiClient.get('/admin/accommodations/hostels');
      return response.data;
    } catch (error) {
      throw handleApiError(error);
    }
  },

  // Get available rooms for a hostel
  getAvailableRooms: async (hostelId: number) => {
    try {
      const response = await apiClient.get(`/admin/accommodations/rooms/available`, {
        params: { hostel_id: hostelId }
      });
      return response.data;
    } catch (error) {
      throw handleApiError(error);
    }
  },

  // Get pending requests
  getPendingRequests: async () => {
    try {
      const response = await apiClient.get('/admin/accommodations/pending');
      return response.data;
    } catch (error) {
      throw handleApiError(error);
    }
  },

  // Get single request
  getRequest: async (id: number) => {
    try {
      const response = await apiClient.get(`/admin/accommodations/${id}`);
      return response.data;
    } catch (error) {
      throw handleApiError(error);
    }
  },

  // Allocate room
  allocateRoom: async (requestId: number, hostelId: number, roomId: number) => {
    try {
      const response = await apiClient.post(`/admin/accommodations/allocate`, {
        request_id: requestId,
        hostel_id: hostelId,
        room_id: roomId,
      });
      return response.data;
    } catch (error) {
      throw handleApiError(error);
    }
  },

  // Vacate room
  vacateRoom: async (requestId: number) => {
    try {
      const response = await apiClient.post(`/admin/accommodations/vacate`, {
        request_id: requestId,
      });
      return response.data;
    } catch (error) {
      throw handleApiError(error);
    }
  },

  // Bulk allocate
  bulkAllocate: async (allocations: Array<{ request_id: number; hostel_id: number; room_id: number }>) => {
    try {
      const response = await apiClient.post('/admin/accommodations/bulk-allocate', { allocations });
      return response.data;
    } catch (error) {
      throw handleApiError(error);
    }
  },

  // Get statistics
  getStatistics: async () => {
    try {
      const response = await apiClient.get('/admin/accommodations/statistics');
      return response.data;
    } catch (error) {
      throw handleApiError(error);
    }
  },

  // Get occupancy report
  getOccupancyReport: async () => {
    try {
      const response = await apiClient.get('/admin/accommodations/occupancy');
      return response.data;
    } catch (error) {
      throw handleApiError(error);
    }
  },
};

export const adminInsuranceApi = {
  // Get all submissions
  getSubmissions: async (status?: string) => {
    try {
      const params = status ? { status } : {};
      const response = await apiClient.get('/admin/insurance', { params });
      return response.data;
    } catch (error) {
      throw handleApiError(error);
    }
  },

  // Get pending submissions
  getPendingSubmissions: async () => {
    try {
      const response = await apiClient.get('/admin/insurance/pending');
      return response.data;
    } catch (error) {
      throw handleApiError(error);
    }
  },

  // Get single submission
  getSubmission: async (id: number) => {
    try {
      const response = await apiClient.get(`/admin/insurance/${id}`);
      return response.data;
    } catch (error) {
      throw handleApiError(error);
    }
  },

  // Verify insurance
  verifyInsurance: async (id: number) => {
    try {
      const response = await apiClient.post(`/admin/insurance/${id}/verify`);
      return response.data;
    } catch (error) {
      throw handleApiError(error);
    }
  },

  // Reject insurance
  rejectInsurance: async (id: number, reason: string) => {
    try {
      const response = await apiClient.post(`/admin/insurance/${id}/reject`, { reason });
      return response.data;
    } catch (error) {
      throw handleApiError(error);
    }
  },

  // Request resubmission
  requestResubmission: async (id: number, feedback: string) => {
    try {
      const response = await apiClient.post(`/admin/insurance/${id}/request-resubmission`, { feedback });
      return response.data;
    } catch (error) {
      throw handleApiError(error);
    }
  },

  // Get statistics
  getStatistics: async () => {
    try {
      const response = await apiClient.get('/admin/insurance/statistics');
      return response.data;
    } catch (error) {
      throw handleApiError(error);
    }
  },
};

export const adminEnrollmentApi = {
  // Get all enrollments
  getEnrollments: async (status?: string) => {
    try {
      const params = status ? { status } : {};
      const response = await apiClient.get('/admin/enrollments', { params });
      return response.data;
    } catch (error) {
      throw handleApiError(error);
    }
  },

  // Get pending enrollments
  getPendingEnrollments: async () => {
    try {
      const response = await apiClient.get('/admin/enrollments/pending');
      return response.data;
    } catch (error) {
      throw handleApiError(error);
    }
  },

  // Get single enrollment
  getEnrollment: async (id: number) => {
    try {
      const response = await apiClient.get(`/admin/enrollments/${id}`);
      return response.data;
    } catch (error) {
      throw handleApiError(error);
    }
  },

  // Approve enrollment
  approveEnrollment: async (id: number) => {
    try {
      const response = await apiClient.post(`/admin/enrollments/${id}/approve`);
      return response.data;
    } catch (error) {
      throw handleApiError(error);
    }
  },

  // Reject enrollment
  rejectEnrollment: async (id: number, reason: string) => {
    try {
      const response = await apiClient.post(`/admin/enrollments/${id}/reject`, { reason });
      return response.data;
    } catch (error) {
      throw handleApiError(error);
    }
  },

  // Bulk approve
  bulkApprove: async (enrollmentIds: number[]) => {
    try {
      const response = await apiClient.post('/admin/enrollments/bulk-approve', { enrollment_ids: enrollmentIds });
      return response.data;
    } catch (error) {
      throw handleApiError(error);
    }
  },

  // Bulk reject
  bulkReject: async (enrollmentIds: number[], reason: string) => {
    try {
      const response = await apiClient.post('/admin/enrollments/bulk-reject', {
        enrollment_ids: enrollmentIds,
        reason,
      });
      return response.data;
    } catch (error) {
      throw handleApiError(error);
    }
  },

  // Get audit logs
  getAuditLogs: async (enrollmentId: number) => {
    try {
      const response = await apiClient.get(`/admin/enrollments/${enrollmentId}/audit-logs`);
      return response.data;
    } catch (error) {
      throw handleApiError(error);
    }
  },

  // Get statistics
  getStatistics: async () => {
    try {
      const response = await apiClient.get('/admin/enrollments/statistics');
      return response.data;
    } catch (error) {
      throw handleApiError(error);
    }
  },
};

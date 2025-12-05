import { useQuery } from '@tanstack/react-query';
import { authStorage } from '@/lib/api';

interface AttendanceRecord {
  id: number;
  date: string;
  status: 'present' | 'absent' | 'late' | 'excused';
  notes?: string;
  marked_at: string;
  course: {
    id: number;
    name: string;
    code: string;
  };
  marked_by: {
    id: number;
    name: string;
  };
}

interface CourseStatistics {
  course: {
    id: number;
    name: string;
    code: string;
  };
  statistics: {
    total_classes: number;
    present: number;
    late: number;
    absent: number;
    excused: number;
    attendance_percentage: number;
  };
}

interface AttendanceData {
  student: {
    id: number;
    name: string;
    student_id: string;
  };
  overall_statistics: {
    total_records: number;
    present: number;
    absent: number;
    late: number;
    excused: number;
    attendance_percentage: number;
  };
  course_statistics: CourseStatistics[];
  attendance_records: AttendanceRecord[];
}

export const useStudentAttendance = (studentId?: number) => {
  return useQuery({
    queryKey: ['student-attendance', studentId],
    queryFn: async (): Promise<AttendanceData> => {
      const token = authStorage.getToken();
      if (!token || !studentId) {
        throw new Error('Authentication required');
      }

      const response = await fetch(`http://127.0.0.1:8000/api/students/${studentId}/attendance`, {
        headers: {
          'Accept': 'application/json',
          'Authorization': `Bearer ${token}`,
        },
      });

      if (!response.ok) {
        throw new Error('Failed to fetch attendance data');
      }

      const result = await response.json();
      if (!result.success) {
        throw new Error(result.message || 'Failed to fetch attendance data');
      }

      return result.data;
    },
    enabled: !!authStorage.getToken() && !!studentId,
    retry: 1,
  });
};

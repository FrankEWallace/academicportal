import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Progress } from '@/components/ui/progress';
import { Calendar, Clock, User, CheckCircle, XCircle, Clock12, UserX } from 'lucide-react';
import { useStudentAttendance } from '@/hooks/use-student-attendance';
import { format } from 'date-fns';

interface AttendanceCardProps {
  studentId: number;
}

const getStatusIcon = (status: string) => {
  switch (status) {
    case 'present':
      return <CheckCircle className="h-4 w-4 text-green-600" />;
    case 'absent':
      return <XCircle className="h-4 w-4 text-red-600" />;
    case 'late':
      return <Clock12 className="h-4 w-4 text-yellow-600" />;
    case 'excused':
      return <UserX className="h-4 w-4 text-blue-600" />;
    default:
      return <User className="h-4 w-4 text-gray-600" />;
  }
};

const getStatusColor = (status: string) => {
  switch (status) {
    case 'present':
      return 'bg-green-100 text-green-800';
    case 'absent':
      return 'bg-red-100 text-red-800';
    case 'late':
      return 'bg-yellow-100 text-yellow-800';
    case 'excused':
      return 'bg-blue-100 text-blue-800';
    default:
      return 'bg-gray-100 text-gray-800';
  }
};

const getAttendanceColor = (percentage: number) => {
  if (percentage >= 90) return 'text-green-600';
  if (percentage >= 75) return 'text-yellow-600';
  return 'text-red-600';
};

export function AttendanceCard({ studentId }: AttendanceCardProps) {
  const { data: attendanceData, isLoading, error } = useStudentAttendance(studentId);

  if (isLoading) {
    return (
      <Card>
        <CardHeader>
          <CardTitle className="flex items-center gap-2">
            <Calendar className="h-5 w-5" />
            Attendance Overview
          </CardTitle>
        </CardHeader>
        <CardContent>
          <div className="animate-pulse space-y-4">
            <div className="h-4 bg-gray-200 rounded w-3/4"></div>
            <div className="h-4 bg-gray-200 rounded w-1/2"></div>
            <div className="h-16 bg-gray-200 rounded"></div>
          </div>
        </CardContent>
      </Card>
    );
  }

  if (error || !attendanceData) {
    return (
      <Card>
        <CardHeader>
          <CardTitle className="flex items-center gap-2">
            <Calendar className="h-5 w-5" />
            Attendance Overview
          </CardTitle>
        </CardHeader>
        <CardContent>
          <p className="text-sm text-gray-500">Unable to load attendance data</p>
        </CardContent>
      </Card>
    );
  }

  const { overall_statistics, course_statistics, attendance_records } = attendanceData;

  return (
    <div className="space-y-6">
      {/* Overall Attendance Card */}
      <Card>
        <CardHeader>
          <CardTitle className="flex items-center gap-2">
            <Calendar className="h-5 w-5" />
            Overall Attendance
          </CardTitle>
        </CardHeader>
        <CardContent>
          <div className="space-y-4">
            <div className="flex items-center justify-between">
              <span className="text-sm font-medium">Attendance Rate</span>
              <span className={`text-2xl font-bold ${getAttendanceColor(overall_statistics.attendance_percentage)}`}>
                {overall_statistics.attendance_percentage.toFixed(1)}%
              </span>
            </div>
            
            <Progress 
              value={overall_statistics.attendance_percentage} 
              className="w-full"
            />
            
            <div className="grid grid-cols-2 md:grid-cols-4 gap-4 mt-4">
              <div className="text-center">
                <div className="text-2xl font-bold text-green-600">{overall_statistics.present}</div>
                <div className="text-xs text-gray-500">Present</div>
              </div>
              <div className="text-center">
                <div className="text-2xl font-bold text-yellow-600">{overall_statistics.late}</div>
                <div className="text-xs text-gray-500">Late</div>
              </div>
              <div className="text-center">
                <div className="text-2xl font-bold text-red-600">{overall_statistics.absent}</div>
                <div className="text-xs text-gray-500">Absent</div>
              </div>
              <div className="text-center">
                <div className="text-2xl font-bold text-blue-600">{overall_statistics.excused}</div>
                <div className="text-xs text-gray-500">Excused</div>
              </div>
            </div>
            
            <div className="text-center text-sm text-gray-500 mt-2">
              Total Classes: {overall_statistics.total_records}
            </div>
          </div>
        </CardContent>
      </Card>

      {/* Course-wise Attendance */}
      <Card>
        <CardHeader>
          <CardTitle>Course Attendance</CardTitle>
        </CardHeader>
        <CardContent>
          <div className="space-y-4">
            {course_statistics.map((courseStats) => (
              <div key={courseStats.course.id} className="p-4 border rounded-lg">
                <div className="flex items-center justify-between mb-2">
                  <h4 className="font-medium">{courseStats.course.name}</h4>
                  <span className="text-xs text-gray-500">{courseStats.course.code}</span>
                </div>
                
                <div className="flex items-center justify-between mb-2">
                  <span className="text-sm text-gray-600">Attendance</span>
                  <span className={`font-semibold ${getAttendanceColor(courseStats.statistics.attendance_percentage)}`}>
                    {courseStats.statistics.attendance_percentage.toFixed(1)}%
                  </span>
                </div>
                
                <Progress 
                  value={courseStats.statistics.attendance_percentage} 
                  className="mb-3"
                />
                
                <div className="grid grid-cols-4 gap-2 text-xs">
                  <div className="text-center">
                    <span className="font-medium text-green-600">{courseStats.statistics.present}</span>
                    <div className="text-gray-500">Present</div>
                  </div>
                  <div className="text-center">
                    <span className="font-medium text-yellow-600">{courseStats.statistics.late}</span>
                    <div className="text-gray-500">Late</div>
                  </div>
                  <div className="text-center">
                    <span className="font-medium text-red-600">{courseStats.statistics.absent}</span>
                    <div className="text-gray-500">Absent</div>
                  </div>
                  <div className="text-center">
                    <span className="font-medium text-blue-600">{courseStats.statistics.excused}</span>
                    <div className="text-gray-500">Excused</div>
                  </div>
                </div>
              </div>
            ))}
          </div>
        </CardContent>
      </Card>

      {/* Recent Attendance Records */}
      <Card>
        <CardHeader>
          <CardTitle className="flex items-center gap-2">
            <Clock className="h-5 w-5" />
            Recent Attendance
          </CardTitle>
        </CardHeader>
        <CardContent>
          <div className="space-y-3">
            {attendance_records.slice(0, 10).map((record) => (
              <div key={record.id} className="flex items-center justify-between p-3 border rounded-lg">
                <div className="flex items-center gap-3">
                  {getStatusIcon(record.status)}
                  <div>
                    <div className="font-medium text-sm">
                      {record.course.name}
                    </div>
                    <div className="text-xs text-gray-500">
                      {format(new Date(record.date), 'MMM dd, yyyy')}
                    </div>
                  </div>
                </div>
                
                <div className="text-right">
                  <Badge 
                    className={`${getStatusColor(record.status)} border-0`}
                  >
                    {record.status.charAt(0).toUpperCase() + record.status.slice(1)}
                  </Badge>
                  {record.notes && (
                    <div className="text-xs text-gray-500 mt-1">
                      {record.notes}
                    </div>
                  )}
                </div>
              </div>
            ))}
            
            {attendance_records.length === 0 && (
              <div className="text-center py-8 text-gray-500">
                <Calendar className="h-12 w-12 mx-auto mb-2 opacity-50" />
                <p>No attendance records found</p>
              </div>
            )}
          </div>
        </CardContent>
      </Card>
    </div>
  );
}

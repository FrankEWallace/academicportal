import { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { DashboardLayout } from '@/components/DashboardLayout';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import { Calendar } from '@/components/ui/calendar';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { 
  CheckCircle, 
  XCircle, 
  Clock, 
  UserX, 
  CalendarIcon, 
  Users, 
  Save, 
  ArrowLeft,
  Loader2
} from 'lucide-react';
import { format } from 'date-fns';
import { cn } from '@/lib/utils';
import { authStorage } from '@/lib/api';
import { toast } from '@/hooks/use-toast';

interface Student {
  id: number;
  name: string;
  student_id: string;
  email: string;
}

interface Enrollment {
  id: number;
  student: Student;
  status: string;
}

interface Course {
  id: number;
  name: string;
  code: string;
  description: string;
}

interface AttendanceRecord {
  student_id: number;
  status: 'present' | 'absent' | 'late' | 'excused';
  notes?: string;
}

const statusOptions = [
  { value: 'present', label: 'Present', icon: CheckCircle, color: 'bg-green-100 text-green-800' },
  { value: 'absent', label: 'Absent', icon: XCircle, color: 'bg-red-100 text-red-800' },
  { value: 'late', label: 'Late', icon: Clock, color: 'bg-yellow-100 text-yellow-800' },
  { value: 'excused', label: 'Excused', icon: UserX, color: 'bg-blue-100 text-blue-800' },
];

export default function AttendanceList() {
  const { courseId } = useParams<{ courseId: string }>();
  const navigate = useNavigate();
  
  const [course, setCourse] = useState<Course | null>(null);
  const [enrollments, setEnrollments] = useState<Enrollment[]>([]);
  const [attendanceRecords, setAttendanceRecords] = useState<Record<number, AttendanceRecord>>({});
  const [selectedDate, setSelectedDate] = useState<Date>(new Date());
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);

  // Bulk operations for better UX
  const markAllPresent = () => {
    const newRecords: Record<number, AttendanceRecord> = {};
    enrollments.forEach((enrollment) => {
      newRecords[enrollment.student.id] = {
        student_id: enrollment.student.id,
        status: 'present',
        notes: '',
      };
    });
    setAttendanceRecords(newRecords);
    
    toast({
      title: "All Students Marked Present",
      description: `${enrollments.length} students marked as present`,
    });
  };

  const markAllAbsent = () => {
    const newRecords: Record<number, AttendanceRecord> = {};
    enrollments.forEach((enrollment) => {
      newRecords[enrollment.student.id] = {
        student_id: enrollment.student.id,
        status: 'absent',
        notes: '',
      };
    });
    setAttendanceRecords(newRecords);
    
    toast({
      title: "All Students Marked Absent",
      description: `${enrollments.length} students marked as absent`,
    });
  };

  const clearAllAttendance = () => {
    setAttendanceRecords({});
    toast({
      title: "Attendance Cleared",
      description: "All attendance records have been cleared",
    });
  };

  // Fetch course details and enrolled students
  useEffect(() => {
    if (!courseId) return;
    
    const fetchData = async () => {
      try {
        const token = authStorage.getToken();
        if (!token) return;

        // Fetch course details
        const courseResponse = await fetch(`http://127.0.0.1:8000/api/courses/${courseId}`, {
          headers: {
            'Accept': 'application/json',
            'Authorization': `Bearer ${token}`,
          },
        });

        if (courseResponse.ok) {
          const courseResult = await courseResponse.json();
          setCourse(courseResult.data);
        }

        // Fetch enrolled students
        const enrollmentsResponse = await fetch(`http://127.0.0.1:8000/api/courses/${courseId}/enrollments`, {
          headers: {
            'Accept': 'application/json',
            'Authorization': `Bearer ${token}`,
          },
        });

        if (enrollmentsResponse.ok) {
          const enrollmentsResult = await enrollmentsResponse.json();
          const activeEnrollments = enrollmentsResult.data.filter((e: Enrollment) => e.status === 'enrolled');
          setEnrollments(activeEnrollments);
        }

        // Fetch existing attendance for the selected date
        await fetchExistingAttendance();

      } catch (error) {
        console.error('Error fetching data:', error);
        toast({
          title: "Error",
          description: "Failed to load course data",
          variant: "destructive",
        });
      } finally {
        setLoading(false);
      }
    };

    fetchData();
  }, [courseId]);

  // Fetch existing attendance for selected date
  const fetchExistingAttendance = async () => {
    if (!courseId) return;

    try {
      const token = authStorage.getToken();
      if (!token) return;

      const response = await fetch(`http://127.0.0.1:8000/api/attendance/course/${courseId}?date=${format(selectedDate, 'yyyy-MM-dd')}`, {
        headers: {
          'Accept': 'application/json',
          'Authorization': `Bearer ${token}`,
        },
      });

      if (response.ok) {
        const result = await response.json();
        const existingRecords: Record<number, AttendanceRecord> = {};
        
        // Find attendance for the selected date
        const dateRecords = result.data?.attendance_by_date?.find(
          (dayData: any) => dayData.date === format(selectedDate, 'yyyy-MM-dd')
        );

        if (dateRecords?.records) {
          dateRecords.records.forEach((record: any) => {
            existingRecords[record.student.id] = {
              student_id: record.student.id,
              status: record.status,
              notes: record.notes,
            };
          });
        }

        setAttendanceRecords(existingRecords);
      }
    } catch (error) {
      console.error('Error fetching existing attendance:', error);
    }
  };

  // Update attendance for selected date
  useEffect(() => {
    if (selectedDate) {
      fetchExistingAttendance();
    }
  }, [selectedDate, courseId]);

  const updateAttendance = (studentId: number, field: keyof AttendanceRecord, value: any) => {
    setAttendanceRecords(prev => ({
      ...prev,
      [studentId]: {
        ...prev[studentId],
        student_id: studentId,
        [field]: value,
      }
    }));
  };

  const saveAttendance = async () => {
    if (!courseId) return;

    // Validate that we have at least one attendance record to save
    if (Object.keys(attendanceRecords).length === 0) {
      toast({
        title: "No Changes",
        description: "Please mark attendance for at least one student before saving",
        variant: "destructive",
      });
      return;
    }

    setSaving(true);
    let successCount = 0;
    let errorCount = 0;
    const errors: string[] = [];

    try {
      const token = authStorage.getToken();
      if (!token) {
        throw new Error('Authentication token not found');
      }

      const promises = Object.values(attendanceRecords).map(async (record) => {
        try {
          const response = await fetch('http://127.0.0.1:8000/api/attendance', {
            method: 'POST',
            headers: {
              'Accept': 'application/json',
              'Content-Type': 'application/json',
              'Authorization': `Bearer ${token}`,
            },
            body: JSON.stringify({
              student_id: record.student_id,
              course_id: parseInt(courseId),
              date: format(selectedDate, 'yyyy-MM-dd'),
              status: record.status,
              notes: record.notes || '',
            }),
          });

          if (!response.ok) {
            const errorData = await response.json();
            throw new Error(errorData.message || `Failed to save attendance for student ${record.student_id}`);
          }
          
          successCount++;
          return response.json();
        } catch (err) {
          errorCount++;
          errors.push(err instanceof Error ? err.message : 'Unknown error');
          throw err;
        }
      });

      await Promise.allSettled(promises);

      // Show appropriate message based on results
      if (successCount > 0 && errorCount === 0) {
        toast({
          title: "Success",
          description: `Attendance saved successfully for ${successCount} student${successCount > 1 ? 's' : ''}`,
        });
        
        // Refresh the attendance data
        await fetchExistingAttendance();
      } else if (successCount > 0 && errorCount > 0) {
        toast({
          title: "Partial Success",
          description: `Saved ${successCount} records, ${errorCount} failed. Please try again for failed records.`,
          variant: "destructive",
        });
      } else {
        toast({
          title: "Error",
          description: errors.length > 0 ? errors[0] : "Failed to save attendance records",
          variant: "destructive",
        });
      }

    } catch (error) {
      console.error('Error saving attendance:', error);
      toast({
        title: "Error",
        description: error instanceof Error ? error.message : "Failed to save attendance",
        variant: "destructive",
      });
    } finally {
      setSaving(false);
    }
  };

  if (loading) {
    return (
      <DashboardLayout>
        <div className="flex items-center justify-center h-64">
          <Loader2 className="h-8 w-8 animate-spin" />
          <span className="ml-2">Loading attendance...</span>
        </div>
      </DashboardLayout>
    );
  }

  return (
    <DashboardLayout>
      <div className="space-y-6">
        {/* Header */}
        <div className="flex items-center justify-between">
          <div className="flex items-center gap-4">
            <Button variant="ghost" size="sm" onClick={() => navigate(-1)}>
              <ArrowLeft className="h-4 w-4 mr-2" />
              Back
            </Button>
            <div>
              <h1 className="text-2xl font-bold">Mark Attendance</h1>
              <p className="text-muted-foreground">
                {course?.name} ({course?.code})
              </p>
            </div>
          </div>
          <div className="flex items-center gap-2">
            <Badge variant="outline" className="flex items-center gap-1">
              <Users className="h-3 w-3" />
              {enrollments.length} Students
            </Badge>
          </div>
        </div>

        {/* Date Selection */}
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <CalendarIcon className="h-5 w-5" />
              Select Date
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div className="flex items-center gap-4">
              <Popover>
                <PopoverTrigger asChild>
                  <Button
                    variant="outline"
                    className={cn(
                      "w-[240px] justify-start text-left font-normal",
                      !selectedDate && "text-muted-foreground"
                    )}
                  >
                    <CalendarIcon className="mr-2 h-4 w-4" />
                    {selectedDate ? format(selectedDate, "PPP") : <span>Pick a date</span>}
                  </Button>
                </PopoverTrigger>
                <PopoverContent className="w-auto p-0" align="start">
                  <Calendar
                    mode="single"
                    selected={selectedDate}
                    onSelect={(date) => date && setSelectedDate(date)}
                    initialFocus
                  />
                </PopoverContent>
              </Popover>
              <div className="flex items-center gap-2 ml-auto">
                <div className="flex items-center gap-1">
                  <Button
                    variant="outline"
                    size="sm"
                    onClick={markAllPresent}
                    disabled={saving || enrollments.length === 0}
                  >
                    <CheckCircle className="h-3 w-3 mr-1" />
                    All Present
                  </Button>
                  <Button
                    variant="outline"
                    size="sm"
                    onClick={markAllAbsent}
                    disabled={saving || enrollments.length === 0}
                  >
                    <XCircle className="h-3 w-3 mr-1" />
                    All Absent
                  </Button>
                  <Button
                    variant="ghost"
                    size="sm"
                    onClick={clearAllAttendance}
                    disabled={saving || Object.keys(attendanceRecords).length === 0}
                  >
                    Clear All
                  </Button>
                </div>
                <Button
                  onClick={saveAttendance}
                  disabled={saving || Object.keys(attendanceRecords).length === 0}
                >
                  {saving ? (
                    <>
                      <Loader2 className="h-4 w-4 mr-2 animate-spin" />
                      Saving...
                    </>
                  ) : (
                    <>
                      <Save className="h-4 w-4 mr-2" />
                      Save Attendance
                    </>
                  )}
                </Button>
              </div>
            </div>
          </CardContent>
        </Card>

        {/* Attendance List */}
        <Card>
          <CardHeader>
            <CardTitle>Student Attendance - {format(selectedDate, "MMMM dd, yyyy")}</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="space-y-4">
              {enrollments.map((enrollment) => {
                const student = enrollment.student;
                const record = attendanceRecords[student.id] || { student_id: student.id, status: 'present' as const };
                
                return (
                  <div key={student.id} className="border rounded-lg p-4 space-y-4">
                    <div className="flex items-center justify-between">
                      <div>
                        <h4 className="font-medium">{student.name}</h4>
                        <p className="text-sm text-muted-foreground">
                          {student.student_id} • {student.email}
                        </p>
                      </div>
                    </div>

                    <div className="grid gap-4 md:grid-cols-2">
                      {/* Status Selection */}
                      <div className="space-y-2">
                        <Label>Attendance Status</Label>
                        <Select
                          value={record.status}
                          onValueChange={(value: 'present' | 'absent' | 'late' | 'excused') => 
                            updateAttendance(student.id, 'status', value)
                          }
                        >
                          <SelectTrigger>
                            <SelectValue />
                          </SelectTrigger>
                          <SelectContent>
                            {statusOptions.map((option) => {
                              const IconComponent = option.icon;
                              return (
                                <SelectItem key={option.value} value={option.value}>
                                  <div className="flex items-center gap-2">
                                    <IconComponent className="h-4 w-4" />
                                    {option.label}
                                  </div>
                                </SelectItem>
                              );
                            })}
                          </SelectContent>
                        </Select>
                      </div>

                      {/* Notes */}
                      <div className="space-y-2">
                        <Label>Notes (Optional)</Label>
                        <Textarea
                          placeholder="Additional notes..."
                          value={record.notes || ''}
                          onChange={(e) => updateAttendance(student.id, 'notes', e.target.value)}
                          className="h-[38px] resize-none"
                        />
                      </div>
                    </div>

                    {/* Status Badge */}
                    <div className="flex justify-end">
                      {(() => {
                        const statusOption = statusOptions.find(opt => opt.value === record.status);
                        const IconComponent = statusOption?.icon || CheckCircle;
                        return (
                          <Badge className={statusOption?.color}>
                            <IconComponent className="h-3 w-3 mr-1" />
                            {statusOption?.label}
                          </Badge>
                        );
                      })()}
                    </div>
                  </div>
                );
              })}

              {enrollments.length === 0 && (
                <div className="text-center py-8 text-muted-foreground">
                  <Users className="h-12 w-12 mx-auto mb-2 opacity-50" />
                  <p>No students enrolled in this course</p>
                </div>
              )}
            </div>
          </CardContent>
        </Card>
      </div>
    </DashboardLayout>
  );
}

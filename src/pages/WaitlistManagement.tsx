import { useState, useEffect } from 'react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Clock, Users, TrendingUp, CheckCircle2, XCircle } from 'lucide-react';
import { waitlistApi, type CourseWaitlist } from '@/lib/academicApi';
import { useToast } from '@/hooks/use-toast';
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table';
import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
  AlertDialogTrigger,
} from '@/components/ui/alert-dialog';

interface WaitlistManagementProps {
  studentId?: number;
  courseId?: number;
  viewMode: 'student' | 'admin';
}

export default function WaitlistManagement({ studentId, courseId, viewMode }: WaitlistManagementProps) {
  const [waitlist, setWaitlist] = useState<CourseWaitlist[]>([]);
  const [courseInfo, setCourseInfo] = useState<any>(null);
  const [loading, setLoading] = useState(true);
  const { toast } = useToast();

  useEffect(() => {
    if (viewMode === 'student' && studentId) {
      fetchStudentWaitlist();
    } else if (courseId) {
      fetchCourseWaitlist();
    }
  }, [studentId, courseId, viewMode]);

  const fetchStudentWaitlist = async () => {
    if (!studentId) return;
    
    try {
      setLoading(true);
      const response = await waitlistApi.getStudentWaitlist(studentId);
      if (response.success) {
        setWaitlist(response.data);
      }
    } catch (error) {
      toast({
        title: 'Error',
        description: 'Failed to fetch waitlist',
        variant: 'destructive'
      });
    } finally {
      setLoading(false);
    }
  };

  const fetchCourseWaitlist = async () => {
    if (!courseId) return;
    
    try {
      setLoading(true);
      const response = await waitlistApi.getCourseWaitlist(courseId);
      if (response.success) {
        setWaitlist(response.data.waitlist);
        setCourseInfo(response.data.course);
      }
    } catch (error) {
      toast({
        title: 'Error',
        description: 'Failed to fetch course waitlist',
        variant: 'destructive'
      });
    } finally {
      setLoading(false);
    }
  };

  const handleLeaveWaitlist = async (waitlistId: number) => {
    try {
      const response = await waitlistApi.leave(waitlistId);
      if (response.success) {
        toast({
          title: 'Success',
          description: 'Removed from waitlist',
        });
        if (viewMode === 'student') {
          fetchStudentWaitlist();
        } else {
          fetchCourseWaitlist();
        }
      }
    } catch (error) {
      toast({
        title: 'Error',
        description: 'Failed to leave waitlist',
        variant: 'destructive'
      });
    }
  };

  const handleProcessWaitlist = async () => {
    if (!courseId) return;
    
    try {
      const response = await waitlistApi.process(courseId);
      if (response.success) {
        toast({
          title: 'Success',
          description: `${response.data.enrolled_count} student(s) enrolled from waitlist`,
        });
        fetchCourseWaitlist();
      }
    } catch (error) {
      toast({
        title: 'Error',
        description: 'Failed to process waitlist',
        variant: 'destructive'
      });
    }
  };

  if (loading) {
    return (
      <div className="flex items-center justify-center h-64">
        <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-primary"></div>
      </div>
    );
  }

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex justify-between items-center">
        <div>
          <h2 className="text-3xl font-bold tracking-tight">
            {viewMode === 'student' ? 'My Waitlists' : 'Course Waitlist'}
          </h2>
          <p className="text-muted-foreground">
            {viewMode === 'student' 
              ? 'Courses you are waiting to enroll in' 
              : courseInfo?.name || 'Manage course waitlist'}
          </p>
        </div>
        {viewMode === 'admin' && courseId && (
          <AlertDialog>
            <AlertDialogTrigger asChild>
              <Button>
                <CheckCircle2 className="h-4 w-4 mr-2" />
                Process Waitlist
              </Button>
            </AlertDialogTrigger>
            <AlertDialogContent>
              <AlertDialogHeader>
                <AlertDialogTitle>Process Waitlist?</AlertDialogTitle>
                <AlertDialogDescription>
                  This will automatically enroll students from the waitlist into available spots.
                  Students will be enrolled in order of their waitlist position.
                </AlertDialogDescription>
              </AlertDialogHeader>
              <AlertDialogFooter>
                <AlertDialogCancel>Cancel</AlertDialogCancel>
                <AlertDialogAction onClick={handleProcessWaitlist}>
                  Process Now
                </AlertDialogAction>
              </AlertDialogFooter>
            </AlertDialogContent>
          </AlertDialog>
        )}
      </div>

      {/* Statistics for admin view */}
      {viewMode === 'admin' && courseInfo && (
        <div className="grid gap-4 md:grid-cols-3">
          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Total Waitlist</CardTitle>
              <Users className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">{waitlist.length}</div>
              <p className="text-xs text-muted-foreground">Students waiting</p>
            </CardContent>
          </Card>

          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Average Wait Time</CardTitle>
              <Clock className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">
                {waitlist.length > 0 
                  ? Math.round(waitlist.reduce((acc, w) => {
                      const days = Math.floor((new Date().getTime() - new Date(w.added_at).getTime()) / (1000 * 60 * 60 * 24));
                      return acc + days;
                    }, 0) / waitlist.length)
                  : 0}
              </div>
              <p className="text-xs text-muted-foreground">Days average</p>
            </CardContent>
          </Card>

          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Course Capacity</CardTitle>
              <TrendingUp className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">
                {courseInfo.enrolled_students || 0}/{courseInfo.max_students || 0}
              </div>
              <p className="text-xs text-muted-foreground">
                {courseInfo.max_students - (courseInfo.enrolled_students || 0)} spots available
              </p>
            </CardContent>
          </Card>
        </div>
      )}

      {/* Waitlist Table */}
      <Card>
        <CardHeader>
          <CardTitle>Waitlist Queue</CardTitle>
          <CardDescription>
            {viewMode === 'student' 
              ? 'Your position in various course waitlists' 
              : 'Students waiting to enroll in this course'}
          </CardDescription>
        </CardHeader>
        <CardContent>
          {waitlist.length === 0 ? (
            <div className="text-center py-8 text-muted-foreground">
              {viewMode === 'student' 
                ? 'You are not on any waitlists' 
                : 'No students on waitlist'}
            </div>
          ) : (
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead className="w-20">Position</TableHead>
                  {viewMode === 'admin' && <TableHead>Student</TableHead>}
                  {viewMode === 'student' && <TableHead>Course</TableHead>}
                  <TableHead>Added On</TableHead>
                  <TableHead>Wait Time</TableHead>
                  <TableHead>Semester</TableHead>
                  <TableHead>Status</TableHead>
                  <TableHead className="text-right">Actions</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {waitlist.map((entry) => {
                  const daysWaiting = Math.floor(
                    (new Date().getTime() - new Date(entry.added_at).getTime()) / (1000 * 60 * 60 * 24)
                  );
                  
                  return (
                    <TableRow key={entry.id}>
                      <TableCell>
                        <Badge variant={entry.position <= 3 ? 'default' : 'secondary'}>
                          #{entry.position}
                        </Badge>
                      </TableCell>
                      {viewMode === 'admin' && entry.student && (
                        <TableCell className="font-medium">
                          {entry.student.name}
                          <div className="text-xs text-muted-foreground">{entry.student.email}</div>
                        </TableCell>
                      )}
                      {viewMode === 'student' && entry.course && (
                        <TableCell className="font-medium">
                          {entry.course.name}
                          <div className="text-xs text-muted-foreground">{entry.course.course_code}</div>
                        </TableCell>
                      )}
                      <TableCell>
                        {new Date(entry.added_at).toLocaleDateString()}
                      </TableCell>
                      <TableCell>
                        <span className="flex items-center gap-1">
                          <Clock className="h-3 w-3" />
                          {daysWaiting} day{daysWaiting !== 1 ? 's' : ''}
                        </span>
                      </TableCell>
                      <TableCell>Semester {entry.semester}</TableCell>
                      <TableCell>
                        <Badge variant={
                          entry.status === 'waiting' ? 'secondary' :
                          entry.status === 'enrolled' ? 'default' :
                          'outline'
                        }>
                          {entry.status}
                        </Badge>
                      </TableCell>
                      <TableCell className="text-right">
                        {entry.status === 'waiting' && (
                          <AlertDialog>
                            <AlertDialogTrigger asChild>
                              <Button variant="ghost" size="sm">
                                <XCircle className="h-4 w-4 mr-1" />
                                Leave
                              </Button>
                            </AlertDialogTrigger>
                            <AlertDialogContent>
                              <AlertDialogHeader>
                                <AlertDialogTitle>Leave Waitlist?</AlertDialogTitle>
                                <AlertDialogDescription>
                                  Are you sure you want to leave this waitlist? You will lose your current position.
                                </AlertDialogDescription>
                              </AlertDialogHeader>
                              <AlertDialogFooter>
                                <AlertDialogCancel>Cancel</AlertDialogCancel>
                                <AlertDialogAction onClick={() => handleLeaveWaitlist(entry.id)}>
                                  Leave Waitlist
                                </AlertDialogAction>
                              </AlertDialogFooter>
                            </AlertDialogContent>
                          </AlertDialog>
                        )}
                      </TableCell>
                    </TableRow>
                  );
                })}
              </TableBody>
            </Table>
          )}
        </CardContent>
      </Card>

      {/* Tips Card */}
      <Card className="border-blue-200 bg-blue-50">
        <CardHeader>
          <CardTitle className="text-blue-800">Waitlist Tips</CardTitle>
        </CardHeader>
        <CardContent className="text-sm text-blue-700 space-y-2">
          <p>• You will be automatically enrolled when a spot becomes available</p>
          <p>• Your position in the queue is based on when you joined the waitlist</p>
          <p>• You'll receive an email notification when enrolled from the waitlist</p>
          <p>• You can leave the waitlist at any time, but you'll lose your position</p>
        </CardContent>
      </Card>
    </div>
  );
}

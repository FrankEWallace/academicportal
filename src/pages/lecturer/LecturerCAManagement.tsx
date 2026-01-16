import { useState, useEffect } from 'react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { CheckCircle, XCircle, Lock, Unlock, Upload, Download, BarChart3 } from 'lucide-react';
import { lecturerCAApi } from '@/lib/api/lecturerApi';
import { useToast } from '@/hooks/use-toast';

interface Course {
  id: number;
  code: string;
  title: string;
  semester: number;
  academic_year: string;
  total_students: number;
  ca_submitted: number;
  ca_locked: boolean;
}

interface Student {
  id: number;
  student_id: string;
  name: string;
  matric_number: string;
  current_score: number | null;
  max_score: number;
  submitted_at: string | null;
  locked: boolean;
}

interface CAStatistics {
  total_courses: number;
  total_submissions: number;
  pending_approvals: number;
  locked_courses: number;
}

export default function LecturerCAManagement() {
  const [courses, setCourses] = useState<Course[]>([]);
  const [selectedCourse, setSelectedCourse] = useState<Course | null>(null);
  const [students, setStudents] = useState<Student[]>([]);
  const [statistics, setStatistics] = useState<CAStatistics | null>(null);
  const [loading, setLoading] = useState(false);
  const [message, setMessage] = useState('');
  const { toast } = useToast();

  useEffect(() => {
    fetchCourses();
    fetchStatistics();
  }, []);

  const fetchCourses = async () => {
    setLoading(true);
    try {
      const response = await lecturerCAApi.getCourses();
      if (response.success) {
        setCourses(response.data);
      }
    } catch (error: any) {
      toast({
        title: 'Error',
        description: error.message || 'Failed to fetch courses',
        variant: 'destructive',
      });
    } finally {
      setLoading(false);
    }
  };

  const fetchStatistics = async () => {
    try {
      const response = await lecturerCAApi.getStatistics();
      if (response.success) {
        setStatistics(response.data);
      }
    } catch (error: any) {
      console.error('Failed to fetch statistics:', error);
    }
  };

  const fetchStudents = async (courseId: number) => {
    setLoading(true);
    try {
      const response = await lecturerCAApi.getCourseStudents(courseId);
      if (response.success) {
        setStudents(response.data);
      }
    } catch (error: any) {
      toast({
        title: 'Error',
        description: error.message || 'Failed to fetch students',
        variant: 'destructive',
      });
    } finally {
      setLoading(false);
    }
  };

  const handleUpdateScore = async (studentId: number, score: number) => {
    try {
      const response = await lecturerCAApi.updateScore(studentId, { score });
      if (response.success) {
        toast({
          title: 'Success',
          description: 'Score updated successfully',
        });
        // Refresh students list
        if (selectedCourse) {
          fetchStudents(selectedCourse.id);
        }
      }
    } catch (error: any) {
      toast({
        title: 'Error',
        description: error.message || 'Failed to update score',
        variant: 'destructive',
      });
    }
  };

  const handleLockCourse = async (courseId: number) => {
    try {
      const response = await lecturerCAApi.lockCourse(courseId);
      if (response.success) {
        toast({
          title: 'Success',
          description: 'Course locked successfully',
        });
        fetchCourses();
      }
    } catch (error: any) {
      toast({
        title: 'Error',
        description: error.message || 'Failed to lock course',
        variant: 'destructive',
      });
    }
  };

  const handleSubmitForApproval = async (courseId: number) => {
    try {
      const response = await lecturerCAApi.submitForApproval(courseId);
      if (response.success) {
        toast({
          title: 'Success',
          description: 'Submitted for approval',
        });
      }
    } catch (error: any) {
      toast({
        title: 'Error',
        description: error.message || 'Failed to submit',
        variant: 'destructive',
      });
    }
  };

  return (
    <div className="space-y-6 p-6">
      <div className="flex justify-between items-center">
        <div>
          <h1 className="text-3xl font-bold">CA Score Management</h1>
          <p className="text-muted-foreground">Manage continuous assessment scores for your courses</p>
        </div>
        <Button>
          <Download className="mr-2 h-4 w-4" />
          Export Report
        </Button>
      </div>

      {message && (
        <Alert>
          <CheckCircle className="h-4 w-4" />
          <AlertDescription>{message}</AlertDescription>
        </Alert>
      )}

      {/* Statistics Cards */}
      {statistics && (
        <div className="grid gap-4 md:grid-cols-4">
          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Total Courses</CardTitle>
              <BarChart3 className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">{statistics.total_courses}</div>
            </CardContent>
          </Card>
          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Total Submissions</CardTitle>
              <CheckCircle className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">{statistics.total_submissions}</div>
            </CardContent>
          </Card>
          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Pending Approvals</CardTitle>
              <Upload className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">{statistics.pending_approvals}</div>
            </CardContent>
          </Card>
          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Locked Courses</CardTitle>
              <Lock className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">{statistics.locked_courses}</div>
            </CardContent>
          </Card>
        </div>
      )}

      <Tabs defaultValue="courses">
        <TabsList>
          <TabsTrigger value="courses">My Courses</TabsTrigger>
          <TabsTrigger value="scores">Score Entry</TabsTrigger>
        </TabsList>

        <TabsContent value="courses" className="space-y-4">
          <Card>
            <CardHeader>
              <CardTitle>Assigned Courses</CardTitle>
              <CardDescription>Select a course to manage CA scores</CardDescription>
            </CardHeader>
            <CardContent>
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead>Course Code</TableHead>
                    <TableHead>Course Title</TableHead>
                    <TableHead>Students</TableHead>
                    <TableHead>Submissions</TableHead>
                    <TableHead>Status</TableHead>
                    <TableHead>Actions</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {courses.map((course) => (
                    <TableRow key={course.id}>
                      <TableCell className="font-medium">{course.code}</TableCell>
                      <TableCell>{course.title}</TableCell>
                      <TableCell>{course.total_students}</TableCell>
                      <TableCell>
                        {course.ca_submitted}/{course.total_students}
                      </TableCell>
                      <TableCell>
                        {course.ca_locked ? (
                          <Badge variant="secondary">
                            <Lock className="mr-1 h-3 w-3" />
                            Locked
                          </Badge>
                        ) : (
                          <Badge variant="default">
                            <Unlock className="mr-1 h-3 w-3" />
                            Open
                          </Badge>
                        )}
                      </TableCell>
                      <TableCell>
                        <div className="flex gap-2">
                          <Button
                            size="sm"
                            variant="outline"
                            onClick={() => {
                              setSelectedCourse(course);
                              fetchStudents(course.id);
                            }}
                          >
                            Manage Scores
                          </Button>
                          {!course.ca_locked && (
                            <Button
                              size="sm"
                              variant="destructive"
                              onClick={() => handleLockCourse(course.id)}
                            >
                              <Lock className="mr-1 h-3 w-3" />
                              Lock
                            </Button>
                          )}
                        </div>
                      </TableCell>
                    </TableRow>
                  ))}
                </TableBody>
              </Table>
            </CardContent>
          </Card>
        </TabsContent>

        <TabsContent value="scores" className="space-y-4">
          {selectedCourse ? (
            <Card>
              <CardHeader>
                <CardTitle>
                  {selectedCourse.code} - {selectedCourse.title}
                </CardTitle>
                <CardDescription>Enter or update CA scores (Max: 30 marks)</CardDescription>
              </CardHeader>
              <CardContent>
                <div className="space-y-4">
                  <div className="flex justify-between items-center">
                    <div>
                      <p className="text-sm text-muted-foreground">
                        Total Students: {students.length}
                      </p>
                    </div>
                    <div className="flex gap-2">
                      <Button variant="outline">
                        <Upload className="mr-2 h-4 w-4" />
                        Bulk Upload
                      </Button>
                      <Button onClick={() => handleSubmitForApproval(selectedCourse.id)}>
                        Submit for Approval
                      </Button>
                    </div>
                  </div>

                  <Table>
                    <TableHeader>
                      <TableRow>
                        <TableHead>Matric Number</TableHead>
                        <TableHead>Student Name</TableHead>
                        <TableHead>Current Score</TableHead>
                        <TableHead>Max Score</TableHead>
                        <TableHead>Action</TableHead>
                      </TableRow>
                    </TableHeader>
                    <TableBody>
                      {students.map((student) => (
                        <TableRow key={student.id}>
                          <TableCell className="font-medium">{student.matric_number}</TableCell>
                          <TableCell>{student.name}</TableCell>
                          <TableCell>
                            <Input
                              type="number"
                              min="0"
                              max={student.max_score}
                              defaultValue={student.current_score || ''}
                              className="w-20"
                              disabled={student.locked}
                              onBlur={(e) => {
                                const score = parseFloat(e.target.value);
                                if (score >= 0 && score <= student.max_score) {
                                  handleUpdateScore(student.id, score);
                                }
                              }}
                            />
                          </TableCell>
                          <TableCell>{student.max_score}</TableCell>
                          <TableCell>
                            {student.submitted_at ? (
                              <Badge variant="outline">
                                <CheckCircle className="mr-1 h-3 w-3" />
                                Saved
                              </Badge>
                            ) : (
                              <Badge variant="secondary">
                                <XCircle className="mr-1 h-3 w-3" />
                                Pending
                              </Badge>
                            )}
                          </TableCell>
                        </TableRow>
                      ))}
                    </TableBody>
                  </Table>
                </div>
              </CardContent>
            </Card>
          ) : (
            <Card>
              <CardContent className="flex items-center justify-center py-12">
                <p className="text-muted-foreground">Select a course from the "My Courses" tab to manage scores</p>
              </CardContent>
            </Card>
          )}
        </TabsContent>
      </Tabs>
    </div>
  );
}

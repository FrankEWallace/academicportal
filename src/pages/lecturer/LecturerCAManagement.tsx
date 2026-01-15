import { useState, useEffect } from 'react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { CheckCircle, XCircle, Lock, Unlock, Upload, Download, BarChart3 } from 'lucide-react';

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

  useEffect(() => {
    fetchCourses();
    fetchStatistics();
  }, []);

  const fetchCourses = async () => {
    setLoading(true);
    try {
      // TODO: Replace with actual API call
      // const response = await fetch('/api/lecturer/ca/courses');
      // const data = await response.json();
      
      // Mock data
      setCourses([
        {
          id: 1,
          code: 'CS301',
          title: 'Data Structures',
          semester: 1,
          academic_year: '2025/2026',
          total_students: 45,
          ca_submitted: 38,
          ca_locked: false,
        },
        {
          id: 2,
          code: 'CS302',
          title: 'Database Systems',
          semester: 1,
          academic_year: '2025/2026',
          total_students: 52,
          ca_submitted: 52,
          ca_locked: true,
        },
      ]);
    } catch (error) {
      console.error('Failed to fetch courses:', error);
    } finally {
      setLoading(false);
    }
  };

  const fetchStatistics = async () => {
    try {
      // TODO: Replace with actual API call
      setStatistics({
        total_courses: 4,
        total_submissions: 156,
        pending_approvals: 2,
        locked_courses: 1,
      });
    } catch (error) {
      console.error('Failed to fetch statistics:', error);
    }
  };

  const fetchStudents = async (courseId: number) => {
    setLoading(true);
    try {
      // TODO: Replace with actual API call
      // const response = await fetch(`/api/lecturer/ca/courses/${courseId}/students`);
      // const data = await response.json();
      
      // Mock data
      setStudents([
        {
          id: 1,
          student_id: '1001',
          name: 'John Doe',
          matric_number: 'CS/2023/001',
          current_score: 18,
          max_score: 30,
          submitted_at: '2026-01-10',
          locked: false,
        },
        {
          id: 2,
          student_id: '1002',
          name: 'Jane Smith',
          matric_number: 'CS/2023/002',
          current_score: null,
          max_score: 30,
          submitted_at: null,
          locked: false,
        },
      ]);
    } catch (error) {
      console.error('Failed to fetch students:', error);
    } finally {
      setLoading(false);
    }
  };

  const handleUpdateScore = async (studentId: number, score: number) => {
    try {
      // TODO: Replace with actual API call
      // await fetch(`/api/lecturer/ca/scores/${studentId}`, {
      //   method: 'PUT',
      //   body: JSON.stringify({ score }),
      // });
      
      setMessage('Score updated successfully');
      setTimeout(() => setMessage(''), 3000);
    } catch (error) {
      console.error('Failed to update score:', error);
    }
  };

  const handleLockCourse = async (courseId: number) => {
    try {
      // TODO: Replace with actual API call
      // await fetch(`/api/lecturer/ca/courses/${courseId}/lock`, {
      //   method: 'POST',
      // });
      
      setMessage('Course locked successfully');
      fetchCourses();
    } catch (error) {
      console.error('Failed to lock course:', error);
    }
  };

  const handleSubmitForApproval = async (courseId: number) => {
    try {
      // TODO: Replace with actual API call
      // await fetch(`/api/lecturer/ca/courses/${courseId}/submit-approval`, {
      //   method: 'POST',
      // });
      
      setMessage('Submitted for approval');
    } catch (error) {
      console.error('Failed to submit:', error);
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

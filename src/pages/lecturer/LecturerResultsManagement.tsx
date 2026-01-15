import { useState, useEffect } from 'react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { CheckCircle, XCircle, Lock, Unlock, Upload, Download, FileText } from 'lucide-react';

interface Course {
  id: number;
  code: string;
  title: string;
  semester: number;
  academic_year: string;
  total_students: number;
  results_submitted: number;
  results_locked: boolean;
  moderation_status: 'pending' | 'approved' | 'rejected';
}

interface StudentResult {
  id: number;
  student_id: string;
  name: string;
  matric_number: string;
  ca_score: number;
  exam_score: number | null;
  total_score: number | null;
  grade: string | null;
  locked: boolean;
}

interface ResultStatistics {
  total_courses: number;
  completed_courses: number;
  pending_moderation: number;
  approved_courses: number;
}

export default function LecturerResultsManagement() {
  const [courses, setCourses] = useState<Course[]>([]);
  const [selectedCourse, setSelectedCourse] = useState<Course | null>(null);
  const [students, setStudents] = useState<StudentResult[]>([]);
  const [statistics, setStatistics] = useState<ResultStatistics | null>(null);
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
      setCourses([
        {
          id: 1,
          code: 'CS301',
          title: 'Data Structures',
          semester: 1,
          academic_year: '2025/2026',
          total_students: 45,
          results_submitted: 40,
          results_locked: false,
          moderation_status: 'pending',
        },
        {
          id: 2,
          code: 'CS302',
          title: 'Database Systems',
          semester: 1,
          academic_year: '2025/2026',
          total_students: 52,
          results_submitted: 52,
          results_locked: true,
          moderation_status: 'approved',
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
      setStatistics({
        total_courses: 4,
        completed_courses: 2,
        pending_moderation: 1,
        approved_courses: 1,
      });
    } catch (error) {
      console.error('Failed to fetch statistics:', error);
    }
  };

  const fetchStudentResults = async (courseId: number) => {
    setLoading(true);
    try {
      // TODO: Replace with actual API call
      setStudents([
        {
          id: 1,
          student_id: '1001',
          name: 'John Doe',
          matric_number: 'CS/2023/001',
          ca_score: 25,
          exam_score: 58,
          total_score: 83,
          grade: 'A',
          locked: false,
        },
        {
          id: 2,
          student_id: '1002',
          name: 'Jane Smith',
          matric_number: 'CS/2023/002',
          ca_score: 22,
          exam_score: null,
          total_score: null,
          grade: null,
          locked: false,
        },
      ]);
    } catch (error) {
      console.error('Failed to fetch student results:', error);
    } finally {
      setLoading(false);
    }
  };

  const calculateGrade = (total: number): string => {
    if (total >= 70) return 'A';
    if (total >= 60) return 'B';
    if (total >= 50) return 'C';
    if (total >= 45) return 'D';
    if (total >= 40) return 'E';
    return 'F';
  };

  const handleUpdateExamScore = async (studentId: number, examScore: number) => {
    try {
      // TODO: Replace with actual API call
      setMessage('Exam score updated successfully');
      setTimeout(() => setMessage(''), 3000);
    } catch (error) {
      console.error('Failed to update exam score:', error);
    }
  };

  const handleLockResults = async (courseId: number) => {
    try {
      // TODO: Replace with actual API call
      setMessage('Results locked successfully');
      fetchCourses();
    } catch (error) {
      console.error('Failed to lock results:', error);
    }
  };

  const handleSubmitForModeration = async (courseId: number) => {
    try {
      // TODO: Replace with actual API call
      setMessage('Results submitted for moderation');
    } catch (error) {
      console.error('Failed to submit:', error);
    }
  };

  const getModerationStatusBadge = (status: string) => {
    switch (status) {
      case 'approved':
        return <Badge variant="default">Approved</Badge>;
      case 'rejected':
        return <Badge variant="destructive">Rejected</Badge>;
      default:
        return <Badge variant="secondary">Pending</Badge>;
    }
  };

  return (
    <div className="space-y-6 p-6">
      <div className="flex justify-between items-center">
        <div>
          <h1 className="text-3xl font-bold">Exam Results Management</h1>
          <p className="text-muted-foreground">Manage exam results and final grades for your courses</p>
        </div>
        <Button>
          <Download className="mr-2 h-4 w-4" />
          Export Results
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
              <FileText className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">{statistics.total_courses}</div>
            </CardContent>
          </Card>
          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Completed</CardTitle>
              <CheckCircle className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">{statistics.completed_courses}</div>
            </CardContent>
          </Card>
          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Pending Moderation</CardTitle>
              <Upload className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">{statistics.pending_moderation}</div>
            </CardContent>
          </Card>
          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Approved</CardTitle>
              <CheckCircle className="h-4 w-4 text-green-600" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">{statistics.approved_courses}</div>
            </CardContent>
          </Card>
        </div>
      )}

      <Tabs defaultValue="courses">
        <TabsList>
          <TabsTrigger value="courses">My Courses</TabsTrigger>
          <TabsTrigger value="results">Results Entry</TabsTrigger>
        </TabsList>

        <TabsContent value="courses" className="space-y-4">
          <Card>
            <CardHeader>
              <CardTitle>Course Results Status</CardTitle>
              <CardDescription>View and manage exam results for your courses</CardDescription>
            </CardHeader>
            <CardContent>
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead>Course Code</TableHead>
                    <TableHead>Course Title</TableHead>
                    <TableHead>Students</TableHead>
                    <TableHead>Results</TableHead>
                    <TableHead>Status</TableHead>
                    <TableHead>Moderation</TableHead>
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
                        {course.results_submitted}/{course.total_students}
                      </TableCell>
                      <TableCell>
                        {course.results_locked ? (
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
                      <TableCell>{getModerationStatusBadge(course.moderation_status)}</TableCell>
                      <TableCell>
                        <div className="flex gap-2">
                          <Button
                            size="sm"
                            variant="outline"
                            onClick={() => {
                              setSelectedCourse(course);
                              fetchStudentResults(course.id);
                            }}
                          >
                            Manage Results
                          </Button>
                          {!course.results_locked && (
                            <Button
                              size="sm"
                              variant="destructive"
                              onClick={() => handleLockResults(course.id)}
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

        <TabsContent value="results" className="space-y-4">
          {selectedCourse ? (
            <Card>
              <CardHeader>
                <CardTitle>
                  {selectedCourse.code} - {selectedCourse.title}
                </CardTitle>
                <CardDescription>Enter exam scores (Max: 70 marks) and review final grades</CardDescription>
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
                      <Button onClick={() => handleSubmitForModeration(selectedCourse.id)}>
                        Submit for Moderation
                      </Button>
                    </div>
                  </div>

                  <Table>
                    <TableHeader>
                      <TableRow>
                        <TableHead>Matric Number</TableHead>
                        <TableHead>Student Name</TableHead>
                        <TableHead>CA Score (30)</TableHead>
                        <TableHead>Exam Score (70)</TableHead>
                        <TableHead>Total (100)</TableHead>
                        <TableHead>Grade</TableHead>
                      </TableRow>
                    </TableHeader>
                    <TableBody>
                      {students.map((student) => {
                        const total = student.exam_score ? student.ca_score + student.exam_score : null;
                        const grade = total ? calculateGrade(total) : null;
                        
                        return (
                          <TableRow key={student.id}>
                            <TableCell className="font-medium">{student.matric_number}</TableCell>
                            <TableCell>{student.name}</TableCell>
                            <TableCell>
                              <Badge variant="outline">{student.ca_score}</Badge>
                            </TableCell>
                            <TableCell>
                              <Input
                                type="number"
                                min="0"
                                max="70"
                                defaultValue={student.exam_score || ''}
                                className="w-20"
                                disabled={student.locked}
                                onBlur={(e) => {
                                  const score = parseFloat(e.target.value);
                                  if (score >= 0 && score <= 70) {
                                    handleUpdateExamScore(student.id, score);
                                  }
                                }}
                              />
                            </TableCell>
                            <TableCell>
                              <span className="font-medium">{total || '-'}</span>
                            </TableCell>
                            <TableCell>
                              {grade ? (
                                <Badge
                                  variant={grade === 'F' ? 'destructive' : 'default'}
                                >
                                  {grade}
                                </Badge>
                              ) : (
                                '-'
                              )}
                            </TableCell>
                          </TableRow>
                        );
                      })}
                    </TableBody>
                  </Table>
                </div>
              </CardContent>
            </Card>
          ) : (
            <Card>
              <CardContent className="flex items-center justify-center py-12">
                <p className="text-muted-foreground">
                  Select a course from the "My Courses" tab to manage results
                </p>
              </CardContent>
            </Card>
          )}
        </TabsContent>
      </Tabs>
    </div>
  );
}

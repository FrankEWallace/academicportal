import { useParams } from "react-router-dom";
import { DashboardLayout } from "@/components/DashboardLayout";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/components/ui/table";
import { 
  User, 
  BookOpen, 
  Calendar,
  GraduationCap,
  Mail,
  Phone,
  MapPin,
  Loader2,
  ArrowLeft,
  UserMinus
} from "lucide-react";
import { 
  useUser, 
  useStudentCourses, 
  useUnenrollStudent 
} from "@/hooks/useApi";
import { useNavigate } from "react-router-dom";

const StudentDetail = () => {
  const { id } = useParams<{ id: string }>();
  const navigate = useNavigate();
  const studentId = parseInt(id || '0');
  
  const { data: userResponse, isLoading: userLoading } = useUser(studentId);
  const { data: enrollments, isLoading: enrollmentsLoading } = useStudentCourses(studentId);
  const unenrollStudentMutation = useUnenrollStudent();

  const user = userResponse?.data;

  const handleUnenrollFromCourse = (enrollmentId: number, courseName: string) => {
    if (window.confirm(`Are you sure you want to unenroll this student from ${courseName}?`)) {
      unenrollStudentMutation.mutate(enrollmentId);
    }
  };

  if (userLoading) {
    return (
      <DashboardLayout>
        <div className="flex items-center justify-center h-64">
          <Loader2 className="h-8 w-8 animate-spin" />
          <span className="ml-2">Loading student details...</span>
        </div>
      </DashboardLayout>
    );
  }

  if (!user || user.role !== 'student') {
    return (
      <DashboardLayout>
        <div className="text-center py-12">
          <h2 className="text-2xl font-bold mb-2">Student Not Found</h2>
          <p className="text-muted-foreground">The requested student could not be found.</p>
          <Button onClick={() => navigate(-1)} className="mt-4">
            <ArrowLeft className="w-4 h-4 mr-2" />
            Go Back
          </Button>
        </div>
      </DashboardLayout>
    );
  }

  const totalCredits = enrollments?.reduce((total, enrollment) => {
    return total + (enrollment.course?.credits || 0);
  }, 0) || 0;

  const activeEnrollments = enrollments?.filter(e => e.status === 'enrolled') || [];
  const completedEnrollments = enrollments?.filter(e => e.status === 'completed') || [];

  return (
    <DashboardLayout>
      <div className="space-y-6">
        {/* Header */}
        <div className="flex items-center justify-between">
          <div className="flex items-center gap-4">
            <Button variant="outline" size="sm" onClick={() => navigate(-1)}>
              <ArrowLeft className="w-4 h-4 mr-2" />
              Back
            </Button>
            <div>
              <h1 className="text-3xl font-bold">{user.name}</h1>
              <p className="text-muted-foreground">Student Profile</p>
            </div>
          </div>
          <Badge variant="outline" className="text-lg px-4 py-2">
            <GraduationCap className="w-4 h-4 mr-2" />
            Student
          </Badge>
        </div>

        <div className="grid gap-6 md:grid-cols-3">
          {/* Student Information */}
          <div className="md:col-span-2 space-y-6">
            <Card>
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <User className="w-5 h-5" />
                  Student Information
                </CardTitle>
              </CardHeader>
              <CardContent className="space-y-4">
                <div className="grid gap-4 md:grid-cols-2">
                  <div>
                    <h4 className="font-semibold mb-2 flex items-center gap-2">
                      <Mail className="w-4 h-4" />
                      Email
                    </h4>
                    <p className="text-muted-foreground">{user.email}</p>
                  </div>
                  <div>
                    <h4 className="font-semibold mb-2">Student ID</h4>
                    <p className="text-muted-foreground">STU{user.id.toString().padStart(3, '0')}</p>
                  </div>
                  <div>
                    <h4 className="font-semibold mb-2 flex items-center gap-2">
                      <Calendar className="w-4 h-4" />
                      Joined Date
                    </h4>
                    <p className="text-muted-foreground">
                      {new Date(user.created_at).toLocaleDateString()}
                    </p>
                  </div>
                  <div>
                    <h4 className="font-semibold mb-2">Status</h4>
                    <Badge variant="default">Active</Badge>
                  </div>
                </div>
              </CardContent>
            </Card>

            {/* Current Courses */}
            <Card>
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <BookOpen className="w-5 h-5" />
                  Current Enrollments ({activeEnrollments.length})
                </CardTitle>
                <CardDescription>
                  Courses the student is currently enrolled in
                </CardDescription>
              </CardHeader>
              <CardContent>
                {enrollmentsLoading ? (
                  <div className="flex items-center justify-center py-8">
                    <Loader2 className="h-6 w-6 animate-spin" />
                    <span className="ml-2">Loading enrollments...</span>
                  </div>
                ) : activeEnrollments.length > 0 ? (
                  <Table>
                    <TableHeader>
                      <TableRow>
                        <TableHead>Course</TableHead>
                        <TableHead>Code</TableHead>
                        <TableHead>Credits</TableHead>
                        <TableHead>Enrolled Date</TableHead>
                        <TableHead>Status</TableHead>
                        <TableHead className="text-right">Actions</TableHead>
                      </TableRow>
                    </TableHeader>
                    <TableBody>
                      {activeEnrollments.map((enrollment) => (
                        <TableRow key={enrollment.id}>
                          <TableCell>
                            <div>
                              <div className="font-medium">{enrollment.course?.name}</div>
                              <div className="text-sm text-muted-foreground">
                                {enrollment.course?.teacher?.user?.name || 'No teacher assigned'}
                              </div>
                            </div>
                          </TableCell>
                          <TableCell>{enrollment.course?.code}</TableCell>
                          <TableCell>
                            <Badge variant="outline">
                              {enrollment.course?.credits} Credits
                            </Badge>
                          </TableCell>
                          <TableCell>
                            {new Date(enrollment.enrollment_date).toLocaleDateString()}
                          </TableCell>
                          <TableCell>
                            <Badge variant="default">{enrollment.status}</Badge>
                          </TableCell>
                          <TableCell className="text-right">
                            <Button
                              variant="outline"
                              size="sm"
                              onClick={() => handleUnenrollFromCourse(
                                enrollment.id, 
                                enrollment.course?.name || 'course'
                              )}
                              disabled={unenrollStudentMutation.isPending}
                            >
                              {unenrollStudentMutation.isPending ? (
                                <Loader2 className="w-4 h-4 animate-spin" />
                              ) : (
                                <UserMinus className="w-4 h-4" />
                              )}
                            </Button>
                          </TableCell>
                        </TableRow>
                      ))}
                    </TableBody>
                  </Table>
                ) : (
                  <div className="text-center py-8">
                    <BookOpen className="w-12 h-12 mx-auto text-muted-foreground mb-4" />
                    <h3 className="text-lg font-semibold mb-2">No Current Enrollments</h3>
                    <p className="text-muted-foreground">
                      This student is not currently enrolled in any courses.
                    </p>
                  </div>
                )}
              </CardContent>
            </Card>

            {/* Completed Courses */}
            {completedEnrollments.length > 0 && (
              <Card>
                <CardHeader>
                  <CardTitle className="flex items-center gap-2">
                    <GraduationCap className="w-5 h-5" />
                    Completed Courses ({completedEnrollments.length})
                  </CardTitle>
                  <CardDescription>
                    Courses the student has completed
                  </CardDescription>
                </CardHeader>
                <CardContent>
                  <Table>
                    <TableHeader>
                      <TableRow>
                        <TableHead>Course</TableHead>
                        <TableHead>Code</TableHead>
                        <TableHead>Credits</TableHead>
                        <TableHead>Grade</TableHead>
                        <TableHead>Status</TableHead>
                      </TableRow>
                    </TableHeader>
                    <TableBody>
                      {completedEnrollments.map((enrollment) => (
                        <TableRow key={enrollment.id}>
                          <TableCell>
                            <div className="font-medium">{enrollment.course?.name}</div>
                          </TableCell>
                          <TableCell>{enrollment.course?.code}</TableCell>
                          <TableCell>
                            <Badge variant="outline">
                              {enrollment.course?.credits} Credits
                            </Badge>
                          </TableCell>
                          <TableCell>
                            {enrollment.grade ? (
                              <Badge variant="default">{enrollment.grade}</Badge>
                            ) : (
                              <span className="text-muted-foreground">Not graded</span>
                            )}
                          </TableCell>
                          <TableCell>
                            <Badge variant="secondary">{enrollment.status}</Badge>
                          </TableCell>
                        </TableRow>
                      ))}
                    </TableBody>
                  </Table>
                </CardContent>
              </Card>
            )}
          </div>

          {/* Quick Stats */}
          <div className="space-y-4">
            <Card>
              <CardContent className="p-6">
                <div className="text-center space-y-2">
                  <BookOpen className="w-8 h-8 mx-auto text-primary" />
                  <div className="text-3xl font-bold">{activeEnrollments.length}</div>
                  <p className="text-sm text-muted-foreground">Current Courses</p>
                </div>
              </CardContent>
            </Card>
            
            <Card>
              <CardContent className="p-6">
                <div className="text-center space-y-2">
                  <GraduationCap className="w-8 h-8 mx-auto text-success" />
                  <div className="text-3xl font-bold">{totalCredits}</div>
                  <p className="text-sm text-muted-foreground">Total Credits</p>
                </div>
              </CardContent>
            </Card>

            <Card>
              <CardContent className="p-6">
                <div className="text-center space-y-2">
                  <Calendar className="w-8 h-8 mx-auto text-info" />
                  <div className="text-3xl font-bold">{completedEnrollments.length}</div>
                  <p className="text-sm text-muted-foreground">Completed Courses</p>
                </div>
              </CardContent>
            </Card>
          </div>
        </div>
      </div>
    </DashboardLayout>
  );
};

export default StudentDetail;

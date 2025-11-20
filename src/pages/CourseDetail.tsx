import { useState } from "react";
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
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from "@/components/ui/dialog";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import { 
  BookOpen, 
  Users, 
  Calendar,
  Clock,
  MapPin,
  UserPlus,
  UserMinus,
  Loader2,
  ArrowLeft
} from "lucide-react";
import { 
  useCourse, 
  useCourseEnrollments, 
  useUsers, 
  useEnrollStudent, 
  useUnenrollStudent 
} from "@/hooks/useApi";
import { useNavigate } from "react-router-dom";

const CourseDetail = () => {
  const { id } = useParams<{ id: string }>();
  const navigate = useNavigate();
  const courseId = parseInt(id || '0');
  
  const { data: course, isLoading: courseLoading } = useCourse(courseId);
  const { data: enrollments, isLoading: enrollmentsLoading } = useCourseEnrollments(courseId);
  const { data: usersResponse } = useUsers();
  const enrollStudentMutation = useEnrollStudent();
  const unenrollStudentMutation = useUnenrollStudent();

  const [isEnrollDialogOpen, setIsEnrollDialogOpen] = useState(false);
  const [selectedStudentId, setSelectedStudentId] = useState<string>("");

  const students = usersResponse?.data?.filter(user => user.role === 'student') || [];
  const enrolledStudentIds = enrollments?.map(enrollment => enrollment.student_id) || [];
  const availableStudents = students.filter(student => !enrolledStudentIds.includes(student.id));

  const handleEnrollStudent = () => {
    if (!selectedStudentId) return;
    
    enrollStudentMutation.mutate(
      { courseId, studentId: parseInt(selectedStudentId) },
      {
        onSuccess: () => {
          setIsEnrollDialogOpen(false);
          setSelectedStudentId("");
        }
      }
    );
  };

  const handleUnenrollStudent = (enrollmentId: number) => {
    if (window.confirm("Are you sure you want to unenroll this student?")) {
      unenrollStudentMutation.mutate(enrollmentId);
    }
  };

  if (courseLoading) {
    return (
      <DashboardLayout>
        <div className="flex items-center justify-center h-64">
          <Loader2 className="h-8 w-8 animate-spin" />
          <span className="ml-2">Loading course details...</span>
        </div>
      </DashboardLayout>
    );
  }

  if (!course) {
    return (
      <DashboardLayout>
        <div className="text-center py-12">
          <h2 className="text-2xl font-bold mb-2">Course Not Found</h2>
          <p className="text-muted-foreground">The requested course could not be found.</p>
          <Button onClick={() => navigate(-1)} className="mt-4">
            <ArrowLeft className="w-4 h-4 mr-2" />
            Go Back
          </Button>
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
            <Button variant="outline" size="sm" onClick={() => navigate(-1)}>
              <ArrowLeft className="w-4 h-4 mr-2" />
              Back
            </Button>
            <div>
              <h1 className="text-3xl font-bold">{course.name}</h1>
              <p className="text-muted-foreground">{course.code}</p>
            </div>
          </div>
          <Badge variant="outline" className="text-lg px-4 py-2">
            {course.credits} Credits
          </Badge>
        </div>

        <div className="grid gap-6 md:grid-cols-3">
          {/* Course Information */}
          <div className="md:col-span-2 space-y-6">
            <Card>
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <BookOpen className="w-5 h-5" />
                  Course Information
                </CardTitle>
              </CardHeader>
              <CardContent className="space-y-4">
                <div>
                  <h4 className="font-semibold mb-2">Description</h4>
                  <p className="text-muted-foreground">{course.description}</p>
                </div>
                
                <div className="grid gap-4 md:grid-cols-2">
                  <div>
                    <h4 className="font-semibold mb-2">Department</h4>
                    <p className="text-muted-foreground">
                      {course.department?.name || 'N/A'}
                    </p>
                  </div>
                  <div>
                    <h4 className="font-semibold mb-2">Teacher</h4>
                    <p className="text-muted-foreground">
                      {course.teacher?.user?.name || 'Not assigned'}
                    </p>
                  </div>
                  <div>
                    <h4 className="font-semibold mb-2 flex items-center gap-2">
                      <Calendar className="w-4 h-4" />
                      Semester
                    </h4>
                    <p className="text-muted-foreground">{course.semester}</p>
                  </div>
                  <div>
                    <h4 className="font-semibold mb-2">Section</h4>
                    <p className="text-muted-foreground">{course.section}</p>
                  </div>
                  <div>
                    <h4 className="font-semibold mb-2 flex items-center gap-2">
                      <MapPin className="w-4 h-4" />
                      Room
                    </h4>
                    <p className="text-muted-foreground">{course.room || 'TBA'}</p>
                  </div>
                  <div>
                    <h4 className="font-semibold mb-2 flex items-center gap-2">
                      <Users className="w-4 h-4" />
                      Capacity
                    </h4>
                    <p className="text-muted-foreground">
                      {enrollments?.length || 0} / {course.max_students} students
                    </p>
                  </div>
                </div>

                {course.schedule && course.schedule.length > 0 && (
                  <div>
                    <h4 className="font-semibold mb-2 flex items-center gap-2">
                      <Clock className="w-4 h-4" />
                      Schedule
                    </h4>
                    <div className="flex flex-wrap gap-2">
                      {course.schedule.map((schedule, index) => (
                        <Badge key={index} variant="outline">
                          {schedule.day} {schedule.time}
                        </Badge>
                      ))}
                    </div>
                  </div>
                )}
              </CardContent>
            </Card>
          </div>

          {/* Quick Stats */}
          <div className="space-y-4">
            <Card>
              <CardContent className="p-6">
                <div className="text-center space-y-2">
                  <Users className="w-8 h-8 mx-auto text-primary" />
                  <div className="text-3xl font-bold">{enrollments?.length || 0}</div>
                  <p className="text-sm text-muted-foreground">Enrolled Students</p>
                </div>
              </CardContent>
            </Card>
            
            <Card>
              <CardContent className="p-6">
                <div className="text-center space-y-2">
                  <BookOpen className="w-8 h-8 mx-auto text-success" />
                  <div className="text-3xl font-bold">{course.credits}</div>
                  <p className="text-sm text-muted-foreground">Credit Hours</p>
                </div>
              </CardContent>
            </Card>

            <Card>
              <CardContent className="p-6">
                <div className="text-center space-y-2">
                  <Calendar className="w-8 h-8 mx-auto text-info" />
                  <div className="text-3xl font-bold">{course.semester}</div>
                  <p className="text-sm text-muted-foreground">Semester</p>
                </div>
              </CardContent>
            </Card>
          </div>
        </div>

        {/* Enrolled Students */}
        <Card>
          <CardHeader>
            <div className="flex items-center justify-between">
              <div>
                <CardTitle className="flex items-center gap-2">
                  <Users className="w-5 h-5" />
                  Enrolled Students ({enrollments?.length || 0})
                </CardTitle>
                <CardDescription>
                  Students currently enrolled in this course
                </CardDescription>
              </div>
              <Dialog open={isEnrollDialogOpen} onOpenChange={setIsEnrollDialogOpen}>
                <DialogTrigger asChild>
                  <Button>
                    <UserPlus className="w-4 h-4 mr-2" />
                    Enroll Student
                  </Button>
                </DialogTrigger>
                <DialogContent>
                  <DialogHeader>
                    <DialogTitle>Enroll Student</DialogTitle>
                    <DialogDescription>
                      Select a student to enroll in {course.name}
                    </DialogDescription>
                  </DialogHeader>
                  <div className="space-y-4">
                    <Select value={selectedStudentId} onValueChange={setSelectedStudentId}>
                      <SelectTrigger>
                        <SelectValue placeholder="Select a student" />
                      </SelectTrigger>
                      <SelectContent>
                        {availableStudents.map((student) => (
                          <SelectItem key={student.id} value={student.id.toString()}>
                            {student.name} ({student.email})
                          </SelectItem>
                        ))}
                      </SelectContent>
                    </Select>
                    {availableStudents.length === 0 && (
                      <p className="text-sm text-muted-foreground">
                        No available students to enroll.
                      </p>
                    )}
                  </div>
                  <DialogFooter>
                    <Button
                      onClick={handleEnrollStudent}
                      disabled={!selectedStudentId || enrollStudentMutation.isPending}
                    >
                      {enrollStudentMutation.isPending ? (
                        <>
                          <Loader2 className="w-4 h-4 mr-2 animate-spin" />
                          Enrolling...
                        </>
                      ) : (
                        "Enroll Student"
                      )}
                    </Button>
                  </DialogFooter>
                </DialogContent>
              </Dialog>
            </div>
          </CardHeader>
          <CardContent>
            {enrollmentsLoading ? (
              <div className="flex items-center justify-center py-8">
                <Loader2 className="h-6 w-6 animate-spin" />
                <span className="ml-2">Loading enrollments...</span>
              </div>
            ) : enrollments && enrollments.length > 0 ? (
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead>Student Name</TableHead>
                    <TableHead>Student ID</TableHead>
                    <TableHead>Email</TableHead>
                    <TableHead>Enrollment Date</TableHead>
                    <TableHead>Status</TableHead>
                    <TableHead className="text-right">Actions</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {enrollments.map((enrollment) => (
                    <TableRow key={enrollment.id}>
                      <TableCell className="font-medium">
                        {enrollment.student?.user?.name || 'N/A'}
                      </TableCell>
                      <TableCell>{enrollment.student?.student_id || 'N/A'}</TableCell>
                      <TableCell>{enrollment.student?.user?.email || 'N/A'}</TableCell>
                      <TableCell>
                        {new Date(enrollment.enrollment_date).toLocaleDateString()}
                      </TableCell>
                      <TableCell>
                        <Badge 
                          variant={enrollment.status === 'enrolled' ? 'default' : 'secondary'}
                        >
                          {enrollment.status}
                        </Badge>
                      </TableCell>
                      <TableCell className="text-right">
                        <Button
                          variant="outline"
                          size="sm"
                          onClick={() => handleUnenrollStudent(enrollment.id)}
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
                <Users className="w-12 h-12 mx-auto text-muted-foreground mb-4" />
                <h3 className="text-lg font-semibold mb-2">No Students Enrolled</h3>
                <p className="text-muted-foreground">
                  This course doesn't have any enrolled students yet.
                </p>
              </div>
            )}
          </CardContent>
        </Card>
      </div>
    </DashboardLayout>
  );
};

export default CourseDetail;

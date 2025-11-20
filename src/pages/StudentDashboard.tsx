import { DashboardLayout } from "@/components/DashboardLayout";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { Progress } from "@/components/ui/progress";
import { BookOpen, Calendar, TrendingUp, Bell, Loader2 } from "lucide-react";
import { useStudentCourses, useCurrentUser } from "@/hooks/useApi";
import { useNavigate } from "react-router-dom";

const StudentDashboard = () => {
  const navigate = useNavigate();
  const { data: currentUser } = useCurrentUser();
  const { data: enrollments, isLoading: enrollmentsLoading } = useStudentCourses();

  const activeEnrollments = enrollments?.filter(e => e.status === 'enrolled') || [];
  const totalCredits = activeEnrollments.reduce((total, enrollment) => {
    return total + (enrollment.course?.credits || 0);
  }, 0);

  if (enrollmentsLoading) {
    return (
      <DashboardLayout>
        <div className="flex items-center justify-center h-64">
          <Loader2 className="h-8 w-8 animate-spin" />
          <span className="ml-2">Loading dashboard...</span>
        </div>
      </DashboardLayout>
    );
  }
  return (
    <DashboardLayout>
      <div className="space-y-6">
        {/* Welcome Banner */}
        <Card className="bg-gradient-to-r from-primary to-primary/80 text-primary-foreground shadow-lg">
          <CardContent className="p-6">
            <div className="flex items-center gap-4">
              <div className="w-16 h-16 rounded-full bg-white/20 flex items-center justify-center text-2xl font-bold">
                {currentUser?.data?.name?.charAt(0) || 'S'}
              </div>
              <div>
                <h2 className="text-2xl font-bold">Welcome back, {currentUser?.data?.name || 'Student'}!</h2>
                <p className="text-primary-foreground/80">Student ID: STU{currentUser?.data?.id.toString().padStart(3, '0')} • Computer Science</p>
              </div>
            </div>
          </CardContent>
        </Card>

        {/* Quick Stats */}
        <div className="grid gap-4 md:grid-cols-3">
          <Card>
            <CardHeader className="pb-2">
              <CardTitle className="text-sm font-medium flex items-center gap-2">
                <TrendingUp className="w-4 h-4 text-success" />
                Current GPA
              </CardTitle>
            </CardHeader>
            <CardContent>
              <div className="text-3xl font-bold">3.85</div>
              <p className="text-xs text-muted-foreground mt-1">Excellent performance</p>
            </CardContent>
          </Card>

          <Card>
            <CardHeader className="pb-2">
              <CardTitle className="text-sm font-medium flex items-center gap-2">
                <BookOpen className="w-4 h-4 text-primary" />
                Registered Courses
              </CardTitle>
            </CardHeader>
            <CardContent>
              <div className="text-3xl font-bold">{activeEnrollments.length}</div>
              <p className="text-xs text-muted-foreground mt-1">{totalCredits} credit hours</p>
            </CardContent>
          </Card>

          <Card>
            <CardHeader className="pb-2">
              <CardTitle className="text-sm font-medium flex items-center gap-2">
                <Calendar className="w-4 h-4 text-info" />
                Attendance
              </CardTitle>
            </CardHeader>
            <CardContent>
              <div className="text-3xl font-bold">92%</div>
              <p className="text-xs text-muted-foreground mt-1">Great attendance</p>
            </CardContent>
          </Card>
        </div>

        <div className="grid gap-4 md:grid-cols-2">
          {/* Today's Classes */}
          <Card>
            <CardHeader>
              <CardTitle className="flex items-center gap-2">
                <Calendar className="w-5 h-5 text-primary" />
                Today's Timetable
              </CardTitle>
              <CardDescription>Tuesday, January 16, 2024</CardDescription>
            </CardHeader>
            <CardContent className="space-y-3">
              {[
                { time: "9:00 AM", course: "Data Structures", room: "Room 301", status: "upcoming" },
                { time: "11:00 AM", course: "Database Systems", room: "Lab 101", status: "upcoming" },
                { time: "2:00 PM", course: "Web Development", room: "Room 205", status: "upcoming" },
              ].map((class_, idx) => (
                <div key={idx} className="flex items-center justify-between p-3 bg-muted rounded-lg">
                  <div>
                    <div className="font-medium">{class_.course}</div>
                    <div className="text-sm text-muted-foreground">{class_.time} • {class_.room}</div>
                  </div>
                  <Badge>{class_.status}</Badge>
                </div>
              ))}
            </CardContent>
          </Card>

          {/* Announcements */}
          <Card>
            <CardHeader>
              <CardTitle className="flex items-center gap-2">
                <Bell className="w-5 h-5 text-primary" />
                Recent Announcements
              </CardTitle>
              <CardDescription>Important updates</CardDescription>
            </CardHeader>
            <CardContent className="space-y-3">
              {[
                { title: "Semester Registration", date: "2 days ago", type: "important" },
                { title: "Library Hours Extended", date: "3 days ago", type: "info" },
                { title: "Career Fair Next Week", date: "5 days ago", type: "event" },
              ].map((announcement, idx) => (
                <div key={idx} className="p-3 border border-border rounded-lg">
                  <div className="flex items-start justify-between">
                    <div className="font-medium">{announcement.title}</div>
                    <Badge variant="outline" className="text-xs">{announcement.type}</Badge>
                  </div>
                  <div className="text-xs text-muted-foreground mt-1">{announcement.date}</div>
                </div>
              ))}
            </CardContent>
          </Card>
        </div>

        {/* Current Courses */}
        <Card>
          <CardHeader>
            <CardTitle>Current Courses</CardTitle>
            <CardDescription>Your enrolled courses this semester</CardDescription>
          </CardHeader>
          <CardContent>
            <div className="grid gap-4 md:grid-cols-2">
              {activeEnrollments.length > 0 ? (
                activeEnrollments.map((enrollment) => (
                  <div 
                    key={enrollment.id} 
                    className="p-4 border border-border rounded-lg space-y-2 cursor-pointer hover:bg-muted/50 transition-colors"
                    onClick={() => navigate(`/courses/${enrollment.course?.id}`)}
                  >
                    <div className="flex items-start justify-between">
                      <div>
                        <div className="font-semibold">{enrollment.course?.name}</div>
                        <div className="text-sm text-muted-foreground">{enrollment.course?.code}</div>
                      </div>
                      <Badge variant="outline">{enrollment.course?.teacher?.user?.name || 'No teacher'}</Badge>
                    </div>
                    <div className="space-y-1">
                      <div className="flex justify-between text-xs">
                        <span className="text-muted-foreground">Credits</span>
                        <span className="font-medium">{enrollment.course?.credits} credits</span>
                      </div>
                      <div className="flex justify-between text-xs">
                        <span className="text-muted-foreground">Status</span>
                        <Badge variant="default" className="text-xs">{enrollment.status}</Badge>
                      </div>
                    </div>
                  </div>
                ))
              ) : (
                <div className="col-span-2 text-center py-8">
                  <BookOpen className="w-12 h-12 mx-auto text-muted-foreground mb-4" />
                  <h3 className="text-lg font-semibold mb-2">No Enrolled Courses</h3>
                  <p className="text-muted-foreground">
                    You are not currently enrolled in any courses.
                  </p>
                </div>
              )}
            </div>
          </CardContent>
        </Card>
      </div>
    </DashboardLayout>
  );
};

export default StudentDashboard;

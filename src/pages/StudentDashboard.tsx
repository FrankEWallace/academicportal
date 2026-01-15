import { DashboardLayout } from "@/components/DashboardLayout";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { Progress } from "@/components/ui/progress";
import { Button } from "@/components/ui/button";
import { BookOpen, Calendar, TrendingUp, Bell, Loader2, Clock, FileText, Award, CalendarDays, ListChecks } from "lucide-react";
import { useStudentCourses, useCurrentUser, useStudentGPA } from "@/hooks/useApi";
import { useAuth } from "@/contexts/AuthContext";
import { useNavigate } from "react-router-dom";
import { AttendanceCard } from "@/components/AttendanceCard";
import { useStudentAttendance } from "@/hooks/use-student-attendance";

const StudentDashboard = () => {
  const navigate = useNavigate();
  const { user } = useAuth();
  const { data: currentUser } = useCurrentUser();
  const { data: enrollments, isLoading: enrollmentsLoading } = useStudentCourses();
  const { data: gpaData, isLoading: gpaLoading } = useStudentGPA();
  const { data: attendanceData } = useStudentAttendance(currentUser?.data?.user?.id);

  const activeEnrollments = enrollments?.filter(e => e.status === 'enrolled') || [];
  const totalCredits = activeEnrollments.reduce((total, enrollment) => {
    return total + (enrollment.course?.credits || 0);
  }, 0);

  if (enrollmentsLoading || gpaLoading) {
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
                {user?.name?.charAt(0) || 'S'}
              </div>
              <div>
                <h2 className="text-2xl font-bold">Welcome back, {user?.name || 'Student'}!</h2>
                <p className="text-primary-foreground/80">Student ID: STU{user?.id?.toString().padStart(3, '0')} • Computer Science</p>
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
              <div className="text-3xl font-bold">
                {gpaData?.gpa?.current_gpa || '0.00'}
              </div>
              <p className="text-xs text-muted-foreground mt-1">
                {gpaData?.gpa?.courses_completed} courses completed • {gpaData?.gpa?.total_credits} credits
              </p>
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
              <div className="text-3xl font-bold">
                {attendanceData?.overall_statistics?.attendance_percentage?.toFixed(1) || '0.0'}%
              </div>
              <p className="text-xs text-muted-foreground mt-1">
                {(attendanceData?.overall_statistics?.attendance_percentage || 0) >= 90 ? 'Excellent attendance' :
                 (attendanceData?.overall_statistics?.attendance_percentage || 0) >= 75 ? 'Good attendance' : 
                 'Needs improvement'}
              </p>
            </CardContent>
          </Card>
        </div>

        {/* Quick Access Cards - New Academic Features */}
        <div className="grid gap-4 md:grid-cols-4">
          <Card 
            className="cursor-pointer hover:shadow-md transition-all hover:border-primary"
            onClick={() => navigate('/student/timetable')}
          >
            <CardContent className="p-4">
              <div className="flex flex-col items-center text-center gap-2">
                <div className="w-12 h-12 bg-primary/10 rounded-lg flex items-center justify-center">
                  <Clock className="w-6 h-6 text-primary" />
                </div>
                <h3 className="font-semibold">Timetable</h3>
                <p className="text-xs text-muted-foreground">View your schedule</p>
              </div>
            </CardContent>
          </Card>

          <Card 
            className="cursor-pointer hover:shadow-md transition-all hover:border-primary"
            onClick={() => navigate('/student/calendar')}
          >
            <CardContent className="p-4">
              <div className="flex flex-col items-center text-center gap-2">
                <div className="w-12 h-12 bg-blue-500/10 rounded-lg flex items-center justify-center">
                  <CalendarDays className="w-6 h-6 text-blue-500" />
                </div>
                <h3 className="font-semibold">Calendar</h3>
                <p className="text-xs text-muted-foreground">Academic events</p>
              </div>
            </CardContent>
          </Card>

          <Card 
            className="cursor-pointer hover:shadow-md transition-all hover:border-primary"
            onClick={() => navigate('/student/progress')}
          >
            <CardContent className="p-4">
              <div className="flex flex-col items-center text-center gap-2">
                <div className="w-12 h-12 bg-green-500/10 rounded-lg flex items-center justify-center">
                  <TrendingUp className="w-6 h-6 text-green-500" />
                </div>
                <h3 className="font-semibold">Progress</h3>
                <p className="text-xs text-muted-foreground">Degree tracking</p>
              </div>
            </CardContent>
          </Card>

          <Card 
            className="cursor-pointer hover:shadow-md transition-all hover:border-primary"
            onClick={() => navigate('/student/waitlist')}
          >
            <CardContent className="p-4">
              <div className="flex flex-col items-center text-center gap-2">
                <div className="w-12 h-12 bg-orange-500/10 rounded-lg flex items-center justify-center">
                  <ListChecks className="w-6 h-6 text-orange-500" />
                </div>
                <h3 className="font-semibold">Waitlist</h3>
                <p className="text-xs text-muted-foreground">Course queues</p>
              </div>
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

        {/* GPA and Grade Summary */}
        {gpaData && (
          <div className="grid gap-4 md:grid-cols-2">
            {/* Assignment Performance */}
            <Card>
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <Award className="w-5 h-5 text-primary" />
                  Assignment Performance
                </CardTitle>
                <CardDescription>Your assignment grades and statistics</CardDescription>
              </CardHeader>
              <CardContent className="space-y-4">
                <div className="grid grid-cols-2 gap-4 text-sm">
                  <div>
                    <span className="text-muted-foreground">Total Assignments</span>
                    <div className="text-lg font-bold">{gpaData.assignment_performance.total_assignments}</div>
                  </div>
                  <div>
                    <span className="text-muted-foreground">Average Score</span>
                    <div className="text-lg font-bold">{gpaData.assignment_performance.average_score}%</div>
                  </div>
                  <div>
                    <span className="text-muted-foreground">Highest Score</span>
                    <div className="text-lg font-bold">{gpaData.assignment_performance.highest_score}</div>
                  </div>
                  <div>
                    <span className="text-muted-foreground">Lowest Score</span>
                    <div className="text-lg font-bold">{gpaData.assignment_performance.lowest_score}</div>
                  </div>
                </div>
                {gpaData.assignment_performance.average_percentage > 0 && (
                  <div>
                    <div className="flex justify-between text-sm mb-2">
                      <span>Overall Performance</span>
                      <span>{gpaData.assignment_performance.average_percentage}%</span>
                    </div>
                    <Progress value={gpaData.assignment_performance.average_percentage} className="h-2" />
                  </div>
                )}
              </CardContent>
            </Card>

            {/* Grade Distribution */}
            <Card>
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <FileText className="w-5 h-5 text-primary" />
                  Grade Distribution
                </CardTitle>
                <CardDescription>Distribution of your grades</CardDescription>
              </CardHeader>
              <CardContent>
                <div className="space-y-3">
                  {Object.entries(gpaData.grade_distribution)
                    .filter(([_, count]) => count > 0)
                    .sort(([a], [b]) => {
                      // Sort grades in order: A+, A, A-, B+, B, etc.
                      const gradeOrder = ['A+', 'A', 'A-', 'B+', 'B', 'B-', 'C+', 'C', 'C-', 'D', 'F'];
                      return gradeOrder.indexOf(a) - gradeOrder.indexOf(b);
                    })
                    .map(([grade, count]) => (
                      <div key={grade} className="flex items-center justify-between">
                        <div className="flex items-center gap-2">
                          <Badge 
                            variant={grade.startsWith('A') ? 'default' : 
                                   grade.startsWith('B') ? 'secondary' : 
                                   grade.startsWith('C') ? 'outline' : 'destructive'}
                          >
                            {grade}
                          </Badge>
                          <span className="text-sm text-muted-foreground">
                            {count} grade{count !== 1 ? 's' : ''}
                          </span>
                        </div>
                        <span className="font-medium">{count}</span>
                      </div>
                    ))}
                  {Object.values(gpaData.grade_distribution).every(count => count === 0) && (
                    <div className="text-center py-4 text-muted-foreground">
                      <FileText className="w-8 h-8 mx-auto mb-2 opacity-50" />
                      <p>No grades yet</p>
                    </div>
                  )}
                </div>
              </CardContent>
            </Card>

            {/* Performance Trend */}
            {gpaData.recent_trend && (
              <Card className="md:col-span-2">
                <CardHeader>
                  <CardTitle className="flex items-center gap-2">
                    <TrendingUp className="w-5 h-5 text-primary" />
                    Recent Performance Trend
                  </CardTitle>
                  <CardDescription>Your academic performance over the past 6 months</CardDescription>
                </CardHeader>
                <CardContent>
                  <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div className="text-center p-4 bg-muted rounded-lg">
                      <div className="text-2xl font-bold mb-1">
                        {gpaData.recent_trend.recent_course_average.toFixed(2)}
                      </div>
                      <div className="text-sm text-muted-foreground">Course Average</div>
                    </div>
                    <div className="text-center p-4 bg-muted rounded-lg">
                      <div className="text-2xl font-bold mb-1">
                        {gpaData.recent_trend.recent_assignment_average.toFixed(2)}
                      </div>
                      <div className="text-sm text-muted-foreground">Assignment Average</div>
                    </div>
                    <div className="text-center p-4 bg-muted rounded-lg">
                      <div className="flex items-center justify-center gap-2 text-2xl font-bold mb-1">
                        {gpaData.recent_trend.trend_direction === 'improving' && (
                          <>
                            <TrendingUp className="w-6 h-6 text-green-500" />
                            <span className="text-green-500">↗</span>
                          </>
                        )}
                        {gpaData.recent_trend.trend_direction === 'declining' && (
                          <>
                            <TrendingUp className="w-6 h-6 text-red-500 rotate-180" />
                            <span className="text-red-500">↘</span>
                          </>
                        )}
                        {gpaData.recent_trend.trend_direction === 'stable' && (
                          <>
                            <span className="text-blue-500">→</span>
                          </>
                        )}
                      </div>
                      <div className="text-sm text-muted-foreground capitalize">
                        {gpaData.recent_trend.trend_direction}
                      </div>
                    </div>
                  </div>
                </CardContent>
              </Card>
            )}
          </div>
        )}

        {/* Attendance Section */}
        {currentUser?.data?.user?.id && (
          <AttendanceCard studentId={currentUser.data.user.id} />
        )}
      </div>
    </DashboardLayout>
  );
};

export default StudentDashboard;

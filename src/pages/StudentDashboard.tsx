import { DashboardLayout } from "@/components/DashboardLayout";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { Progress } from "@/components/ui/progress";
import { BookOpen, Calendar, TrendingUp, Bell } from "lucide-react";

const StudentDashboard = () => {
  return (
    <DashboardLayout>
      <div className="space-y-6">
        {/* Welcome Banner */}
        <Card className="bg-gradient-to-r from-primary to-primary/80 text-primary-foreground shadow-lg">
          <CardContent className="p-6">
            <div className="flex items-center gap-4">
              <div className="w-16 h-16 rounded-full bg-white/20 flex items-center justify-center text-2xl font-bold">
                JS
              </div>
              <div>
                <h2 className="text-2xl font-bold">Welcome back, John Smith!</h2>
                <p className="text-primary-foreground/80">Student ID: STU001 • Computer Science</p>
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
              <div className="text-3xl font-bold">6</div>
              <p className="text-xs text-muted-foreground mt-1">18 credit hours</p>
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
              {[
                { code: "CS301", name: "Data Structures", teacher: "Dr. Smith", progress: 65 },
                { code: "CS302", name: "Database Systems", teacher: "Prof. Johnson", progress: 58 },
                { code: "CS303", name: "Web Development", teacher: "Dr. Williams", progress: 72 },
                { code: "CS304", name: "Algorithms", teacher: "Prof. Davis", progress: 45 },
              ].map((course) => (
                <div key={course.code} className="p-4 border border-border rounded-lg space-y-2">
                  <div className="flex items-start justify-between">
                    <div>
                      <div className="font-semibold">{course.name}</div>
                      <div className="text-sm text-muted-foreground">{course.code}</div>
                    </div>
                    <Badge variant="outline">{course.teacher}</Badge>
                  </div>
                  <div className="space-y-1">
                    <div className="flex justify-between text-xs">
                      <span className="text-muted-foreground">Progress</span>
                      <span className="font-medium">{course.progress}%</span>
                    </div>
                    <Progress value={course.progress} />
                  </div>
                </div>
              ))}
            </div>
          </CardContent>
        </Card>
      </div>
    </DashboardLayout>
  );
};

export default StudentDashboard;

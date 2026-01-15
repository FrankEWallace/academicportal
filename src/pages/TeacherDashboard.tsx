import { DashboardLayout } from "@/components/DashboardLayout";
import { StatCard } from "@/components/StatCard";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { BookOpen, Users, FileText, Calendar, Upload, Edit, Clock, CalendarDays } from "lucide-react";
import { useNavigate } from "react-router-dom";

const TeacherDashboard = () => {
  const navigate = useNavigate();
  
  return (
    <DashboardLayout>
      <div className="space-y-6">
        {/* Stats Grid */}
        <div className="grid gap-4 md:grid-cols-3">
          <StatCard
            title="Courses Assigned"
            value="4"
            icon={BookOpen}
            description="Active this semester"
            colorClass="bg-primary"
          />
          <StatCard
            title="Total Students"
            value="156"
            icon={Users}
            description="Across all courses"
            colorClass="bg-success"
          />
          <StatCard
            title="Pending Assessments"
            value="23"
            icon={FileText}
            description="Awaiting grading"
            colorClass="bg-warning"
          />
        </div>

        {/* Quick Actions */}
        <Card>
          <CardHeader>
            <CardTitle>Quick Actions</CardTitle>
            <CardDescription>Common tasks and operations</CardDescription>
          </CardHeader>
          <CardContent>
            <div className="grid gap-3 md:grid-cols-5">
              <Button className="h-auto py-4 flex flex-col gap-2" variant="outline" onClick={() => navigate('/teacher/timetable')}>
                <Clock className="w-5 h-5" />
                <span>My Timetable</span>
              </Button>
              <Button className="h-auto py-4 flex flex-col gap-2" variant="outline" onClick={() => navigate('/teacher/calendar')}>
                <CalendarDays className="w-5 h-5" />
                <span>Calendar</span>
              </Button>
              <Button className="h-auto py-4 flex flex-col gap-2" variant="outline">
                <Upload className="w-5 h-5" />
                <span>Upload Grades</span>
              </Button>
              <Button className="h-auto py-4 flex flex-col gap-2" variant="outline">
                <Edit className="w-5 h-5" />
                <span>Mark Attendance</span>
              </Button>
              <Button className="h-auto py-4 flex flex-col gap-2" variant="outline">
                <FileText className="w-5 h-5" />
                <span>Course Materials</span>
              </Button>
            </div>
          </CardContent>
        </Card>

        <div className="grid gap-4 md:grid-cols-2">
          {/* Upcoming Classes */}
          <Card>
            <CardHeader>
              <CardTitle className="flex items-center gap-2">
                <Calendar className="w-5 h-5 text-primary" />
                Upcoming Lessons
              </CardTitle>
              <CardDescription>Your schedule for today</CardDescription>
            </CardHeader>
            <CardContent className="space-y-3">
              {[
                { time: "9:00 AM", course: "CS301 - Data Structures", room: "Room 301", students: 45 },
                { time: "11:00 AM", course: "CS302 - Database Systems", room: "Lab 101", students: 38 },
                { time: "2:00 PM", course: "CS303 - Web Development", room: "Room 205", students: 42 },
              ].map((lesson, idx) => (
                <div key={idx} className="p-3 border border-border rounded-lg">
                  <div className="flex items-center justify-between mb-2">
                    <div className="font-semibold">{lesson.course}</div>
                    <div className="text-sm text-muted-foreground">{lesson.time}</div>
                  </div>
                  <div className="text-sm text-muted-foreground">
                    {lesson.room} • {lesson.students} students
                  </div>
                </div>
              ))}
            </CardContent>
          </Card>

          {/* My Courses */}
          <Card>
            <CardHeader>
              <CardTitle className="flex items-center gap-2">
                <BookOpen className="w-5 h-5 text-primary" />
                My Courses
              </CardTitle>
              <CardDescription>Courses you're teaching</CardDescription>
            </CardHeader>
            <CardContent className="space-y-3">
              {[
                { code: "CS301", name: "Data Structures", students: 45, section: "A" },
                { code: "CS302", name: "Database Systems", students: 38, section: "B" },
                { code: "CS303", name: "Web Development", students: 42, section: "A" },
                { code: "CS304", name: "Algorithms", students: 31, section: "C" },
              ].map((course) => (
                <div key={course.code} className="p-3 border border-border rounded-lg">
                  <div className="flex items-center justify-between mb-1">
                    <div className="font-semibold">{course.code} - {course.name}</div>
                    <Button size="sm" variant="ghost">View</Button>
                  </div>
                  <div className="text-sm text-muted-foreground">
                    Section {course.section} • {course.students} students
                  </div>
                </div>
              ))}
            </CardContent>
          </Card>
        </div>
      </div>
    </DashboardLayout>
  );
};

export default TeacherDashboard;

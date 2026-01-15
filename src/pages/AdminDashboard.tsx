import { useState } from "react";
import { useNavigate } from "react-router-dom";
import { 
  Users, 
  BookOpen, 
  Calendar, 
  TrendingUp, 
  UserPlus, 
  BookPlus,
  GraduationCap,
  Building2,
  ClipboardCheck,
  Clock,
  CalendarDays
} from "lucide-react";
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
import { DashboardLayout } from "@/components/DashboardLayout";
import { useAuth } from "@/contexts/AuthContext";
import { useAdminDashboard } from "@/hooks/useApi";

const AdminDashboard = () => {
  const navigate = useNavigate();
  const { user } = useAuth();
  const { data: dashboardData, isLoading } = useAdminDashboard();

  const quickActions = [
    {
      title: "Add Student",
      description: "Register a new student",
      icon: UserPlus,
      action: () => navigate("/admin/students"),
      color: "bg-blue-500"
    },
    {
      title: "Create Course",
      description: "Set up a new course",
      icon: BookPlus,
      action: () => navigate("/admin/courses"),
      color: "bg-green-500"
    },
    {
      title: "Timetable",
      description: "Manage class schedules",
      icon: Clock,
      action: () => navigate("/admin/timetable"),
      color: "bg-purple-500"
    },
    {
      title: "Calendar",
      description: "Academic events & dates",
      icon: CalendarDays,
      action: () => navigate("/admin/calendar"),
      color: "bg-orange-500"
    },
    {
      title: "View Reports",
      description: "Generate analytics reports",
      icon: TrendingUp,
      action: () => navigate("/admin/reports"),
      color: "bg-pink-500"
    },
    {
      title: "Manage Departments",
      description: "Organize academic departments",
      icon: Building2,
      action: () => navigate("/admin/departments"),
      color: "bg-cyan-500"
    }
  ];

  const stats = [
    {
      title: "Total Students",
      value: dashboardData?.data?.total_students || 0,
      icon: Users,
      color: "text-blue-600",
      bgColor: "bg-blue-100"
    },
    {
      title: "Active Courses",
      value: dashboardData?.data?.active_courses || 0,
      icon: BookOpen,
      color: "text-green-600",
      bgColor: "bg-green-100"
    },
    {
      title: "Faculty Members",
      value: dashboardData?.data?.faculty_count || 0,
      icon: GraduationCap,
      color: "text-purple-600",
      bgColor: "bg-purple-100"
    },
    {
      title: "Departments",
      value: dashboardData?.data?.departments_count || 0,
      icon: Building2,
      color: "text-orange-600",
      bgColor: "bg-orange-100"
    }
  ];

  return (
    <DashboardLayout>
      <div className="flex-1 space-y-6 p-6">
        {/* Welcome Banner */}
        <div className="bg-gradient-to-r from-primary to-primary/80 rounded-lg p-6 text-primary-foreground">
          <div className="flex items-center gap-4">
            <div className="w-16 h-16 rounded-full bg-white/20 flex items-center justify-center text-2xl font-bold">
              {user?.name?.charAt(0) || 'A'}
            </div>
            <div>
              <h2 className="text-2xl font-bold">Welcome back, {user?.name || 'Admin'}!</h2>
              <p className="text-primary-foreground/80">Administrator • Academic Portal Management</p>
            </div>
          </div>
        </div>

        {/* Quick Actions */}
        <div>
          <h3 className="text-lg font-semibold mb-4">Quick Actions</h3>
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            {quickActions.map((action, index) => (
              <Card key={index} className="cursor-pointer hover:shadow-md transition-shadow" onClick={action.action}>
                <CardContent className="p-4">
                  <div className="flex items-center gap-3">
                    <div className={`w-10 h-10 ${action.color} rounded-lg flex items-center justify-center text-white`}>
                      <action.icon className="w-5 h-5" />
                    </div>
                    <div>
                      <h4 className="font-medium">{action.title}</h4>
                      <p className="text-sm text-muted-foreground">{action.description}</p>
                    </div>
                  </div>
                </CardContent>
              </Card>
            ))}
          </div>
        </div>

        {/* Stats Grid */}
        <div>
          <h3 className="text-lg font-semibold mb-4">Overview</h3>
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            {stats.map((stat, index) => (
              <Card key={index}>
                <CardContent className="p-6">
                  <div className="flex items-center justify-between">
                    <div>
                      <p className="text-sm font-medium text-muted-foreground">{stat.title}</p>
                      <p className="text-2xl font-bold">{isLoading ? '...' : stat.value}</p>
                    </div>
                    <div className={`w-12 h-12 ${stat.bgColor} rounded-lg flex items-center justify-center`}>
                      <stat.icon className={`w-6 h-6 ${stat.color}`} />
                    </div>
                  </div>
                </CardContent>
              </Card>
            ))}
          </div>
        </div>

        {/* Recent Enrollments */}
        <Card>
          <CardHeader>
            <div className="flex justify-between items-center">
              <div>
                <CardTitle>Recent Enrollments</CardTitle>
                <CardDescription>Latest student course enrollments</CardDescription>
              </div>
              <Button variant="outline" onClick={() => navigate("/admin/enrollments")}>
                View All
              </Button>
            </div>
          </CardHeader>
          <CardContent>
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>Student</TableHead>
                  <TableHead>Course</TableHead>
                  <TableHead>Date</TableHead>
                  <TableHead>Status</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                <TableRow>
                  <TableCell colSpan={4} className="text-center text-muted-foreground py-8">
                    Recent enrollments will appear here once enrollment data is available
                  </TableCell>
                </TableRow>
              </TableBody>
            </Table>
          </CardContent>
        </Card>
      </div>
    </DashboardLayout>
  );
};

export default AdminDashboard;

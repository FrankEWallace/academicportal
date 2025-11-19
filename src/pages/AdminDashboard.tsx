import { DashboardLayout } from "@/components/DashboardLayout";
import { StatCard } from "@/components/StatCard";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { 
  Users, 
  GraduationCap, 
  BookOpen, 
  CreditCard,
  TrendingUp,
  Clock,
  Loader2
} from "lucide-react";
import { useAdminDashboard, useCourses, useUsers } from "@/hooks/useApi";
import {
  BarChart,
  Bar,
  XAxis,
  YAxis,
  CartesianGrid,
  Tooltip,
  ResponsiveContainer,
  PieChart,
  Pie,
  Cell,
  Legend
} from "recharts";
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/components/ui/table";
import { Badge } from "@/components/ui/badge";

// Mock data for charts
const enrollmentData = [
  { month: "Jan", students: 400 },
  { month: "Feb", students: 450 },
  { month: "Mar", students: 520 },
  { month: "Apr", students: 580 },
  { month: "May", students: 620 },
  { month: "Jun", students: 680 },
];

const feeCollectionData = [
  { name: "Collected", value: 75, color: "hsl(var(--success))" },
  { name: "Pending", value: 20, color: "hsl(var(--warning))" },
  { name: "Overdue", value: 5, color: "hsl(var(--destructive))" },
];

const recentEnrollments = [
  { id: "STU001", name: "John Smith", program: "Computer Science", date: "2024-01-15", status: "Active" },
  { id: "STU002", name: "Sarah Johnson", program: "Business Admin", date: "2024-01-14", status: "Active" },
  { id: "STU003", name: "Michael Brown", program: "Engineering", date: "2024-01-14", status: "Pending" },
  { id: "STU004", name: "Emily Davis", program: "Medicine", date: "2024-01-13", status: "Active" },
];

const recentPayments = [
  { id: "PAY001", student: "John Smith", amount: "$2,500", date: "2024-01-15", status: "Completed" },
  { id: "PAY002", student: "Sarah Johnson", amount: "$2,500", date: "2024-01-14", status: "Completed" },
  { id: "PAY003", student: "Michael Brown", amount: "$2,500", date: "2024-01-13", status: "Pending" },
  { id: "PAY004", student: "Emily Davis", amount: "$2,500", date: "2024-01-12", status: "Completed" },
];

const AdminDashboard = () => {
  const { data: dashboardData, isLoading: dashboardLoading } = useAdminDashboard();
  const { data: courses, isLoading: coursesLoading } = useCourses();
  const { data: users, isLoading: usersLoading } = useUsers();

  if (dashboardLoading || coursesLoading || usersLoading) {
    return (
      <DashboardLayout>
        <div className="flex items-center justify-center h-64">
          <Loader2 className="h-8 w-8 animate-spin" />
          <span className="ml-2">Loading dashboard...</span>
        </div>
      </DashboardLayout>
    );
  }

  // Calculate stats from real data
  const totalStudents = users?.data?.filter(user => user.role === 'student').length || 0;
  const totalTeachers = users?.data?.filter(user => user.role === 'teacher').length || 0;
  const totalCourses = courses?.data?.length || 0;

  return (
    <DashboardLayout>
      <div className="space-y-6">
        {/* Stats Grid */}
        <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
          <StatCard
            title="Total Students"
            value={totalStudents.toString()}
            icon={Users}
            description="Active enrollments"
            trend={{ value: "Live data", positive: true }}
            colorClass="bg-primary"
          />
          <StatCard
            title="Total Teachers"
            value={totalTeachers.toString()}
            icon={GraduationCap}
            description="Faculty members"
            trend={{ value: "Live data", positive: true }}
            colorClass="bg-success"
          />
          <StatCard
            title="Total Courses"
            value={totalCourses.toString()}
            icon={BookOpen}
            description="Active courses"
            trend={{ value: "Live data", positive: true }}
            colorClass="bg-info"
          />
          <StatCard
            title="Dashboard Stats"
            value={dashboardData?.stats?.total_users ? dashboardData.stats.total_users.toString() : "N/A"}
            icon={CreditCard}
            description="Total system users"
            trend={{ value: "Live data", positive: true }}
            colorClass="bg-warning"
          />
        </div>

        {/* Charts Row */}
        <div className="grid gap-4 md:grid-cols-2">
          {/* Enrollment Overview */}
          <Card className="shadow-card">
            <CardHeader>
              <CardTitle className="flex items-center gap-2">
                <TrendingUp className="w-5 h-5 text-primary" />
                Enrollment Overview
              </CardTitle>
              <CardDescription>Student enrollment over the last 6 months</CardDescription>
            </CardHeader>
            <CardContent>
              <ResponsiveContainer width="100%" height={300}>
                <BarChart data={enrollmentData}>
                  <CartesianGrid strokeDasharray="3 3" stroke="hsl(var(--border))" />
                  <XAxis dataKey="month" stroke="hsl(var(--muted-foreground))" />
                  <YAxis stroke="hsl(var(--muted-foreground))" />
                  <Tooltip 
                    contentStyle={{ 
                      backgroundColor: "hsl(var(--card))",
                      border: "1px solid hsl(var(--border))",
                      borderRadius: "8px"
                    }}
                  />
                  <Bar dataKey="students" fill="hsl(var(--primary))" radius={[8, 8, 0, 0]} />
                </BarChart>
              </ResponsiveContainer>
            </CardContent>
          </Card>

          {/* Fee Collection Status */}
          <Card className="shadow-card">
            <CardHeader>
              <CardTitle className="flex items-center gap-2">
                <CreditCard className="w-5 h-5 text-primary" />
                Fee Collection Status
              </CardTitle>
              <CardDescription>Current semester payment breakdown</CardDescription>
            </CardHeader>
            <CardContent>
              <ResponsiveContainer width="100%" height={300}>
                <PieChart>
                  <Pie
                    data={feeCollectionData}
                    cx="50%"
                    cy="50%"
                    labelLine={false}
                    label={({ name, value }) => `${name}: ${value}%`}
                    outerRadius={100}
                    fill="#8884d8"
                    dataKey="value"
                  >
                    {feeCollectionData.map((entry, index) => (
                      <Cell key={`cell-${index}`} fill={entry.color} />
                    ))}
                  </Pie>
                  <Tooltip 
                    contentStyle={{ 
                      backgroundColor: "hsl(var(--card))",
                      border: "1px solid hsl(var(--border))",
                      borderRadius: "8px"
                    }}
                  />
                  <Legend />
                </PieChart>
              </ResponsiveContainer>
            </CardContent>
          </Card>
        </div>

        {/* Tables Row */}
        <div className="grid gap-4 md:grid-cols-2">
          {/* Recent Enrollments */}
          <Card className="shadow-card">
            <CardHeader>
              <CardTitle className="flex items-center gap-2">
                <Clock className="w-5 h-5 text-primary" />
                Recent Enrollments
              </CardTitle>
              <CardDescription>Latest student registrations</CardDescription>
            </CardHeader>
            <CardContent>
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead>Student</TableHead>
                    <TableHead>Program</TableHead>
                    <TableHead>Status</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {users?.data?.filter(user => user.role === 'student').slice(0, 4).map((student) => (
                    <TableRow key={student.id}>
                      <TableCell>
                        <div>
                          <div className="font-medium">{student.name}</div>
                          <div className="text-xs text-muted-foreground">{student.email}</div>
                        </div>
                      </TableCell>
                      <TableCell className="text-sm">Computer Science</TableCell>
                      <TableCell>
                        <Badge variant="default">
                          Active
                        </Badge>
                      </TableCell>
                    </TableRow>
                  ))}
                </TableBody>
              </Table>
            </CardContent>
          </Card>

          {/* Latest Payments */}
          <Card className="shadow-card">
            <CardHeader>
              <CardTitle className="flex items-center gap-2">
                <CreditCard className="w-5 h-5 text-primary" />
                Latest Payments
              </CardTitle>
              <CardDescription>Recent fee transactions</CardDescription>
            </CardHeader>
            <CardContent>
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead>Student</TableHead>
                    <TableHead>Amount</TableHead>
                    <TableHead>Status</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {recentPayments.map((payment) => (
                    <TableRow key={payment.id}>
                      <TableCell>
                        <div>
                          <div className="font-medium">{payment.student}</div>
                          <div className="text-xs text-muted-foreground">{payment.date}</div>
                        </div>
                      </TableCell>
                      <TableCell className="font-semibold">{payment.amount}</TableCell>
                      <TableCell>
                        <Badge variant={payment.status === "Completed" ? "default" : "secondary"}>
                          {payment.status}
                        </Badge>
                      </TableCell>
                    </TableRow>
                  ))}
                </TableBody>
              </Table>
            </CardContent>
          </Card>
        </div>
      </div>
    </DashboardLayout>
  );
};

export default AdminDashboard;

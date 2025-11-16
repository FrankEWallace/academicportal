import { DashboardLayout } from "@/components/DashboardLayout";
import { StatCard } from "@/components/StatCard";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { 
  Users, 
  GraduationCap, 
  BookOpen, 
  CreditCard,
  TrendingUp,
  Clock
} from "lucide-react";
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
  return (
    <DashboardLayout>
      <div className="space-y-6">
        {/* Stats Grid */}
        <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
          <StatCard
            title="Total Students"
            value="1,284"
            icon={Users}
            description="Active enrollments"
            trend={{ value: "12% from last month", positive: true }}
            colorClass="bg-primary"
          />
          <StatCard
            title="Total Teachers"
            value="156"
            icon={GraduationCap}
            description="Faculty members"
            trend={{ value: "3% from last month", positive: true }}
            colorClass="bg-success"
          />
          <StatCard
            title="Total Courses"
            value="89"
            icon={BookOpen}
            description="Active courses"
            trend={{ value: "5 new this semester", positive: true }}
            colorClass="bg-info"
          />
          <StatCard
            title="Fee Collection"
            value="$185K"
            icon={CreditCard}
            description="This semester"
            trend={{ value: "8% from last month", positive: true }}
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
                  {recentEnrollments.map((enrollment) => (
                    <TableRow key={enrollment.id}>
                      <TableCell>
                        <div>
                          <div className="font-medium">{enrollment.name}</div>
                          <div className="text-xs text-muted-foreground">{enrollment.id}</div>
                        </div>
                      </TableCell>
                      <TableCell className="text-sm">{enrollment.program}</TableCell>
                      <TableCell>
                        <Badge variant={enrollment.status === "Active" ? "default" : "secondary"}>
                          {enrollment.status}
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

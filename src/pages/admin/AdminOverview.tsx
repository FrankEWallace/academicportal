import { DashboardLayout } from '@/components/DashboardLayout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { 
  ClipboardList, 
  Shield, 
  UserCheck, 
  Award, 
  BedDouble, 
  MessageSquare,
  Users,
  GraduationCap,
  BookOpen,
  TrendingUp
} from 'lucide-react';
import { Link } from 'react-router-dom';
import { cn } from '@/lib/utils';

interface QuickActionCard {
  title: string;
  description: string;
  icon: React.ComponentType<{ className?: string }>;
  url: string;
  color: string;
}

const quickActions: QuickActionCard[] = [
  {
    title: 'Registrations',
    description: 'Review and manage student registration requests',
    icon: ClipboardList,
    url: '/admin/registrations',
    color: 'text-blue-600 bg-blue-50 border-blue-200',
  },
  {
    title: 'Insurance',
    description: 'Verify student insurance submissions',
    icon: Shield,
    url: '/admin/insurance',
    color: 'text-green-600 bg-green-50 border-green-200',
  },
  {
    title: 'Enrollments',
    description: 'Approve enrollment confirmations',
    icon: UserCheck,
    url: '/admin/enrollments',
    color: 'text-purple-600 bg-purple-50 border-purple-200',
  },
  {
    title: 'Results Moderation',
    description: 'Review and moderate exam results',
    icon: Award,
    url: '/admin/results',
    color: 'text-orange-600 bg-orange-50 border-orange-200',
  },
  {
    title: 'Accommodations',
    description: 'Manage student accommodation requests',
    icon: BedDouble,
    url: '/admin/accommodations',
    color: 'text-cyan-600 bg-cyan-50 border-cyan-200',
  },
  {
    title: 'Feedback',
    description: 'View and manage student feedback',
    icon: MessageSquare,
    url: '/admin/feedback',
    color: 'text-pink-600 bg-pink-50 border-pink-200',
  },
];

const stats = [
  {
    title: 'Total Students',
    value: '1,234',
    change: '+12%',
    icon: Users,
  },
  {
    title: 'Total Teachers',
    value: '89',
    change: '+3%',
    icon: GraduationCap,
  },
  {
    title: 'Active Courses',
    value: '156',
    change: '+8%',
    icon: BookOpen,
  },
  {
    title: 'Enrollment Rate',
    value: '94%',
    change: '+2%',
    icon: TrendingUp,
  },
];

export default function AdminOverview() {
  return (
    <DashboardLayout title="Administrator Dashboard">
      <div className="space-y-6">
        {/* Welcome Section */}
        <div>
          <h1 className="text-3xl font-bold tracking-tight">Welcome back, Administrator</h1>
          <p className="text-muted-foreground mt-2">
            Here's what's happening with your institution today.
          </p>
        </div>

        {/* Stats Grid */}
        <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
          {stats.map((stat) => (
            <Card key={stat.title}>
              <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                <CardTitle className="text-sm font-medium">
                  {stat.title}
                </CardTitle>
                <stat.icon className="h-4 w-4 text-muted-foreground" />
              </CardHeader>
              <CardContent>
                <div className="text-2xl font-bold">{stat.value}</div>
                <p className="text-xs text-muted-foreground">
                  <span className="text-green-600">{stat.change}</span> from last month
                </p>
              </CardContent>
            </Card>
          ))}
        </div>

        {/* Quick Actions */}
        <div>
          <h2 className="text-xl font-semibold mb-4">Administrative Tasks</h2>
          <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
            {quickActions.map((action) => (
              <Link key={action.title} to={action.url}>
                <Card className={cn(
                  "h-full transition-all hover:shadow-lg hover:-translate-y-1 cursor-pointer border-2",
                  action.color
                )}>
                  <CardHeader>
                    <div className="flex items-center gap-3">
                      <div className={cn("p-2 rounded-lg", action.color)}>
                        <action.icon className="h-6 w-6" />
                      </div>
                      <CardTitle className="text-lg">{action.title}</CardTitle>
                    </div>
                  </CardHeader>
                  <CardContent>
                    <CardDescription className="text-foreground/70">
                      {action.description}
                    </CardDescription>
                  </CardContent>
                </Card>
              </Link>
            ))}
          </div>
        </div>

        {/* Recent Activity */}
        <Card>
          <CardHeader>
            <CardTitle>Recent Activity</CardTitle>
            <CardDescription>Latest updates across the system</CardDescription>
          </CardHeader>
          <CardContent>
            <div className="space-y-4">
              <div className="flex items-start gap-4 pb-4 border-b">
                <div className="w-2 h-2 rounded-full bg-blue-600 mt-2" />
                <div className="flex-1">
                  <p className="font-medium">New registration request</p>
                  <p className="text-sm text-muted-foreground">John Doe submitted a registration request</p>
                  <p className="text-xs text-muted-foreground mt-1">2 minutes ago</p>
                </div>
              </div>
              <div className="flex items-start gap-4 pb-4 border-b">
                <div className="w-2 h-2 rounded-full bg-green-600 mt-2" />
                <div className="flex-1">
                  <p className="font-medium">Insurance verification completed</p>
                  <p className="text-sm text-muted-foreground">5 insurance submissions verified</p>
                  <p className="text-xs text-muted-foreground mt-1">1 hour ago</p>
                </div>
              </div>
              <div className="flex items-start gap-4">
                <div className="w-2 h-2 rounded-full bg-purple-600 mt-2" />
                <div className="flex-1">
                  <p className="font-medium">Enrollment approved</p>
                  <p className="text-sm text-muted-foreground">12 students enrolled for Spring 2024</p>
                  <p className="text-xs text-muted-foreground mt-1">3 hours ago</p>
                </div>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>
    </DashboardLayout>
  );
}
